<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\OrderStockResolutionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class OrderResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'products';

    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Pedidos y Cotizaciones');
    }
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información del pedido')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('order_number')
                        ->label(__('admin.Número de pedido'))
                        ->disabled(),
                    Forms\Components\Select::make('status')
                        ->label(__('admin.Estado'))
                        ->options(fn (?Order $record): array => [
                            'pending' => 'Pendiente',
                            'processing' => 'En proceso',
                            'shipped' => 'Enviado',
                            'delivered' => 'Entregado',
                            'cancelled' => 'Cancelado',
                            ...($record?->status === 'stock_review' ? ['stock_review' => 'Revisión de existencias'] : []),
                        ])
                        ->disabled(fn (?Order $record): bool => $record?->status === 'stock_review')
                        ->required(),
                    Forms\Components\Select::make('payment_status')
                        ->label(__('admin.Estado de pago'))
                        ->options([
                            'unpaid' => 'No pagado',
                            'paid' => 'Pagado',
                            'refunded' => 'Reembolsado',
                            'failed' => 'Pago fallido',
                        ])
                        ->disabled()
                        ->dehydrated(false)
                        ->required(),
                ]),

            Forms\Components\Section::make('Datos del cliente')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->label(__('admin.Nombre'))
                        ->required(),
                    Forms\Components\TextInput::make('customer_email')
                        ->label(__('admin.Email'))
                        ->email()
                        ->required(),
                    Forms\Components\TextInput::make('customer_phone')
                        ->label(__('admin.Teléfono')),
                    Forms\Components\TextInput::make('company_name')
                        ->label(__('admin.Empresa')),
                    Forms\Components\TextInput::make('tax_id')
                        ->label(__('admin.NIF/CIF')),
                ]),

            Forms\Components\Section::make('Dirección de envío')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('shipping_address_line_1')
                        ->label(__('admin.Dirección'))
                        ->required(),
                    Forms\Components\TextInput::make('shipping_address_line_2')
                        ->label(__('admin.Dirección (línea 2)')),
                    Forms\Components\TextInput::make('shipping_city')
                        ->label(__('admin.Ciudad'))
                        ->required(),
                    Forms\Components\TextInput::make('shipping_state')
                        ->label(__('admin.Provincia')),
                    Forms\Components\TextInput::make('shipping_postal_code')
                        ->label(__('admin.Código postal'))
                        ->required(),
                    Forms\Components\TextInput::make('shipping_country')
                        ->label(__('admin.País'))
                        ->default('ES'),
                ]),

            Forms\Components\Section::make('Importes')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->label(__('admin.Subtotal (€)'))
                        ->numeric()
                        ->prefix('€'),
                    Forms\Components\TextInput::make('shipping_cost')
                        ->label(__('admin.Envío (€)'))
                        ->numeric()
                        ->prefix('€'),
                    Forms\Components\TextInput::make('tax')
                        ->label(__('admin.IVA (€)'))
                        ->numeric()
                        ->prefix('€'),
                    Forms\Components\TextInput::make('total')
                        ->label(__('admin.Total (€)'))
                        ->numeric()
                        ->prefix('€')
                        ->required(),
                ]),

            Forms\Components\Section::make('Artículos del pedido')
                ->relationship('items')
                ->schema([
                    Forms\Components\TextInput::make('product_name')
                        ->label(__('admin.Producto'))
                        ->required(),
                    Forms\Components\TextInput::make('variant_name')
                        ->label(__('admin.Variante')),
                    Forms\Components\TextInput::make('quantity')
                        ->label(__('admin.Cantidad'))
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('unit_price')
                        ->label(__('admin.Precio unitario (€)'))
                        ->numeric()
                        ->prefix('€'),
                    Forms\Components\TextInput::make('total_price')
                        ->label(__('admin.Total (€)'))
                        ->numeric()
                        ->prefix('€'),
                ])
                ->columns(5)
                ->collapsible(),

            Forms\Components\Section::make('Notas')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label(__('admin.Notas'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label(__('admin.Pedido'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label(__('admin.Cliente'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->label(__('admin.Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('admin.Total'))
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', '.') . ' €')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.Estado'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'stock_review' => 'danger',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('admin.Pago'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'refunded' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('stock_review_refund_status')
                    ->label('Reembolso por falta de stock')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'succeeded' => 'Reembolsado',
                        'failed' => 'Fallido; revisar Stripe',
                        default => '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'succeeded' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.Fecha'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'stock_review' => 'Revisión de existencias',
                        'pending' => 'Pendiente',
                        'processing' => 'En proceso',
                        'shipped' => 'Enviado',
                        'delivered' => 'Entregado',
                        'cancelled' => 'Cancelado',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label(__('admin.Estado de pago'))
                    ->options([
                        'unpaid' => 'No pagado',
                        'paid' => 'Pagado',
                        'refunded' => 'Reembolsado',
                        'failed' => 'Pago fallido',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('fulfill_after_restock')
                    ->label('Resolver con existencias')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Comprueba que se haya repuesto el stock. Se descontarán las cantidades del pedido y pasará a preparación.')
                    ->visible(fn (Order $record): bool => $record->status === 'stock_review' && $record->payment_status === 'paid')
                    ->action(function (Order $record, OrderStockResolutionService $resolution): void {
                        try {
                            $resolution->fulfillAfterRestock($record);
                        } catch (ValidationException $exception) {
                            $messages = collect($exception->errors())->flatten()->implode(' ');
                            Notification::make()->title('No se pudo resolver el pedido')->body($messages)->danger()->send();

                            return;
                        }

                        Notification::make()->title('Pedido resuelto y existencias descontadas')->success()->send();
                    }),
                Tables\Actions\Action::make('refund_stock_review')
                    ->label(fn (Order $record): string => $record->stock_review_refund_status === 'pending'
                        ? 'Comprobar / reintentar reembolso'
                        : 'Reembolsar y cancelar')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Se solicitará a Stripe el reembolso completo del pago. La orden solo se cancelará y marcará reembolsada cuando Stripe lo confirme.')
                    ->visible(fn (Order $record): bool => $record->status === 'stock_review'
                        && $record->payment_status === 'paid'
                        && filled($record->stripe_payment_intent_id)
                        && $record->stock_review_refund_status !== 'failed'
                        && $record->stock_review_refund_status !== 'succeeded')
                    ->action(function (Order $record, OrderStockResolutionService $resolution): void {
                        try {
                            $resolved = $resolution->refundStockReview($record);
                        } catch (ValidationException $exception) {
                            $messages = collect($exception->errors())->flatten()->implode(' ');
                            Notification::make()->title('No se pudo reembolsar el pedido')->body($messages)->danger()->send();

                            return;
                        } catch (ApiErrorException $exception) {
                            report($exception);
                            Notification::make()
                                ->title('Stripe no confirmó el reembolso')
                                ->body('El caso queda pendiente. Reintenta dentro de 23 horas o concilia el estado en Stripe; no se ha marcado como reembolsado.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title($resolved->payment_status === 'refunded' ? 'Pedido reembolsado y cancelado' : 'Reembolso enviado a Stripe')
                            ->body($resolved->payment_status === 'refunded'
                                ? 'Stripe confirmó el reembolso completo.'
                                : 'El pedido sigue en revisión hasta que Stripe confirme el reembolso.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
