<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'products';

    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Tienda');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Categorías');
    }
    public static function getModelLabel(): string
    {
        return __('admin.Categoría');
    }
    public static function getPluralModelLabel(): string
    {
        return __('admin.Categorías');
    }
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información de la Categoría')
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
                            ->dehydrateStateUsing(fn (string $state) => Str::slug($state))
                            ->helperText(__('admin.Se genera automáticamente del nombre')),

                        Forms\Components\Select::make('parent_id')
                            ->label(__('admin.Categoría padre'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder(__('admin.Sin categoría padre')),

                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('admin.Orden'))
                            ->numeric()
                            ->default(0),

                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.Descripción'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.Activa'))
                            ->default(true),
                    ]),
                ]),

            Section::make('Imagen de Categoría')
                ->schema([
                    Grid::make(1)->schema([
                        Forms\Components\FileUpload::make('category_image')
                            ->label(__('admin.Imagen de categoría'))
                            ->image()
                            ->disk('public')
                            ->directory('categories')
                            ->imagePreviewHeight('150')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.Nombre'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('admin.Categoría padre'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('admin.Productos'))
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.Activa'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.Orden'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
