<div class="hidden items-center gap-2 sm:flex">
    <a
        href="{{ route('filament.super-panel.pages.appearance') }}"
        class="fi-btn fi-btn-sm fi-btn-primary flex items-center gap-1.5 px-3 py-1.5"
        title="Cambiar la plantilla y el estilo del sitio público"
    >
        <x-heroicon-o-swatch class="h-4 w-4" />
        <span class="font-medium">Plantillas y estilos</span>
    </a>
    <a
        href="{{ route('filament.super-panel.pages.modules') }}"
        class="fi-btn fi-btn-sm fi-btn-color-gray flex items-center gap-1.5 px-3 py-1.5"
        title="Activar o desactivar módulos de la plataforma"
    >
        <x-heroicon-o-squares-2x2 class="h-4 w-4" />
        <span class="font-medium">Módulos</span>
    </a>
</div>
