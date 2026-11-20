@extends('layouts.app')

@section('title', __('mapscalculator.measure_interior') . ' - ' . $project->name)

@section('content')
{{-- Espacio de trabajo: la cabecera es una barra fija y el editor se queda con
     todo el resto de la ventana. El layout no pinta navegación, pie ni widgets
     flotantes cuando recibe `workspace` (ver MapulatorController). --}}
<div class="flex flex-col min-h-screen md:h-screen md:max-h-screen md:overflow-hidden">
    <header class="h-14 shrink-0 flex items-center gap-3 px-3 bg-white border-b border-gray-200">
        <a href="{{ route('mapscalculator.project.show', $project) }}"
           class="p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition shrink-0"
           title="{{ __('mapscalculator.back_to_project') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div class="min-w-0">
            <h1 class="text-base font-semibold text-gray-900 truncate">{{ __('mapscalculator.measure_interior_h') }}: {{ $project->name }}</h1>
            <p class="text-xs text-gray-500 truncate">{{ __('mapscalculator.client') }}: {{ $project->client_name }} • {{ $project->type_label }}</p>
        </div>
        <div class="ml-auto flex items-center gap-2 shrink-0">
            <span class="hidden lg:inline text-xs text-gray-400">{{ __('mapscalculator.upload_image_hint') }}</span>
            <a href="{{ route('mapscalculator.index') }}"
               class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition">{{ __('mapscalculator.dashboard') }}</a>
            <a href="{{ route('mapscalculator.project.create') }}"
               class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition hidden md:inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('mapscalculator.new_project') }}
            </a>
            <a href="{{ route('mapscalculator.project.show', $project) }}"
               class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.back_to_project') }}</a>
        </div>
    </header>

    <div class="flex flex-1 min-h-0">
        {{-- El editor de interiores funciona con canvas 2D nativo: ya no se carga
             Fabric.js (300 KB) porque no se usaba para nada. --}}
        <livewire:maps-calculator.interior-measurement :project="$project" :measurement="$measurement ?? null" />
    </div>
</div>
@endsection
