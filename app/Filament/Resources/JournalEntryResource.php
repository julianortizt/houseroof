<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\AccountingAccount;
use App\Models\JournalEntry;
use App\Services\AccountingJournalService;
use App\Services\PlatformModules;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class JournalEntryResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'accounting';
    protected static ?string $model = JournalEntry::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Contabilidad (base interna)';
    }

    public static function getNavigationLabel(): string
    {
        return 'Asientos internos';
    }

    public static function getModelLabel(): string
    {
        return 'Asiento interno';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Asientos internos';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('journal_notice')
                ->label('Aviso')
                ->content('Diario interno de partida doble. No es un libro oficial ni calcula impuestos; contabilizar requiere que el asiento cuadre exactamente.'),
            Forms\Components\DatePicker::make('entry_date')
                ->label('Fecha')
                ->required()
                ->default(today()),
            Forms\Components\TextInput::make('description')
                ->label('Descripción')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Repeater::make('lines')
                ->relationship()
                ->label('Apuntes')
                ->minItems(2)
                ->schema([
                    Forms\Components\Select::make('accounting_account_id')
                        ->label('Cuenta')
                        ->options(fn (): array => AccountingAccount::query()
                            ->where('is_active', true)
                            ->orderBy('code')
                            ->get()
                            ->mapWithKeys(fn (AccountingAccount $account): array => [
                                $account->id => "{$account->code} — {$account->name}",
                            ])
                            ->all())
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('debit')
                        ->label('Debe')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->default(0)
                        ->required(),
                    Forms\Components\TextInput::make('credit')
                        ->label('Haber')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->default(0)
                        ->required(),
                    Forms\Components\TextInput::make('description')
                        ->label('Concepto del apunte')
                        ->maxLength(255),
                ])
                ->columns(4)
                ->defaultItems(2)
                ->addActionLabel('Añadir apunte')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entry_number')->label('Referencia')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('entry_date')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Descripción')->searchable()->limit(60),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'posted' => 'success',
                        'reversed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('lines_sum_debit')->label('Debe')->money('EUR', locale: 'es_ES'),
                Tables\Columns\TextColumn::make('lines_sum_credit')->label('Haber')->money('EUR', locale: 'es_ES'),
                Tables\Columns\TextColumn::make('creator.name')->label('Creado por'),
            ])
            ->defaultSort('entry_date', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn (JournalEntry $record): bool => static::canEdit($record)),
                Tables\Actions\Action::make('post')
                    ->label('Contabilizar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('El asiento quedará bloqueado. Para corregirlo deberá registrar un reverso.')
                    ->visible(fn (JournalEntry $record): bool => $record->status === 'draft')
                    ->action(function (JournalEntry $record): void {
                        app(AccountingJournalService::class)->post($record);
                        Notification::make()->title('Asiento contabilizado')->success()->send();
                    }),
                Tables\Actions\Action::make('reverse')
                    ->label('Revertir')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Motivo')->required()->maxLength(1000),
                    ])
                    ->requiresConfirmation()
                    ->visible(fn (JournalEntry $record): bool => $record->status === 'posted' && $record->reversed_by_entry_id === null)
                    ->action(function (JournalEntry $record, array $data): void {
                        app(AccountingJournalService::class)->reverse($record, $data['reason']);
                        Notification::make()->title('Asiento revertido')->success()->send();
                    }),
            ]);
    }

    public static function canEdit(Model $record): bool
    {
        return app(PlatformModules::class)->enabled('accounting')
            && $record->status === 'draft'
            && parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        return app(PlatformModules::class)->enabled('accounting')
            && $record->status === 'draft'
            && parent::canDelete($record);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('creator')
            ->withSum('lines', 'debit')
            ->withSum('lines', 'credit');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
            'view' => Pages\ViewJournalEntry::route('/{record}'),
        ];
    }
}
