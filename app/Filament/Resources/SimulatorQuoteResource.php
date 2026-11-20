<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SimulatorQuoteResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\Simulator\Quote;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Response;

class SimulatorQuoteResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'simulator';

    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Simulador';

    protected static ?string $modelLabel = 'Cotización 3D';

    protected static ?string $pluralModelLabel = 'Cotizaciones 3D';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false; // Quotes are created from the frontend
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos del cliente')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nombre')
                            ->disabled(),

                        Forms\Components\TextInput::make('customer_email')
                            ->label('Email')
                            ->disabled(),

                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Teléfono')
                            ->disabled(),
                    ]),
                ]),

            Section::make('Detalles de la cotización')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('roomTemplate.name')
                            ->label('Ambiente')
                            ->disabled(),

                        Forms\Components\TextInput::make('finishVariant.name')
                            ->label('Acabado')
                            ->disabled(),

                        Forms\Components\TextInput::make('target_surface')
                            ->label('Superficie')
                            ->disabled(),
                    ]),

                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('area_m2')
                            ->label('Área')
                            ->suffix('m²')
                            ->disabled(),

                        Forms\Components\TextInput::make('price_per_m2_snapshot')
                            ->label('Precio/m²')
                            ->prefix('€')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Total')
                            ->prefix('€')
                            ->disabled()
                            ->extraAttributes(['class' => 'font-bold']),
                    ]),
                ]),

            Section::make('Gestión')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pending' => 'Pendiente',
                            'contacted' => 'Contactado',
                            'closed' => 'Cerrado',
                        ])
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('roomTemplate.name')
                    ->label('Ambiente'),

                Tables\Columns\TextColumn::make('finishVariant.name')
                    ->label('Acabado'),

                Tables\Columns\TextColumn::make('target_surface')
                    ->label('Superficie')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'floor' => 'Suelo',
                        'counter' => 'Cubierta',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('area_m2')
                    ->label('Área')
                    ->suffix(' m²'),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'contacted' => 'info',
                        'closed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'contacted' => 'Contactado',
                        'closed' => 'Cerrado',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'contacted' => 'Contactado',
                        'closed' => 'Cerrado',
                    ]),
                Tables\Filters\SelectFilter::make('roomTemplate')
                    ->relationship('roomTemplate', 'name')
                    ->label('Ambiente'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('markContacted')
                    ->label('Marcar contactado')
                    ->icon('heroicon-o-phone')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(fn (Quote $record) => $record->update(['status' => 'contacted']))
                    ->visible(fn (Quote $record): bool => $record->status === 'pending'),

                Tables\Actions\Action::make('exportCsv')
                    ->label('Exportar CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $quotes = Quote::with(['roomTemplate', 'finishVariant'])->get();

                        $csv = "ID,Cliente,Email,Teléfono,Ambiente,Acabado,Superficie,Área m²,Precio/m²,Total,Estado,Fecha\n";

                        foreach ($quotes as $q) {
                            $csv .= implode(',', [
                                $q->id,
                                '"' . str_replace('"', '""', $q->customer_name ?? '') . '"',
                                $q->customer_email ?? '',
                                $q->customer_phone ?? '',
                                '"' . ($q->roomTemplate->name ?? '') . '"',
                                '"' . ($q->finishVariant->name ?? '') . '"',
                                $q->target_surface,
                                $q->area_m2,
                                $q->price_per_m2_snapshot,
                                $q->total_price,
                                $q->status,
                                $q->created_at->format('d/m/Y H:i'),
                            ]) . "\n";
                        }

                        return Response::streamDownload(function () use ($csv) {
                            echo $csv;
                        }, 'cotizaciones-simulador-' . now()->format('Y-m-d') . '.csv');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSimulatorQuotes::route('/'),
            'edit' => Pages\EditSimulatorQuote::route('/{record}/edit'),
        ];
    }
}
