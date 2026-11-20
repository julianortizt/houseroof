@extends('layouts.app')

@section('title', __('mapscalculator.index_title'))

@section('content')
<div class="w-full max-w-[1600px] mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ config('platform.name') }} — {{ __('MapsCalculator') }}</h1>
            <p class="text-gray-500 mt-1">{{ __('mapscalculator.index_subtitle') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('mapscalculator.client.index') }}" class="px-4 py-3 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium">
                {{ __('mapscalculator.clients') }}
            </a>
            @if(app(\App\Services\PlatformModules::class)->enabled('work_orders'))
                <a href="{{ route('mapscalculator.work-order.upcoming') }}" class="px-4 py-3 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium">
                    {{ __('mapscalculator.upcoming_works') }}
                </a>
            @endif
            <a href="{{ route('mapscalculator.project.create') }}" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('mapscalculator.new_project') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.project') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.client') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.area') }} (m²)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.created') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($projects as $project)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $project->name }}</div>
                                <div class="text-sm text-gray-500">{{ $project->measurements->count() }} {{ __('mapscalculator.measurements') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $project->client_name }}</div>
                                @if($project->client?->email)
                                    <div class="text-sm text-gray-500">{{ $project->client->email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @switch($project->type)
                                        @case('interior') bg-blue-100 text-blue-800 @break
                                        @case('exterior') bg-green-100 text-green-800 @break
                                        @case('mixto') bg-yellow-100 text-yellow-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch
                                ">{{ $project->type_label }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
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
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ number_format($project->total_area, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $project->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('mapscalculator.project.show', $project) }}" class="px-3 py-1.5 text-sm bg-primary-600 text-white rounded hover:bg-primary-700 transition">{{ __('mapscalculator.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <p class="text-lg font-medium">{{ __('mapscalculator.no_projects') }}</p>
                                <p class="text-sm mt-1">{{ __('mapscalculator.no_projects_hint') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $projects->links() }}
    </div>
</div>
@endsection