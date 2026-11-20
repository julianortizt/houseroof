<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Filament\Resources\InventoryLocationResource\Pages;
use App\Models\InventoryLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryLocationResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'inventory';
    protected static ?string $model = InventoryLocation::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Inventario');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.Ubicaciones');
    }

    public static function getModelLabel(): string
    {
        return __('admin.Ubicación');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.Ubicaciones');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.Ubicación de existencias'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label(__('admin.Código de ubicación'))
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.Nombre'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.Descripción'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('admin.Activo'))
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('movements_sum_quantity_change')
                    ->label(__('admin.Existencias totales'))
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withSum('movements', 'quantity_change');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryLocations::route('/'),
            'create' => Pages\CreateInventoryLocation::route('/create'),
            'edit' => Pages\EditInventoryLocation::route('/{record}/edit'),
        ];
    }
}
