<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\InventorySupplierResource\Pages;
use App\Models\InventorySupplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventorySupplierResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'inventory';
    protected static ?string $model = InventorySupplier::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.Inventario');
    }

    public static function getNavigationLabel(): string
    {
        return 'Proveedores';
    }

    public static function getModelLabel(): string
    {
        return 'Proveedor';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Proveedores';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label(__('admin.Nombre'))->required()->maxLength(255),
            Forms\Components\TextInput::make('tax_id')->label('NIF / identificador fiscal')->maxLength(255),
            Forms\Components\TextInput::make('email')->label(__('admin.Email'))->email()->maxLength(255),
            Forms\Components\TextInput::make('phone')->label(__('admin.Teléfono'))->tel()->maxLength(255),
            Forms\Components\Textarea::make('address')->label(__('admin.Dirección'))->rows(3)->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->label(__('admin.Activo'))->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('admin.Nombre'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tax_id')->label('NIF / identificador fiscal')->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('admin.Email')),
                Tables\Columns\TextColumn::make('phone')->label(__('admin.Teléfono')),
                Tables\Columns\IconColumn::make('is_active')->label(__('admin.Activo'))->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (InventorySupplier $record): bool => ! $record->purchaseOrders()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventorySuppliers::route('/'),
            'create' => Pages\CreateInventorySupplier::route('/create'),
            'edit' => Pages\EditInventorySupplier::route('/{record}/edit'),
        ];
    }
}
