<?php

namespace App\Filament\Resources\MapsCalculator;

use App\Filament\Resources\MapsCalculator\PriceItemResource\Pages;
use App\Models\MapsCalculator\PriceItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PriceItemResource extends Resource
{
    protected static ?string $model = PriceItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';
    protected static ?string $navigationGroup = 'Mapscalculator';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Configuración de precio')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('mapscalculator.name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('slug')
                        ->label(__('mapscalculator.slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Select::make('category')
                        ->label(__('mapscalculator.category'))
                        ->options([
                            'resina_autonivelante' => 'Resina autonivelante',
                            'resina_decorativa' => 'Resina decorativa',
                            'resina_industrial' => 'Resina industrial',
                            'preparacion_superficie' => 'Preparación superficie',
                            'imprimacion' => 'Imprimación',
                            'zocalo_perimetral' => 'Zócalo perimetral',
                            'mano_obra' => 'Mano de obra',
                            'traslado' => 'Traslado',
                            'otro' => 'Otro',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('unit')
                        ->label(__('mapscalculator.unit'))
                        ->options([
                            'm2' => 'm²',
                            'ml' => 'ml',
                            'unidad' => 'ud',
                            'hora' => 'h',
                            'bulto' => 'bulto',
                        ])
                        ->required()
                        ->native(false)
                        ->default('m2'),
                    Forms\Components\TextInput::make('unit_price_m2')
                        ->label(__('mapscalculator.unit_price'))
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->required()
                        ->default(0),
                    Forms\Components\Textarea::make('description')
                        ->label(__('mapscalculator.description'))
                        ->rows(3)
                        ->columnSpan(2),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('mapscalculator.active'))
                        ->default(true),
                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('mapscalculator.sort_order'))
                        ->numeric()
                        ->default(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('mapscalculator.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label(__('mapscalculator.category'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'resina_autonivelante' => 'primary',
                        'resina_decorativa' => 'success',
                        'resina_industrial' => 'warning',
                        'preparacion_superficie' => 'info',
                        'imprimacion' => 'purple',
                        'zocalo_perimetral' => 'orange',
                        'mano_obra' => 'gray',
                        'traslado' => 'indigo',
                        'otro' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => PriceItem::categoryLabel($state)),
                Tables\Columns\TextColumn::make('unit')
                    ->label(__('mapscalculator.unit'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'm2' => 'm²',
                        'ml' => 'ml',
                        'unidad' => 'ud',
                        'hora' => 'h',
                        'bulto' => 'bulto',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('unit_price_m2')
                    ->label(__('mapscalculator.unit_price'))
                    ->money('EUR', locale: 'es_ES')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('mapscalculator.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('mapscalculator.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
->label(__('mapscalculator.category'))
                        ->options([
                        'resina_autonivelante' => 'Resina autonivelante',
                        'resina_decorativa' => 'Resina decorativa',
                        'resina_industrial' => 'Resina industrial',
                        'preparacion_superficie' => 'Preparación superficie',
                        'imprimacion' => 'Imprimación',
                        'zocalo_perimetral' => 'Zócalo perimetral',
                        'mano_obra' => 'Mano de obra',
                        'traslado' => 'Traslado',
                        'otro' => 'Otro',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('mapscalculator.active'))
                    ->placeholder('Todos')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriceItems::route('/'),
            'create' => Pages\CreatePriceItem::route('/create'),
            'edit' => Pages\EditPriceItem::route('/{record}/edit'),
        ];
    }
}