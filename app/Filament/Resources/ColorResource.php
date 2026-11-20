<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColorResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\Color;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ColorResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'products';

    protected static ?string $model = Color::class;
    protected static ?string $navigationIcon = 'heroicon-o-swatch';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Tienda');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Colores');
    }
    public static function getModelLabel(): string
    {
        return __('admin.Color');
    }
    public static function getPluralModelLabel(): string
    {
        return __('admin.Colores');
    }
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Color')
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

                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.Descripción'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.Activo'))
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('admin.Orden'))
                            ->numeric()
                            ->default(0),
                    ]),
                ]),

            Section::make('Imagen del Color')
                ->schema([
                    Grid::make(1)->schema([
                        Forms\Components\FileUpload::make('color_image')
                            ->label(__('admin.Imagen del color'))
                            ->image()
                            ->disk('public')
                            ->directory('colors')
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

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.Activo'))
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColors::route('/'),
            'create' => Pages\CreateColor::route('/create'),
            'edit' => Pages\EditColor::route('/{record}/edit'),
        ];
    }
}
