<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingAccountResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\AccountingAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Services\PlatformModules;

class AccountingAccountResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'accounting';
    protected static ?string $model = AccountingAccount::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Contabilidad (base interna)';
    }

    public static function getNavigationLabel(): string
    {
        return 'Cuentas internas';
    }

    public static function getModelLabel(): string
    {
        return 'Cuenta interna';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Cuentas internas';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('accounting_notice')
                ->label('Aviso')
                ->content('Estructura contable interna genérica. No sustituye el plan contable oficial ni asesoría profesional.'),
            Forms\Components\TextInput::make('code')->label('Código')->required()->maxLength(32)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(255),
            Forms\Components\Select::make('account_type')
                ->label('Tipo')
                ->options([
                    'asset' => 'Activo',
                    'liability' => 'Pasivo',
                    'equity' => 'Patrimonio neto',
                    'income' => 'Ingreso',
                    'expense' => 'Gasto',
                ])
                ->required()
                ->native(false),
            Forms\Components\Toggle::make('is_active')->label('Activa')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Código')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('account_type')->label('Tipo')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
            ])
            ->defaultSort('code')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (AccountingAccount $record): bool => ! $record->lines()->exists()),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('lines');
    }

    public static function canDelete(Model $record): bool
    {
        return app(PlatformModules::class)->enabled('accounting')
            && ! $record->lines()->exists()
            && parent::canDelete($record);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingAccounts::route('/'),
            'create' => Pages\CreateAccountingAccount::route('/create'),
            'edit' => Pages\EditAccountingAccount::route('/{record}/edit'),
        ];
    }
}
