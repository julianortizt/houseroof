<?php

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

if (! function_exists('uploads_configured')) {
    /**
     * Whether the public disk can safely resolve the S3 adapter. Until the
     * R2/AWS variables exist, public pages must not instantiate that adapter.
     */
    function uploads_configured(): bool
    {
        $disk = config('filesystems.disks.public', []);

        if (($disk['driver'] ?? 'local') !== 's3') {
            return false;
        }

        return filled($disk['key'] ?? null)
            && filled($disk['secret'] ?? null)
            && filled($disk['bucket'] ?? null);
    }
}

if (! function_exists('upload_disk')) {
    /** Disk used for public reads: S3 when configured, bundled local files otherwise. */
    function upload_disk(): Filesystem
    {
        $disk = config('filesystems.disks.public', []);

        if (($disk['driver'] ?? null) === 's3' && ! uploads_configured()) {
            config([
                'filesystems.disks.public.driver' => 'local',
                'filesystems.disks.public.root' => storage_path('app/public'),
                'filesystems.disks.public.url' => env('APP_URL') . '/storage',
                'filesystems.disks.public.visibility' => 'public',
            ]);
            Storage::purge('public');
        }

        return Storage::disk('public');
    }
}

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

        $url = upload_disk()->url(ltrim($path, '/'));

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

        $disk = upload_disk();

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
