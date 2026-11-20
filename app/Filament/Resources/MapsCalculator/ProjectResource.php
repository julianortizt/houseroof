<?php

namespace App\Filament\Resources\MapsCalculator;

use App\Filament\Resources\MapsCalculator\ProjectResource\Pages;
use App\Models\MapsCalculator\Client;
use App\Models\MapsCalculator\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Mapscalculator';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'));
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (request()->has('client_id')) {
            $query->where('client_id', request()->get('client_id'));
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('client_id')
                        ->label(__('mapscalculator.client'))
                        ->options(Client::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('name')
                        ->label(__('mapscalculator.project_name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->label(__('mapscalculator.type'))
                        ->options([
                            'interior' => 'Interior',
                            'exterior' => 'Exterior',
                            'mixto' => 'Mixto',
                        ])
                        ->required()
                        ->native(false)
                        ->default('interior'),
                    Forms\Components\Select::make('status')
                        ->label(__('mapscalculator.status'))
                        ->options([
                            'nuevo' => 'Nuevo',
                            'medido' => 'Medido',
                            'cotizado' => 'Cotizado',
                            'aprobado' => 'Aprobado',
                            'rechazado' => 'Rechazado',
                            'agendada' => 'Agendada',
                            'en_preparacion' => 'En preparación',
                            'en_ejecucion' => 'En ejecución',
                            'finalizada' => 'Finalizada',
                            'entregada' => 'Entregada',
                            'cancelada' => 'Cancelada',
                        ])
                        ->required()
                        ->native(false)
                        ->default('nuevo'),
                    Forms\Components\Select::make('created_by')
                        ->label(__('mapscalculator.created_by'))
                        ->options(User::whereHas('roles', fn ($q) => $q->where('name', '!=', 'cliente'))->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->default(auth()->id()),
                    Forms\Components\Select::make('assigned_to')
                        ->label(__('mapscalculator.assigned_to'))
                        ->options(User::whereHas('roles', fn ($q) => $q->where('name', '!=', 'cliente'))->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                    Forms\Components\DatePicker::make('scheduled_date')
                        ->label(__('mapscalculator.scheduled_date'))
                        ->native(false),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('mapscalculator.notes'))
                        ->rows(4)
                        ->columnSpan(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('mapscalculator.project'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('client.name')
                    ->label(__('mapscalculator.client'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('mapscalculator.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'interior' => 'info',
                        'exterior' => 'success',
                        'mixto' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'interior' => 'Interior',
                        'exterior' => 'Exterior',
                        'mixto' => 'Mixto',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('mapscalculator.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nuevo' => 'gray',
                        'medido' => 'info',
                        'cotizado' => 'warning',
                        'aprobado' => 'success',
                        'rechazado' => 'danger',
                        'agendada' => 'primary',
                        'en_preparacion' => 'warning',
                        'en_ejecucion' => 'info',
                        'finalizada' => 'success',
                        'entregada' => 'gray',
                        'cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nuevo' => 'Nuevo',
                        'medido' => 'Medido',
                        'cotizado' => 'Cotizado',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'agendada' => 'Agendada',
                        'en_preparacion' => 'En preparación',
                        'en_ejecucion' => 'En ejecución',
                        'finalizada' => 'Finalizada',
                        'entregada' => 'Entregada',
                        'cancelada' => 'Cancelada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('total_area')
                    ->label(__('mapscalculator.area_m2'))
                    ->numeric(2, ',', '.')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('mapscalculator.created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('mapscalculator.type'))
                    ->options([
                        'interior' => 'Interior',
                        'exterior' => 'Exterior',
                        'mixto' => 'Mixto',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('mapscalculator.status'))
                    ->options([
                        'nuevo' => 'Nuevo',
                        'medido' => 'Medido',
                        'cotizado' => 'Cotizado',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'agendada' => 'Agendada',
                        'en_preparacion' => 'En preparación',
                        'en_ejecucion' => 'En ejecución',
                        'finalizada' => 'Finalizada',
                        'entregada' => 'Entregada',
                        'cancelada' => 'Cancelada',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('measure')
                    ->label('Medir')
                    ->icon('heroicon-o-scale')
                    ->color('primary')
                    ->url(fn (Project $record) => route('mapscalculator.project.show', $record)),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}