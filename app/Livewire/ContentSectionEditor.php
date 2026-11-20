<?php

namespace App\Livewire;

use App\Models\ContentBlock;
use Filament\Notifications\Notification;
use Livewire\Component;

class ContentSectionEditor extends Component
{
    public ?int $pageId = null;
    public string $pageSlug = '';
    public string $pageTitle = '';
    public array $sections = [];
    public array $openSections = [];
    public bool $showNewSection = false;
    public string $newSectionKey = '';
    public ?string $deleteSectionKey = null;

    public function mount(?int $pageId = null, ?string $pageSlug = null): void
    {
        $this->pageId = $pageId;
        $this->pageSlug = $pageSlug ?? '';

        if ($pageId && $pageSlug) {
            $page = \App\Models\Page::find($pageId);
            $this->pageTitle = $page->title ?? $pageSlug;
            $this->loadSections();
        }
    }

    protected function cbPage(): string
    {
        return match ($this->pageSlug) {
            'about-us' => 'about',
            default => $this->pageSlug,
        };
    }

    public function loadSections(): void
    {
        $blocks = ContentBlock::where('page', $this->cbPage())
            ->whereIn('locale', ['es', 'en'])
            ->orderBy('sort_order')
            ->get();

        $grouped = [];
        foreach ($blocks as $block) {
            $sk = $block->section;
            $k = $block->key;
            $loc = $block->locale;

            if (!isset($grouped[$sk])) {
                $grouped[$sk] = ['fields' => [], 'sort_order' => $block->sort_order];
            }
            if (!isset($grouped[$sk]['fields'][$k])) {
                $grouped[$sk]['fields'][$k] = ['type' => $block->type, 'es' => '', 'en' => ''];
            }
            $grouped[$sk]['fields'][$k][$loc] = $block->value ?? '';
            $grouped[$sk]['sort_order'] = min($grouped[$sk]['sort_order'], $block->sort_order);
        }

        uasort($grouped, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);
        $this->sections = $grouped;
        $this->openSections = array_keys($this->sections);
    }

    public function toggle(string $key): void
    {
        if (in_array($key, $this->openSections)) {
            $this->openSections = array_diff($this->openSections, [$key]);
        } else {
            $this->openSections[] = $key;
        }
    }

    public function updateField(string $section, string $field, string $locale, mixed $value): void
    {
        if (isset($this->sections[$section]['fields'][$field][$locale])) {
            $this->sections[$section]['fields'][$field][$locale] = $value;
        }
    }

    public function saveSection(string $sectionKey): void
    {
        $cbPage = $this->cbPage();
        $section = $this->sections[$sectionKey] ?? null;
        if (!$section) return;

        foreach ($section['fields'] as $key => $data) {
            foreach (['es', 'en'] as $locale) {
                $value = $data[$locale] ?? '';
                if (is_array($value)) $value = $value[0] ?? '';
                ContentBlock::set($cbPage, $sectionKey, $key, $value, $data['type'], $locale, $section['sort_order'] ?? 0);
            }
        }

        Notification::make()->title("Sección guardada: {$sectionKey}")->success()->send();
    }

    public function saveAll(): void
    {
        foreach (array_keys($this->sections) as $k) {
            $this->saveSection($k);
        }
        Notification::make()->title('Todas las secciones guardadas')->success()->send();
    }

    public function openNewSection(): void
    {
        $this->newSectionKey = '';
        $this->showNewSection = true;
    }

    public function createSection(): void
    {
        $key = \Illuminate\Support\Str::slug(trim($this->newSectionKey));
        if (empty($key)) return;
        if (isset($this->sections[$key])) {
            Notification::make()->title('Esa sección ya existe')->error()->send();
            return;
        }
        $cbPage = $this->cbPage();
        $sortOrder = (count($this->sections) + 1) * 10;
        ContentBlock::set($cbPage, $key, 'title', '', 'text', 'es', $sortOrder);
        ContentBlock::set($cbPage, $key, 'title', '', 'text', 'en', $sortOrder);
        $this->sections[$key] = ['fields' => ['title' => ['type' => 'text', 'es' => '', 'en' => '']], 'sort_order' => $sortOrder];
        $this->openSections[] = $key;
        $this->showNewSection = false;
        $this->newSectionKey = '';
        Notification::make()->title("Sección \"{$key}\" creada")->success()->send();
    }

    public function confirmDelete(string $sectionKey): void
    {
        $this->deleteSectionKey = $sectionKey;
    }

    public function deleteSection(string $sectionKey): void
    {
        ContentBlock::where('page', $this->cbPage())->where('section', $sectionKey)->delete();
        unset($this->sections[$sectionKey]);
        $this->openSections = array_diff($this->openSections, [$sectionKey]);
        $this->deleteSectionKey = null;
        Notification::make()->title('Sección eliminada')->success()->send();
    }

    public function addField(string $sectionKey): void
    {
        $n = count($this->sections[$sectionKey]['fields'] ?? []) + 1;
        $this->sections[$sectionKey]['fields']["field_{$n}"] = ['type' => 'text', 'es' => '', 'en' => ''];
    }

    public function removeField(string $sectionKey, string $key): void
    {
        ContentBlock::where('page', $this->cbPage())->where('section', $sectionKey)->where('key', $key)->delete();
        unset($this->sections[$sectionKey]['fields'][$key]);
    }

    public function setFieldType(string $sectionKey, string $key, string $type): void
    {
        if (isset($this->sections[$sectionKey]['fields'][$key])) {
            $this->sections[$sectionKey]['fields'][$key]['type'] = $type;
        }
    }

    public function moveUp(string $sectionKey): void
    {
        $keys = array_keys($this->sections);
        $i = array_search($sectionKey, $keys);
        if ($i > 0) {
            [$this->sections[$keys[$i]], $this->sections[$keys[$i - 1]]] = [$this->sections[$keys[$i - 1]], $this->sections[$keys[$i]]];
        }
    }

    public function moveDown(string $sectionKey): void
    {
        $keys = array_keys($this->sections);
        $i = array_search($sectionKey, $keys);
        if ($i < count($keys) - 1) {
            [$this->sections[$keys[$i]], $this->sections[$keys[$i + 1]]] = [$this->sections[$keys[$i + 1]], $this->sections[$keys[$i]]];
        }
    }

    public function getPreviewUrl(): string
    {
        return match ($this->pageSlug) {
            'about-us' => url('/about-us'),
            'privacy-policy' => url('/legal/privacy-policy'),
            'cookie-policy' => url('/legal/cookie-policy'),
            'terms-of-sale' => url('/legal/terms-of-sale'),
            'legal-notice' => url('/legal/legal-notice'),
            default => url('/' . $this->pageSlug),
        };
    }

    public function render()
    {
        return view('livewire.content-section-editor', [
            'pages' => \App\Models\Page::orderBy('title')->get(),
        ]);
    }
}
