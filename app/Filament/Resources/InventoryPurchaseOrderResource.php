<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\InventoryPurchaseOrderResource\Pages;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryPurchaseOrder;
use App\Models\InventoryPurchaseOrderItem;
use App\Models\InventorySupplier;
use App\Services\InventoryProcurementService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InventoryPurchaseOrderResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'inventory';
    protected static ?string $model = InventoryPurchaseOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.Inventario');
    }

    public static function getNavigationLabel(): string
    {
        return 'Pedidos de compra';
    }

    public static function getModelLabel(): string
    {
        return 'Pedido de compra';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pedidos de compra';
    }

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record) && $record->status === 'draft';
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pedido')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('number')->label('Referencia')->disabled()->dehydrated(false),
                    Forms\Components\Select::make('supplier_id')
                        ->label('Proveedor')
                        ->options(InventorySupplier::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->rule(Rule::exists('inventory_suppliers', 'id')->where('is_active', true)),
                    Forms\Components\Textarea::make('notes')->label(__('admin.Notas'))->rows(2)->columnSpanFull(),
                ]),
            Forms\Components\Repeater::make('items')
                ->relationship()
                ->label('Líneas solicitadas')
                ->minItems(1)
                ->schema([
                    Forms\Components\Select::make('inventory_item_id')
                        ->label('Artículo')
                        ->options(InventoryItem::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->rule(Rule::exists('inventory_items', 'id')->where('is_active', true)),
                    Forms\Components\Select::make('inventory_location_id')
                        ->label('Recibir en')
                        ->options(InventoryLocation::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->rule(Rule::exists('inventory_locations', 'id')->where('is_active', true)),
                    Forms\Components\TextInput::make('quantity')
                        ->label('Cantidad pedida')
                        ->numeric()
                        ->minValue(0.001)
                        ->step(0.001)
                        ->required(),
                    Forms\Components\TextInput::make('unit_cost')
                        ->label('Coste unitario de referencia')
                        ->helperText('Dato informativo; no calcula valoración ni asientos.')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.0001),
                ])
                ->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('Referencia')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Proveedor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'ordered' => 'info',
                        'partially_received' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Borrador',
                        'ordered' => 'Enviado',
                        'partially_received' => 'Recibido parcialmente',
                        'received' => 'Recibido',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('items_count')->label('Líneas')->counts('items'),
                Tables\Columns\TextColumn::make('ordered_at')->label('Fecha de pedido')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('creator.name')->label('Creado por'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('mark_ordered')
                    ->label('Marcar enviado')
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->visible(fn (InventoryPurchaseOrder $record): bool => $record->status === 'draft')
                    ->action(fn (InventoryPurchaseOrder $record) => app(InventoryProcurementService::class)->markOrdered($record)),
                Tables\Actions\Action::make('receive')
                    ->label('Registrar recepción')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->visible(fn (InventoryPurchaseOrder $record): bool => in_array($record->status, ['ordered', 'partially_received'], true))
                    ->form(fn (InventoryPurchaseOrder $record): array => [
                        Forms\Components\Select::make('purchase_order_item_id')
                            ->label('Línea pendiente')
                            ->options($record->items()
                                ->with('item')
                                ->get()
                                ->filter(fn (InventoryPurchaseOrderItem $line): bool => (float) $line->outstanding_quantity > 0)
                                ->mapWithKeys(fn (InventoryPurchaseOrderItem $line): array => [
                                    $line->id => $line->item->name . ' · pendiente ' . $line->outstanding_quantity,
                                ]))
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad recibida')
                            ->numeric()
                            ->minValue(0.001)
                            ->step(0.001)
                            ->required(),
                        Forms\Components\TextInput::make('unit_cost')
                            ->label('Coste unitario final (opcional)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.0001),
                        Forms\Components\TextInput::make('reference')->label('Referencia de albarán')->maxLength(255),
                        Forms\Components\Textarea::make('notes')->label(__('admin.Notas'))->maxLength(2000),
                    ])
                    ->action(function (InventoryPurchaseOrder $record, array $data): void {
                        app(InventoryProcurementService::class)->receive($record, $data);
                        Notification::make()->title('Recepción registrada y existencias actualizadas')->success()->send();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar pedido')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (InventoryPurchaseOrder $record): bool => in_array($record->status, ['draft', 'ordered'], true)
                        && ! $record->receipts()->exists())
                    ->action(fn (InventoryPurchaseOrder $record) => app(InventoryProcurementService::class)->cancel($record)),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['supplier', 'creator'])->withCount('items');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryPurchaseOrders::route('/'),
            'create' => Pages\CreateInventoryPurchaseOrder::route('/create'),
            'edit' => Pages\EditInventoryPurchaseOrder::route('/{record}/edit'),
        ];
    }

    public static function generateNumber(): string
    {
        return 'OC-' . now()->format('Ymd') . '-' . Str::upper((string) Str::ulid());
    }
}
