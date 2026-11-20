@extends('layouts.app')

@section('title', __('mapscalculator.project_label', ['name' => $project->name]))

@section('content')
{{-- A todo el ancho: el panel del proyecto es una herramienta de trabajo y el
     contenedor centrado lo dejaba con mucho espacio desaprovechado. --}}
<div class="w-full px-3 py-6">
    @include('mapscalculator.partials.nav')

    <livewire:maps-calculator.mapulator-project :project="$project" />
</div>
@endsection