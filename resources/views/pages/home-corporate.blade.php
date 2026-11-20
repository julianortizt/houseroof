@extends('layouts.app')

@section('title', __('Plataforma empresarial') . ' — ' . config('platform.public_name'))
@section('meta_description', app()->getLocale() === 'es'
    ? 'Una plataforma interna para coordinar clientes, proyectos, cotizaciones y órdenes de trabajo.'
    : 'An internal platform for coordinating customers, projects, quotes, and work orders.')
@section('meta_keywords', app()->getLocale() === 'es'
    ? 'plataforma interna, gestión de clientes, cotizaciones, órdenes de trabajo'
    : 'internal platform, customer management, quotes, work orders')

@php
    $isSpanish = app()->getLocale() === 'es';
    $copy = fn (string $spanish, string $english): string => $isSpanish ? $spanish : $english;
    $moduleStates = app(\App\Services\PlatformModules::class)->all();
    $quotesEnabled = $moduleStates['quotes']['enabled'];
    $workOrdersEnabled = $moduleStates['work_orders']['enabled'];
    $inventoryEnabled = $moduleStates['inventory']['enabled'];
    $invoicingEnabled = $moduleStates['invoicing']['enabled'];
    $accountingEnabled = $moduleStates['accounting']['enabled'];
    $paymentsEnabled = $moduleStates['payments']['enabled'];
    $customerPortalEnabled = $moduleStates['customer_portal']['enabled'];

    $modules = [
        [
            'icon' => 'heroicon-o-user-group',
            'title' => $copy('Gestión de clientes', 'Customer management'),
            'description' => $copy('Organiza contactos, empresas y proyectos desde el CRM interno.', 'Organize contacts, companies, and projects in the internal CRM.'),
            'status' => $copy('Disponible para el equipo', 'Available to the team'),
            'available' => true,
        ],
        [
            'icon' => 'heroicon-o-document-text',
            'title' => $copy('Cotizaciones', 'Quotes'),
            'description' => $copy('Prepara propuestas, envíalas y da seguimiento a sus versiones y aprobaciones.', 'Prepare proposals, send them, and track versions and approvals.'),
            'status' => $quotesEnabled ? $copy('Disponible parcialmente', 'Partially available') : $copy('Desactivado', 'Disabled'),
            'available' => $quotesEnabled,
        ],
        [
            'icon' => 'heroicon-o-wrench-screwdriver',
            'title' => $copy('Órdenes de trabajo', 'Work orders'),
            'description' => $copy('Programa trabajos, asigna responsables y registra el avance.', 'Schedule work, assign team members, and track progress.'),
            'status' => $workOrdersEnabled ? $copy('Disponible parcialmente', 'Partially available') : $copy('Desactivado', 'Disabled'),
            'available' => $workOrdersEnabled,
        ],
        [
            'icon' => 'heroicon-o-map',
            'title' => 'MapsCalculator',
            'description' => $copy('Mide espacios y conecta proyectos con cotizaciones y órdenes de trabajo.', 'Measure spaces and connect projects to quotes and work orders.'),
            'status' => $copy('Disponible para el equipo', 'Available to the team'),
            'available' => true,
        ],
        [
            'icon' => 'heroicon-o-archive-box',
            'title' => $copy('Inventario', 'Inventory'),
            'description' => $copy('Controla existencias por ubicación; reserva materiales y registra consumos vinculados a órdenes de trabajo.', 'Track stock by location, reserve materials, and record consumption linked to work orders.'),
            'status' => $inventoryEnabled ? $copy('Disponible para el equipo', 'Available to the team') : $copy('Desactivado', 'Disabled'),
            'available' => $inventoryEnabled,
        ],
        [
            'icon' => 'heroicon-o-receipt-percent',
            'title' => $copy('Facturación', 'Invoicing'),
            'description' => $copy('Solo se preparan borradores internos no fiscales desde cotizaciones aprobadas; no se emiten ni cobran.', 'Only internal non-fiscal drafts from approved quotes are available; invoices are not issued or charged.'),
            'status' => $invoicingEnabled ? $copy('Borradores internos no fiscales', 'Internal non-fiscal drafts') : $copy('Desactivado', 'Disabled'),
            'available' => $invoicingEnabled,
        ],
        [
            'icon' => 'heroicon-o-calculator',
            'title' => $copy('Contabilidad', 'Accounting'),
            'description' => $copy('Diario genérico de partida doble y balance interno; el plan español oficial, impuestos y conciliación bancaria siguen pendientes.', 'A generic double-entry journal and internal trial balance; the official Spanish chart, tax books, and bank reconciliation remain pending.'),
            'status' => $accountingEnabled ? $copy('En desarrollo; no usar para libros oficiales', 'In development; not for statutory books') : $copy('Desactivado', 'Disabled'),
            'available' => $accountingEnabled,
        ],
        [
            'icon' => 'heroicon-o-credit-card',
            'title' => $copy('Cobros online', 'Online payments'),
            'description' => $copy('Los cobros están desactivados por defecto y requieren Stripe, los módulos necesarios y revisión fiscal.', 'Payments are disabled by default and require Stripe, the required modules, and fiscal review.'),
            'status' => $paymentsEnabled ? $copy('Requiere configuración y habilitación explícita', 'Requires configuration and explicit enablement') : $copy('Desactivado', 'Disabled'),
            'available' => $paymentsEnabled,
        ],
        [
            'icon' => 'heroicon-o-lock-closed',
            'title' => $copy('Acceso de clientes', 'Client access'),
            'description' => $copy('Portal web con cuenta verificada, cotizaciones propias y seguimiento de proyectos asociados al correo.', 'Web portal with verified accounts, customer-owned quotes, and projects associated with the verified email.'),
            'status' => $customerPortalEnabled ? $copy('Disponible parcialmente', 'Partially available') : $copy('Desactivado', 'Disabled'),
            'available' => $customerPortalEnabled,
        ],
    ];
@endphp

@section('content')
<section class="relative overflow-hidden bg-gray-950 text-white">
    <div class="absolute inset-0 opacity-30" aria-hidden="true">
        <div class="absolute -right-32 -top-40 h-96 w-96 rounded-full bg-amber-500 blur-3xl"></div>
        <div class="absolute -bottom-48 left-1/4 h-96 w-96 rounded-full bg-cyan-700 blur-3xl"></div>
    </div>

    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-28">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-amber-200">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                {{ $copy('OPERACIONES EMPRESARIALES EN UN SOLO FLUJO', 'BUSINESS OPERATIONS IN ONE WORKFLOW') }}
            </span>
            <h1 class="mt-7 max-w-2xl text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                {{ $copy('Una plataforma para coordinar cada proyecto.', 'One platform to coordinate every project.') }}
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-gray-300">
                {{ $copy('Conecta clientes, mediciones, cotizaciones y órdenes de trabajo en un espacio de trabajo diseñado para tu equipo.', 'Connect customers, measurements, quotes, and work orders in a workspace built for your team.') }}
            </p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('quote.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-5 py-3 font-semibold text-white transition hover:bg-amber-700">
                    {{ $copy('Solicitar una cotización', 'Request a quote') }}
                    <x-heroicon-o-arrow-right class="h-5 w-5" />
                </a>
                <a href="#modulos" class="inline-flex items-center rounded-lg border border-white/20 px-5 py-3 font-semibold text-white transition hover:bg-white/10">
                    {{ $copy('Explorar módulos', 'Explore modules') }}
                </a>
            </div>
            <p class="mt-5 text-sm text-gray-400">
                {{ $copy('Las funciones se indican según su estado actual; los módulos pendientes no están habilitados.', 'Capabilities are labeled according to their current status; pending modules are not enabled.') }}
            </p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-2xl backdrop-blur sm:p-6">
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div>
                    <p class="text-sm font-semibold text-white">{{ $copy('Flujo de trabajo', 'Workflow') }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $copy('Vista conceptual, no datos de clientes', 'Conceptual view, not customer data') }}</p>
                </div>
                <span class="rounded-full bg-amber-400/10 px-3 py-1 text-xs font-semibold text-amber-200">{{ $copy('Proceso', 'Workflow') }}</span>
            </div>

            <div class="mt-5 space-y-3">
                @foreach([
                    ['number' => '01', 'title' => $copy('Cliente y proyecto', 'Customer and project'), 'description' => $copy('CRM · alcance · ubicación', 'CRM · scope · location')],
                    ['number' => '02', 'title' => $copy('Medición y cotización', 'Measurement and quote'), 'description' => 'MapsCalculator · propuesta'],
                    ['number' => '03', 'title' => $copy('Orden y ejecución', 'Work order and delivery'), 'description' => $copy('Planificación · equipo · avance', 'Scheduling · team · progress')],
                ] as $step)
                    <div class="flex items-center gap-4 rounded-xl border border-white/10 bg-gray-900/70 p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 text-sm font-bold text-amber-300">{{ $step['number'] }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-white">{{ $step['title'] }}</p>
                            <p class="mt-1 truncate text-sm text-gray-400">{{ $step['description'] }}</p>
                        </div>
                        <x-heroicon-o-check-circle class="h-5 w-5 shrink-0 text-emerald-400" />
                    </div>
                @endforeach
            </div>
            <div class="mt-4 rounded-xl border border-dashed border-amber-300/30 bg-amber-300/5 p-4">
                <p class="text-sm font-semibold text-amber-200">{{ $copy('Siguiente etapa', 'Next stage') }}</p>
                <p class="mt-1 text-sm text-gray-300">{{ $copy('Facturación: borradores internos no fiscales. Contabilidad: diario interno. Los cobros siguen desactivados hasta completar su configuración y revisión fiscal.', 'Invoicing: internal non-fiscal drafts only. Accounting: internal journal. Payments remain disabled until configuration and fiscal review are complete.') }}</p>
            </div>
        </div>
    </div>
</section>

<section id="modulos" class="bg-gray-50 py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="font-semibold uppercase tracking-wider text-amber-700">{{ $copy('Espacio de trabajo modular', 'Modular workspace') }}</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl">{{ $copy('Herramientas para todo el ciclo del proyecto', 'Tools for the entire project lifecycle') }}</h2>
            <p class="mt-4 text-lg leading-8 text-gray-600">{{ $copy('Revisa qué está disponible hoy para el equipo y qué requiere desarrollo antes de ofrecerse a los clientes.', 'See what is available to the team today and what needs development before it can be offered to customers.') }}</p>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($modules as $module)
                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl {{ $module['available'] ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-500' }}">
                            <x-dynamic-component :component="$module['icon']" class="h-6 w-6" />
                        </span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $module['available'] ? 'bg-emerald-50 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ $module['status'] }}
                        </span>
                    </div>
                    <h3 class="mt-5 text-xl font-bold text-gray-950">{{ $module['title'] }}</h3>
                    <p class="mt-2 leading-7 text-gray-600">{{ $module['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-white py-20">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
        <div>
            <p class="font-semibold uppercase tracking-wider text-amber-700">{{ $copy('Para cada tipo de acceso', 'Access for every role') }}</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-gray-950">{{ $copy('El equipo administra; el cliente consulta su proyecto.', 'Teams manage work; customers follow their projects.') }}</h2>
            <p class="mt-4 leading-7 text-gray-600">{{ $copy('El panel interno muestra los módulos que el desarrollador habilite. Los clientes pueden crear una cuenta verificada y consultar las cotizaciones y proyectos asociados a su correo cuando el portal esté activo.', 'The internal workspace shows the modules enabled by the developer. Customers can create a verified account and view quotes and projects associated with their email when the portal is active.') }}</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('contact') }}" class="inline-flex items-center rounded-lg bg-gray-950 px-5 py-3 font-semibold text-white transition hover:bg-gray-800">
                    {{ $copy('Hablar con el equipo', 'Talk to the team') }}
                </a>
                <a href="{{ url('/admin/login') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-3 font-semibold text-gray-800 transition hover:bg-gray-50">
                    {{ $copy('Acceso del equipo', 'Team sign in') }}
                </a>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-gray-950">{{ $copy('Estado de capacidades', 'Capability status') }}</h3>
            <ul class="mt-5 space-y-4">
                <li class="flex gap-3">
                    <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                    <span class="text-gray-700">{{ $copy('Los módulos de operación habilitados se controlan desde el Super Panel y conservan sus datos al desactivarse.', 'Enabled operational modules are controlled from the Super Panel and retain their data when disabled.') }}</span>
                </li>
                <li class="flex gap-3">
                    <x-heroicon-o-clock class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                    <span class="text-gray-700">{{ $copy('Las facturas son borradores internos no fiscales; contabilidad es un diario genérico. Los cobros requieren configuración y revisión fiscal antes de habilitarse.', 'Invoices are internal non-fiscal drafts; accounting is a generic journal. Payments require configuration and fiscal review before enablement.') }}</span>
                </li>
            </ul>
        </div>
    </div>
</section>

<section class="bg-amber-600 py-16 text-white sm:py-20">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div>
            <h2 class="text-3xl font-bold">{{ $copy('Coordina tu próximo proyecto desde el mismo espacio.', 'Coordinate your next project in one workspace.') }}</h2>
            <p class="mt-3 max-w-2xl text-amber-50">{{ $copy('Cuéntanos qué necesitas y te ayudaremos a definir el flujo adecuado para tu equipo.', 'Tell us what you need and we will help define the right workflow for your team.') }}</p>
        </div>
        <a href="{{ route('contact') }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-white px-5 py-3 font-semibold text-amber-800 transition hover:bg-amber-50">
            {{ $copy('Contactar', 'Contact us') }}
        </a>
    </div>
</section>
@endsection
