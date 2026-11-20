@extends('layouts.app')

@section('title', __('mapscalculator.quote_page_title') . ' - ' . $project->name)

@section('content')
<div class="w-full max-w-6xl mx-auto px-4 py-4">
    @include('mapscalculator.partials.nav')

    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('mapscalculator.project.show', $project) }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('mapscalculator.quote_page_title') }}: {{ $project->name }}</h1>
                <p class="text-gray-500">{{ __('mapscalculator.client') }}: {{ $project->client_name }} • {{ __('mapscalculator.total_area') }}: {{ number_format($project->total_area, 2, ',', '.') }} m²</p>
            </div>
        </div>
        <a href="{{ route('mapscalculator.project.show', $project) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.back') }}</a>
    </div>

    @if($quote)
        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ __('mapscalculator.quote_existing') }}</h2>
                    <p class="text-gray-500">{{ __('mapscalculator.quote_no') }} {{ $quote->formatted_number }} • {{ __('mapscalculator.status') }}: <span class="px-2 py-0.5 text-xs font-medium rounded-full
                        @switch($quote->status)
                            @case('borrador') bg-gray-100 text-gray-800 @break
                            @case('enviado') bg-blue-100 text-blue-800 @break
                            @case('aprobado') bg-green-100 text-green-800 @break
                            @case('rechazado') bg-red-100 text-red-800 @break
                            @case('expirado') bg-yellow-100 text-yellow-800 @break
                            @default bg-gray-100 text-gray-800
                        @endswitch
                    ">{{ $quote->status_label }}</span></p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('mapscalculator.quote.pdf', $quote) }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">PDF</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.concept') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.qty') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.unit_price') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.discount') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($quoteItems as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                                <td class="px-4 py-3 text-right">{{ $item->discount_percent }}%</td>
                                <td class="px-4 py-3 text-right font-medium">{{ number_format($item->total_price, 2, ',', '.') }} €</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end pt-4 border-t">
                <div class="text-right">
                    <div class="text-sm text-gray-500">{{ __('mapscalculator.subtotal') }}</div>
                    <div class="text-xl font-bold">{{ number_format($quote->subtotal, 2, ',', '.') }} €</div>
                </div>
                <div class="text-right ml-8">
                    <div class="text-sm text-gray-500">{{ __('mapscalculator.vat') }} ({{ number_format($quote->tax_percent, 2, ',', '.') }}%)</div>
                    <div class="text-xl font-bold text-amber-600">{{ number_format($quote->tax_amount, 2, ',', '.') }} €</div>
                </div>
                <div class="text-right ml-8">
                    <div class="text-sm text-gray-500">{{ __('mapscalculator.total') }}</div>
                    <div class="text-2xl font-bold text-primary-600">{{ number_format($quote->total, 2, ',', '.') }} €</div>
                </div>
            </div>
            @if($quote->deposit_target_cents > 0)
                <p class="border-t pt-4 text-sm text-gray-600">
                    Anticipo inicial ({{ number_format($quote->deposit_percent, 2, ',', '.') }}%):
                    {{ number_format($quote->deposit_target_cents / 100, 2, ',', '.') }} €.
                    El saldo se solicitará al finalizar el trabajo.
                </p>
            @endif

            @if($quote->status === 'borrador')
                <div class="flex gap-3 pt-4 border-t">
                    <form method="POST" action="{{ route('mapscalculator.quote.send', $quote) }}">@csrf <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">{{ __('mapscalculator.send_to_client') }}</button></form>
                    <a href="{{ route('mapscalculator.quote.pdf', $quote) }}" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition flex items-center">{{ __('mapscalculator.view_pdf') }}</a>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
                {{-- Sin wire:submit: esta página no es un componente Livewire (el
                     form es HTML normal). --}}
            <form method="POST" action="{{ route('mapscalculator.quote.store', $project) }}">
                @csrf
                {{-- El índice debe ser único en TODA la tabla: antes se usaba
                     $loop->index dentro de cada categoría, así que las líneas de
                     categorías distintas se pisaban entre sí al enviar. --}}
                @php $itemIndex = 0; @endphp
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('mapscalculator.generate_quote') }}</h2>
                    <p class="text-gray-500">{{ __('mapscalculator.generate_quote_desc', ['area' => number_format($project->total_area, 2, ',', '.')]) }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.concept') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.qty') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.unit_price') }} (€)</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.discount') }} %</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.total') }} (€)</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($priceItems as $category => $items)
                                <tr class="bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-700 uppercase text-sm">{{ \App\Models\MapsCalculator\PriceItem::categoryLabel($category) }}</td>
                                    <td></td><td></td><td></td><td></td><td></td>
                                </tr>
                                @foreach($items as $priceItem)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="hidden" name="items[{{ $itemIndex }}][price_item_id]" value="{{ $priceItem->id }}">
                                            <input type="text" name="items[{{ $itemIndex }}][name]" value="{{ $priceItem->name }}" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0" name="items[{{ $itemIndex }}][quantity]" value="{{ ($priceItem->unit ?? null) === 'ml' || $priceItem->category === 'zocalo_perimetral' ? $project->total_perimeter : $project->total_area }}" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0" name="items[{{ $itemIndex }}][unit_price]" value="{{ $priceItem->unit_price_m2 }}" class="w-24 px-2 py-1 border border-gray-300 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0" max="100" name="items[{{ $itemIndex }}][discount_percent]" value="0" class="w-16 px-2 py-1 border border-gray-300 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-primary-600" id="item-total-{{ $itemIndex }}">0,00</td>
                                        @php $itemIndex++; @endphp
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" onclick="removeItem(this)" class="text-red-500 hover:text-red-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v12m-6 0h12a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12"></path></svg></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.vat') }} %</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_percent" value="21" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" oninput="calculateTotals()">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.conditions') }}</label>
                        <textarea name="terms_conditions" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">{{ __('mapscalculator.default_terms') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-4 border-t">
                    <div class="text-right">
                        <div class="text-sm text-gray-500">{{ __('mapscalculator.subtotal') }}</div>
                        <div class="text-xl font-bold" id="subtotal-display">0,00 €</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">{{ __('mapscalculator.vat') }}</div>
                        <div class="text-xl font-bold text-amber-600" id="tax-display">0,00 €</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">{{ __('mapscalculator.total') }}</div>
                        <div class="text-2xl font-bold text-primary-600" id="total-display">0,00 €</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium text-lg">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('mapscalculator.create_quote') }}
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('tbody tr:not(.bg-gray-50)').forEach(row => {
        const qty = parseFloat(row.querySelector('input[name$="[quantity]"]')?.value) || 0;
        const price = parseFloat(row.querySelector('input[name$="[unit_price]"]')?.value) || 0;
        const discount = parseFloat(row.querySelector('input[name$="[discount_percent]"]')?.value) || 0;
        const total = qty * price * (1 - discount / 100);
        row.querySelector('[id^="item-total-"]').textContent = total.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
        subtotal += total;
    });

    const taxPercent = parseFloat(document.querySelector('input[name="tax_percent"]')?.value) || 21;
    const tax = subtotal * (taxPercent / 100);
    const total = subtotal + tax;

    document.getElementById('subtotal-display').textContent = subtotal.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
    document.getElementById('tax-display').textContent = tax.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
    document.getElementById('total-display').textContent = total.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
}

function removeItem(btn) {
    btn.closest('tr').remove();
    calculateTotals();
}

// Initial calculation
document.addEventListener('DOMContentLoaded', calculateTotals);
document.addEventListener('input', (e) => {
    if (e.target.matches('input[name$="[quantity]"], input[name$="[unit_price]"], input[name$="[discount_percent]"], input[name="tax_percent"]')) {
        calculateTotals();
    }
});
</script>
@endpush