<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SimulatorRoomTemplateResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\Simulator\RoomTemplate;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class SimulatorRoomTemplateResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'simulator';

    protected static ?string $model = RoomTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Simulador';

    protected static ?string $modelLabel = 'Ambiente 3D';

    protected static ?string $pluralModelLabel = 'Ambientes 3D';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Ambiente')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('default_area_m2')
                            ->label('Área por defecto (m²)')
                            ->required()
                            ->numeric()
                            ->default(20.00),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
                ]),

            Section::make('Foto del Ambiente')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\FileUpload::make('preview_image_path')
                            ->label('Foto del ambiente (requerida)')
                            ->disk('public')
                            ->directory('simulator/rooms')
                            ->image()
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('photo_url')
                            ->label('URL de foto (alternativa)')
                            ->helperText('URL de imagen externa (Unsplash, etc.) — se usa si no se sube archivo')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('current_photo_preview')
                            ->label('Foto actual')
                            ->content(fn ($record) => new HtmlString(
                                $record->photo_url
                                    ? '<img src="'.$record->photo_url.'" style="max-width:400px;border-radius:8px;border:1px solid #3a3628;">'
                                    : '<span style="color:#a39c8a;">Sin foto — sube una o añade una URL</span>'
                            ))
                            ->visible(fn ($record) => $record !== null),
                    ]),
                ]),

            Section::make('Modelo 3D (opcional)')
                ->schema([
                    Forms\Components\FileUpload::make('model_glb_path')
                        ->label('Archivo .glb (opcional)')
                        ->disk('public')
                        ->directory('simulator/models')
                        ->acceptedFileTypes(['model/gltf-binary', 'model/gltf+json'])
                        ->maxSize(10240),

                    KeyValue::make('configurable_meshes')
                        ->label('Meshes configurables')
                        ->helperText('Mapea nombre lógico (floor, counter) al nombre real del mesh en el .glb')
                        ->keyLabel('Nombre lógico')
                        ->valueLabel('Nombre del mesh')
                        ->reorderable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview_image_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => $record->photo_url ? $record->photo_url : null)
                    ->size(60),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('default_area_m2')
                    ->label('Área default')
                    ->suffix(' m²')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('photo_url')
                    ->label('Fuente foto')
                    ->limit(30)
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('name')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSimulatorRoomTemplates::route('/'),
            'create' => Pages\CreateSimulatorRoomTemplate::route('/create'),
            'edit' => Pages\EditSimulatorRoomTemplate::route('/{record}/edit'),
        ];
    }
}
