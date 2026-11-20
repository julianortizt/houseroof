<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('mapscalculator.quote_page_title') }} {{ $quote->formatted_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .page { padding: 30mm 20mm; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 3px solid #1e3a5f; padding-bottom: 20px; }
        .logo { font-size: 28px; font-weight: bold; color: #1e3a5f; }
        .logo span { color: #e67e22; }
        .quote-title { text-align: right; }
        .quote-title h1 { font-size: 24px; color: #1e3a5f; margin-bottom: 5px; }
        .quote-title .quote-number { font-size: 16px; color: #666; }
        .quote-title .quote-date { font-size: 12px; color: #999; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #1e3a5f; }
        .info-box h3 { font-size: 12px; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .info-box p { margin: 4px 0; }
        .info-box .label { font-weight: bold; color: #555; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #1e3a5f; color: white; padding: 12px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        .items-table td { padding: 10px; border-bottom: 1px solid #eee; font-size: 11px; }
        .items-table tr:last-child td { border-bottom: 2px solid #1e3a5f; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .item-name { font-weight: 600; }
        .item-desc { color: #666; font-size: 10px; margin-top: 2px; }
        .totals { width: 100%; max-width: 350px; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 8px 15px; font-size: 11px; }
        .totals .label { text-align: right; color: #555; }
        .totals .value { text-align: right; font-weight: 600; }
        .totals .subtotal .value { color: #333; }
        .totals .tax .value { color: #e67e22; }
        .totals .total { border-top: 2px solid #1e3a5f; }
        .totals .total .value { font-size: 16px; color: #1e3a5f; font-weight: bold; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 10px; color: #888; }
        .footer h4 { font-size: 11px; color: #333; margin-bottom: 10px; }
        .terms { font-size: 9px; color: #999; line-height: 1.6; }
        .validity { text-align: right; margin-top: 15px; font-size: 10px; color: #666; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-borrador { background: #f3f4f6; color: #374151; }
        .status-enviado { background: #dbeafe; color: #1e40af; }
        .status-aprobado { background: #dcfce7; color: #166534; }
        .status-rechazado { background: #fee2e2; color: #991b1b; }
        .status-expirado { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="page">
        {{-- Membrete: logo de la empresa desde Ajustes (o el logo de texto si no
             hay imagen configurada) más los datos fiscales. --}}
        <div class="header">
            <div class="logo">
                @if($company['logo'] ?? null)
                    <img src="{{ $company['logo'] }}" alt="{{ $company['name'] }}" style="max-height: 70px; max-width: 220px;">
                    <div style="font-size: 9px; color: #666; margin-top: 6px; font-weight: normal;">
                        {{ $company['name'] }}
                        @if($company['nif']) · {{ __('mapscalculator.nif') }}: {{ $company['nif'] }} @endif
                    </div>
                @else
                    <div>{{ $company['name'] }}</div>
                @endif
            </div>
            <div class="quote-title">
                <h1>{{ __('mapscalculator.quote_page_title') }}</h1>
                <div class="quote-number">{{ $quote->formatted_number }}</div>
                <div class="quote-date">{{ __('mapscalculator.date') }}: {{ $quote->created_at->format('d/m/Y') }}</div>
                <div class="quote-date mt-2"><span class="status-badge status-{{ $quote->status }}">{{ $quote->status_label }}</span></div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>{{ __('mapscalculator.client_data') }}</h3>
                <p><span class="label">{{ __('mapscalculator.name') }}:</span> {{ $quote->name }}</p>
                @if($quote->project && $quote->project->client->company)
                    <p><span class="label">{{ __('mapscalculator.company') }}:</span> {{ $quote->project->client->company }}</p>
                @endif
                @if($quote->email)
                    <p><span class="label">{{ __('mapscalculator.email') }}:</span> {{ $quote->email }}</p>
                @endif
                @if($quote->phone)
                    <p><span class="label">{{ __('mapscalculator.phone') }}:</span> {{ $quote->phone }}</p>
                @endif
                @if($quote->address)
                    <p><span class="label">{{ __('mapscalculator.address') }}:</span> {{ $quote->address }}</p>
                @endif
                @if($quote->project)
                    <p><span class="label">{{ __('mapscalculator.project') }}:</span> {{ $quote->project->name }}</p>
                    <p><span class="label">{{ __('mapscalculator.type') }}:</span> {{ $quote->project->type_label }}</p>
                    @if($quote->project->total_area > 0)
                        <p><span class="label">{{ __('mapscalculator.total_area') }}:</span> {{ number_format($quote->project->total_area, 2, ',', '.') }} m²</p>
                    @endif
                    @if($quote->project->total_perimeter > 0)
                        <p><span class="label">{{ __('mapscalculator.total_perimeter') }}:</span> {{ number_format($quote->project->total_perimeter, 2, ',', '.') }} ml</p>
                    @endif
                @endif
            </div>

            <div class="info-box">
                <h3>{{ __('mapscalculator.company_data') }}</h3>
                <p><span class="label">{{ __('mapscalculator.company_name') }}:</span> {{ $company['name'] }}</p>
                @if($company['nif'])
                    <p><span class="label">{{ __('mapscalculator.nif') }}:</span> {{ $company['nif'] }}</p>
                @endif
                @if($company['address'])
                    <p><span class="label">{{ __('mapscalculator.address') }}:</span> {{ $company['address'] }}</p>
                @endif
                @if($company['phone'])
                    <p><span class="label">{{ __('mapscalculator.phone') }}:</span> {{ $company['phone'] }}</p>
                @endif
                @if($company['email'])
                    <p><span class="label">{{ __('mapscalculator.email') }}:</span> {{ $company['email'] }}</p>
                @endif
            </div>

            <div class="info-box">
                <h3>{{ __('mapscalculator.quote_details') }}</h3>
                @if($quote->area_m2)
                    <p><span class="label">{{ __('mapscalculator.area_to_resin') }}:</span> {{ number_format($quote->area_m2, 2, ',', '.') }} m²</p>
                @endif
                @if($quote->valid_until)
                    <p><span class="label">{{ __('mapscalculator.valid_until') }}:</span> {{ $quote->valid_until->format('d/m/Y') }}</p>
                @endif
                <p><span class="label">{{ __('mapscalculator.vat_applied') }}:</span> {{ number_format($quote->tax_percent, 2, ',', '.') }}%</p>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">{{ __('mapscalculator.no_short') }}</th>
                    <th style="width: 40%;">{{ __('mapscalculator.concept') }}</th>
                    <th style="width: 10%;" class="text-center">{{ __('mapscalculator.qty') }}</th>
                    <th style="width: 15%;" class="text-right">{{ __('mapscalculator.unit_price') }}</th>
                    <th style="width: 10%;" class="text-center">{{ __('mapscalculator.discount') }}</th>
                    <th style="width: 20%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->items()->get() as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description)
                        <div class="item-desc">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                    <td class="text-center">{{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '—' }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Detalle de la superficie medida con el croquis del polígono. --}}
        @if($measurements->isNotEmpty())
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 12px; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                {{ __('mapscalculator.measured_surface') }}
            </h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">{{ __('mapscalculator.measurement') }}</th>
                        <th style="width: 15%;" class="text-center">{{ __('mapscalculator.type') }}</th>
                        <th style="width: 22%;" class="text-right">{{ __('mapscalculator.area') }}</th>
                        <th style="width: 23%;" class="text-right">{{ __('mapscalculator.perimeter') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($measurements as $measurement)
                    <tr>
                        <td class="item-name">{{ $measurement->label ?: __('mapscalculator.measurement_short', ['id' => $measurement->id]) }}</td>
                        <td class="text-center">{{ __('mapscalculator.type_' . $measurement->type) }}</td>
                        <td class="text-right">{{ number_format((float) $measurement->area_m2, 2, ',', '.') }} m²</td>
                        <td class="text-right">{{ number_format((float) $measurement->perimeter_m, 2, ',', '.') }} ml</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if(!empty($sketches))
                <div style="margin-top: 10px;">
                    @foreach($sketches as $measurementId => $sketch)
                        <div style="display: inline-block; width: 48%; vertical-align: top; margin: 0 1% 10px 0; text-align: center;">
                            <img src="{{ $sketch }}" alt="Captura de la medición" style="width: 100%; border: 1px solid #ddd; border-radius: 6px;">
                            <div style="font-size: 9px; color: #666; margin-top: 3px;">
                                {{ $measurements->firstWhere('id', $measurementId)?->label ?: __('mapscalculator.measurement_short', ['id' => $measurementId]) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endif

        <table class="totals">
            <tr class="subtotal">
                <td class="label">{{ __('mapscalculator.subtotal') }}</td>
                <td class="value">{{ number_format($quote->subtotal, 2, ',', '.') }} €</td>
            </tr>
            @if($quote->tax_percent > 0)
            <tr class="tax">
                <td class="label">{{ __('mapscalculator.vat') }} ({{ number_format($quote->tax_percent, 2, ',', '.') }}%)</td>
                <td class="value">{{ number_format($quote->tax_amount, 2, ',', '.') }} €</td>
            </tr>
            @endif
            <tr class="total">
                <td class="label">Total</td>
                <td class="value">{{ number_format($quote->total, 2, ',', '.') }} €</td>
            </tr>
        </table>

        <div class="footer">
            @if($quote->deposit_target_cents > 0)
            <h4>Condiciones de pago</h4>
            <div class="terms">
                Anticipo inicial ({{ number_format($quote->deposit_percent, 2, ',', '.') }}%):
                {{ number_format($quote->deposit_target_cents / 100, 2, ',', '.') }} €.
                El saldo se solicitará al finalizar el trabajo.
            </div>
            @endif

            @if($quote->terms_conditions)
            <h4>{{ __('mapscalculator.conditions') }}</h4>
            <div class="terms">{{ $quote->terms_conditions }}</div>
            @endif

            <div class="validity">
                {{ __('mapscalculator.valid_until') }}: {{ $quote->valid_until?->format('d/m/Y') ?? '—' }}
            </div>

            <div style="margin-top: 20px; text-align: center; color: #999; font-size: 9px;">
                {{ config('platform.name') }} - {{ config('app.url') }}<br>
                {{ __('mapscalculator.generated_on', ['date' => now()->format('d/m/Y H:i')]) }}
            </div>
        </div>
    </div>
</body>
</html>