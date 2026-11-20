<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $guarded = [];

    protected $casts = [
        'value' => 'string',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $setting = static::where('key', $key)->first();
            if (!$setting) return $default;

            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        } catch (\Throwable) {
            return $default;
        }
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
            $settings = static::whereIn('key', $keys)
                ->get()
                ->keyBy('key');

            $result = [];
            foreach ($keys as $key) {
                $result[$key] = $settings->has($key)
                    ? match ($settings[$key]->type) {
                        'boolean' => (bool) $settings[$key]->value,
                        'json' => json_decode($settings[$key]->value, true),
                        default => $settings[$key]->value,
                    }
                    : $default;
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
