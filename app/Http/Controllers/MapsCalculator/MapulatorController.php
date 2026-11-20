<?php

namespace App\Http\Controllers\MapsCalculator;

use App\Http\Controllers\Controller;
use App\Models\MapsCalculator\Client;
use App\Models\MapsCalculator\Measurement;
use App\Models\MapsCalculator\Project;
use App\Models\MapsCalculator\WorkLog;
use App\Models\MapsCalculator\WorkOrder;
use App\Models\MapsCalculator\PriceItem;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Mail\QuoteSent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MapulatorController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::with(['client', 'measurements'])
            ->where(function ($query) {
                // El administrador del módulo (o un super_admin) ve todo. Antes se
                // miraba sólo isComercial()/isTecnico(), y como la columna nace con
                // 'comercial' por defecto, cualquier super_admin veía únicamente los
                // proyectos que había creado él mismo.
                if (auth()->user()->isMapscalculatorAdmin()) {
                    return;
                }

                if (auth()->user()->isComercial()) {
                    $query->where('created_by', auth()->id());
                } elseif (auth()->user()->isTecnico()) {
                    $query->where('assigned_to', auth()->id());
                }
            })
            ->latest()
            ->paginate(20);

        return view('mapscalculator.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        $clients = Client::orderBy('name')->get();
        return view('mapscalculator.create', compact('clients'));
    }

    /**
     * Alta de cliente dentro del módulo (sin pasar por el panel admin).
     */
    public function clientCreate()
    {
        $this->authorize('create', Client::class);

        return view('mapscalculator.client-create');
    }

    public function clientStore(Request $request)
    {
        $this->authorize('create', Client::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'nif' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
        ]);

        $client = Client::create($request->only(['name', 'company', 'email', 'phone', 'nif', 'address']));

        return redirect()->route('mapscalculator.project.create', ['client' => $client->id])
            ->with('success', __('mapscalculator.client_created', ['name' => $client->name]));
    }

    /**
     * CRM de clientes (Módulo 3 del prompt): listado con búsqueda y filtro.
     */
    public function clients(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $user = $request->user();
        $search = trim((string) $request->query('q', ''));

        $clients = Client::query()
            ->visibleTo($user)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount(['projects as projects_count' => fn ($query) => $query->visibleTo($user)])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('mapscalculator.clients', compact('clients', 'search'));
    }

    /**
     * Ficha de cliente: historial de proyectos, presupuestos y obras.
     */
    public function clientShow(Client $client)
    {
        $this->authorize('view', $client);

        $user = request()->user();
        $modules = app(\App\Services\PlatformModules::class);
        $quotesEnabled = $modules->enabled('quotes');
        $workOrdersEnabled = $modules->enabled('work_orders');
        $relations = [
            'projects'                => fn ($q) => $q->visibleTo($user)->latest(),
            'projects.measurements',
        ];

        if ($quotesEnabled) {
            $relations['projects.latestQuote'] = fn ($q) => $q->whereHas('project', fn ($project) => $project->visibleTo($user));
            $relations['quotes'] = fn ($q) => $q
                ->whereHas('project', fn ($project) => $project->visibleTo($user))
                ->with('items');
        }

        if ($workOrdersEnabled) {
            $relations['projects.workOrder'] = fn ($q) => $q->visibleTo($user);
            $relations['workOrders'] = fn ($q) => $q->visibleTo($user)->with('project');
        }

        $client->load($relations);

        $stats = [
            'projects'        => $client->projects->count(),
            'area'            => round((float) $client->projects->sum(fn ($p) => $p->total_area), 2),
            'quoted'          => $quotesEnabled ? round((float) $client->quotes->sum('total'), 2) : 0,
            'approved'        => $quotesEnabled ? round((float) $client->quotes->where('status', 'aprobado')->sum('total'), 2) : 0,
            'work_orders'     => $workOrdersEnabled ? $client->workOrders->count() : 0,
            'finished_works'  => $workOrdersEnabled ? $client->workOrders->whereIn('status', ['finalizada', 'entregada'])->count() : 0,
        ];

        return view('mapscalculator.client-show', compact('client', 'stats', 'quotesEnabled', 'workOrdersEnabled'));
    }

    /**
     * Listado de próximas obras (Módulo 4 del prompt).
     */
    public function upcomingWorkOrders(Request $request)
    {
        $this->authorize('viewAny', WorkOrder::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['agendada', 'en_preparacion', 'en_ejecucion', 'finalizada', 'entregada', 'cancelada'])],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereIn('mapscalculator_role', ['admin', 'comercial', 'tecnico']),
            ],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $workOrders = WorkOrder::query()
            ->visibleTo($request->user())
            ->with(['project.client', 'assignee', 'project.measurements'])
            ->when(
                filled($filters['status'] ?? null),
                fn ($query) => $query->where('status', $filters['status']),
                fn ($query) => $query->whereIn('status', ['agendada', 'en_preparacion', 'en_ejecucion'])
            )
            ->when(isset($filters['assigned_to']), fn ($query) => $query->where('assigned_to', $filters['assigned_to']))
            ->when(isset($filters['from']), fn ($query) => $query->whereDate('scheduled_start', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($query) => $query->whereDate('scheduled_start', '<=', $filters['to']))
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $query->whereHas('project', function ($projectQuery) use ($filters): void {
                    $projectQuery->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhereHas('client', fn ($clientQuery) => $clientQuery->where('name', 'like', '%' . $filters['q'] . '%'));
                });
            })
            ->orderByRaw('COALESCE(scheduled_start, created_at) asc')
            ->paginate(20)
            ->withQueryString();

        $today = now()->startOfDay();

        return view('mapscalculator.upcoming', compact('workOrders', 'today', 'filters'));
    }

    /**
     * Reporte final de obra descargable, con bitácora y fotos (Módulo 4).
     */
    public function workOrderReport(WorkOrder $workOrder)
    {
        $this->authorize('viewReport', $workOrder);

        $workOrder->load([
            'project.client',
            'project.measurements',
            'project.latestQuote.items',
            'logs.creator',
            'assignee',
        ]);

        $photos = [];
        foreach ($workOrder->logs as $log) {
            if (! $log->photo_path) {
                continue;
            }

            $absolute = Storage::disk('public')->path($log->photo_path);
            if (! is_file($absolute)) {
                continue;
            }

            $photos[$log->id] = 'data:' . (mime_content_type($absolute) ?: 'image/jpeg')
                . ';base64,' . base64_encode(file_get_contents($absolute));
        }

        $pdf = Pdf::loadView('mapscalculator.work-order-report', [
            'workOrder' => $workOrder,
            'photos'    => $photos,
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('informe-obra-' . $workOrder->id . '.pdf');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $request->validate([
            'client_id' => 'required_without:new_client_name|exists:mapscalculator_clients,id',
            'new_client_name' => 'required_without:client_id|string|max:255',
            'new_client_email' => 'nullable|email|max:255',
            'new_client_phone' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:interior,exterior,mixto',
        ]);

        if ($request->filled('new_client_name')) {
            $this->authorize('create', Client::class);
            $client = Client::create([
                'name' => $request->new_client_name,
                'email' => $request->new_client_email,
                'phone' => $request->new_client_phone,
            ]);
            $clientId = $client->id;
        } else {
            $clientId = $request->client_id;
        }

        $project = Project::create([
            'client_id' => $clientId,
            'name' => $request->name,
            'type' => $request->type,
            'status' => 'nuevo',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('mapscalculator.project.show', $project)
            ->with('success', __('mapscalculator.project_created'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:interior,exterior,mixto',
            'status' => 'required|in:nuevo,medido,cotizado,aprobado,rechazado,agendada,en_preparacion,en_ejecucion,finalizada,entregada,cancelada',
            'assigned_to' => 'nullable|exists:users,id',
            'scheduled_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // $guarded = [] on the model, so never pass $request->all() through:
        // only the validated fields may be written.
        $project->update($request->safe()->only([
            'name', 'type', 'status', 'assigned_to', 'scheduled_date', 'notes',
        ]));

        return redirect()->route('mapscalculator.project.show', $project)
            ->with('success', __('mapscalculator.project_updated'));
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();
        return redirect()->route('mapscalculator.index')
            ->with('success', __('mapscalculator.project_deleted'));
    }

    public function measureExterior(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $measurement = $this->resolveMeasurement($request, $project, 'exterior');

        // `workspace` hace que el layout no pinte navegación, pie ni widgets
        // flotantes: el editor necesita toda la ventana para el mapa.
        return view('mapscalculator.measure-exterior', compact('project', 'measurement') + ['workspace' => true]);
    }

    public function measureInterior(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $measurement = $this->resolveMeasurement($request, $project, 'interior');

        return view('mapscalculator.measure-interior', compact('project', 'measurement') + ['workspace' => true]);
    }

    /**
     * Recupera la medición a editar a partir del query string `?measurement={id}`.
     * El editor se abría siempre en blanco porque la ruta no aceptaba la medición.
     */
    private function resolveMeasurement(Request $request, Project $project, string $type): ?Measurement
    {
        $id = $request->integer('measurement');

        if (! $id) {
            return null;
        }

        return $project->measurements()->where('type', $type)->find($id);
    }

    public function storeMeasurement(Request $request, Project $project)
    {
        $this->authorize('createForProject', [Measurement::class, $project]);

        $request->validate([
            'type' => 'required|in:interior,exterior',
            'label' => 'nullable|string|max:255',
            'geojson' => 'nullable|array',
            'reference_image' => 'nullable|image|max:10240',
            'scale_factor' => 'nullable|numeric|min:0',
            'polygon_points' => 'nullable|array',
            'calibration_line' => 'nullable|array',
            'area_m2' => 'required|numeric|min:0',
            'perimeter_m' => 'required|numeric|min:0',
        ]);

        $data = [
            'project_id' => $project->id,
            'type' => $request->type,
            'label' => $request->label ?? ($request->type === 'exterior' ? 'Medición exterior' : 'Medición interior'),
            'area_m2' => $request->area_m2,
            'perimeter_m' => $request->perimeter_m,
            'sort_order' => $project->measurements()->count(),
        ];

        if ($request->type === 'exterior') {
            $data['geojson'] = $request->geojson;
        } else {
            if ($request->hasFile('reference_image')) {
                $data['reference_image_path'] = $request->file('reference_image')->store('mapscalculator/measurements', 'public');
            }
            $data['scale_factor'] = $request->scale_factor;
            $data['polygon_points'] = $request->polygon_points;
            $data['calibration_line'] = $request->calibration_line;
        }

        $measurement = Measurement::create($data);

        $project->update(['status' => 'medido']);

        return response()->json([
            'success' => true,
            'measurement' => $measurement,
            'message' => 'Medición guardada correctamente',
        ]);
    }

    public function updateMeasurement(Request $request, Measurement $measurement)
    {
        $this->authorize('update', $measurement);

        $request->validate([
            'label' => 'nullable|string|max:255',
            'geojson' => 'nullable|array',
            'scale_factor' => 'nullable|numeric|min:0',
            'polygon_points' => 'nullable|array',
            'calibration_line' => 'nullable|array',
            'area_m2' => 'required|numeric|min:0',
            'perimeter_m' => 'required|numeric|min:0',
        ]);

        $data = [
            'label' => $request->label ?? $measurement->label,
            'area_m2' => $request->area_m2,
            'perimeter_m' => $request->perimeter_m,
        ];

        if ($measurement->type === 'exterior') {
            $data['geojson'] = $request->geojson;
        } else {
            $data['scale_factor'] = $request->scale_factor;
            $data['polygon_points'] = $request->polygon_points;
            $data['calibration_line'] = $request->calibration_line;
        }

        $measurement->update($data);

        return response()->json([
            'success' => true,
            'measurement' => $measurement,
            'message' => 'Medición actualizada',
        ]);
    }

    public function destroyMeasurement(Measurement $measurement)
    {
        $this->authorize('delete', $measurement);

        $measurement->delete();
        return response()->json(['success' => true, 'message' => 'Medición eliminada']);
    }

    public function quote(Project $project)
    {
        $this->authorize('view', $project);

        $priceItems = PriceItem::where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $quote = $project->latestQuote;
        // $quote->items would return the legacy JSON column, not the rows.
        $quoteItems = $quote ? $quote->items()->get() : collect();

        return view('mapscalculator.quote', compact('project', 'priceItems', 'quote', 'quoteItems'));
    }

    public function storeQuote(Request $request, Project $project)
    {
        $this->authorize('create', Quote::class);

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'terms_conditions' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $project) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $discount = ($item['discount_percent'] ?? 0) / 100;
                $subtotal += $item['quantity'] * $item['unit_price'] * (1 - $discount);
            }

            $taxPercent = $request->tax_percent ?? 21;
            $taxAmount = round($subtotal * ($taxPercent / 100), 2);
            $total = round($subtotal + $taxAmount, 2);

            $quote = Quote::create([
                'project_id' => $project->id,
                // El CRM permite clientes sin email/teléfono/dirección.
                'name' => $project->client->name ?? '',
                'email' => $project->client->email ?? '',
                'phone' => $project->client->phone ?? '',
                'address' => $project->client->address ?? '',
                'service_type' => 'resin',
                'area_m2' => $project->total_area,
                'status' => 'borrador',
                'is_admin_created' => true,
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'terms_conditions' => $request->terms_conditions,
                'valid_until' => now()->addDays(30),
            ]);

            foreach ($request->items as $index => $item) {
                $discount = ($item['discount_percent'] ?? 0) / 100;
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'price_item_id' => $item['price_item_id'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'total_price' => round($item['quantity'] * $item['unit_price'] * (1 - $discount), 2),
                    'sort_order' => $index,
                ]);
            }

            // El hook saved() de Quote recalcula los totales al crear la fila,
            // cuando todavía no existen líneas, así que los deja a 0. Hay que
            // recalcular una vez insertadas.
            $quote->calculateTotals();

            $project->update(['status' => 'cotizado']);
        });

        return redirect()->route('mapscalculator.project.show', $project)
            ->with('success', __('mapscalculator.quote_created'));
    }

    public function updateQuote(Request $request, Quote $quote)
    {
        $this->authorize('update', $quote);

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'terms_conditions' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $quote) {
            $quote->items()->delete();

            $subtotal = 0;
            foreach ($request->items as $index => $item) {
                $discount = ($item['discount_percent'] ?? 0) / 100;
                $totalPrice = round($item['quantity'] * $item['unit_price'] * (1 - $discount), 2);
                $subtotal += $totalPrice;

                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'price_item_id' => $item['price_item_id'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'total_price' => $totalPrice,
                    'sort_order' => $index,
                ]);
            }

            $taxPercent = $request->tax_percent ?? $quote->tax_percent;
            $taxAmount = round($subtotal * ($taxPercent / 100), 2);
            $total = round($subtotal + $taxAmount, 2);

            $quote->update([
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'terms_conditions' => $request->terms_conditions,
            ]);
        });

        return redirect()->route('mapscalculator.project.show', $quote->project)
            ->with('success', __('mapscalculator.quote_updated'));
    }

    public function sendQuote(Quote $quote)
    {
        $this->authorize('send', $quote);

        $sent = $this->sendQuoteEmailTo($quote);

        $quote->markAsSent();
        $quote->project->update(['status' => 'cotizado']);

        return redirect()->route('mapscalculator.project.show', $quote->project)
            ->with(
                $sent ? 'success' : 'warning',
                $sent
                    ? __('mapscalculator.quote_sent_success')
                    : __('mapscalculator.quote_sent_warning')
            );
    }

    /**
     * Envía el presupuesto por email con el PDF adjunto.
     * No propaga errores de SMTP: la marca de "enviado" debe aplicarse igual
     * y el fallo sólo queda registrado en el log.
     */
    private function sendQuoteEmailTo(Quote $quote): bool
    {
        if (! $quote->email) {
            return false;
        }

        try {
            $quote->project?->client?->ensurePortalToken();

            Mail::to($quote->email)->send(new QuoteSent($quote));

            return true;
        } catch (\Throwable $e) {
            \Log::error('Error sending quote email: ' . $e->getMessage());

            return false;
        }
    }

    public function approveQuote(Quote $quote)
    {
        $this->authorize('approve', $quote);

        $quote->markAsApproved();

        return redirect()->route('mapscalculator.project.show', $quote->project)
            ->with('success', __('mapscalculator.quote_approved_workorder'));
    }

    /**
     * Crea una nueva versión del presupuesto (Módulo 2: historial de versiones).
     */
    public function newQuoteVersion(Quote $quote)
    {
        $this->authorize('update', $quote);

        if (! $quote->project) {
            abort(404, 'El presupuesto no está asociado a ningún proyecto');
        }

        $newQuote = $quote->createNewVersion();

        return redirect()->route('mapscalculator.project.show', $quote->project)
            ->with('success', __('mapscalculator.quote_new_version', ['version' => $newQuote->version]));
    }

    public function rejectQuote(Quote $quote)
    {
        $this->authorize('reject', $quote);

        $quote->markAsRejected();

        return redirect()->route('mapscalculator.project.show', $quote->project)
            ->with('success', __('mapscalculator.quote_rejected'));
    }

    public function workOrder(WorkOrder $workOrder)
    {
        $this->authorize('view', $workOrder);

        $workOrder->load(['project.client', 'logs.creator']);
        return view('mapscalculator.work-order', compact('workOrder'));
    }

    public function createWorkOrder(Request $request)
    {
        $this->authorize('create', WorkOrder::class);

        $request->validate([
            'project_id' => 'required|exists:mapscalculator_projects,id',
            'quote_id' => 'required|exists:quotes,id',
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereIn('mapscalculator_role', ['admin', 'comercial', 'tecnico']),
            ],
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after_or_equal:scheduled_start',
        ]);

        $workOrder = DB::transaction(function () use ($request): WorkOrder {
            $project = Project::query()->lockForUpdate()->findOrFail($request->project_id);
            $this->authorize('update', $project);

            if ($project->workOrder()->exists()) {
                throw ValidationException::withMessages([
                    'project_id' => __('mapscalculator.work_order_already_exists'),
                ]);
            }

            $quote = Quote::query()
                ->whereKey($request->quote_id)
                ->where('project_id', $project->id)
                ->first();

            if (! $quote || $quote->status !== 'aprobado') {
                throw ValidationException::withMessages([
                    'quote_id' => __('mapscalculator.work_order_requires_approved_quote'),
                ]);
            }

            $this->authorize('update', $quote);

            $workOrder = WorkOrder::create([
                'project_id' => $project->id,
                'quote_id' => $quote->id,
                'status' => 'agendada',
                'scheduled_start' => $request->scheduled_start,
                'scheduled_end' => $request->scheduled_end,
                'assigned_to' => $request->assigned_to,
            ]);

            $project->update([
                'status' => 'agendada',
                'assigned_to' => $request->assigned_to,
            ]);

            return $workOrder;
        });

        return redirect()->route('mapscalculator.work-order.show', $workOrder)
            ->with('success', __('mapscalculator.workorder_created'));
    }

    public function updateWorkOrder(Request $request, WorkOrder $workOrder)
    {
        $this->authorize('update', $workOrder);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['agendada', 'en_preparacion', 'en_ejecucion', 'finalizada', 'entregada', 'cancelada'])],
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after_or_equal:scheduled_start',
            'actual_start' => 'nullable|date',
            'actual_end' => 'nullable|date|after_or_equal:actual_start',
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereIn('mapscalculator_role', ['admin', 'comercial', 'tecnico']),
            ],
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $workOrder, $validated): void {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);
            $nextStatus = $validated['status'];

            if ($nextStatus !== $lockedWorkOrder->status && ! $lockedWorkOrder->transitionTo($nextStatus)) {
                throw ValidationException::withMessages([
                    'status' => __('mapscalculator.invalid_work_order_transition'),
                ]);
            }

            if ($nextStatus === 'en_ejecucion' && empty($validated['actual_start'])) {
                $validated['actual_start'] = $lockedWorkOrder->actual_start;
            }
            if ($nextStatus === 'finalizada' && empty($validated['actual_end'])) {
                $validated['actual_end'] = $lockedWorkOrder->actual_end;
            }

            $validated = array_intersect_key($validated, array_flip([
                'status', 'scheduled_start', 'scheduled_end', 'actual_start', 'actual_end', 'assigned_to', 'notes',
            ]));
            $lockedWorkOrder->update($validated);

            $projectChanges = ['status' => $nextStatus];
            if (array_key_exists('assigned_to', $validated)) {
                $projectChanges['assigned_to'] = $validated['assigned_to'];
            }
            $lockedWorkOrder->project()->update($projectChanges);
        });

        return redirect()->route('mapscalculator.work-order.show', $workOrder)
            ->with('success', __('mapscalculator.workorder_updated'));
    }

    public function addWorkLog(Request $request, WorkOrder $workOrder)
    {
        $this->authorize('update', $workOrder);

        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:5000',
            'type' => 'required|in:antes,durante,despues,general',
            'photo' => 'nullable|image|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('mapscalculator/work-logs', 'public');
        }

        WorkLog::create([
            'work_order_id' => $workOrder->id,
            'created_by' => auth()->id(),
            'date' => $request->date,
            'description' => $request->description,
            'photo_path' => $photoPath,
            'type' => $request->type,
        ]);

        return redirect()->route('mapscalculator.work-order.show', $workOrder)
            ->with('success', __('mapscalculator.log_added'));
    }

    public function syncWorkLogs(Request $request, WorkOrder $workOrder)
    {
        $this->authorize('update', $workOrder);

        $validated = $request->validate([
            'logs' => 'required|array|min:1|max:50',
            'logs.*.client_uuid' => 'required|uuid|distinct',
            'logs.*.date' => 'required|date',
            'logs.*.description' => 'required|string|max:5000',
            'logs.*.type' => ['required', Rule::in(['antes', 'durante', 'despues', 'general'])],
        ]);

        $synced = DB::transaction(function () use ($validated, $workOrder, $request): array {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);
            $this->authorize('update', $lockedWorkOrder);
            $synced = [];

            foreach ($validated['logs'] as $entry) {
                $log = WorkLog::query()->where('client_uuid', $entry['client_uuid'])->first();
                if ($log) {
                    if (
                        (int) $log->work_order_id !== (int) $lockedWorkOrder->id
                        || (int) $log->created_by !== (int) $request->user()->id
                    ) {
                        abort(409, 'El registro local ya está asociado a otro usuario u orden.');
                    }

                    $synced[] = $entry['client_uuid'];
                    continue;
                }

                WorkLog::create([
                    'work_order_id' => $lockedWorkOrder->id,
                    'created_by' => $request->user()->id,
                    'client_uuid' => $entry['client_uuid'],
                    'date' => $entry['date'],
                    'description' => $entry['description'],
                    'type' => $entry['type'],
                ]);
                $synced[] = $entry['client_uuid'];
            }

            return $synced;
        });

        return response()->json(['synced' => $synced]);
    }

    public function deleteWorkLog(WorkLog $workLog)
    {
        $this->authorize('deleteLog', $workLog);

        $workOrder = $workLog->workOrder;
        if ($workLog->photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($workLog->photo_path);
        }
        $workLog->delete();

        return redirect()->route('mapscalculator.work-order.show', $workOrder)
            ->with('success', __('mapscalculator.log_deleted'));
    }

    /**
     * Portal privado del cliente. Acepta dos tipos de enlace:
     *  - token del cliente (acceso a todos sus proyectos)
     *  - share_token de un proyecto concreto (route/attr Project::share_url)
     * Antes sólo se buscaba por token de cliente, así que los enlaces generados
     * con Project::share_token (`/mapscalculator/portal/{share_token}`) daban 404.
     */
    public function clientPortal(string $token)
    {
        $modules = app(\App\Services\PlatformModules::class);
        $quotesEnabled = $modules->enabled('quotes');
        $workOrdersEnabled = $modules->enabled('work_orders');
        $relations = ['measurements'];

        if ($quotesEnabled) {
            $relations[] = 'latestQuote';
        }

        if ($workOrdersEnabled) {
            $relations[] = 'workOrder';
        }

        $client = Client::findByPortalToken($token);
        $projects = null;

        if ($client) {
            $projects = $client->projects()->with($relations)->latest()->get();
        } else {
            $sharedProject = Project::where('share_token', $token)
                ->with($relations)
                ->first();

            if ($sharedProject) {
                $client = $sharedProject->client;
                $projects = collect([$sharedProject]);
            }
        }

        if (! $client) {
            abort(404, 'Enlace de portal inválido o expirado');
        }

        return view('mapscalculator.client-portal', compact('client', 'projects', 'quotesEnabled', 'workOrdersEnabled'));
    }
}