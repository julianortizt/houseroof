<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Services\InvoiceDraftService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class InvoiceResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'invoicing';
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Finanzas (borradores internos)';
    }

    public static function getNavigationLabel(): string
    {
        return 'Borradores de factura';
    }

    public static function getModelLabel(): string
    {
        return 'Borrador interno';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Borradores internos';
    }

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
            Forms\Components\Placeholder::make('draft_notice')
                ->label('Aviso')
                ->content('Borrador interno no fiscal. No es una factura emitida ni un documento válido para declarar impuestos o exigir el pago.'),
            Forms\Components\TextInput::make('display_number')
                ->label('Referencia')
                ->formatStateUsing(fn (?string $state, Invoice $record): string => $record->display_number)
                ->disabled(),
            Forms\Components\TextInput::make('status')->label('Estado')->disabled(),
            Forms\Components\TextInput::make('customer_name')->label('Cliente')->disabled(),
            Forms\Components\TextInput::make('customer_email')->label('Correo')->disabled(),
            Forms\Components\TextInput::make('subtotal')->label('Subtotal (borrador)')->disabled(),
            Forms\Components\TextInput::make('tax_percent')->label('Impuesto heredado de cotización (%)')->disabled(),
            Forms\Components\TextInput::make('tax_amount')->label('Impuesto (borrador)')->disabled(),
            Forms\Components\TextInput::make('total')->label('Total (borrador)')->disabled(),
            Forms\Components\Repeater::make('items')
                ->relationship()
                ->label('Líneas copiadas de la cotización')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Concepto')->disabled(),
                    Forms\Components\TextInput::make('quantity')->label('Cantidad')->disabled(),
                    Forms\Components\TextInput::make('unit_price')->label('Precio unitario')->disabled(),
                    Forms\Components\TextInput::make('total_price')->label('Total de línea')->disabled(),
                ])
                ->columns(4)
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_number')->label('Referencia')->searchable(query: fn (Builder $query, string $search) => $query->where('id', 'like', "%{$search}%")),
                Tables\Columns\TextColumn::make('customer_name')->label('Cliente')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer_email')->label('Correo')->searchable(),
                Tables\Columns\TextColumn::make('quote.formatted_number')->label('Cotización')->toggleable(),
                Tables\Columns\TextColumn::make('total')->label('Total (borrador)')->money('EUR', locale: 'es_ES')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Creado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('issue')
                    ->label('Emitir factura')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Invoice $record): bool => $record->status === 'draft')
                    ->action(function (Invoice $record) {
                        app(InvoiceDraftService::class)->issue($record);

                        Notification::make()
                            ->title('Factura emitida')
                            ->body('Se contabilizó el asiento y la factura quedó activa en el sistema.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['quote', 'items']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
