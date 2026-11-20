<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContentBlock extends Model
{
    protected $guarded = [];

    /** All blocks for the current request, keyed by page/section/key/locale. */
    private static ?array $allBlocks = null;

    private static ?int $snapshotRequest = null;

    /**
     * Get a content block value by page, section, key and locale.
     *
     * Blocks are loaded once per request from a single cached collection. With
     * the HTTP libSQL driver one query per block would be too slow on the
     * homepage (hundreds of round trips to Turso).
     */
    public static function get(string $page, string $section, string $key, ?string $locale = null, mixed $default = null, bool $fallback = true): mixed
    {
        $locale = $locale ?? app()->getLocale();
        $blocks = static::allBlocks();

        $block = $blocks[static::cacheKey($page, $section, $key, $locale)] ?? null;

        // Fallback to the configured fallback locale. This must also run when
        // the requested locale is the app's own default, otherwise content that
        // only exists in the fallback locale is silently dropped.
        if (!$block && $fallback) {
            $fallbackLocale = config('app.fallback_locale', 'en');

            if ($fallbackLocale !== $locale) {
                $block = $blocks[static::cacheKey($page, $section, $key, $fallbackLocale)] ?? null;
            }
        }

        if (!$block) {
            return $default;
        }

        return match ($block['type']) {
            'json' => json_decode($block['value'], true),
            'boolean' => (bool) $block['value'],
            default => $block['value'],
        };
    }

    protected static function cacheKey(string $page, string $section, string $key, string $locale): string
    {
        return "{$page}\0{$section}\0{$key}\0{$locale}";
    }

    protected static function allBlocks(): array
    {
        // FrankenPHP reuses the process between requests, so the snapshot must
        // be tied to the current Request instance, not to the process lifetime.
        $request = app()->bound('request') ? app('request') : null;
        $requestId = $request ? spl_object_id($request) : 0;

        if (static::$snapshotRequest !== $requestId) {
            static::$allBlocks = null;
            static::$snapshotRequest = $requestId;
        }

        if (static::$allBlocks !== null) {
            return static::$allBlocks;
        }

        try {
            static::$allBlocks = Cache::remember('content_blocks_all', 3600, function (): array {
                return static::query()
                    ->get(['page', 'section', 'key', 'locale', 'value', 'type'])
                    ->mapWithKeys(static function (ContentBlock $block): array {
                        return [
                            static::cacheKey($block->page, $block->section, $block->key, $block->locale) => [
                                'value' => $block->value,
                                'type' => $block->type,
                            ],
                        ];
                    })
                    ->all();
            });
        } catch (\Throwable) {
            static::$allBlocks = [];
        }

        return static::$allBlocks;
    }

    /** Reset the in-request copy and the shared cache after an edit. */
    public static function flushCache(): void
    {
        static::$allBlocks = null;
        Cache::forget('content_blocks_all');
    }

    /**
     * Set a content block value.
     */
    public static function set(string $page, string $section, string $key, mixed $value, string $type = 'text', string $locale = 'es', int $sortOrder = 0): void
    {
        static::updateOrCreate(
            [
                'page' => $page,
                'section' => $section,
                'key' => $key,
                'locale' => $locale,
            ],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'sort_order' => $sortOrder,
            ]
        );

        static::flushCache();
    }

    /**
     * Get all blocks for a page and section.
     */
    public static function getSection(string $page, string $section, ?string $locale = null): \Illuminate\Support\Collection
    {
        $locale = $locale ?? app()->getLocale();

        return static::where('page', $page)
            ->where('section', $section)
            ->where('locale', $locale)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get image URL for a block (handles both relative and full URLs).
     */
    public static function getImageUrl(string $page, string $section, string $key, ?string $locale = null, ?string $default = null): ?string
    {
        $value = static::get($page, $section, $key, $locale, $default);

        if (!$value) {
            return $default;
        }

        // If it's already a full URL
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // If it's a storage path
        if (upload_disk()->exists($value)) {
            return asset('storage/' . $value);
        }

        // If it's in public root
        return asset($value);
    }

    /**
     * Get video URL for a block.
     */
    public static function getVideoUrl(string $page, string $section, string $key, ?string $locale = null): ?string
    {
        $value = static::get($page, $section, $key, $locale);

        if (!$value) {
            return null;
        }

        // YouTube URL
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/', $value, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}";
        }

        // Vimeo URL
        if (preg_match('/vimeo\.com\/(\d+)/', $value, $matches)) {
            return "https://player.vimeo.com/video/{$matches[1]}";
        }

        // Local video file
        if (upload_disk()->exists($value)) {
            return asset('storage/' . $value);
        }

        return $value;
    }

    /**
     * Clear all cache for a page.
     */
    public static function clearPageCache(string $page): void
    {
        static::flushCache();
    }

    /**
     * After a text/richtext block is saved: fill sibling locales that are empty
     * (copy first, then AI-translate) and re-translate any locale that still
     * holds a verbatim copy of the previous or current source (untranslated).
     * Non-text siblings are only created when missing. Returns locales written.
     */
    public function propagateTranslations(?string $previousValue = null): int
    {
        $source = trim((string) $this->value);
        if ($source === '' || ! in_array($this->type, ['text', 'richtext'], true)) {
            return 0;
        }

        $others = \App\Services\AutoTranslate::otherLocales($this->locale);
        $sortOrder = static::where('page', $this->page)
            ->where('section', $this->section)
            ->where('key', $this->key)
            ->where('locale', $this->locale)
            ->value('sort_order') ?? $this->sort_order ?? 0;

        $targets = [];
        foreach ($others as $l) {
            $existing = static::where('page', $this->page)
                ->where('section', $this->section)
                ->where('key', $this->key)
                ->where('locale', $l)
                ->value('value');

            if ($existing === null || trim((string) $existing) === '') {
                $targets[] = $l;
                continue;
            }

            $existing = trim((string) $existing);
            if ($existing === $source
                || ($previousValue !== null && $existing === trim((string) $previousValue))) {
                $targets[] = $l;
            }
        }

        if ($targets === []) {
            return 0;
        }

        $texts = ['block' => $this->value];
        $res = \App\Services\AutoTranslate::translateArrayAi($texts, $this->locale);

        $written = 0;
        foreach ($targets as $l) {
            $val = $res[$l]['block'] ?? null;
            if (! is_string($val) || trim($val) === '') {
                $val = $this->value; // last-resort: source copy, never blank
            }
            static::set($this->page, $this->section, $this->key, $val, $this->type, $l, $sortOrder);
            $written++;
        }

        static::clearPageCache($this->page);
        return $written;
    }
}
