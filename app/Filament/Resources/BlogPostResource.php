<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Contenido');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Blog');
    }
    public static function getModelLabel(): string
    {
        return __('admin.Artículo');
    }
    public static function getPluralModelLabel(): string
    {
        return __('admin.Artículos');
    }
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Publicación del Blog')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.Título'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('admin.Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn (string $state) => Str::slug($state))
                            ->helperText(__('admin.Se genera automáticamente del nombre')),

                        Forms\Components\Select::make('category_id')
                            ->label(__('admin.Categoría'))
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder(__('admin.Sin categoría')),

                        Forms\Components\Select::make('status')
                            ->label(__('admin.Estado'))
                            ->options([
                                'draft' => 'Borrador',
                                'published' => 'Publicado',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label(__('admin.Fecha de publicación')),

                        Forms\Components\Toggle::make('is_featured')
                            ->label(__('admin.Destacado'))
                            ->default(false),
                    ]),
                ]),

            Section::make('Imágenes y Video')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->label(__('admin.Imagen destacada'))
                            ->image()
                            ->disk('public')
                            ->directory('blog/images')
                            ->imagePreviewHeight('150')
                            ->maxSize(5120)
                            ->helperText(__('admin.JPG, PNG, WebP. Max 5MB.')),

                        Forms\Components\FileUpload::make('gallery_images')
                            ->label(__('admin.Galería de imágenes'))
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('blog/gallery')
                            ->imagePreviewHeight('100')
                            ->maxSize(5120)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('video_url')
                            ->label(__('admin.URL de video (YouTube/Vimeo)'))
                            ->url()
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('Contenido del Artículo')
                ->schema([
                    Grid::make(1)->schema([
                        Forms\Components\Textarea::make('excerpt')
                            ->label(__('admin.Extracto'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label(__('admin.Contenido'))
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'h2', 'h3', 'h4',
                                'bulletList', 'orderedList',
                                'blockquote', 'codeBlock',
                                'link', 'attachFiles',
                                'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.Título'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.Categoría'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.Estado'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.Destacado'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('admin.Publicado'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured'),
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
