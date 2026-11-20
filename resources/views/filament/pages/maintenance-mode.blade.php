<x-filament::page>
    {{-- Status Banner --}}
    <div class="mb-6">
        @if($isMaintenanceMode)
            <div class="bg-amber-50 border-2 border-amber-400 rounded-xl p-6">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-amber-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-amber-800">⚠️ Modo Mantenimiento ACTIVO</h3>
                        <p class="text-sm text-amber-700">El sitio web está actualmente en mantenimiento. Los visitantes ven un mensaje de mantenimiento.</p>
                        @if($maintenanceSecret)
                            <p class="text-xs text-amber-600 mt-1 font-mono bg-amber-100 px-2 py-1 rounded inline-block">
                                Ver como admin: ?secret={{ $maintenanceSecret }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="bg-green-50 border-2 border-green-400 rounded-xl p-6">
                <div class="flex items-center space-x-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-bold text-green-800">✅ Sitio en línea</h3>
                        <p class="text-sm text-green-700">El sitio web funciona normalmente para todos los visitantes.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Form --}}
    <form wire:submit="{{ $isMaintenanceMode ? 'deactivateMaintenance' : 'activateMaintenance' }}">
        {{ $this->form }}

        <div class="mt-6 flex items-center space-x-3">
            @if($isMaintenanceMode)
                <x-filament::button type="submit" color="danger" size="lg" icon="heroicon-o-check-circle">
                    Desactivar Mantenimiento (Volver a línea)
                </x-filament::button>
            @else
                <x-filament::button type="submit" color="warning" size="lg" icon="heroicon-o-wrench-screwdriver">
                    Activar Mantenimiento
                </x-filament::button>
            @endif
        </div>
    </form>

    {{-- Info Section --}}
    <div class="mt-8">
        <x-filament::section>
            <x-slot name="heading">
                Información sobre el modo mantenimiento
            </x-slot>

            <div class="prose text-sm text-gray-600 space-y-3">
                <p><strong>¿Qué hace?</strong> Cuando está activo, todos los visitantes del sitio web ven un mensaje de "Mantenimiento" en lugar del contenido normal.</p>
                <p><strong>¿Quién puede ver el sitio?</strong> Solo los usuarios administradores autenticados en el panel de Filament pueden seguir navegando el sitio.</p>
                <p><strong>Secreto:</strong> Si establece un secreto, cualquier persona con la URL completa (?secret=TU_SECRETO) también podrá ver el sitio. Útil para hacer pruebas con clientes.</p>
                <p><strong>Cola de trabajos:</strong> El modo mantenimiento NO detiene la cola de trabajos (queue). Los emails y tareas pendientes seguirán procesándose.</p>
                <p><strong>SEO:</strong> Los motores de búsqueda recibirán un código HTTP 503, indicando que el sitio está temporalmente no disponible.</p>
            </div>
        </x-filament::section>
    </div>
</x-filament::page>
