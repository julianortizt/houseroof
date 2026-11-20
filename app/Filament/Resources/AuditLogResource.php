<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditLogResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'administration';
    protected static ?string $model = AuditLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.Configuración');
    }

    public static function getNavigationLabel(): string
    {
        return 'Bitácora de auditoría';
    }

    public static function getModelLabel(): string
    {
        return 'Evento de auditoría';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Bitácora de auditoría';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label(__('admin.Creado'))->dateTime('d/m/Y H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Usuario')
                    ->placeholder(fn (AuditLog $record): ?string => $record->actor_label ?: 'Sistema')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event')->label('Evento')->searchable(),
                Tables\Columns\TextColumn::make('subject_type')->label('Tipo de registro')->toggleable(),
                Tables\Columns\TextColumn::make('subject_id')->label('ID de registro')->toggleable(),
                Tables\Columns\TextColumn::make('old_values')
                    ->label('Antes')
                    ->formatStateUsing(fn ($state): string => $state ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '—')
                    ->wrap()
                    ->limit(100),
                Tables\Columns\TextColumn::make('new_values')
                    ->label('Después')
                    ->formatStateUsing(fn ($state): string => $state ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '—')
                    ->wrap()
                    ->limit(100),
                Tables\Columns\TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options(fn (): array => AuditLog::query()->distinct()->orderBy('event')->pluck('event', 'event')->all()),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('actor');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAuditLogs::route('/')];
    }
}
