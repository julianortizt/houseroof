@extends('layouts.app')

@section('title', $client->name . ' - ' . config('platform.name'))

@section('content')
<div class="w-full max-w-[1600px] mx-auto px-4 py-8">
    @include('mapscalculator.partials.nav')

    <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('mapscalculator.client.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ __('mapscalculator.clients') }}</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-1">{{ $client->name }}</h1>
            <p class="text-gray-500 mt-1">
                @if($client->company){{ $client->company }} • @endif
                {{ $client->email ?: __('mapscalculator.no_email') }}
                @if($client->phone) • {{ $client->phone }}@endif
            </p>
            @if($client->address)
                <p class="text-sm text-gray-500 mt-1">{{ $client->address }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @if($client->portal_token)
                <a href="{{ $client->portal_url }}" target="_blank" rel="noopener"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    {{ __('mapscalculator.open_portal') }}
                </a>
            @endif
            <a href="{{ route('mapscalculator.project.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium">
                {{ __('mapscalculator.new_project') }}
            </a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white rounded-xl border shadow-sm p-4">
            <div class="text-sm text-gray-500">{{ __('mapscalculator.projects') }}</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['projects'] }}</div>
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-4">
            <div class="text-sm text-gray-500">{{ __('mapscalculator.measured_area') }}</div>
            <div class="text-2xl font-bold text-primary-600">{{ number_format($stats['area'], 2, ',', '.') }} m²</div>
        </div>
        @if($quotesEnabled)
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <div class="text-sm text-gray-500">{{ __('mapscalculator.quoted_total') }}</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['quoted'], 2, ',', '.') }} €</div>
            </div>
        @endif
        @if($workOrdersEnabled)
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <div class="text-sm text-gray-500">{{ __('mapscalculator.finished_works') }}</div>
                <div class="text-2xl font-bold text-green-600">{{ $stats['finished_works'] }}/{{ $stats['work_orders'] }}</div>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b">
            <h2 class="font-semibold text-gray-900">{{ __('mapscalculator.projects') }} ({{ $stats['projects'] }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.project') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.area') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.created') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($client->projects as $project)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('mapscalculator.project.show', $project) }}" class="font-medium text-primary-700 hover:underline">{{ $project->name }}</a>
                                <div class="text-xs text-gray-500">{{ $project->type_label }} • {{ $project->measurements->count() }} {{ __('mapscalculator.measurements') }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ $project->status_label }}</span>
                            </td>
                            <td class="px-6 py-3 text-right">{{ number_format($project->total_area, 2, ',', '.') }} m²</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $project->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">{{ __('mapscalculator.no_projects_client') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($quotesEnabled)
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b">
            <h2 class="font-semibold text-gray-900">{{ __('mapscalculator.quotes') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.quote') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.total') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.pdf') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($client->quotes->sortByDesc('created_at') as $quote)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium">{{ $quote->formatted_number }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ $quote->status_label }}</span>
                            </td>
                            <td class="px-6 py-3 text-right font-medium">{{ $quote->formatted_total }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('mapscalculator.quote.pdf.view', $quote) }}" target="_blank" rel="noopener" class="text-primary-700 hover:underline text-sm">
                                    {{ __('mapscalculator.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">{{ __('mapscalculator.no_quotes') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($client->notes)
        <div class="bg-white rounded-xl border shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-2">{{ __('mapscalculator.internal_notes') }}</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $client->notes }}</p>
        </div>
    @endif
</div>
@endsection
