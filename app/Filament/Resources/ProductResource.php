<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'products';

    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Tienda');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Productos');
    }
    public static function getModelLabel(): string
    {
        return __('admin.Producto');
    }
    public static function getPluralModelLabel(): string
    {
        return __('admin.Productos');
    }
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Producto')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.Nombre'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('admin.Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->dehydrated()
                            ->helperText(__('admin.Se genera automáticamente del nombre')),

                        Forms\Components\Select::make('category_id')
                            ->label(__('admin.Categoría'))
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('sku')
                            ->label(__('admin.SKU'))
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label(__('admin.Activo')),

                        Forms\Components\Toggle::make('is_featured')
                            ->default(false)
                            ->label(__('admin.Destacado')),

                        Forms\Components\Toggle::make('is_coming_soon')
                            ->default(false)
                            ->label(__('admin.Coming Soon'))
                            ->helperText(__('admin.Si activo, se muestra pero no se puede comprar')),

                        Forms\Components\Hidden::make('price')
                            ->default(0),

                        Forms\Components\Hidden::make('stock')
                            ->default(0),
                    ]),
                ]),

            Section::make('Descripción')
                ->schema([
                    Grid::make(1)->schema([
                        Forms\Components\Textarea::make('short_description')
                            ->label(__('admin.Descripción corta'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->label(__('admin.Descripción completa'))
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'h2', 'h3', 'h4',
                                'bulletList', 'orderedList',
                                'blockquote', 'codeBlock',
                                'link', 'attachFiles',
                                'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
                ]),



            Section::make('Dimensiones y Envío')
                ->schema([
                    Grid::make(1)->schema([
                        Forms\Components\Toggle::make('has_dimensions')
                            ->label(__('admin.Incluir dimensiones de envío'))
                            ->default(false)
                            ->reactive()
                            ->dehydrated(false)
                            ->afterStateUpdated(fn ($set, $state) => !$state ? collect(['weight_kg', 'width_cm', 'height_cm', 'depth_cm'])->each(fn ($f) => $set($f, null)) : null),

                        Forms\Components\Grid::make(4)
                            ->visible(fn ($get) => $get('has_dimensions'))
                            ->schema([
                                Forms\Components\TextInput::make('weight_kg')
                                    ->label(__('admin.Peso (kg)'))
                                    ->numeric()
                                    ->step(0.01),

                                Forms\Components\TextInput::make('width_cm')
                                    ->label(__('admin.Ancho (cm)'))
                                    ->numeric()
                                    ->step(0.01),

                                Forms\Components\TextInput::make('height_cm')
                                    ->label(__('admin.Alto (cm)'))
                                    ->numeric()
                                    ->step(0.01),

                                Forms\Components\TextInput::make('depth_cm')
                                    ->label(__('admin.Profundidad (cm)'))
                                    ->numeric()
                                    ->step(0.01),
                            ]),
                    ]),
                ]),

            Section::make('Imágenes y Video')
                ->schema([
                    Grid::make(1)->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label(__('admin.Imagen principal'))
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->imagePreviewHeight('150')
                            ->panelLayout('compact')
                            ->maxSize(5120)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('video_path')
                            ->label(__('admin.Video del producto'))
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                            ->disk('public')
                            ->directory('products/videos')
                            ->maxSize(51200)
                            ->helperText(__('admin.MP4, WebM. Máximo 50MB.'))
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('video_url')
                            ->label(__('admin.O YouTube/Vimeo URL'))
                            ->url()
                            ->placeholder(__('admin.https://www.youtube.com/watch?v=...'))
                            ->helperText(__('admin.Si subes un video, esta URL se ignora'))
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('Variantes')
                ->collapsible()
                ->schema([
                    Grid::make(1)->schema([
                        Forms\Components\Repeater::make('variants')
                            ->relationship()
                            ->defaultItems(0)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('admin.Nombre de variante'))
                                    ->required(),

                                Forms\Components\TextInput::make('slug')
                                    ->label(__('admin.Slug'))
                                    ->required(),

                                Forms\Components\TextInput::make('sku')
                                    ->label(__('admin.SKU')),

                                Forms\Components\TextInput::make('price')
                                    ->label(__('admin.Precio (€)'))
                                    ->numeric()
                                    ->prefix('€'),

                                Forms\Components\TextInput::make('stock')
                                    ->label(__('admin.Stock'))
                                    ->numeric()
                                    ->default(0),

                                Forms\Components\Toggle::make('is_active')
                                    ->label(__('admin.Activa'))
                                    ->default(true),

                                Forms\Components\KeyValue::make('attributes')
                                    ->label(__('admin.Atributos')),

                                Forms\Components\FileUpload::make('image_path')
                                    ->label(__('admin.Imagen de variante'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('products/variants'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Agregar variante'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label(__('admin.Imagen'))
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/logo.png'))
                    ->limit(50),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.Nombre'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.Categoría'))
                    ->sortable(),



                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.Activo'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.Destacado'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_coming_soon')
                    ->label(__('admin.Coming Soon'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.Actualizado'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label(__('admin.Categoría')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.Activo')),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.Destacado')),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
