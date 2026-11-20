<?php

namespace App\Filament\Resources\MapsCalculator;

use App\Filament\Resources\MapsCalculator\WorkOrderResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\MapsCalculator\Project;
use App\Models\MapsCalculator\WorkOrder;
use App\Models\Quote;
use App\Models\User;
use App\Services\PlatformModules;
use App\Services\InventoryValuationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkOrderResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'work_orders';

    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Mapscalculator';
    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return app(PlatformModules::class)->enabled(static::$requiredPlatformModule)
            && auth()->check()
            && (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Orden de trabajo')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label(__('mapscalculator.project'))
                        ->options(Project::with('client')->get()->mapWithKeys(fn ($p) => [$p->id => $p->name . ' - ' . $p->client->name]))
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('quote_id')
                        ->label('Cotización aprobada')
                        ->options(fn (Forms\Get $get): array => Quote::query()
                            ->where('status', 'aprobado')
                            ->when($get('project_id'), fn ($query, $projectId) => $query->where('project_id', $projectId))
                            ->whereDoesntHave('workOrder')
                            ->orderByDesc('id')
                            ->get()
                            ->mapWithKeys(fn (Quote $quote): array => [
                                $quote->id => $quote->formatted_number . ' — ' . $quote->name,
                            ])
                            ->all())
                        ->searchable()
                        ->required()
                        ->visible(fn (?WorkOrder $record): bool => $record === null),
                    Forms\Components\Select::make('status')
                        ->label(__('mapscalculator.status'))
                        ->options([
                            'agendada' => 'Agendada',
                            'en_preparacion' => 'En preparación',
                            'en_ejecucion' => 'En ejecución',
                            'finalizada' => 'Finalizada',
                            'entregada' => 'Entregada',
                            'cancelada' => 'Cancelada',
                        ])
                        ->required()
                        ->native(false)
                        ->default('agendada')
                        ->disabled(fn (?WorkOrder $record): bool => $record !== null)
                        ->dehydrated(fn (?WorkOrder $record): bool => $record === null),
                    Forms\Components\DatePicker::make('scheduled_start')
                        ->label(__('mapscalculator.scheduled_start'))
                        ->native(false),
                    Forms\Components\DatePicker::make('scheduled_end')
                        ->label(__('mapscalculator.scheduled_end'))
                        ->native(false),
                    Forms\Components\DatePicker::make('actual_start')
                        ->label(__('mapscalculator.actual_start'))
                        ->native(false),
                    Forms\Components\DatePicker::make('actual_end')
                        ->label(__('mapscalculator.actual_end'))
                        ->native(false),
                    Forms\Components\TextInput::make('labor_cost')
                        ->label('Coste de mano de obra (EUR)')
                        ->numeric()
                        ->minValue(0)
                        ->step('0.01')
                        ->default(0),
                    Forms\Components\TextInput::make('other_cost')
                        ->label('Otros costes (EUR)')
                        ->numeric()
                        ->minValue(0)
                        ->step('0.01')
                        ->default(0),
                    Forms\Components\Select::make('assigned_to')
                        ->label(__('mapscalculator.assigned_to'))
                        ->options(User::whereHas('roles', fn ($q) => $q->where('name', '!=', 'cliente'))->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('project.name')
                    ->label(__('mapscalculator.project'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('project.client.name')
                    ->label(__('mapscalculator.client'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('mapscalculator.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'agendada' => 'info',
                        'en_preparacion' => 'warning',
                        'en_ejecucion' => 'primary',
                        'finalizada' => 'success',
                        'entregada' => 'gray',
                        'cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'agendada' => 'Agendada',
                        'en_preparacion' => 'En preparación',
                        'en_ejecucion' => 'En ejecución',
                        'finalizada' => 'Finalizada',
                        'entregada' => 'Entregada',
                        'cancelada' => 'Cancelada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('scheduled_start')
                    ->label(__('mapscalculator.scheduled_start'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('scheduled_end')
                    ->label(__('mapscalculator.scheduled_end'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assigned_to.name')
                    ->label(__('mapscalculator.assigned_to'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('inventory_movements_sum_total_cost')
                    ->label('Coste de materiales (EUR)')
                    ->money('EUR', locale: 'es_ES')
                    ->visible(fn (): bool => app(PlatformModules::class)->enabled('inventory')
                        && app(InventoryValuationService::class)->isCostingEnabled())
                    ->toggleable(),
                Tables\Columns\TextColumn::make('labor_cost')
                    ->label('Mano de obra (EUR)')
                    ->money('EUR', locale: 'es_ES')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('other_cost')
                    ->label('Otros costes (EUR)')
                    ->money('EUR', locale: 'es_ES')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('mapscalculator.status'))
                    ->options([
                        'agendada' => 'Agendada',
                        'en_preparacion' => 'En preparación',
                        'en_ejecucion' => 'En ejecución',
                        'finalizada' => 'Finalizada',
                        'entregada' => 'Entregada',
                        'cancelada' => 'Cancelada',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('transition_status')
                    ->label('Cambiar estado')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label(__('mapscalculator.status'))
                            ->options(fn (WorkOrder $record): array => array_filter(
                                [
                                    'agendada' => 'Agendada',
                                    'en_preparacion' => 'En preparación',
                                    'en_ejecucion' => 'En ejecución',
                                    'finalizada' => 'Finalizada',
                                    'entregada' => 'Entregada',
                                    'cancelada' => 'Cancelada',
                                ],
                                fn (string $label, string $status): bool => $record->canTransitionTo($status),
                                ARRAY_FILTER_USE_BOTH,
                            ))
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (WorkOrder $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $lockedOrder = WorkOrder::query()->lockForUpdate()->findOrFail($record->id);
                            if (! $lockedOrder->transitionTo($data['status'])) {
                                throw ValidationException::withMessages([
                                    'status' => __('mapscalculator.invalid_work_order_transition'),
                                ]);
                            }
                            $lockedOrder->project()->update(['status' => $lockedOrder->status]);
                        });

                        Notification::make()->title('Estado de la orden actualizado')->success()->send();
                    }),
                Tables\Actions\Action::make('view_logs')
                    ->label('Bitácora')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn (WorkOrder $record) => route('mapscalculator.work-order.show', $record)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withSum('inventoryMovements', 'total_cost');
    }
}