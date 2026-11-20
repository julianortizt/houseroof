@extends('layouts.app')

@section('title', __('mapscalculator.work_order_short', ['id' => $workOrder->id]) . ' - ' . $workOrder->project->name)

@section('content')
<div class="w-full max-w-5xl mx-auto px-4 py-4">
    @include('mapscalculator.partials.nav')

    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('mapscalculator.project.show', $workOrder->project) }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('mapscalculator.work_order_short', ['id' => $workOrder->id]) }}</h1>
                <p class="text-gray-500">{{ $workOrder->project->name }} • {{ $workOrder->project->client->name }}</p>
            </div>
        </div>
        <a href="{{ route('mapscalculator.project.show', $workOrder->project) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.back_to_project') }}</a>
    </div>

    <livewire:maps-calculator.work-order :workOrder="$workOrder" :project="$workOrder->project" />
</div>
@endsection