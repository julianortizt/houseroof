<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\ContentBlock;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Contenido');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Páginas');
    }
    public static function getModelLabel(): string
    {
        return __('admin.Página');
    }
    public static function getPluralModelLabel(): string
    {
        return __('admin.Páginas');
    }

    /**
     * Map page slugs to their frontend URLs.
     */
    public static function getPageUrl(string $slug): string
    {
        return match ($slug) {
            'about-us' => url('/about-us'),
            'privacy-policy' => url('/legal/privacy-policy'),
            'cookie-policy' => url('/legal/cookie-policy'),
            'terms-of-sale' => url('/legal/terms-of-sale'),
            'legal-notice' => url('/legal/legal-notice'),
            default => url('/' . $slug),
        };
    }

    /**
     * Get the page slug that corresponds to a ContentBlock page key.
     * E.g., ContentBlock page='about' → Page slug='about-us'
     */
    public static function slugToContentBlockPage(string $slug): string
    {
        return match ($slug) {
            'about-us' => 'about',
            default => $slug,
        };
    }

    /**
     * Dynamically discover all content sections for a page from the database.
     * No hardcoded sections — reads directly from ContentBlocks.
     */
    public static function getDynamicSections(string $slug): array
    {
        $cbPage = self::slugToContentBlockPage($slug);
        $locale = app()->getLocale();

        // Get all unique sections for this page
        $sections = ContentBlock::where('page', $cbPage)
            ->distinct()
            ->pluck('section')
            ->sort()
            ->toArray();

        $result = [];

        foreach ($sections as $section) {
            // Get all blocks in this section
            $blocks = ContentBlock::where('page', $cbPage)
                ->where('section', $section)
                ->orderBy('sort_order')
                ->get()
                ->unique('key');

            $keys = [];
            foreach ($blocks as $block) {
                $keys[$block->key] = [
                    'label' => ucwords(str_replace('_', ' ', $block->key)),
                    'type' => $block->type,
                ];
            }

            $result[$section] = [
                'label' => ucwords(str_replace('_', ' ', $section)),
                'keys' => $keys,
            ];
        }

        return $result;
    }

    /**
     * Build dynamic form fields for all content blocks of a page.
     */
    public static function buildDynamicContentFields(string $slug): array
    {
        $sections = self::getDynamicSections($slug);
        $schema = [];
        $locale = app()->getLocale();
        $cbPage = self::slugToContentBlockPage($slug);

        // Section icons (empty - use clean labels)
        $sectionIcons = [];

        foreach ($sections as $sectionKey => $sectionConfig) {
            $fields = [];

            foreach ($sectionConfig['keys'] as $key => $keyConfig) {
                $fieldName = "cb__{$sectionKey}__{$key}";
                $currentValue = ContentBlock::get($cbPage, $sectionKey, $key, $locale, '');

                $field = match ($keyConfig['type']) {
                    'richtext' => Forms\Components\RichEditor::make($fieldName)
                        ->label($keyConfig['label'])
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
                    'image' => Forms\Components\FileUpload::make($fieldName)
                        ->label($keyConfig['label'])
                        ->image()
                        ->disk('public')
                        ->directory('content-blocks/' . $slug)
                        ->imagePreviewHeight('150')
                        ->maxSize(5120)
                        ->default($currentValue)
                        ->columnSpanFull(),
                    'video' => Forms\Components\TextInput::make($fieldName)
                        ->label($keyConfig['label'])
                        ->url()
                        ->placeholder(__('admin.https://youtube.com/watch?v=...'))
                        ->helperText(__('admin.URL del video (YouTube, Vimeo, etc.)'))
                        ->default($currentValue)
                        ->columnSpanFull(),
                    default => Forms\Components\TextInput::make($fieldName)
                        ->label($keyConfig['label'])
                        ->maxLength(1000)
                        ->default($currentValue)
                        ->columnSpanFull(),
                };

                $fields[] = $field;
            }

            $sectionLabel = ucwords(str_replace('_', ' ', $sectionKey));

            $schema[] = Forms\Components\Section::make($sectionLabel)
                ->description(__('admin.Edita el contenido — se guarda automáticamente en Content Blocks'))
                ->schema($fields)
                ->collapsible()
                ->collapsed(false);
        }

        return $schema;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Configuración de la Página')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.Título'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateHydrated(function ($component, $state) {
                                if (is_array($state)) {
                                    $component->state($state['en'] ?? $state['es'] ?? reset($state));
                                }
                            })
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('admin.Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn (string $state) => Str::slug($state)),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.Visible'))
                            ->default(true),
                    ]),
                ]),

            // Dynamic content sections are added in EditPage::form()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.Título'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return $state['en'] ?? $state['es'] ?? reset($state) ?? '';
                        }
                        $decoded = json_decode((string) $state, true);
                        if (is_array($decoded)) {
                            return $decoded['en'] ?? $decoded['es'] ?? reset($decoded) ?? '';
                        }
                        return (string) $state;
                    }),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.Slug'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.Activa'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.Actualizada'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.Estado')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
