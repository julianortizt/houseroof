<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovementResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'inventory';
    protected static ?string $model = InventoryMovement::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Inventario');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.Movimientos de existencias');
    }

    public static function getModelLabel(): string
    {
        return __('admin.Movimiento de existencias');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.Movimientos de existencias');
    }

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.Registrar movimiento de existencias'))
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('inventory_item_id')
                        ->label(__('admin.Artículo de inventario'))
                        ->options(InventoryItem::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('inventory_location_id')
                        ->label(__('admin.Ubicación'))
                        ->options(InventoryLocation::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('movement_type')
                        ->label(__('admin.Tipo de movimiento'))
                        ->options([
                            'receipt' => __('admin.Entrada'),
                            'issue' => __('admin.Salida'),
                            'adjustment' => __('admin.Ajuste'),
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('quantity')
                        ->label(__('admin.Cantidad del movimiento'))
                        ->numeric()
                        ->step(0.001)
                        ->required()
                        ->helperText(__('admin.Las entradas y salidas usan una cantidad positiva; los ajustes usan una diferencia con signo.')),
                    Forms\Components\TextInput::make('unit_cost')
                        ->label('Coste unitario de entrada (EUR)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.0001)
                        ->visible(fn (Forms\Get $get): bool => in_array($get('movement_type'), ['receipt', 'adjustment'], true))
                        ->helperText('Obligatorio para entradas con coste medio ponderado activo.'),
                    Forms\Components\TextInput::make('reference')
                        ->label(__('admin.Referencia'))
                        ->maxLength(255),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('admin.Notas'))
                        ->rows(3)
                        ->maxLength(2000)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label(__('admin.Creado'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('item.sku')->label(__('admin.Código SKU'))->searchable(),
                Tables\Columns\TextColumn::make('item.name')->label(__('admin.Artículo de inventario'))->searchable(),
                Tables\Columns\TextColumn::make('location.name')->label(__('admin.Ubicación'))->searchable(),
                Tables\Columns\TextColumn::make('workOrder.id')
                    ->label(__('mapscalculator.work_order'))
                    ->formatStateUsing(fn (?string $state): string => $state ? '#' . $state : '—')
                    ->url(fn (InventoryMovement $record): ?string => $record->work_order_id
                        && app(\App\Services\PlatformModules::class)->enabled('work_orders')
                        ? route('mapscalculator.work-order.show', $record->work_order_id)
                        : null)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'receipt' => 'success',
                        'issue' => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('quantity_change')
                    ->label(__('admin.Cantidad del movimiento'))
                    ->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label(__('admin.Saldo posterior'))
                    ->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('unit_cost')->label('Coste unitario (EUR)')->numeric(decimalPlaces: 4)->toggleable(),
                Tables\Columns\TextColumn::make('total_cost')->label('Coste del movimiento (EUR)')->money('EUR', locale: 'es_ES')->toggleable(),
                Tables\Columns\TextColumn::make('reference')->label(__('admin.Referencia'))->searchable(),
                Tables\Columns\TextColumn::make('creator.name')->label(__('admin.Registrado por')),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['item', 'location', 'creator', 'workOrder']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
            'create' => Pages\CreateInventoryMovement::route('/create'),
        ];
    }
}
