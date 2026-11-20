<?php

namespace App\Filament\Resources\MapsCalculator;

use App\Filament\Resources\MapsCalculator\ClientResource\Pages;
use App\Models\MapsCalculator\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * CRM básico del módulo (Módulo 3 del prompt): alta, edición, búsqueda y
 * fichas de cliente, con acceso directo a su portal privado.
 *
 * No existía ningún recurso de clientes: los clientes sólo se podían crear
 * por consola o por la semilla, así que el módulo no se podía usar de punta a
 * punta sin tocar la base de datos a mano.
 */
class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Mapscalculator';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user !== null
            && ($user->isMapscalculatorAdmin() || $user->isComercial() || $user->isTecnico());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('mapscalculator.client'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('mapscalculator.name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('company')
                        ->label(__('mapscalculator.company'))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label(__('mapscalculator.email'))
                        ->email()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->label(__('mapscalculator.phone'))
                        ->tel()
                        ->maxLength(50),
                    Forms\Components\TextInput::make('nif')
                        ->label(__('mapscalculator.nif'))
                        ->maxLength(30),
                    Forms\Components\Textarea::make('address')
                        ->label(__('mapscalculator.address'))
                        ->rows(2)
                        ->columnSpan(2),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('mapscalculator.notes'))
                        ->rows(3)
                        ->columnSpan(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('mapscalculator.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Client $record): ?string => $record->company),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('mapscalculator.email'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('mapscalculator.phone'))
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('projects_count')
                    ->label(__('mapscalculator.projects'))
                    ->counts('projects')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\IconColumn::make('portal_token')
                    ->label(__('mapscalculator.client_portal'))
                    ->boolean()
                    ->getStateUsing(fn (Client $record): bool => filled($record->portal_token))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('mapscalculator.created'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->searchPlaceholder(__('mapscalculator.search_client'))
            ->filters([
                Tables\Filters\Filter::make('with_projects')
                    ->label(__('mapscalculator.with_projects'))
                    ->query(fn ($query) => $query->has('projects')),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('ficha')
                    ->label(__('mapscalculator.client_sheet'))
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('gray')
                    ->url(fn (Client $record): string => route('mapscalculator.client.show', $record)),
                Tables\Actions\Action::make('portal')
                    ->label(__('mapscalculator.client_portal'))
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->action(function (Client $record): void {
                        $record->ensurePortalToken();
                    })
                    ->successNotificationTitle(__('mapscalculator.portal_ready')),
                Tables\Actions\Action::make('open_portal')
                    ->label(__('mapscalculator.open_portal'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->visible(fn (Client $record): bool => filled($record->portal_token))
                    ->url(fn (Client $record): string => $record->portal_url, shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'company', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            __('mapscalculator.email') => $record->email,
            __('mapscalculator.phone') => $record->phone,
        ]);
    }
}
