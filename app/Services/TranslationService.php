<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    public const LOCALES = ['en', 'es', 'de', 'it', 'fr', 'pt', 'nl'];

    protected function providerConfig(): array
    {
        $provider = (string) \App\Models\Setting::get('ai_provider', config('services.ai.default_provider', 'groq'));
        $model = (string) \App\Models\Setting::get('ai_model', config('services.ai.default_model', 'openai/gpt-oss-120b'));
        $key = (string) \App\Models\Setting::get('ai_api_key', config("services.{$provider}.api_key", ''));
        return [$provider, $model, $key];
    }

    protected function localeName(string $code): string
    {
        return match ($code) {
            'en' => 'English',
            'es' => 'Spanish',
            'de' => 'German',
            'it' => 'Italian',
            'fr' => 'French',
            'pt' => 'Portuguese',
            'nl' => 'Dutch',
            default => $code,
        };
    }

    /**
     * Translate $text from $from to each language in $targets.
     * Returns [langCode => translatedText].
     */
    public function translateToAll(string $text, string $from, array $targets): array
    {
        $text = trim($text);
        if ($text === '' || empty($targets)) {
            return [];
        }
        [$provider, $model, $key] = $this->providerConfig();
        if (empty($key)) {
            return [];
        }

        $fromName = $this->localeName($from);
        $parts = [];
        foreach ($targets as $t) {
            $parts[] = "{$t} ({$this->localeName($t)})";
        }
        $targetDesc = implode(', ', $parts);

        $system = "You are a professional translator for a company that sells decorative resin flooring, epoxy, and granite chip systems. Translate accurately, preserve industry terminology, tone and any HTML tags exactly. Never add commentary.";
        $user = "Translate the following text from {$fromName} to each of these languages: {$targetDesc}.\nReturn ONLY a JSON object (no markdown), where each key is the language code and the value is the translation.\n\nTEXT:\n{$text}";

        $raw = $this->callLlm($system, $user, $model, $key, $provider, 2000);
        if ($raw === null) {
            return [];
        }

        $json = $this->extractJson($raw);
        $result = [];
        foreach ($targets as $t) {
            $val = $json[$t] ?? null;
            $result[$t] = is_string($val) ? trim($val) : '';
        }
        return $result;
    }

    public function translate(string $text, string $from, string $to): string
    {
        $r = $this->translateToAll($text, $from, [$to]);
        return $r[$to] ?? '';
    }

    /**
     * Translate many texts at once: [id => text] from $from into all $targets.
     * Returns [id => [locale => translated]] with only successful entries.
     * Chunks inputs so one LLM call handles several short texts (fewer calls).
     */
    public function translateBatch(array $texts, string $from, array $targets): array
    {
        $texts = array_filter($texts, fn ($t) => is_string($t) && trim($t) !== '');
        if (! $texts || empty($targets)) {
            return [];
        }

        [$provider, $model, $key] = $this->providerConfig();
        if (empty($key)) {
            return [];
        }

        // Group texts into chunks of ≤1500 chars (keeps outputs within token limits)
        $chunks = [];
        $current = [];
        $currentLen = 0;
        foreach ($texts as $id => $text) {
            $len = mb_strlen($text);
            if ($current && $currentLen + $len > 1500) {
                $chunks[] = $current;
                $current = [];
                $currentLen = 0;
            }
            $current[$id] = $text;
            $currentLen += $len;
        }
        if ($current) {
            $chunks[] = $current;
        }

        $results = [];
        foreach ($chunks as $chunk) {
            $fromName = $this->localeName($from);
            $targetDesc = implode(', ', array_map(fn ($t) => "{$t} ({$this->localeName($t)})", $targets));
            $system = 'You are a professional translator for a company that sells decorative resin flooring, epoxy, and granite chip systems. Translate accurately, preserve industry terminology, tone and any HTML tags exactly. Never add commentary.';
            $user = "Translate each text from {$fromName} to each language: {$targetDesc}.\n"
                .'Return ONLY a JSON object (no markdown): top-level keys are the given text ids, each value is an object keyed by language code.\n\nTEXTS:'
                ."\n".json_encode($chunk, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $raw = $this->callLlm($system, $user, $model, $key, $provider, 4096);
            if ($raw === null) {
                continue;
            }
            $json = $this->extractJson($raw);
            foreach ($chunk as $id => $text) {
                $entry = $json[$id] ?? null;
                if (! is_array($entry)) {
                    continue;
                }
                foreach ($targets as $t) {
                    $val = $entry[$t] ?? null;
                    if (is_string($val) && trim($val) !== '') {
                        $results[$id][$t] = trim($val);
                    }
                }
            }
        }

        return $results;
    }

    protected function callLlm(string $system, string $user, string $model, string $key, string $provider, int $maxTokens): ?string
    {
        try {
            if ($provider === 'anthropic') {
                $response = Http::withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'system' => $system,
                    'messages' => [['role' => 'user', 'content' => $user]],
                ]);
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['content'][0]['text'] ?? null;
                }
                Log::warning('Translation anthropic error: ' . ($response->json()['error']['message'] ?? $response->body()));
                return null;
            }

            $base = match ($provider) {
                'openai' => 'https://api.openai.com/v1',
                'deepseek' => 'https://api.deepseek.com',
                default => 'https://api.groq.com/openai/v1',
            };
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$key}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post("{$base}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'temperature' => 0.3,
                'max_tokens' => $maxTokens,
            ]);
            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }
            Log::warning("Translation {$provider} error: " . ($response->json()['error']['message'] ?? $response->body()));
            return null;
        } catch (\Throwable $e) {
            Log::warning('Translation exception: ' . $e->getMessage());
            return null;
        }
    }

    protected function extractJson(string $raw): array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/i', '', $raw);
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start === false || $end === false) {
            return [];
        }
        $jsonStr = substr($raw, $start, $end - $start + 1);
        $decoded = json_decode($jsonStr, true);
        return is_array($decoded) ? $decoded : [];
    }
}
