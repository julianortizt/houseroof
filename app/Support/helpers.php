<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('upload_url')) {
    /**
     * Build the public URL for a file stored on the configured uploads disk.
     *
     * Locally this resolves to /storage/...; on Vercel, when PUBLIC_DISK_DRIVER=s3,
     * it resolves to the object store URL (AWS_URL). Existing absolute URLs are
     * returned untouched so old database values keep working.
     */
    function upload_url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        }

        $url = Storage::disk('public')->url(ltrim($path, '/'));

        return str_starts_with($url, 'http') ? $url : url($url);
    }
}

if (! function_exists('upload_local_path')) {
    /**
     * Return a filesystem path for a file on the uploads disk. Remote disks
     * are downloaded to a temporary file that is removed at request shutdown.
     */
    function upload_local_path(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        if (config('filesystems.disks.public.driver') === 'local') {
            return $disk->path($path);
        }

        $temp = tempnam(sys_get_temp_dir(), 'cr_upload_');
        file_put_contents($temp, $disk->get($path));
        register_shutdown_function(static function () use ($temp) {
            if (is_file($temp)) {
                @unlink($temp);
            }
        });

        return $temp;
    }
}
