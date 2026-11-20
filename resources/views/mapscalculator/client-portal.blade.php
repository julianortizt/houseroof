@extends('layouts.app')

@section('title', __('mapscalculator.client_portal') . ' - ' . $client->name)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8 text-center">
        <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $client->name }}</h1>
        <p class="text-gray-500 mt-2">{{ __('mapscalculator.portal_subtitle') }}</p>
    </div>

    @if($projects->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <p class="text-lg font-medium text-gray-900 mb-2">{{ __('mapscalculator.no_projects_for_client') }}</p>
            <p class="text-gray-500">{{ __('mapscalculator.no_projects_contact') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($projects as $project)
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $project->name }}</h3>
                                <p class="text-gray-500 text-sm">{{ $project->type_label }} • {{ $project->measurements->count() }} {{ __('mapscalculator.measurements') }}</p>
                            </div>
                            <span class="px-3 py-1.5 text-sm font-medium rounded-full
                                @switch($project->status)
                                    @case('nuevo') bg-gray-100 text-gray-800 @break
                                    @case('medido') bg-blue-100 text-blue-800 @break
                                    @case('cotizado') bg-purple-100 text-purple-800 @break
                                    @case('aprobado') bg-green-100 text-green-800 @break
                                    @case('rechazado') bg-red-100 text-red-800 @break
                                    @case('agendada') bg-indigo-100 text-indigo-800 @break
                                    @case('en_preparacion') bg-orange-100 text-orange-800 @break
                                    @case('en_ejecucion') bg-blue-100 text-blue-800 @break
                                    @case('finalizada') bg-green-100 text-green-800 @break
                                    @case('entregada') bg-gray-100 text-gray-800 @break
                                    @case('cancelada') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">{{ $project->status_label }}</span>
                        </div>

                        @if($project->total_area > 0)
                            <div class="grid grid-cols-2 {{ $quotesEnabled ? 'lg:grid-cols-3' : '' }} gap-4 mb-4 text-center">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-2xl font-bold text-primary-600">{{ number_format($project->total_area, 2, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">{{ __('mapscalculator.total_area_label') }}</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-2xl font-bold text-secondary-600">{{ number_format($project->total_perimeter, 2, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">{{ __('mapscalculator.perimeter_label') }}</div>
                                </div>
                                @if($quotesEnabled)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    @if($project->latestQuote)
                                        <div class="text-2xl font-bold text-amber-600">{{ number_format($project->latestQuote->total, 2, ',', '.') }} €</div>
                                    @else
                                        <div class="text-2xl font-bold text-gray-400">—</div>
                                    @endif
                                    <div class="text-xs text-gray-500">{{ __('mapscalculator.quote_label') }}</div>
                                </div>
                                @endif
                            </div>
                        @endif

                        @if($quotesEnabled && $project->latestQuote)
                            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-gray-900">{{ __('mapscalculator.quote_label') }} {{ $project->latestQuote->formatted_number }}</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @switch($project->latestQuote->status)
                                            @case('borrador') bg-gray-100 text-gray-800 @break
                                            @case('enviado') bg-blue-100 text-blue-800 @break
                                            @case('aprobado') bg-green-100 text-green-800 @break
                                            @case('rechazado') bg-red-100 text-red-800 @break
                                            @case('expirado') bg-yellow-100 text-yellow-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">{{ $project->latestQuote->status_label }}</span>
                                </div>
                                <div class="text-right text-lg font-bold text-primary-600">{{ number_format($project->latestQuote->total, 2, ',', '.') }} €</div>
                            </div>
                        @endif

                        @if($workOrdersEnabled && $project->workOrder)
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-blue-800">{{ __('mapscalculator.work_in_progress') }}</p>
                                        <p class="text-sm text-blue-600">{{ __('mapscalculator.status') }}: {{ $project->workOrder->status_label }} ({{ $project->workOrder->progress }}%)</p>
                                        @if($project->workOrder->scheduled_start)
                                            <p class="text-sm text-blue-600">{{ __('mapscalculator.planned') }}: {{ $project->workOrder->scheduled_start->format('d/m/Y') }} - {{ $project->workOrder->scheduled_end?->format('d/m/Y') }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-blue-600">{{ $project->workOrder->progress }}%</p>
                                        <p class="text-xs text-blue-500">{{ __('mapscalculator.completed') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection