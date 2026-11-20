<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.Contenido');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.Galería de Proyectos');
    }

    public static function getModelLabel(): string
    {
        return __('admin.Proyecto');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.Proyectos');
    }

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Trabajo realizado')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.Título'))
                            ->required()
                            ->maxLength(500)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('admin.Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn (string $state) => Str::slug($state))
                            ->helperText(__('admin.Se genera automáticamente del nombre')),

                        Forms\Components\TextInput::make('location')
                            ->label(__('admin.Ubicación'))
                            ->maxLength(255)
                            ->placeholder('Ciudad, provincia'),

                        Forms\Components\Select::make('category')
                            ->label(app()->getLocale() === 'es' ? 'Categoría' : 'Category')
                            ->options(Project::categoryOptions())
                            ->native(false)
                            ->searchable(),

                        Forms\Components\TextInput::make('video_url')
                            ->label(__('admin.URL de video (YouTube/Vimeo)'))
                            ->url()
                            ->maxLength(500),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.Activo'))
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->label(__('admin.Destacado'))
                            ->default(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('admin.Orden'))
                            ->numeric()
                            ->default(0),
                    ]),
                ]),

            Section::make('Descripción')
                ->schema([
                    Forms\Components\RichEditor::make('description')
                        ->label(__('admin.Descripción'))
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike',
                            'h2', 'h3', 'h4',
                            'bulletList', 'orderedList',
                            'blockquote',
                            'link',
                            'undo', 'redo',
                        ])
                        ->columnSpanFull(),
                ]),

            Section::make('Imágenes del proyecto')
                ->schema([
                    Forms\Components\FileUpload::make('project_images')
                        ->label(__('admin.Galería de imágenes'))
                        ->multiple()
                        ->image()
                        ->disk('public')
                        ->directory('projects/images')
                        ->imagePreviewHeight('120')
                        ->maxSize(10240)
                        ->columnSpanFull()
                        ->helperText(__('admin.JPG, PNG, WebP. Max 10MB. La primera imagen es la principal.')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('admin.Imagen'))
                    ->disk('public')
                    ->getStateUsing(fn ($record) => $record->image_url),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.Título'))
                    ->searchable()
                    ->sortable()
                    ->limit(60),

                Tables\Columns\TextColumn::make('location')
                    ->label(__('admin.Ubicación'))
                    ->placeholder('—')
                    ->limit(30),

                Tables\Columns\TextColumn::make('category')
                    ->label(app()->getLocale() === 'es' ? 'Categoría' : 'Category')
                    ->formatStateUsing(fn (?string $state): string => Project::categoryOptions()[$state] ?? '—')
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.Activo'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.Destacado'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.Orden'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.Creado'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\SelectFilter::make('category')
                    ->label(app()->getLocale() === 'es' ? 'Categoría' : 'Category')
                    ->options(Project::categoryOptions()),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
