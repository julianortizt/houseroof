<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public $guarded = [];

    protected $casts = [
        'value' => 'string',
    ];

    /** All settings for the current request, keyed by setting key. */
    private static ?array $allSettings = null;

    private static ?int $snapshotRequest = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $settings = static::allSettings();

            if (!array_key_exists($key, $settings)) {
                return $default;
            }

            $setting = $settings[$key];

            return match ($setting['type']) {
                'boolean' => (bool) $setting['value'],
                'json' => json_decode($setting['value'], true),
                default => $setting['value'],
            };
        } catch (\Throwable) {
            return $default;
        }
    }

    protected static function allSettings(): array
    {
        $request = app()->bound('request') ? app('request') : null;
        $requestId = $request ? spl_object_id($request) : 0;

        if (static::$snapshotRequest !== $requestId) {
            static::$allSettings = null;
            static::$snapshotRequest = $requestId;
        }

        if (static::$allSettings !== null) {
            return static::$allSettings;
        }

        static::$allSettings = Cache::remember('settings_all', 3600, static function (): array {
            return static::query()
                ->get(['key', 'value', 'type'])
                ->mapWithKeys(static function (Setting $setting): array {
                    return [
                        $setting->key => [
                            'value' => $setting->value,
                            'type' => $setting->type,
                        ],
                    ];
                })
                ->all();
        });

        return static::$allSettings;
    }

    /** Reset the in-request copy and the shared cache after an edit. */
    public static function flushCache(): void
    {
        static::$allSettings = null;
        Cache::forget('settings_all');
    }

    public static function safeGet(string $key, mixed $default = null): mixed
    {
        try {
            return static::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Get multiple settings in a single query.
     * Returns an associative array of key => value.
     */
    public static function getMany(array $keys, mixed $default = null): array
    {
        try {
            $all = static::allSettings();
            $result = [];

            foreach ($keys as $key) {
                if (!array_key_exists($key, $all)) {
                    $result[$key] = $default;
                    continue;
                }

                $setting = $all[$key];
                $result[$key] = match ($setting['type']) {
                    'boolean' => (bool) $setting['value'],
                    'json' => json_decode($setting['value'], true),
                    default => $setting['value'],
                };
            }

            return $result;
        } catch (\Throwable) {
            return array_fill_keys($keys, $default);
        }
    }

    public static function safeGetMany(array $keys, mixed $default = null): array
    {
        try {
            return static::getMany($keys, $default);
        } catch (\Throwable) {
            return array_fill_keys($keys, $default);
        }
    }

    /**
     * Get a locale-aware setting value. If the stored value is a JSON map of
     * language codes (produced by the translation backfill), returns the value
     * for the current locale (falling back to 'en'), otherwise returns the raw value.
     */
    public static function translated(string $key, mixed $default = null): mixed
    {
        $raw = static::get($key, $default);

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && isset($decoded[app()->getLocale()])) {
                return $decoded[app()->getLocale()];
            }
            if (is_array($decoded) && isset($decoded['en'])) {
                return $decoded['en'];
            }
            return $raw;
        }

        return $raw ?? $default;
    }

    public static function set(string $key, mixed $value, string $type = 'text', string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        static::flushCache();
    }

    public static function safeSet(string $key, mixed $value, string $type = 'text', string $group = 'general'): bool
    {
        try {
            static::set($key, $value, $type, $group);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
