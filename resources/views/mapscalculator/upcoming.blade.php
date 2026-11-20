@extends('layouts.app')

@section('title', __('Upcoming work') . ' - ' . config('platform.name'))

@section('content')
<div class="w-full max-w-[1600px] mx-auto px-4 py-8">
    @include('mapscalculator.partials.nav')

    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('mapscalculator.upcoming_works') }}</h1>
            <p class="text-gray-500 mt-1">{{ __('mapscalculator.upcoming_works_subtitle') }}</p>
        </div>
        <a href="{{ route('mapscalculator.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            {{ __('mapscalculator.back_to_projects') }}
        </a>
    </div>

    <form method="GET" action="{{ route('mapscalculator.work-order.upcoming') }}" class="mb-6 grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-2 lg:grid-cols-6">
        <div>
            <label for="q" class="mb-1 block text-xs font-medium text-gray-500">{{ __('mapscalculator.search_project') }}</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm" type="search">
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-medium text-gray-500">{{ __('mapscalculator.status') }}</label>
            <select id="status" name="status" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="">{{ __('mapscalculator.active_work_orders') }}</option>
                @foreach([
                    'agendada' => 'status_agendada',
                    'en_preparacion' => 'status_en_preparacion',
                    'en_ejecucion' => 'status_en_ejecucion',
                    'finalizada' => 'status_finalizada',
                    'entregada' => 'status_entregada',
                    'cancelada' => 'status_cancelada',
                ] as $status => $label)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('mapscalculator.' . $label) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="assigned_to" class="mb-1 block text-xs font-medium text-gray-500">{{ __('mapscalculator.assigned_to') }}</label>
            <select id="assigned_to" name="assigned_to" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="">{{ __('mapscalculator.all_assignees') }}</option>
                @foreach(\App\Models\User::query()->whereIn('mapscalculator_role', ['admin', 'comercial', 'tecnico'])->orderBy('name')->get() as $user)
                    <option value="{{ $user->id }}" @selected((string) ($filters['assigned_to'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="from" class="mb-1 block text-xs font-medium text-gray-500">{{ __('mapscalculator.date_from') }}</label>
            <input id="from" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm" type="date">
        </div>
        <div>
            <label for="to" class="mb-1 block text-xs font-medium text-gray-500">{{ __('mapscalculator.date_to') }}</label>
            <input id="to" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm" type="date">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">{{ __('mapscalculator.apply_filters') }}</button>
            <a href="{{ route('mapscalculator.work-order.upcoming') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">{{ __('mapscalculator.clear_filters') }}</a>
        </div>
    </form>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($workOrders as $workOrder)
            @php
                $start = $workOrder->scheduled_start;
                $overdue = $start && $start->lt($today) && $workOrder->status !== 'finalizada';
            @endphp
            <div class="bg-white rounded-xl border shadow-sm p-5 {{ $overdue ? 'border-red-300' : '' }}">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $workOrder->project->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $workOrder->project->client->name }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 whitespace-nowrap">{{ $workOrder->status_label }}</span>
                </div>

                <dl class="text-sm space-y-1 mb-4">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">{{ __('mapscalculator.scheduled_start') }}</dt>
                        <dd class="font-medium {{ $overdue ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $start ? $start->format('d/m/Y') : '—' }}
                            @if($overdue) ({{ __('mapscalculator.overdue') }}) @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">{{ __('mapscalculator.scheduled_end') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $workOrder->scheduled_end?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">{{ __('mapscalculator.assigned_to') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $workOrder->assignee?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">{{ __('mapscalculator.area') }}</dt>
                        <dd class="font-medium text-gray-900">{{ number_format($workOrder->project->total_area, 2, ',', '.') }} m²</dd>
                    </div>
                </dl>

                <div class="mb-4">
                    <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-600" style="width: {{ $workOrder->progress }}%"></div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('mapscalculator.work-order.show', $workOrder) }}" class="flex-1 text-center px-3 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                        {{ __('mapscalculator.work_order') }}
                    </a>
                    <a href="{{ route('mapscalculator.work-order.report', $workOrder) }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
                        {{ __('mapscalculator.report') }}
                    </a>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 lg:col-span-3 bg-white rounded-xl border shadow-sm p-12 text-center text-gray-500">
                {{ __('mapscalculator.no_upcoming_works') }}
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $workOrders->links() }}</div>
</div>
@endsection
