<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SimulatorFinishResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\Simulator\Finish;
use App\Models\Simulator\FinishVariant;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SimulatorFinishResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'simulator';

    protected static ?string $model = Finish::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Simulador';

    protected static ?string $modelLabel = 'Acabado';

    protected static ?string $pluralModelLabel = 'Acabados';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Acabado')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'chips' => 'Chips de granito',
                                'solid' => 'Sólida',
                                'metallic' => 'Metálica',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('price_per_m2')
                            ->label('Precio por m²')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->suffix('/ m²'),

                        Forms\Components\TextInput::make('currency')
                            ->label('Moneda')
                            ->default('EUR')
                            ->maxLength(3),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                    ]),
                ]),

            Section::make('Variantes de color')
                ->schema([
                    Forms\Components\Repeater::make('variants')
                        ->relationship()
                        ->schema([
                            Grid::make(3)->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('base_color_hex')
                                    ->label('Color base (hex)')
                                    ->placeholder('#RRGGBB'),

                                Forms\Components\TextInput::make('chip_density')
                                    ->label('Densidad chips (20-90)')
                                    ->numeric()
                                    ->minValue(20)
                                    ->maxValue(90)
                                    ->visible(fn (Get $get) => $get('..type') === 'chips'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Orden')
                                    ->numeric()
                                    ->default(0),
                            ]),

                            Grid::make(3)->schema([
                                Forms\Components\FileUpload::make('texture_color_path')
                                    ->label('Textura color (albedo)')
                                    ->disk('public')
                                    ->directory('simulator/textures')
                                    ->image()
                                    ->imageEditor()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120),

                                Forms\Components\FileUpload::make('texture_normal_path')
                                    ->label('Normal map')
                                    ->disk('public')
                                    ->directory('simulator/textures')
                                    ->image()
                                    ->imageEditor()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120),

                                Forms\Components\FileUpload::make('texture_roughness_path')
                                    ->label('Roughness map')
                                    ->disk('public')
                                    ->directory('simulator/textures')
                                    ->image()
                                    ->imageEditor()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120),
                            ]),

                            Grid::make(1)->schema([
                                Forms\Components\FileUpload::make('preview_image_path')
                                    ->label('Preview (thumbnail)')
                                    ->disk('public')
                                    ->directory('simulator/previews')
                                    ->image()
                                    ->maxSize(2048),
                            ]),
                        ])
                        ->columns(1)
                        ->defaultItems(0)
                        ->addActionLabel('Añadir variante'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'chips' => 'warning',
                        'solid' => 'info',
                        'metallic' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'chips' => 'Chips',
                        'solid' => 'Sólida',
                        'metallic' => 'Metálica',
                    }),

                Tables\Columns\TextColumn::make('price_per_m2')
                    ->label('Precio/m²')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variantes'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'chips' => 'Chips',
                        'solid' => 'Sólida',
                        'metallic' => 'Metálica',
                    ]),
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
            'index' => Pages\ListSimulatorFinishes::route('/'),
            'create' => Pages\CreateSimulatorFinish::route('/create'),
            'edit' => Pages\EditSimulatorFinish::route('/{record}/edit'),
        ];
    }
}
