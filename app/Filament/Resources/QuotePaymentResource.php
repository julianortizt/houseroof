<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\QuotePaymentResource\Pages;
use App\Models\QuotePayment;
use App\Services\QuotePaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Stripe\Exception\ApiErrorException;

class QuotePaymentResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'quotes';
    protected static ?string $model = QuotePayment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finanzas (borradores internos)';
    protected static ?string $navigationLabel = 'Pagos de servicios';
    protected static ?string $modelLabel = 'Pago de cotización';
    protected static ?string $pluralModelLabel = 'Pagos de cotizaciones';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('payment_notice')
                ->label('Aviso')
                ->content('Los pagos y reembolsos son registros financieros inmutables; no se pueden crear, editar ni borrar desde esta pantalla.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['quote', 'refunds']))
            ->columns([
                Tables\Columns\TextColumn::make('quote.formatted_number')->label('Cotización')->searchable(),
                Tables\Columns\TextColumn::make('quote.name')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Concepto')
                    ->formatStateUsing(fn (string $state): string => $state === 'deposit' ? 'Anticipo' : 'Saldo final'),
                Tables\Columns\TextColumn::make('amount_cents')
                    ->label('Importe')
                    ->formatStateUsing(fn (int $state): string => number_format($state / 100, 2, ',', '.') . ' €')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'paid' => 'Pagado',
                        'failed' => 'Fallido',
                        'expired' => 'Expirado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('refunded_amount_cents')
                    ->label('Reembolsado')
                    ->formatStateUsing(fn (int $state): string => number_format($state / 100, 2, ',', '.') . ' €'),
                Tables\Columns\TextColumn::make('refunds.status')
                    ->label('Estado de reembolsos')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_at')->label('Pagado el')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('refund')
                    ->label('Reembolsar')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Importe a reembolsar (€)')
                            ->numeric()
                            ->minValue(0.01)
                            ->maxValue(fn (QuotePayment $record): float => $record->refundable_amount_cents / 100)
                            ->step(0.01)
                            ->default(fn (QuotePayment $record): float => $record->refundable_amount_cents / 100)
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalDescription('El reembolso se enviará a Stripe de inmediato. Comprueba el importe antes de confirmar.')
                    ->visible(fn (QuotePayment $record): bool => $record->status === 'paid' && $record->refundable_amount_cents > 0)
                    ->action(function (QuotePayment $record, array $data, QuotePaymentService $payments): void {
                        try {
                            $refund = $payments->refund($record, (int) round((float) $data['amount'] * 100));
                        } catch (ApiErrorException $exception) {
                            report($exception);
                            Notification::make()
                                ->title('Stripe no confirmó el reembolso')
                                ->body('El reembolso queda pendiente y puede reintentarse desde esta tabla.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title($refund->status === 'succeeded' ? 'Reembolso procesado' : 'Reembolso enviado a Stripe')
                            ->body('Estado: ' . $refund->status)
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('retry_pending_refunds')
                    ->label('Reintentar pendientes')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalDescription('Se consultará el reembolso existente o se reintentará con la misma clave de idempotencia. Los casos sin ID y con más de 23 horas requieren reconciliación manual en Stripe.')
                    ->visible(fn (QuotePayment $record): bool => $record->refunds()->where('status', 'pending')->exists())
                    ->action(function (QuotePayment $record, QuotePaymentService $payments): void {
                        try {
                            foreach ($record->refunds()->where('status', 'pending')->get() as $refund) {
                                $payments->retryRefund($refund);
                            }
                        } catch (ApiErrorException $exception) {
                            report($exception);
                            Notification::make()
                                ->title('Stripe no confirmó el reintento')
                                ->body('Los reembolsos siguen pendientes; no se ha creado una solicitud duplicada.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()->title('Reembolsos pendientes consultados')->success()->send();
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('quote', fn (Builder $query) => $query->whereNull('deleted_at'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotePayments::route('/'),
        ];
    }
}
