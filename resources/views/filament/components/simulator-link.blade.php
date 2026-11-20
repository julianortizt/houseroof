@if(app(\App\Services\PlatformModules::class)->enabled('simulator'))
    <a
        href="{{ route('simulator.index') }}"
        class="fi-btn fi-btn-sm fi-btn-color-gray flex items-center gap-1.5 px-3 py-1.5"
        title="Open the administrator-only 3D simulator"
    >
        <x-heroicon-o-cube class="h-4 w-4" />
        <span class="font-medium">3D Simulator</span>
    </a>
@endif
