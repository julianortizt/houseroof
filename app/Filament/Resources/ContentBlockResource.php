<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentBlockResource\Pages;
use App\Models\ContentBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContentBlockResource extends Resource
{
    protected static ?string $model = ContentBlock::class;
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Contenido');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Contenido del Sitio');
    }
    public static function getModelLabel(): string
    {
        return __('admin.Bloque de Contenido');
    }
    public static function getPluralModelLabel(): string
    {
        return __('admin.Bloques de Contenido');
    }
    protected static ?int $navigationSort = 1;

    /**
     * Get existing sections from DB for a given page, plus predefined ones.
     */
    protected static function getSectionOptions(?string $page = null): array
    {
        $predefined = [
            'home' => ['hero' => 'Hero Slider', 'trust_bar' => 'Barra de Confianza', 'value_prop' => 'Propuesta de Valor', 'services_section' => 'Sección de Servicios', 'cta' => 'Call to Action'],
            'about' => ['hero' => 'Hero', 'story' => 'Nuestra Historia', 'gallery' => 'Galería'],
            'services' => ['hero' => 'Hero', 'resin' => 'Resina', 'granite' => 'Granite', 'process' => 'Proceso'],
            'contact' => ['hero' => 'Hero', 'info' => 'Info'],
            'shop' => ['hero' => 'Hero', 'banner' => 'Banner'],
            'global' => ['header' => 'Header', 'footer' => 'Footer', 'whatsapp' => 'WhatsApp'],
        ];

        // Merge with existing sections from DB
        $existingSections = ContentBlock::when($page, fn ($q) => $q->where('page', $page))
            ->distinct()
            ->pluck('section')
            ->toArray();

        $base = $predefined[$page] ?? [];
        foreach ($existingSections as $s) {
            if (!isset($base[$s])) {
                $base[$s] = str_replace('_', ' ', ucfirst($s));
            }
        }

        return $base;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Ubicación del Bloque')->description(__('admin.Selecciona la página y sección. Puedes escribir una sección personalizada.'))->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('page')
                        ->label(__('admin.Página'))
                        ->options([
                            'home' => 'Inicio',
                            'about' => 'Sobre Nosotros',
                            'services' => 'Servicios',
                            'contact' => 'Contacto',
                            'shop' => 'Tienda',
                            'global' => 'Global (todo el sitio)',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($set) => $set('section', '')),
                    Forms\Components\Select::make('section')
                        ->label(__('admin.Sección'))
                        ->options(fn (Forms\Get $get) => static::getSectionOptions($get('page')))
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->helperText(__('admin.Escribe para buscar o crear una sección nueva'))
                        ->createOptionForm([
                            Forms\Components\TextInput::make('value')
                                ->label(__('admin.Nombre de la nueva sección'))
                                ->placeholder(__('admin.ej: testimonials, team, faq...'))
                                ->required()
                                ->maxLength(100)
                                ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Str::slug($state)),
                        ]),
                    Forms\Components\Select::make('type')
                        ->label(__('admin.Tipo'))
                        ->options(['text' => 'Texto', 'richtext' => 'Enriquecido', 'image' => 'Imagen', 'video' => 'Video', 'json' => 'JSON'])
                        ->required()
                        ->default('text'),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('key')
                        ->label(__('admin.Campo'))
                        ->required()
                        ->placeholder(__('admin.title, subtitle, image, slide_1_title...'))
                        ->maxLength(100)
                        ->helperText(__('admin.Identificador del campo. Ej: title, subtitle, image, slide_1_title')),
                    Forms\Components\Select::make('locale')
                        ->label(__('admin.Idioma'))
                        ->options(['es' => 'ES', 'en' => 'EN', 'de' => 'DE', 'it' => 'IT', 'fr' => 'FR', 'pt' => 'PT', 'nl' => 'NL'])
                        ->required()
                        ->default('es'),
                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('admin.Orden'))
                        ->numeric()
                        ->default(0),
                ]),
            ]),
            Forms\Components\Section::make('Contenido')->schema([
                Forms\Components\TextInput::make('value')->label(__('admin.Valor'))->visible(fn (Forms\Get $get) => $get('type') === 'text')->columnSpanFull(),
                Forms\Components\RichEditor::make('value')->label(__('admin.Contenido'))->visible(fn (Forms\Get $get) => $get('type') === 'richtext')->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike',
                        'h2', 'h3', 'h4',
                        'bulletList', 'orderedList',
                        'blockquote', 'codeBlock',
                        'link', 'attachFiles',
                        'undo', 'redo',
                    ])
                        ->columnSpanFull(),
                Forms\Components\FileUpload::make('value')->label(__('admin.Imagen'))->image()->disk('public')->directory('content-blocks')->imagePreviewHeight('200')->maxSize(10240)->visible(fn (Forms\Get $get) => $get('type') === 'image')->columnSpanFull(),
                Forms\Components\TextInput::make('value')->label(__('admin.Video URL'))->url()->placeholder(__('admin.https://youtube.com/watch?v=...'))->helperText(__('admin.YouTube, Vimeo o URL de video'))->visible(fn (Forms\Get $get) => $get('type') === 'video')->columnSpanFull(),
                Forms\Components\Textarea::make('value')->label(__('admin.JSON'))->visible(fn (Forms\Get $get) => $get('type') === 'json')->rows(10)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page')
                    ->label(__('admin.Página'))
                    ->badge()
                    ->color(fn (string $state): string => match($state) {'home'=>'success','about'=>'info','services'=>'warning','contact'=>'danger','shop'=>'primary',default=>'gray'})
                    ->formatStateUsing(fn (string $state): string => match($state) {'home'=>'Inicio','about'=>'Sobre Nosotros','services'=>'Servicios','contact'=>'Contacto','shop'=>'Tienda','global'=>'Global',default=>$state})
                    ->sortable(),
                Tables\Columns\TextColumn::make('section')
                    ->label(__('admin.Sección'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('key')
                    ->label(__('admin.Campo'))
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('locale')
                    ->label(__('admin.Idioma'))
                    ->badge()
                    ->color(fn (string $state): string => match($state) {'es'=>'success','en'=>'info','de'=>'warning','it'=>'primary','fr'=>'danger','pt'=>'gray','nl'=>'success',default=>'gray'})
                    ->formatStateUsing(fn (string $state): string => match($state) {'es'=>'ES','en'=>'EN','de'=>'DE','it'=>'IT','fr'=>'FR','pt'=>'PT','nl'=>'NL',default=>$state}),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.Tipo'))
                    ->badge()
                    ->color(fn (string $state): string => match($state) {'text'=>'gray','richtext'=>'info','image'=>'success','video'=>'warning','json'=>'primary',default=>'gray'})
                    ->formatStateUsing(fn (string $state): string => match($state) {'text'=>'Texto','richtext'=>'Enriquecido','image'=>'Imagen','video'=>'Video','json'=>'JSON',default=>$state}),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('admin.Valor'))
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.Orden'))
                    ->sortable(),
            ])
            ->defaultSort('page')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('page')
                    ->label(__('admin.Página'))
                    ->options(['home'=>'Inicio','about'=>'Sobre Nosotros','services'=>'Servicios','contact'=>'Contacto','shop'=>'Tienda','global'=>'Global'])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('locale')
                    ->label(__('admin.Idioma'))
                    ->options(['es'=>'Español','en'=>'English','de'=>'Deutsch','it'=>'Italiano','fr'=>'Français','pt'=>'Português','nl'=>'Nederlands'])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('admin.Tipo'))
                    ->options(['text'=>'Texto','richtext'=>'Enriquecido','image'=>'Imagen','video'=>'Video','json'=>'JSON']),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('admin.Editar'))
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\Action::make('preview')
                    ->label(__('admin.Ver'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Vista Previa del Frontend')
                    ->modalContent(function ($record) {
                        $url = match($record->page) {
                            'home' => url('/'),
                            'about' => url('/about-us'),
                            'services' => url('/servicios'),
                            'contact' => url('/contacto'),
                            'shop' => url('/tienda'),
                            'global' => url('/'),
                            default => url('/'),
                        };
                        return view('filament.components.preview-modal', ['url' => $url]);
                    })
                    ->modalSubmitAction(false),
                Tables\Actions\Action::make('duplicate')
                    ->label('')
                    ->icon('heroicon-o-document-duplicate')
                    ->tooltip('Duplicar bloque')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Duplicar este bloque')
                    ->modalDescription('Se creará una copia exacta de este bloque en el mismo idioma.')
                    ->action(function ($record) {
                        $new = ContentBlock::create([
                            'page' => $record->page,
                            'section' => $record->section,
                            'key' => $record->key . '_copy',
                            'locale' => $record->locale,
                            'type' => $record->type,
                            'value' => $record->value,
                            'sort_order' => $record->sort_order + 1,
                        ]);
                        Notification::make()
                            ->title('Bloque duplicado correctamente')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('copyToAllLanguages')
                    ->label('')
                    ->icon('heroicon-o-globe-alt')
                    ->tooltip('Copiar/traducir a todos los idiomas')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Copiar bloque a todos los idiomas')
                    ->modalDescription('Los textos se traducen con IA a los idiomas que no tengan traducción; el resto de tipos se copian.')
                    ->action(function ($record) {
                        $locales = ['es', 'en', 'de', 'it', 'fr', 'pt', 'nl'];
                        $missing = [];
                        foreach ($locales as $locale) {
                            if ($locale === $record->locale) continue;
                            $exists = ContentBlock::where('page', $record->page)
                                ->where('section', $record->section)
                                ->where('key', $record->key)
                                ->where('locale', $locale)
                                ->exists();
                            if (!$exists) {
                                $missing[] = $locale;
                            }
                        }

                        $isText = in_array($record->type, ['text', 'richtext'], true)
                            && trim((string) $record->value) !== '';

                        $translatedMap = [];
                        if ($missing && $isText) {
                            $res = \App\Services\AutoTranslate::translateArrayAi(
                                ['b' => $record->value],
                                $record->locale
                            );
                            foreach ($missing as $l) {
                                $v = $res[$l]['b'] ?? null;
                                $translatedMap[$l] = (is_string($v) && trim($v) !== '') ? $v : null;
                            }
                        }

                        $created = 0;
                        $aiTranslated = 0;
                        foreach ($missing as $l) {
                            $value = $record->value;
                            if ($isText && ($translatedMap[$l] ?? null) !== null) {
                                $value = $translatedMap[$l];
                                if ($value !== $record->value) {
                                    $aiTranslated++;
                                }
                            }
                            ContentBlock::create([
                                'page' => $record->page,
                                'section' => $record->section,
                                'key' => $record->key,
                                'locale' => $l,
                                'type' => $record->type,
                                'value' => $value,
                                'sort_order' => $record->sort_order,
                            ]);
                            $created++;
                        }
                        ContentBlock::clearPageCache($record->page);

                        $msg = $created === 0
                            ? 'El bloque ya estaba en todos los idiomas'
                            : "Bloque copiado a {$created} idiomas"
                                .($aiTranslated > 0 ? " ({$aiTranslated} traducidos con IA)" : '');
                        Notification::make()
                            ->title($msg)
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label(''),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentBlocks::route('/'),
            'create' => Pages\CreateContentBlock::route('/create'),
            'edit' => Pages\EditContentBlock::route('/{record}/edit'),
        ];
    }
}
