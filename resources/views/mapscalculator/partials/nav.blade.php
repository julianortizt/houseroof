{{-- Barra de navegación del módulo.
     No existía: desde el panel de un proyecto (o desde una obra, un presupuesto
     o la ficha de un cliente) no había ninguna forma de volver al listado ni de
     crear otro proyecto; sólo se llegaba escribiendo la URL a mano. --}}
<nav class="mb-5 flex flex-wrap items-center gap-1.5 border-b border-gray-200 pb-3">
    @php
        $moduleLinks = [
            ['route' => 'mapscalculator.index', 'label' => __('mapscalculator.dashboard'), 'active' => 'mapscalculator.index'],
            ['route' => 'mapscalculator.client.index', 'label' => __('mapscalculator.clients'), 'active' => 'mapscalculator.client.*'],
        ];
        if (app(\App\Services\PlatformModules::class)->enabled('work_orders')) {
            $moduleLinks[] = ['route' => 'mapscalculator.work-order.upcoming', 'label' => __('mapscalculator.upcoming_works'), 'active' => 'mapscalculator.work-order.*'];
        }
    @endphp

    @foreach($moduleLinks as $moduleLink)
        <a href="{{ route($moduleLink['route']) }}"
           class="px-3 py-1.5 text-sm font-medium rounded-lg transition {{ request()->routeIs($moduleLink['active']) ? 'bg-primary-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            {{ $moduleLink['label'] }}
        </a>
    @endforeach

    <a href="{{ route('mapscalculator.project.create') }}"
       class="ml-auto px-4 py-1.5 text-sm font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        {{ __('mapscalculator.new_project') }}
    </a>
</nav>
