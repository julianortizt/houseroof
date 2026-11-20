<?php

namespace App\Livewire\MapsCalculator;

use App\Models\MapsCalculator\Client;
use App\Models\MapsCalculator\Measurement;
use App\Models\MapsCalculator\PriceItem;
use App\Models\MapsCalculator\Project;
use App\Models\MapsCalculator\WorkOrder as WorkOrderModel;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\PlatformModules;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class MapulatorProject extends Component
{
    public ?Project $project = null;
    public ?Client $client = null;

    public string $activeTab = 'measurements'; // measurements, quote, work, settings
    public string $measurementFilter = 'all'; // all, exterior, interior
    public ?int $editingMeasurementId = null;

    public bool $showClientModal = false;
    public bool $showProjectModal = false;
    public bool $showQuoteModal = false;
    public bool $showSettingsModal = false;

    public array $clients = [];
    public array $measurements = [];

    // Quote data
    public array $quoteItems = [];
    public float $quoteSubtotal = 0;
    public float $quoteTaxPercent = 21;
    public float $quoteTaxAmount = 0;
    public float $quoteTotal = 0;
    public string $quoteNotes = '';

    // Nuevo cliente (CRM desde el módulo)
    public string $clientName = '';
    public string $clientCompany = '';
    public string $clientEmail = '';
    public string $clientPhone = '';
    public string $clientAddress = '';

    // Settings
    public string $defaultMapLayer = 'pnoa';
    public ?array $defaultMapBounds = null;
    public ?float $defaultMapZoom = null;
    public ?array $defaultMapCenter = null;

    // Sync
    public bool $showSyncModal = false;

    protected $listeners = [
        'measurementSaved' => 'loadMeasurements',
        'measurementDeleted' => 'loadMeasurements',
        'refreshProject' => 'loadProject',
        'quoteCreated' => 'loadQuoteItems',
    ];

    public function mount(?Project $project = null): void
    {
        $this->project = $project;
        if ($project) {
            $this->client = $project->client;
            $this->loadMeasurements();
            $this->loadQuoteItems();
            $this->loadSettings();
        }
        $this->clients = Client::orderBy('name')->get(['id', 'name', 'email', 'phone'])->toArray();
    }

    public function quotesEnabled(): bool
    {
        return app(PlatformModules::class)->enabled('quotes');
    }

    public function workOrdersEnabled(): bool
    {
        return app(PlatformModules::class)->enabled('work_orders');
    }

    private function requireModule(string $module): void
    {
        abort_unless(app(PlatformModules::class)->enabled($module), 404);
    }

    /**
     * Comprobación de permisos del módulo (admin / comercial / técnico).
     * Las páginas ya pasan por el middleware, pero cada acción se valida
     * también aquí porque Livewire publica sus propios endpoints.
     */
    private function authorizeProject(string $ability = 'view'): void
    {
        if (! $this->project) {
            return;
        }

        Gate::authorize($ability, $this->project);
    }

    private function authorizeQuote(string $ability = 'update'): void
    {
        $this->requireModule('quotes');

        $quote = $this->project?->latestQuote;

        if ($quote) {
            Gate::authorize($ability, $quote);
        }
    }

    public function createWorkOrder(): void
    {
        $this->requireModule('quotes');
        $this->requireModule('work_orders');

        if (! $this->project) {
            return;
        }

        Gate::authorize('create', WorkOrderModel::class);

        $workOrder = DB::transaction(function (): WorkOrderModel {
            $project = Project::query()
                ->lockForUpdate()
                ->with('latestQuote')
                ->findOrFail($this->project->id);

            if ($project->workOrder()->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'work_order' => __('mapscalculator.work_order_already_exists'),
                ]);
            }

            $quote = $project->latestQuote;

            if (! $quote || $quote->status !== 'aprobado') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quote' => __('mapscalculator.work_order_requires_approved_quote'),
                ]);
            }

            $workOrder = $project->workOrder()->create([
                'quote_id' => $quote->id,
                'status' => 'agendada',
            ]);
            $project->update(['status' => 'agendada']);

            return $workOrder;
        });

        $this->loadProject();
        $this->activeTab = 'work';
        $this->dispatch('notify', message: __('mapscalculator.workorder_created'), type: 'success');
    }

    public function loadProject(): void
    {
        if ($this->project) {
            if (! $this->quotesEnabled()) {
                $this->project->unsetRelation('quotes');
                $this->project->unsetRelation('latestQuote');
            }

            if (! $this->workOrdersEnabled()) {
                $this->project->unsetRelation('workOrder');
            }

            $relations = ['client', 'measurements'];

            if ($this->quotesEnabled()) {
                $relations[] = 'quotes.items';
                $relations[] = 'latestQuote.items';
            }

            if ($this->workOrdersEnabled()) {
                $relations[] = 'workOrder.logs';
            }

            $this->project->load($relations);
            $this->client = $this->project->client;
            $this->loadMeasurements();
            $this->loadQuoteItems();
            $this->loadSettings();
        }
    }

    public function loadMeasurements(): void
    {
        if ($this->project) {
            $this->measurements = $this->project->measurements()
                ->with('layers')
                ->orderBy('sort_order')
                ->get()
                ->toArray();
        }
    }

    /**
     * Abre el editor de medición en blanco (mapa o plano).
     *
     * No crea la fila: se creaba una medición vacía de 0 m² cada vez que se
     * abría el editor, y si el usuario no dibujaba nada quedaban "mediciones"
     * basura en el proyecto. La fila se guarda al pulsar Guardar en el editor.
     */
    public function startMeasurement(string $type): void
    {
        if (! $this->project) return;

        $this->authorizeProject('update');

        $type = in_array($type, ['exterior', 'interior'], true) ? $type : 'exterior';

        // Antes sólo se creaba la fila y se lanzaba un evento que nadie
        // escuchaba: el editor no se abría nunca. Se navega directamente.
        $this->redirectRoute(
            $type === 'exterior' ? 'mapscalculator.measure.exterior' : 'mapscalculator.measure.interior',
            ['project' => $this->project->id]
        );
    }

    /**
     * Abre el editor (mapa o imagen) de una medición existente.
     */
    public function loadMeasurement(int $measurementId): void
    {
        $this->authorizeProject('view');

        $measurement = Measurement::find($measurementId);

        if (! $measurement || $measurement->project_id !== $this->project?->id) {
            return;
        }

        $this->redirectToMeasurement($measurement);
    }

    private function redirectToMeasurement(Measurement $measurement): void
    {
        $this->redirectRoute(
            $measurement->type === 'exterior' ? 'mapscalculator.measure.exterior' : 'mapscalculator.measure.interior',
            ['project' => $measurement->project_id, 'measurement' => $measurement->id]
        );

    }

    public function deleteMeasurement(int $measurementId): void
    {
        $this->authorizeProject('update');

        $measurement = \App\Models\MapsCalculator\Measurement::find($measurementId);
        if ($measurement && $measurement->project_id === $this->project?->id) {
            $measurement->delete();
            $this->loadMeasurements();
            $this->dispatch('notify', message: __('mapscalculator.measurement_deleted'), type: 'success');
        }
    }

    public function duplicateMeasurement(int $measurementId): void
    {
        $this->authorizeProject('update');

        $measurement = \App\Models\MapsCalculator\Measurement::find($measurementId);
        if ($measurement && $measurement->project_id === $this->project?->id) {
            $newMeasurement = $measurement->replicate();
            $newMeasurement->label = $measurement->label . ' (' . __('mapscalculator.copy') . ')';
            $newMeasurement->sort_order = $this->project->measurements()->count();
            $newMeasurement->save();
            $this->loadMeasurements();
            $this->dispatch('notify', message: __('mapscalculator.measurement_duplicated'), type: 'success');
        }
    }

    public function loadQuoteItems(): void
    {
        if (! $this->quotesEnabled()) {
            $this->quoteItems = [];
            $this->quoteSubtotal = 0;
            $this->quoteTaxAmount = 0;
            $this->quoteTotal = 0;
            $this->quoteNotes = '';

            return;
        }

        if ($this->project && $this->project->latestQuote) {
            $quote = $this->project->latestQuote;
            $this->quoteItems = $quote->items()->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'price_item_id' => $item->price_item_id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_percent' => $item->discount_percent,
                    'total_price' => $item->total_price,
                ];
            })->toArray();

            $this->quoteSubtotal = $quote->subtotal;
            $this->quoteTaxPercent = $quote->tax_percent;
            $this->quoteTaxAmount = $quote->tax_amount;
            $this->quoteTotal = $quote->total;
            $this->quoteNotes = $quote->terms_conditions ?? '';
        } else {
            $this->generateAutoQuote();
        }
    }

    public function loadSettings(): void
    {
        if ($this->project) {
            $this->defaultMapLayer = $this->project->default_map_layer ?? 'pnoa';
            $this->defaultMapBounds = $this->project->default_map_bounds;
            $this->defaultMapZoom = $this->project->default_map_zoom;
            $this->defaultMapCenter = $this->project->default_map_center;
        }
    }

    public function generateAutoQuote(): void
    {
        $this->requireModule('quotes');

        $totalArea = $this->project?->total_area ?? 0;
        if ($totalArea <= 0) return;

        $priceItems = PriceItem::where('is_active', true)
            ->whereIn('category', ['resina_autonivelante', 'resina_decorativa', 'resina_industrial'])
            ->get();

        $this->quoteItems = [];
        foreach ($priceItems as $priceItem) {
            $this->quoteItems[] = [
                'price_item_id' => $priceItem->id,
                'name' => $priceItem->name,
                'description' => $priceItem->description,
                'quantity' => $totalArea,
                'unit_price' => $priceItem->unit_price_m2,
                'discount_percent' => 0,
                'total_price' => round($totalArea * $priceItem->unit_price_m2, 2),
            ];
        }

        // Add standard items
        $standardItems = PriceItem::where('is_active', true)
            ->whereIn('category', ['preparacion_superficie', 'imprimacion', 'zocalo_perimetral'])
            ->get();

        foreach ($standardItems as $item) {
            $qty = $item->category === 'zocalo_perimetral'
                ? ($this->project?->total_perimeter ?? 0)
                : $totalArea;

            if ($qty > 0) {
                $this->quoteItems[] = [
                    'price_item_id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $qty,
                    'unit_price' => $item->unit_price_m2,
                    'discount_percent' => 0,
                    'total_price' => round($qty * $item->unit_price_m2, 2),
                ];
            }
        }

        $this->calculateQuoteTotals();
    }

    public function calculateQuoteTotals(): void
    {
        $this->requireModule('quotes');

        // El total de cada línea se recalcula aquí: al editar cantidad, precio o
        // descuento en la tabla, el total por línea se quedaba con el valor
        // antiguo y los totales generales no cambiaban.
        foreach ($this->quoteItems as $index => $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount_percent'] ?? 0);

            $this->quoteItems[$index]['total_price'] = round($quantity * $unitPrice * (1 - $discount / 100), 2);
        }

        $this->quoteSubtotal = round(array_sum(array_column($this->quoteItems, 'total_price')), 2);
        $this->quoteTaxAmount = round($this->quoteSubtotal * ($this->quoteTaxPercent / 100), 2);
        $this->quoteTotal = round($this->quoteSubtotal + $this->quoteTaxAmount, 2);
    }

    public function updatedQuoteItems(): void
    {
        $this->calculateQuoteTotals();
    }

    public function updatedQuoteTaxPercent(): void
    {
        $this->calculateQuoteTotals();
    }

    /**
     * Quita una línea del presupuesto. El botón de la tabla hacía
     * $dispatch('removeQuoteItem') sobre el propio componente y nadie lo
     * escuchaba, así que no borraba nada.
     */
    public function removeQuoteItem(int $index): void
    {
        $this->requireModule('quotes');

        if (! array_key_exists($index, $this->quoteItems)) {
            return;
        }

        unset($this->quoteItems[$index]);
        $this->quoteItems = array_values($this->quoteItems);
        $this->calculateQuoteTotals();
    }

    /**
     * Añade una línea vacía para que el comercial pueda meter conceptos
     * personalizados que no están en la tabla de precios.
     */
    public function addQuoteItem(): void
    {
        $this->requireModule('quotes');

        $this->quoteItems[] = [
            'price_item_id' => null,
            'name' => '',
            'description' => null,
            'quantity' => 1,
            'unit_price' => 0,
            'discount_percent' => 0,
            'total_price' => 0,
        ];
    }

    public function createProject(): void
    {
        Gate::authorize('create', Project::class);

        $this->validate([
            'client' => 'required|array',
            'client.id' => 'required|exists:mapscalculator_clients,id',
        ]);

        $this->project = Project::create([
            'client_id' => $this->client['id'],
            'name' => 'Proyecto ' . now()->format('d/m/Y H:i'),
            'type' => 'interior',
            'status' => 'nuevo',
            'created_by' => auth()->id(),
        ]);

        $this->client = Client::find($this->client['id']);
        $this->showProjectModal = false;
        $this->dispatch('notify', message: __('mapscalculator.project_created'), type: 'success');
    }

    /**
     * Alta de cliente desde el propio módulo (el prompt exige poder crear
     * clientes sin salir del flujo de medición). Antes sólo se podía elegir
     * entre los que ya existieran.
     */
    public function createClient(): void
    {
        Gate::authorize('create', Client::class);

        $data = $this->validate([
            'clientName'    => 'required|string|max:255',
            'clientCompany' => 'nullable|string|max:255',
            'clientEmail'   => 'nullable|email|max:255',
            'clientPhone'   => 'nullable|string|max:50',
            'clientAddress' => 'nullable|string|max:500',
        ]);

        $client = Client::create([
            'name'    => $data['clientName'],
            'company' => $data['clientCompany'] ?: null,
            'email'   => $data['clientEmail'] ?: null,
            'phone'   => $data['clientPhone'] ?: null,
            'address' => $data['clientAddress'] ?: null,
        ]);

        $this->clients = Client::orderBy('name')->get(['id', 'name', 'email', 'phone'])->toArray();

        // Si se está creando un proyecto, se deja el cliente nuevo seleccionado.
        if (! $this->project && $this->client === null) {
            $this->client = $client;
        }

        $this->showClientModal = false;
        $this->reset(['clientName', 'clientCompany', 'clientEmail', 'clientPhone', 'clientAddress']);
        $this->dispatch('notify', message: __('mapscalculator.client_created_short'), type: 'success');
    }

    public function updateProjectType(string $type): void
    {
        $this->authorizeProject('update');

        if ($this->project) {
            $this->project->update(['type' => $type]);
            $this->dispatch('notify', message: __('mapscalculator.project_type_updated'), type: 'success');
        }
    }

    public function saveSettings(): void
    {
        if (!$this->project) return;

        $this->authorizeProject('update');

        $this->project->update([
            'default_map_layer' => $this->defaultMapLayer,
            'default_map_bounds' => $this->defaultMapBounds,
            'default_map_zoom' => $this->defaultMapZoom,
            'default_map_center' => $this->defaultMapCenter,
        ]);

        $this->dispatch('notify', message: __('mapscalculator.settings_saved'), type: 'success');
        $this->showSettingsModal = false;
    }

    public function createQuote(): void
    {
        $this->requireModule('quotes');

        if (!$this->project) return;

        Gate::authorize('create', Quote::class);

        // Sin validar las líneas se podía guardar un presupuesto con conceptos
        // vacíos (el botón de añadir línea crea una fila en blanco).
        $this->validate([
            'quoteItems' => 'required|array|min:1',
            'quoteItems.*.name' => 'required|string|max:255',
            'quoteItems.*.quantity' => 'required|numeric|min:0',
            'quoteItems.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
            $quote = Quote::create([
                'project_id' => $this->project->id,
                // El CRM permite clientes sin email/teléfono/dirección.
                'name' => $this->project->client->name ?? '',
                'email' => $this->project->client->email ?? '',
                'phone' => $this->project->client->phone ?? '',
                'address' => $this->project->client->address ?? '',
                'service_type' => 'resin',
                'area_m2' => $this->project->total_area,
                'status' => 'borrador',
                'is_admin_created' => true,
                'subtotal' => $this->quoteSubtotal,
                'tax_percent' => $this->quoteTaxPercent,
                'tax_amount' => $this->quoteTaxAmount,
                'total' => $this->quoteTotal,
                'terms_conditions' => $this->quoteNotes,
                'valid_until' => now()->addDays(30),
            ]);

            foreach ($this->quoteItems as $index => $item) {
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'price_item_id' => $item['price_item_id'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'total_price' => $item['total_price'],
                    'sort_order' => $index,
                ]);
            }

            // El hook saved() de Quote recalcula los totales al crear la fila,
            // cuando todavía no existen líneas, así que los deja a 0. Hay que
            // recalcular una vez insertadas.
            $quote->calculateTotals();

            $this->project->update(['status' => 'cotizado']);
            $this->dispatch('notify', message: __('mapscalculator.quote_created'), type: 'success');
            $this->dispatch('quoteCreated');
        });
    }

    public function sendQuote(): void
    {
        $this->authorizeQuote('send');

        if ($this->project && $this->project->latestQuote) {
            $quote = $this->project->latestQuote;
            $quote->markAsSent();
            $this->project->update(['status' => 'cotizado']);
            $this->loadQuoteItems();
            $this->dispatch('notify', message: __('mapscalculator.quote_sent'), type: 'success');
        }
    }

    public function sendQuoteEmail(): void
    {
        if (! $this->project || ! $this->project->latestQuote) {
            return;
        }

        $this->authorizeQuote('send');

        $quote = $this->project->latestQuote;

        if (! $quote->email) {
            $this->dispatch('notify', message: __('mapscalculator.quote_no_email'), type: 'warning');

            return;
        }

        try {
            // El email del presupuesto incluye el enlace al portal privado del cliente.
            $this->project->client?->ensurePortalToken();

            Mail::to($quote->email)->send(new \App\Mail\QuoteSent($quote, true));

            $quote->markAsSent();
            $this->loadQuoteItems();
            $this->dispatch('notify', message: __('mapscalculator.quote_sent_pdf'), type: 'success');
        } catch (\Throwable $e) {
            \Log::error('Error sending quote email: ' . $e->getMessage());
            $this->dispatch('notify', message: __('mapscalculator.quote_email_failed'), type: 'error');
        }
    }

    /**
     * Genera (si hace falta) el enlace del portal privado del cliente y lo devuelve
     * para que la vista lo muestre y se pueda copiar.
     */
    public function generatePortalLink(): void
    {
        if (! $this->project || ! $this->project->client) {
            return;
        }

        $client = $this->project->client;
        $client->ensurePortalToken();

        $this->client = $client;
        $this->project->setRelation('client', $client);

        $this->dispatch('notify', message: __('mapscalculator.client_portal_link', ['url' => $client->portal_url]), type: 'success');
    }

    /**
     * Nueva versión del presupuesto: replica el actual con sus líneas y lo deja
     * en borrador, sin perder la versión ya enviada (Módulo 2 del prompt).
     */
    public function createQuoteVersion(): void
    {
        $this->authorizeQuote('update');

        $quote = $this->project?->latestQuote;

        if (! $quote) {
            return;
        }

        $newQuote = $quote->createNewVersion();

        $this->project->unsetRelation('latestQuote');
        $this->project->update(['status' => 'cotizado']);
        $this->loadProject();
        $this->loadQuoteItems();

        $this->dispatch('notify', message: __('mapscalculator.quote_new_version', ['version' => $newQuote->version]), type: 'success');
    }

    public function sendQuoteWhatsApp(): void
    {
        $this->authorizeQuote('send');

        if ($this->project && $this->project->latestQuote) {
            $quote = $this->project->latestQuote;
            $quote->markAsSent();
            $this->loadQuoteItems();
            $this->dispatch('notify', message: __('mapscalculator.quote_whatsapp_opened'), type: 'success');
            
            // Return WhatsApp URL for frontend
            $this->dispatch('openWhatsApp', url: $quote->whatsapp_url);
        }
    }

    public function approveQuote(): void
    {
        $this->authorizeQuote('approve');

        if ($this->project && $this->project->latestQuote) {
            $quote = $this->project->latestQuote;
            $quote->markAsApproved();
            $this->loadQuoteItems();
            $this->loadProject();
            $this->dispatch('notify', message: __('mapscalculator.quote_approved_workorder'), type: 'success');
        }
    }

    public function rejectQuote(): void
    {
        $this->authorizeQuote('reject');

        if ($this->project && $this->project->latestQuote) {
            $quote = $this->project->latestQuote;
            $quote->markAsRejected();
            $this->loadQuoteItems();
            $this->loadProject();
            $this->dispatch('notify', message: __('mapscalculator.quote_rejected'), type: 'warning');
        }
    }

    public function syncProject(): void
    {
        if (!$this->project) return;

        $this->authorizeProject('view');
        $this->showSyncModal = true;
        $this->dispatch('syncStarted', [
            'projectId' => $this->project->id,
        ]);
    }

    public function shareProject(): void
    {
        if (!$this->project) return;

        $this->authorizeProject('update');

        if (!$this->project->share_token) {
            $this->project->update([
                'share_token' => \Illuminate\Support\Str::random(32),
                'is_public' => true,
                'shared_at' => now(),
            ]);
        }

        $this->dispatch('notify', message: __('mapscalculator.share_link', ['url' => $this->project->share_url]), type: 'success');
    }

    public function formatArea(float $m2): string
    {
        return number_format($m2, 2, ',', '.') . ' m²';
    }

    public function formatLength(float $m): string
    {
        return number_format($m, 2, ',', '.') . ' m';
    }

    public function formatPrice(float $price): string
    {
        return number_format($price, 2, ',', '.') . ' €';
    }

    public function render()
    {
        if (! $this->quotesEnabled()) {
            $this->project?->unsetRelation('quotes');
            $this->project?->unsetRelation('latestQuote');
            $this->quoteItems = [];
            $this->quoteSubtotal = 0;
            $this->quoteTaxPercent = 21;
            $this->quoteTaxAmount = 0;
            $this->quoteTotal = 0;
            $this->quoteNotes = '';

            if ($this->activeTab === 'quote') {
                $this->activeTab = 'measurements';
            }
        }

        if (! $this->workOrdersEnabled()) {
            $this->project?->unsetRelation('workOrder');

            if ($this->activeTab === 'work') {
                $this->activeTab = 'measurements';
            }
        }

        return view('livewire.mapscalculator.mapulator-project')
            ->layout('layouts.app');
    }
}