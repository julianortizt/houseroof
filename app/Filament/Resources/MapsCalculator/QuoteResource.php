<?php

namespace App\Filament\Resources\MapsCalculator;

use App\Filament\Resources\MapsCalculator\QuoteResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\MapsCalculator\PriceItem;
use App\Models\MapsCalculator\Project;
use App\Models\Quote;
use App\Models\QuoteItem;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class QuoteResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'quotes';

    protected static ?string $model = Quote::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Mapscalculator';
    protected static ?string $recordTitleAttribute = 'formatted_number';

    public static function getEloquentQuery(): Builder
    {
        try {
            return parent::getEloquentQuery()
                ->whereHas('project', function ($query) {
                    $query->where('created_by', auth()->id());
                });
        } catch (\Throwable $e) {
            \Log::error('QuoteResource getEloquentQuery error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public static function form(Form $form): Form
    {
        \Log::info('QuoteResource form() called');
        return $form->schema([
            Section::make('Datos del cliente')
                ->columns(3)
                ->schema([
                    TextInput::make('name')
                        ->label(__('mapscalculator.client_name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label(__('mapscalculator.email'))
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label(__('mapscalculator.phone'))
                        ->tel()
                        ->maxLength(50),
                    TextInput::make('company')
                        ->label(__('mapscalculator.company'))
                        ->maxLength(255),
                    TextInput::make('address')
                        ->label(__('mapscalculator.address'))
                        ->maxLength(255)
                        ->columnSpan(2),
                ]),

            Section::make('Proyecto')
                ->columns(2)
                ->schema([
                    Select::make('project_id')
                        ->label(__('mapscalculator.project'))
                        ->options(Project::with('client')->get()->mapWithKeys(fn ($p) => [
                            $p->id => "{$p->name} ({$p->client->name})"
                        ]))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $project = Project::with('client', 'measurements')->find($state);
                            if ($project) {
                                $set('area_m2', $project->total_area);
                                $set('name', $project->client->name);
                                $set('email', $project->client->email);
                                $set('phone', $project->client->phone);
                                $set('address', $project->client->address);
                            }
                        })
                        ->columnSpan(2),
                    TextInput::make('area_m2')
                        ->label(__('mapscalculator.area_m2'))
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->disabled(),
                ]),

            Section::make('Líneas del presupuesto')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->label(__('mapscalculator.lines'))
                        ->schema([
                            Grid::make(12)->schema([
                                Select::make('price_item_id')
                                    ->label(__('mapscalculator.price_item'))
                                    ->options(
                                        PriceItem::where('is_active', true)
                                            ->orderBy('category')
                                            ->get()
                                            ->mapWithKeys(fn ($p) => [
                                                $p->id => "{$p->name} ({$p->unit} - " . number_format($p->unit_price_m2, 2, ',', '.') . ' €)'
                                            ])
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $item = PriceItem::find($state);
                                        if ($item) {
                                            $set('name', $item->name);
                                            $set('description', $item->description);
                                            $set('unit_price', $item->unit_price_m2);
                                        }
                                    })
                                    ->columnSpan(4),

                                TextInput::make('name')
                                    ->label(__('mapscalculator.concept'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(4),

                                Textarea::make('description')
                                    ->label(__('mapscalculator.description'))
                                    ->rows(2)
                                    ->columnSpan(12),

                                TextInput::make('quantity')
                                    ->label(__('mapscalculator.quantity'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        self::recalculateItem($set, $get);
                                    })
                                    ->columnSpan(2),

                                TextInput::make('unit_price')
                                    ->label(__('mapscalculator.unit_price'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        self::recalculateItem($set, $get);
                                    })
                                    ->columnSpan(3),

                                TextInput::make('discount_percent')
                                    ->label(__('mapscalculator.discount'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        self::recalculateItem($set, $get);
                                    })
                                    ->columnSpan(2),

                                Placeholder::make('total_price')
                                    ->label(__('mapscalculator.line_total'))
                                    ->content(fn (callable $get) => $get('quantity') && $get('unit_price')
                                        ? number_format(
                                            ($get('quantity') * $get('unit_price')) *
                                            (1 - ($get('discount_percent') ?? 0) / 100),
                                            2,
                                            ',',
                                            '.'
                                        ) . ' €'
                                        : '0,00 €')
                                    ->columnSpan(1),
                            ]),
                        ])
                        ->columns(12)
                        ->addActionLabel(__('mapscalculator.add_line'))
                        ->reorderable('sort_order')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->defaultItems(0),
                ]),

            Section::make('Totales e impuestos')
                ->columns(4)
                ->schema([
                    Placeholder::make('subtotal_display')
                        ->label(__('mapscalculator.subtotal'))
                        ->content(fn (callable $get) => number_format(
                            QuoteItem::where('quote_id', $get('id') ?? 0)->sum('total_price'),
                            2,
                            ',',
                            '.'
                        ) . ' €')
                        ->columnSpan(1),

                    TextInput::make('tax_percent')
                        ->label(__('mapscalculator.vat'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.01)
                        ->default(21)
                        ->required()
                        ->columnSpan(1),

                    Placeholder::make('tax_amount_display')
                        ->label(__('mapscalculator.tax_amount'))
                        ->content(function (callable $get) {
                            $subtotal = QuoteItem::where('quote_id', $get('id') ?? 0)->sum('total_price');
                            return number_format($subtotal * (($get('tax_percent') ?? 21) / 100), 2, ',', '.') . ' €';
                        })
                        ->columnSpan(1),

                    Placeholder::make('total_display')
                        ->label(__('mapscalculator.total'))
                        ->content(function (callable $get) {
                            $subtotal = QuoteItem::where('quote_id', $get('id') ?? 0)->sum('total_price');
                            $tax = $subtotal * (($get('tax_percent') ?? 21) / 100);
                            return number_format($subtotal + $tax, 2, ',', '.') . ' €';
                        })
                        ->extraAttributes(['class' => 'font-bold text-lg text-primary-600'])
                        ->columnSpan(1),
                ]),

            Section::make('Condiciones y validez')
                ->columns(2)
                ->schema([
                    DatePicker::make('valid_until')
                        ->label(__('mapscalculator.valid_until'))
                        ->default(now()->addDays(30))
                        ->native(false),
                    Toggle::make('is_admin_created')
                        ->label(__('mapscalculator.admin_created'))
                        ->default(true)
                        ->inline(false),
                    Textarea::make('terms_conditions')
                        ->label(__('mapscalculator.conditions'))
                        ->rows(3)
                        ->columnSpan(2)
                        ->default('Presupuesto válido por 30 días. Precios sin IVA incluido salvo indicación. Importe total incluye IVA al 21%.'),
                    TextInput::make('deposit_percent')
                        ->label('Anticipo inicial (%)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(99)
                        ->step(0.01)
                        ->default(30)
                        ->required()
                        ->disabled(fn (?Quote $record): bool => $record !== null && ($record->status !== 'borrador' || $record->payments()->exists()))
                        ->helperText('Valor inicial del 30%. Solo puede modificarse antes de enviar la cotización.'),
                ]),

            Section::make('Seguimiento interno')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label(__('mapscalculator.status'))
                        ->options([
                            'borrador' => 'Borrador',
                            'enviado' => 'Enviado',
                            'aprobado' => 'Aprobado',
                            'rechazado' => 'Rechazado',
                        ])
                        ->required()
                        ->default('borrador')
                        ->native(false),
                    Textarea::make('admin_notes')
                        ->label(__('mapscalculator.internal_notes'))
                        ->rows(3)
                        ->columnSpan(2),
                ]),
        ]);
    }

    public static function recalculateItem(callable $set, callable $get): void
    {
        $quantity = $get('quantity') ?? 1;
        $unitPrice = $get('unit_price') ?? 0;
        $discount = $get('discount_percent') ?? 0;
        $total = round($quantity * $unitPrice * (1 - $discount / 100), 2);
        $set('total_price', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('formatted_number')
                    ->label(__('mapscalculator.quote_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label(__('mapscalculator.project'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label(__('mapscalculator.client'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('area_m2')
                    ->label(__('mapscalculator.area_m2'))
                    ->numeric(2, ',', '.')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total')
                    ->label(__('mapscalculator.total'))
                    ->money('EUR', locale: 'es_ES')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('mapscalculator.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'borrador' => 'gray',
                        'enviado' => 'info',
                        'aprobado' => 'success',
                        'rechazado' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'borrador' => 'Borrador',
                        'enviado' => 'Enviado',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label(__('mapscalculator.created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('mapscalculator.status'))
                    ->options([
                        'borrador' => 'Borrador',
                        'enviado' => 'Enviado',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),
                SelectFilter::make('project_id')
                    ->label(__('mapscalculator.project'))
                    ->options(Project::with('client')->get()->mapWithKeys(fn ($p) => [
                        $p->id => "{$p->name} ({$p->client->name})"
                    ])),
            ])
            ->actions([
                EditAction::make(),
                Action::make('view_pdf')
                    ->label(__('mapscalculator.view_pdf'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn (Quote $record) => route('mapscalculator.quote.pdf', $record))
                    ->openUrlInNewTab(),
                Action::make('create_invoice_draft')
                    ->label('Preparar borrador interno')
                    ->icon('heroicon-o-document-plus')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Se copiarán los datos y las líneas de la cotización aprobada. El resultado es un borrador interno no fiscal; no se emitirá ni cobrará.')
                    ->visible(fn (Quote $record): bool => $record->status === 'aprobado'
                        && app(\App\Services\PlatformModules::class)->enabled('invoicing')
                        && ! $record->invoice()->exists())
                    ->action(function (Quote $record) {
                        $invoice = app(\App\Services\InvoiceDraftService::class)->createFromApprovedQuote($record);

                        Notification::make()
                            ->title('Borrador interno creado')
                            ->body('No es una factura fiscal ni se ha enviado al cliente.')
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.invoices.view', $invoice);
                    }),
                Action::make('send_email')
                    ->label(__('mapscalculator.send_email'))
                    ->icon('heroicon-o-envelope-open')
                    ->color('primary')
                    ->action(function (Quote $record) {
                        Mail::to($record->email)->send(new \App\Mail\QuoteSent($record, true));
                        $record->markAsSent();
                        Notification::make()
                            ->title(__('mapscalculator.quote_sent'))
                            ->body(__('mapscalculator.quote_sent_email'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Quote $record) => $record->email),
                Action::make('duplicate')
                    ->label(__('mapscalculator.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Quote $record) {
                        $newQuote = $record->replicate(['quote_number', 'sent_at']);
                        $newQuote->status = 'borrador';
                        $newQuote->is_admin_created = true;
                        $newQuote->save();

                        foreach ($record->items()->get() as $item) {
                            $newItem = $item->replicate();
                            $newItem->quote_id = $newQuote->id;
                            $newItem->save();
                        }

                        $newQuote->calculateTotals();

                        Notification::make()
                            ->title(__('mapscalculator.quote_duplicated'))
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.maps-calculator.quotes.edit', $newQuote);
                    })
                    ->requiresConfirmation(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}