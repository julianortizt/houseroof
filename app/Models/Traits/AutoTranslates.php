<?php

namespace App\Models\Traits;

use App\Services\TranslationService;
use Illuminate\Support\Facades\Log;

trait AutoTranslates
{
    public static function bootAutoTranslates()
    {
        static::saving(function ($model) {
            if (!method_exists($model, 'getTranslations') || empty($model->translatable)) {
                return;
            }

            $service = app(TranslationService::class);
            $locales = TranslationService::LOCALES;

            foreach ($model->translatable as $attr) {
                $current = $model->getTranslations($attr);
                if (!is_array($current)) {
                    continue;
                }

                $original = [];
                if (method_exists($model, 'getRawOriginal')) {
                    $orig = $model->getRawOriginal($attr);
                    if (is_string($orig) && $orig !== '') {
                        $original = json_decode($orig, true) ?: [];
                    }
                }

                // Source locale: prefer the one that was just changed.
                $source = null;
                foreach ($locales as $l) {
                    $cur = $current[$l] ?? '';
                    $org = $original[$l] ?? '';
                    if (!empty($cur) && $cur !== $org) {
                        $source = $l;
                        break;
                    }
                }
                if (!$source) {
                    foreach ($locales as $l) {
                        if (!empty($current[$l] ?? '')) {
                            $source = $l;
                            break;
                        }
                    }
                }
                if (!$source) {
                    continue;
                }

                $sourceText = (string) $current[$source];
                if (trim($sourceText) === '') {
                    continue;
                }

                $targets = [];
                foreach ($locales as $l) {
                    if ($l === $source) {
                        continue;
                    }
                    if (empty(trim($current[$l] ?? ''))) {
                        $targets[] = $l;
                    }
                }
                if (empty($targets)) {
                    continue;
                }

                try {
                    $translations = $service->translateToAll($sourceText, $source, $targets);
                    foreach ($translations as $l => $t) {
                        if (!empty(trim($t))) {
                            $model->setTranslation($attr, $l, $t);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('AutoTranslates failed for ' . get_class($model) . '::' . $attr . ': ' . $e->getMessage());
                }
            }
        });
    }
}
