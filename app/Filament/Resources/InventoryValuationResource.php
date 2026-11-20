<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\InventoryValuationResource\Pages;
use App\Models\InventoryValuationBalance;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InventoryValuationResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'inventory';
    protected static ?string $model = InventoryValuationBalance::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.Inventario');
    }

    public static function getNavigationLabel(): string
    {
        return 'Valoración de existencias';
    }

    public static function getModelLabel(): string
    {
        return 'Saldo valorado';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Valoración de existencias';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.sku')->label(__('admin.Código SKU'))->searchable(),
                Tables\Columns\TextColumn::make('item.name')->label(__('admin.Artículo de inventario'))->searchable(),
                Tables\Columns\TextColumn::make('location.name')->label(__('admin.Ubicación'))->searchable(),
                Tables\Columns\TextColumn::make('quantity')->label(__('admin.Existencias'))->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('average_unit_cost')->label('Coste medio unitario (EUR)')->numeric(decimalPlaces: 4),
                Tables\Columns\TextColumn::make('inventory_value')->label('Valor total (EUR)')->money('EUR', locale: 'es_ES'),
                Tables\Columns\TextColumn::make('creator.name')->label('Saldo inicial por'),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['item', 'location', 'creator']);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListInventoryValuations::route('/')];
    }
}
