<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\ContentBlock;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    public bool $showContentEditor = false;
    public array $pageSections = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('viewFrontend')
                ->label(__('admin.Ver en el sitio'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => PageResource::getPageUrl($this->record->slug))
                ->openUrlInNewTab(),
            Actions\Action::make('toggleContent')
                ->label(fn () => $this->showContentEditor ? 'Ocultar contenido' : 'Editar contenido')
                ->icon(fn () => $this->showContentEditor ? 'heroicon-o-x-mark' : 'heroicon-o-pencil')
                ->action(function () {
                    $this->showContentEditor = !$this->showContentEditor;
                    if ($this->showContentEditor) {
                        $this->loadContentBlocks();
                    }
                })
                ->color(fn () => $this->showContentEditor ? 'danger' : 'warning'),
        ];
    }

    public function form(Form $form): Form
    {
        $schema = [
            Section::make('Configuración de la Página')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.Título'))
                            ->required()
                            ->maxLength(255)
                            ->afterStateHydrated(function ($component, $state) {
                                $locale = app()->getLocale();
                                if (is_array($state)) {
                                    $component->state($state[$locale] ?? $state['en'] ?? $state['es'] ?? reset($state));
                                } elseif (is_string($state)) {
                                    $decoded = json_decode($state, true);
                                    if (is_array($decoded)) {
                                        $component->state($decoded[$locale] ?? $decoded['en'] ?? $decoded['es'] ?? reset($decoded));
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('admin.Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.Visible'))
                            ->default(true),
                    ]),
                ]),
        ];

        // Cuerpo de la página. Las páginas (privacidad, cookies, términos, aviso
        // legal, etc.) guardan su contenido en la columna translatable `content`,
        // no en ContentBlocks. Se muestra al activar "✏️ Editar contenido".
        $schema[] = Section::make('Contenido de la Página')
            ->description(__('admin.Edita el cuerpo de la página. Se guarda en el idioma activo del panel (EN/ES).'))
            ->visible(fn () => $this->showContentEditor)
            ->schema([
                Forms\Components\RichEditor::make('content')
                    ->label(__('admin.Contenido'))
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike',
                        'h2', 'h3', 'h4',
                        'bulletList', 'orderedList',
                        'blockquote', 'codeBlock',
                        'link', 'undo', 'redo',
                    ])
                    ->afterStateHydrated(function ($component, $state) {
                        // Spatie translatable + cast 'array' → $state is [locale => html].
                        // Resolve the panel's active locale for the editor.
                        if (is_array($state)) {
                            $locale = app()->getLocale();
                            $component->state($state[$locale] ?? $state['en'] ?? $state['es'] ?? reset($state) ?? '');
                        } elseif (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (is_array($decoded)) {
                                $locale = app()->getLocale();
                                $component->state($decoded[$locale] ?? $decoded['en'] ?? $decoded['es'] ?? reset($decoded) ?? '');
                            }
                        }
                    })
                    ->helperText(__('admin.Puedes pegar texto con formato o HTML. Se guarda solo para el idioma que estás editando.'))
                    ->columnSpanFull(),
            ])
            ->collapsible();

        // Secciones dinámicas gestionadas como ContentBlocks (si existen para esta página).
        $dynamicSections = $this->getContentFormSections();

        if (!empty($dynamicSections)) {
            $schema[] = Section::make('Bloques de Contenido')
                ->description(__('admin.Secciones de la página gestionadas como bloques dinámicos.'))
                ->visible(fn () => $this->showContentEditor)
                ->schema($dynamicSections);
        }

        return $form->schema($schema);
    }

    /**
     * Build the content block sections dynamically from the database.
     */
    protected function getContentFormSections(): array
    {
        if (!$this->record) return [];

        $cbPage = PageResource::slugToContentBlockPage($this->record->slug);
        $locale = app()->getLocale();

        $blocks = ContentBlock::where('page', $cbPage)
            ->orderBy('sort_order')
            ->orderBy('section')
            ->orderBy('key')
            ->get();

        if ($blocks->isEmpty()) return [];

        // Group by section
        $grouped = $blocks->groupBy('section');

        $sectionIcons = [];

        $schema = [];

        foreach ($grouped as $sectionKey => $sectionBlocks) {
            $fields = [];
            $uniqueBlocks = $sectionBlocks->unique('key');

            foreach ($uniqueBlocks as $block) {
                $fieldName = "cb__{$sectionKey}__{$block->key}";
                $currentValue = $block->value ?? '';

                $field = match ($block->type) {
                    'richtext' => Forms\Components\RichEditor::make($fieldName)
                        ->label(ucwords(str_replace('_', ' ', $block->key)))
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike',
                            'h2', 'h3', 'h4',
                            'bulletList', 'orderedList',
                            'blockquote', 'codeBlock',
                            'link', 'attachFiles',
                            'undo', 'redo',
                        ])
                        ->default($currentValue)
                        ->columnSpanFull(),
                    'image' => Forms\Components\TextInput::make($fieldName)
                        ->label(ucwords(str_replace('_', ' ', $block->key)))
                        ->placeholder(__('admin.Ruta de imagen (ej: images/home/hero.jpg)'))
                        ->helperText(__('admin.Ruta relativa a public/storage/'))
                        ->default($currentValue)
                        ->columnSpanFull(),
                    'video' => Forms\Components\TextInput::make($fieldName)
                        ->label(ucwords(str_replace('_', ' ', $block->key)))
                        ->url()
                        ->placeholder(__('admin.https://youtube.com/watch?v=...'))
                        ->default($currentValue)
                        ->columnSpanFull(),
                    default => Forms\Components\TextInput::make($fieldName)
                        ->label(ucwords(str_replace('_', ' ', $block->key)))
                        ->maxLength(2000)
                        ->default($currentValue)
                        ->columnSpanFull(),
                };

                $fields[] = $field;
            }

            $label = ucwords(str_replace('_', ' ', $sectionKey));

            $schema[] = Forms\Components\Section::make($label)
                ->description('Campos: ' . $uniqueBlocks->pluck('key')->implode(', '))
                ->schema($fields)
                ->collapsible()
                ->collapsed(true);
        }

        return $schema;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $locale = app()->getLocale(); // idioma activo del panel (en/es vía AdminLocalization)

        // Title: keep as translatable JSON array, updating only the active admin locale.
        if (isset($data['title']) && is_string($data['title'])) {
            $existingTitle = $this->decodeTranslatableColumn($record, 'title');
            $existingTitle[$locale] = $data['title'];
            $data['title'] = $existingTitle;
        }

        // Content (cuerpo de la página): same JSON-array logic for the RichEditor.
        if (array_key_exists('content', $data)) {
            $existingContent = $this->decodeTranslatableColumn($record, 'content');
            $existingContent[$locale] = $data['content'] ?? '';
            $data['content'] = $existingContent;
        }

        // Extract content block data from form
        $cbData = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'cb__')) {
                $cbData[$key] = $value;
                unset($data[$key]);
            }
        }

        // Update page record
        $record->update($data);

        // Save content blocks
        if (!empty($cbData)) {
            $this->saveContentBlocks($cbData);
        }

        Notification::make()
            ->title('Página actualizada')
            ->success()
            ->send();

        return $record;
    }

    /**
     * Return a translatable column value as [locale => value], whether it is
     * already decoded, a JSON string, or empty.
     */
    protected function decodeTranslatableColumn(Model $record, string $column): array
    {
        $raw = $record->getOriginal($column);

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Extract and save content blocks from form data.
     * Keys are formatted as: cb__{section}__{field}
     */
    protected function saveContentBlocks(array $cbData): void
    {
        $cbPage = PageResource::slugToContentBlockPage($this->record->slug);

        foreach ($cbData as $key => $value) {
            if (!str_starts_with($key, 'cb__')) continue;

            $parts = explode('__', $key);
            // cb__section__field → [cb, section, field]
            $section = $parts[1] ?? '';
            $field = $parts[2] ?? '';

            if (empty($section) || empty($field)) continue;

            // Find existing block to get type
            $existing = ContentBlock::where('page', $cbPage)
                ->where('section', $section)
                ->where('key', $field)
                ->first();

            $type = $existing?->type ?? 'text';
            $order = $existing?->sort_order ?? 0;

            // Save only for the active admin locale so other translations are preserved.
            ContentBlock::set($cbPage, $section, $field, $value, $type, app()->getLocale(), $order);
        }

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();
    }

    public function loadContentBlocks(): void
    {
        // Content blocks are loaded via getContentFormSections()
        // This is called when toggling the content editor
    }
}
