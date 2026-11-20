<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Resolves the brand assets configured in the admin panel.
 *
 * The public header/footer previously used hard-coded images, which is why
 * uploading a new logo in Filament had no visible effect. Every public brand
 * image now goes through this helper.
 */
class SiteLogo
{
    public static function url(string $key = 'logo_path', string $fallback = 'images/house-roof-logo.svg'): string
    {
        $path = trim((string) Setting::get($key, ''));

        if ($path !== '' && static::exists($path)) {
            return upload_url($path);
        }

        return asset($fallback);
    }

    public static function main(): string
    {
        return static::url('logo_path', 'images/house-roof-logo.svg');
    }

    public static function white(): string
    {
        return static::url('logo_white_path', 'images/house-roof-logo-white.svg');
    }

    public static function favicon(): ?string
    {
        $path = trim((string) Setting::get('favicon_path', ''));

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return static::exists($path) ? upload_url($path) : null;
    }

    private static function exists(string $path): bool
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return true;
        }

        try {
            return upload_disk()->exists($path);
        } catch (\Throwable) {
            return false;
        }
    }
}
