<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Reads the House Roof editorial content.
 *
 * Content is always editable from the admin panel (ContentBlock rows). The
 * values shipped in config/roof-content.php are only used as a per-locale
 * fallback, so the site renders correctly before the seeders are run and
 * after a fresh Vercel/Turso deployment.
 */
class RoofContent
{
    public static function text(
        string $page,
        string $section,
        string $key,
        ?string $locale = null,
        ?string $default = null,
    ): string {
        $locale = $locale ?: app()->getLocale();

        try {
            $stored = ContentBlock::get($page, $section, $key, $locale, null, true);
        } catch (\Throwable) {
            $stored = null;
        }

        if (is_string($stored) && trim($stored) !== '') {
            return $stored;
        }

        return static::fallback($page, $section, $key, $locale) ?? $default ?? '';
    }

    /**
     * Locale-aware default shipped with the application.
     */
    public static function fallback(string $page, string $section, string $key, ?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $value = config("roof-content.blocks.{$page}.{$section}.{$key}");

        if (! is_array($value)) {
            return null;
        }

        return $value[$locale] ?? $value['en'] ?? null;
    }

    /**
     * Shortcut for homepage copy.
     */
    public static function home(string $key, ?string $locale = null, ?string $default = null): string
    {
        return static::text('home', 'roof', $key, $locale, $default);
    }

    /**
     * Resolve an editable image path to a public URL. Returns null when no
     * image is configured so callers can render a CSS placeholder instead.
     */
    public static function image(string $page, string $section, string $key, ?string $locale = null): ?string
    {
        $value = static::text($page, $section, $key, $locale, '');

        if ($value === '' || $value === '/') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//')) {
            return $value;
        }

        return upload_url($value);
    }
}
