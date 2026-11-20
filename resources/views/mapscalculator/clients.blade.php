@extends('layouts.app')

@section('title', __('mapscalculator.clients_title'))

@section('content')
<div class="w-full max-w-[1600px] mx-auto px-4 py-8">
    @include('mapscalculator.partials.nav')

    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('mapscalculator.clients') }}</h1>
            <p class="text-gray-500 mt-1">{{ __('mapscalculator.clients_subtitle') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('mapscalculator.client.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                + {{ __('mapscalculator.new_client') }}
            </a>
            <a href="{{ route('mapscalculator.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                {{ __('mapscalculator.back_to_projects') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('mapscalculator.client.index') }}" class="mb-6 flex gap-2">
        <input type="search" name="q" value="{{ $search }}"
               placeholder="{{ __('mapscalculator.search_client') }}"
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium">
            {{ __('mapscalculator.search') }}
        </button>
        @if($search !== '')
            <a href="{{ route('mapscalculator.client.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                {{ __('mapscalculator.clear') }}
            </a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.email') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.phone') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.projects') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('mapscalculator.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($clients as $client)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('mapscalculator.client.show', $client) }}" class="font-medium text-primary-700 hover:underline">{{ $client->name }}</a>
                                @if($client->company)
                                    <div class="text-sm text-gray-500">{{ $client->company }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $client->email ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $client->phone ?: '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ $client->projects_count }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('mapscalculator.client.show', $client) }}" class="px-3 py-1.5 text-sm bg-primary-600 text-white rounded hover:bg-primary-700 transition">
                                    {{ __('mapscalculator.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                {{ $search !== '' ? __('mapscalculator.no_results') : __('mapscalculator.no_clients') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $clients->links() }}</div>
</div>
@endsection
