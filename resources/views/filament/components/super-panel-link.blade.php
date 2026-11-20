@if(auth()->check() && auth()->user()->email === config('platform.developer_email'))
    <a
        href="{{ route('filament.super-panel.pages.dashboard') }}"
        class="fi-btn fi-btn-sm fi-btn-primary hidden items-center gap-1.5 px-3 py-1.5 sm:flex"
        title="Abrir el panel exclusivo del desarrollador"
    >
        <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
        <span class="font-medium">Super Panel</span>
    </a>
@endif
