@extends('layouts.app')

@section('title', __('My account') . ' — ' . config('platform.public_name'))

@section('content')
<main class="min-h-[65vh] bg-gray-50 px-4 py-12">
    <div class="mx-auto max-w-6xl">
        <header class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-amber-700">{{ __('Customer portal') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">{{ __('Hello') }}, {{ $customer->name }}</h1>
                <p class="mt-2 text-gray-600">{{ $customer->email }}</p>
            </div>
            <form method="POST" action="{{ route('customer.logout') }}">
                @csrf
                <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-white">{{ __('Sign out') }}</button>
            </form>
        </header>

        @if (session('status') === 'email-verified')
            <p class="mt-6 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ __('Your email has been verified successfully.') }}</p>
        @endif
        @if (session('status') === 'quote-response-saved')
            <p class="mt-6 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ __('Your response to the quote has been recorded.') }}</p>
        @endif
        @if (request('payment') === 'success')
            <p class="mt-6 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ __('Payment was sent to Stripe. Confirmation will appear here once processed.') }}</p>
        @elseif (request('payment') === 'cancelled')
            <p class="mt-6 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">{{ __('Payment was not completed; you can try again from your quote.') }}</p>
        @endif

        @if ($projects->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-xl font-bold text-gray-900">{{ __('My projects and work orders') }}</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ($projects as $project)
                        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h3 class="font-semibold text-gray-900">{{ $project->name }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Status') }}: {{ $project->status_label }}</p>
                            @if ($project->workOrder)
                                <p class="mt-1 text-sm text-gray-600">{{ __('Order') }} #{{ $project->workOrder->id }} · {{ $project->workOrder->status_label }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($quotes->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-xl font-bold text-gray-900">{{ __('My quotes') }}</h2>
                <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 bg-white">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr><th class="px-4 py-3">{{ __('Reference') }}</th><th class="px-4 py-3">{{ __('Project') }}</th><th class="px-4 py-3">{{ __('Status') }}</th><th class="px-4 py-3 text-right">{{ __('Total') }}</th><th class="px-4 py-3">{{ __('Payments') }}</th><th class="px-4 py-3">{{ __('Action') }}</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($quotes as $quote)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $quote->formatted_number ?? ('#' . $quote->id) }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $quote->project?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $quote->status_label }}</td>
                                    <td class="px-4 py-3 text-right text-gray-900">{{ number_format((float) $quote->total, 2, ',', '.') }} €</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        @if (in_array($quote->status, ['enviado', 'aprobado'], true))
                                            <div>{{ __('Initial deposit') }} ({{ number_format($quote->deposit_percent, 2, ',', '.') }}%): {{ number_format($quote->payment_summary['deposit_target_cents'] / 100, 2, ',', '.') }} €</div>
                                            <div class="mt-1 text-xs">{{ __('The balance will be requested when the work is completed.') }}</div>
                                            @if ($quote->status === 'aprobado' && $quote->payment_summary['ready'])
                                                <div class="mt-1">{{ __('Paid') }}: {{ number_format($quote->payment_summary['paid_cents'] / 100, 2, ',', '.') }} €</div>
                                                @if ($quote->payment_summary['can_pay_deposit'])
                                                    <form method="POST" action="{{ route('customer.quotes.payment', [$quote, 'deposit']) }}" class="mt-2">
                                                        @csrf
                                                        <button class="rounded bg-amber-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-800">{{ __('Pay deposit') }}</button>
                                                    </form>
                                                @elseif ($quote->payment_summary['can_pay_balance'])
                                                    <form method="POST" action="{{ route('customer.quotes.payment', [$quote, 'balance']) }}" class="mt-2">
                                                        @csrf
                                                        <button class="rounded bg-amber-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-800">{{ __('Pay final balance') }}</button>
                                                    </form>
                                                @elseif ($quote->payment_summary['deposit_due_cents'] === 0 && $quote->payment_summary['balance_due_cents'] > 0)
                                                    <div class="mt-1 text-xs">{{ __('The balance will be enabled when the work is completed.') }}</div>
                                                @endif
                                            @endif
                                            @foreach ($quote->payments as $payment)
                                                <div class="mt-1 text-xs">{{ $payment->payment_type_label }} · {{ $payment->status_label }} · {{ number_format($payment->amount_cents / 100, 2, ',', '.') }} €</div>
                                            @endforeach
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if (in_array($quote->status, ['enviado', 'aprobado', 'rechazado', 'expirado'], true))
                                            <a href="{{ route('customer.quotes.pdf', $quote) }}" class="mb-2 inline-flex rounded border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('Download PDF') }}</a>
                                        @endif
                                        @if ($quote->status === 'enviado' && (! $quote->valid_until || $quote->valid_until->isToday() || $quote->valid_until->isFuture()))
                                            <form method="POST" action="{{ route('customer.quotes.respond', $quote) }}" class="flex flex-wrap gap-2">
                                                @csrf
                                                <button name="decision" value="approved" class="rounded bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800">{{ __('Accept') }}</button>
                                                <button name="decision" value="rejected" class="rounded border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('Reject') }}</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($orders->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-xl font-bold text-gray-900">{{ __('My orders') }}</h2>
                <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 bg-white">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr><th class="px-4 py-3">{{ __('Order') }}</th><th class="px-4 py-3">{{ __('Status') }}</th><th class="px-4 py-3">{{ __('Payment') }}</th><th class="px-4 py-3 text-right">{{ __('Total') }}</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $order->order_number }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $order->status_label }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $order->payment_status_label }}</td>
                                    <td class="px-4 py-3 text-right text-gray-900">{{ $order->getTotalFormatted() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($projects->isEmpty() && $quotes->isEmpty() && $orders->isEmpty())
            <section class="mt-10 rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center">
                <h2 class="font-semibold text-gray-900">{{ __('No activity yet') }}</h2>
                <p class="mt-2 text-sm text-gray-600">{{ __('When we register a project, quote or order with this email, it will appear here.') }}</p>
            </section>
        @endif
    </div>
</main>
@endsection
