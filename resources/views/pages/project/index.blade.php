@extends('layouts.app')

@section('meta_description', __('meta_projects_desc'))
@section('meta_keywords', __('meta_projects_kw'))
@section('title', __('Galería de Proyectos'))

@section('content')
<section class="platform-page-hero">
    <div class="platform-page-hero__inner px-4 sm:px-6 lg:px-8 text-center">
        <span class="platform-page-kicker">{{ app()->getLocale() === 'es' ? 'Trabajo realizado' : 'Selected work' }}</span>
        <h1 class="platform-page-heading">{{ __('Galería de Proyectos') }}</h1>
        <p class="platform-page-subtitle">
            {{ app()->getLocale() === 'es'
                ? 'Una selección de proyectos, mediciones y trabajos coordinados desde la plataforma.'
                : 'A selection of projects, measurements, and work coordinated through the platform.' }}
        </p>
    </div>
</section>

<section class="platform-page-body py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('projects.index') }}" class="platform-card mb-8 grid gap-4 p-5 sm:grid-cols-[minmax(0,1fr)_minmax(12rem,0.6fr)_auto] sm:items-end">
            <div>
                <label for="project-search" class="mb-1 block text-sm font-semibold text-gray-800">
                    {{ app()->getLocale() === 'es' ? 'Buscar proyectos' : 'Search projects' }}
                </label>
                <input id="project-search" type="search" name="q" value="{{ $search }}"
                    placeholder="{{ app()->getLocale() === 'es' ? 'Título o ubicación' : 'Title or location' }}"
                    class="w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label for="project-category" class="mb-1 block text-sm font-semibold text-gray-800">
                    {{ app()->getLocale() === 'es' ? 'Categoría' : 'Category' }}
                </label>
                <select id="project-category" name="category" class="w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500">
                    <option value="">{{ app()->getLocale() === 'es' ? 'Todas las categorías' : 'All categories' }}</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-amber-600 px-5 font-semibold text-white transition hover:bg-amber-700">
                    {{ app()->getLocale() === 'es' ? 'Buscar' : 'Search' }}
                </button>
                @if($search !== '' || $category !== '')
                    <a href="{{ route('projects.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-950">
                        {{ app()->getLocale() === 'es' ? 'Limpiar' : 'Clear' }}
                    </a>
                @endif
            </div>
        </form>

        @if($projects->count())
            <p class="mb-5 text-sm text-gray-600" aria-live="polite">
                {{ $projects->total() }}
                {{ app()->getLocale() === 'es' ? 'proyectos encontrados' : 'projects found' }}
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($projects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="platform-card platform-card--interactive group relative aspect-[4/3] overflow-hidden">
                        @if($project->getFirstMediaUrl())
                            <img src="{{ $project->getFirstMediaUrl() }}" alt="{{ $project->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950/85 via-transparent to-transparent opacity-80 transition duration-300 group-hover:opacity-100"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-6 text-white transition duration-300">
                            @if($project->category && isset($categories[$project->category]))
                                <span class="mb-2 inline-flex rounded-full border border-white/20 bg-gray-950/60 px-3 py-1 text-xs font-semibold">
                                    {{ $categories[$project->category] }}
                                </span>
                            @endif
                            <h3 class="text-xl font-bold">{{ $project->title }}</h3>
                            @if($project->location)
                                <p class="text-sm text-gray-300 mt-1">📍 {{ $project->location }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $projects->links() }}
            </div>
        @else
            <div class="platform-card py-16 text-center">
                <h2 class="text-xl font-bold text-gray-900">
                    {{ app()->getLocale() === 'es' ? 'No encontramos proyectos' : 'No projects found' }}
                </h2>
                <p class="mt-2 text-gray-600">
                    {{ $search !== '' || $category !== ''
                        ? (app()->getLocale() === 'es' ? 'Prueba otra búsqueda o categoría.' : 'Try a different search or category.')
                        : (app()->getLocale() === 'es' ? 'Los proyectos publicados aparecerán aquí.' : 'Published projects will appear here.') }}
                </p>
                @if($search !== '' || $category !== '')
                    <a href="{{ route('projects.index') }}" class="mt-5 inline-flex font-semibold text-amber-700 hover:text-amber-800">
                        {{ app()->getLocale() === 'es' ? 'Ver todos los proyectos' : 'View all projects' }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</section>
@endsection
