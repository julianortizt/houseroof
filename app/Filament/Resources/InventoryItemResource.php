<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\InventoryItemResource\Pages;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Services\InventoryValuationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryItemResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'inventory';
    protected static ?string $model = InventoryItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Inventario');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.Artículos de inventario');
    }

    public static function getModelLabel(): string
    {
        return __('admin.Artículo de inventario');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.Artículos de inventario');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.Artículo de inventario'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('sku')
                        ->label(__('admin.Código SKU'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.Nombre'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('unit')
                        ->label(__('admin.Unidad'))
                        ->required()
                        ->maxLength(24)
                        ->default('unit'),
                    Forms\Components\TextInput::make('reorder_level')
                        ->label(__('admin.Nivel de reposición'))
                        ->numeric()
                        ->minValue(0)
                        ->step(0.001)
                        ->default(0),
                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.Descripción'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.Activo'))
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('movements_sum_quantity_change')
                    ->label(__('admin.Existencias'))
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                Tables\Columns\TextColumn::make('reorder_level')
                    ->numeric(decimalPlaces: 3)
                    ->label(__('admin.Nivel de reposición')),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\Action::make('opening_valuation')
                    ->label('Saldo inicial valorado')
                    ->icon('heroicon-o-scale')
                    ->visible(fn (InventoryItem $record): bool => ! app(InventoryValuationService::class)->isCostingEnabled()
                        && (float) $record->movements()->sum('quantity_change') > 0)
                    ->form([
                        Forms\Components\Select::make('inventory_location_id')
                            ->label(__('admin.Ubicación'))
                            ->options(InventoryLocation::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Saldo físico de apertura')
                            ->helperText('Debe coincidir con el saldo actual del libro para la ubicación seleccionada.')
                            ->numeric()
                            ->minValue(0.001)
                            ->step(0.001)
                            ->required(),
                        Forms\Components\TextInput::make('unit_cost')
                            ->label('Coste unitario medio (EUR)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.0001)
                            ->required(),
                    ])
                    ->action(function (InventoryItem $record, array $data): void {
                        app(InventoryValuationService::class)->setOpeningBalance($record, $data);
                        Notification::make()->title('Saldo inicial valorado')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withSum('movements', 'quantity_change');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryItems::route('/'),
            'create' => Pages\CreateInventoryItem::route('/create'),
            'edit' => Pages\EditInventoryItem::route('/{record}/edit'),
        ];
    }
}
