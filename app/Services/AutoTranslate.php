<?php

namespace App\Services;

/**
 * Free auto-translation using MyMemory API.
 * No API key required for basic usage.
 * Falls back to original text if translation fails.
 */
class AutoTranslate
{
    /** Supported locales and their translation codes */
    protected static array $localeMap = [
        'es' => 'es',
        'en' => 'en-GB',
        'de' => 'de',
        'it' => 'it',
        'fr' => 'fr',
        'pt' => 'pt-PT',
        'nl' => 'nl',
    ];

    /**
     * Translate a string from one locale to another.
     * Returns original text if translation fails.
     */
    public static function translate(string $text, string $from, string $to): string
    {
        if (empty($text) || $from === $to) {
            return $text;
        }

        $fromCode = self::$localeMap[$from] ?? $from;
        $toCode = self::$localeMap[$to] ?? $to;

        try {
            $url = sprintf(
                'https://api.mymemory.translated.net/get?q=%s&langpair=%s|%s',
                urlencode($text),
                $fromCode,
                $toCode
            );

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200 || empty($response)) {
                return $text; // Fallback: return original
            }

            $data = json_decode($response, true);
            if (!is_array($data) || empty($data['responseData']['translatedText'])) {
                return $text;
            }

            $translated = $data['responseData']['translatedText'];

            // MyMemory sometimes returns the original text if it can't translate
            if ($translated === $text) {
                return $text;
            }

            return $translated;
        } catch (\Throwable $e) {
            return $text; // Always return something
        }
    }

    /**
     * Translate an array of key=>value pairs from one locale to all target locales.
     * Returns [locale => [key => translated_value]].
     * ALWAYS returns a value for every key (falls back to original text).
     */
    public static function translateArray(array $texts, string $from, ?array $targets = null): array
    {
        $targets = $targets ?? self::otherLocales($from);
        $results = [];

        foreach ($targets as $to) {
            $results[$to] = [];
            foreach ($texts as $key => $text) {
                if (empty($text) || !is_string($text)) {
                    $results[$to][$key] = $text;
                    continue;
                }
                // translate() always returns a string (falls back to original)
                $results[$to][$key] = self::translate($text, $from, $to);
            }
        }

        return $results;
    }

    /**
     * AI translation (Groq via TranslationService) for [id => text] into all
     * locales except the source. Shape matches translateArray():
     * [locale => [id => value]]. Missing/failed targets fall back to MyMemory.
     */
    public static function translateArrayAi(array $texts, string $from): array
    {
        $targets = self::otherLocales($from);
        $out = array_fill_keys($targets, []);

        $texts = array_filter($texts, fn ($t) => is_string($t) && trim($t) !== '');
        if ($texts === [] || $targets === []) {
            return $out;
        }

        $batch = [];
        try {
            $batch = app(\App\Services\TranslationService::class)
                ->translateBatch($texts, $from, $targets);
        } catch (\Throwable $e) {
            $batch = [];
        }

        foreach ($texts as $id => $text) {
            $got = $batch[$id] ?? [];
            foreach ($targets as $to) {
                $val = $got[$to] ?? null;
                $out[$to][$id] = (is_string($val) && trim($val) !== '')
                    ? $val
                    : self::translate($text, $from, $to);
            }
        }

        return $out;
    }

    /**
     * Get all locale codes except the source.
     */
    public static function otherLocales(string $except): array
    {
        return array_values(array_diff(array_keys(self::$localeMap), [$except]));
    }
}
