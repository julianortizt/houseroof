<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto {{ $quote->formatted_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
        .page { padding: 20mm; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #D97706; padding-bottom: 20px; }
        .logo { max-width: 200px; margin-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1F2937; }
        .company-info { font-size: 10px; color: #666; margin-top: 5px; }
        .quote-title { font-size: 18px; font-weight: bold; color: #D97706; margin-top: 15px; }
        .quote-meta { display: flex; justify-content: space-between; margin-top: 20px; font-size: 10px; }
        .meta-block { flex: 1; }
        .meta-label { font-weight: bold; color: #666; }
        .meta-value { margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #D97706; color: white; font-weight: bold; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { margin-top: 20px; float: right; width: 300px; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 10px; }
        .total-row.final { font-weight: bold; font-size: 12px; border-top: 2px solid #D97706; margin-top: 10px; padding-top: 10px; }
        .terms { margin-top: 40px; font-size: 9px; color: #666; }
        .terms h4 { font-size: 11px; color: #333; margin-bottom: 10px; }
        .footer { margin-top: 50px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
        @page { margin: 0; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            @if($quote->company_logo)
                <img src="{{ $quote->company_logo }}" alt="Logo" class="logo">
            @else
                <div class="company-name">{{ config('platform.name') }}</div>
            @endif
            <div class="company-info">
                @if($phone = \App\Models\Setting::get('site_phone'))
                    {{ $phone }}<br>
                @endif
                @if($email = \App\Models\Setting::get('site_email'))
                    {{ $email }}<br>
                @endif
                {{ config('app.url') }}
            </div>
            <div class="quote-title">PRESUPUESTO {{ $quote->formatted_number }}</div>
        </div>

        <div class="quote-meta">
            <div class="meta-block">
                <div class="meta-label">Cliente:</div>
                <div class="meta-value">{{ $quote->name }}</div>
                @if($quote->company)
                    <div class="meta-value">{{ $quote->company }}</div>
                @endif
                @if($quote->email)
                    <div class="meta-value">{{ $quote->email }}</div>
                @endif
                @if($quote->phone)
                    <div class="meta-value">{{ $quote->phone }}</div>
                @endif
                @if($quote->address)
                    <div class="meta-value">{{ $quote->address }}</div>
                    @if($quote->city)
                        , {{ $quote->city }} @if($quote->postal_code)({{ $quote->postal_code }})@endif
                    @endif
                @endif
            </div>
            <div class="meta-block" style="text-align: right;">
                <div class="meta-label">Fecha:</div>
                <div class="meta-value">{{ $quote->created_at->format('d/m/Y') }}</div>
                <div class="meta-label">Válido hasta:</div>
                <div class="meta-value">{{ $quote->valid_until->format('d/m/Y') }}</div>
                <div class="meta-label">Servicio:</div>
                <div class="meta-value">{{ $quote->service_type_label }}</div>
                @if($quote->area_m2)
                    <div class="meta-label">Área estimada:</div>
                    <div class="meta-value">{{ number_format($quote->area_m2, 2, ',', '.') }} m²</div>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Concepto</th>
                    <th style="width: 10%; text-align: center;">Cant.</th>
                    <th style="width: 10%; text-align: center;">Unidad</th>
                    <th style="width: 15%; text-align: right;">P. Unit.</th>
                    <th style="width: 10%; text-align: center;">Dto. %</th>
                    <th style="width: 15%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->items()->get() as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                        @if($item->description)
                            <br><span style="font-size: 9px; color: #666;">{{ $item->description }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $item->unit ?? 'ud' }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                    <td class="text-center">{{ number_format($item->discount_percent ?? 0, 2, ',', '.') }}%</td>
                    <td class="text-right">{{ number_format($item->total_price, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ number_format($quote->subtotal ?? $quote->items()->sum('total_price'), 2, ',', '.') }} €</span>
            </div>
            <div class="total-row">
                <span>IVA ({{ number_format($quote->tax_percent ?? 21, 2, ',', '.') }}%):</span>
                <span>{{ number_format($quote->tax_amount ?? ($quote->subtotal ?? $quote->items()->sum('total_price')) * (($quote->tax_percent ?? 21) / 100), 2, ',', '.') }} €</span>
            </div>
            <div class="total-row final">
                <span>TOTAL:</span>
                <span>{{ number_format($quote->total ?? ($quote->subtotal ?? $quote->items()->sum('total_price')) + ($quote->tax_amount ?? ($quote->subtotal ?? $quote->items()->sum('total_price')) * (($quote->tax_percent ?? 21) / 100)), 2, ',', '.') }} €</span>
            </div>
        </div>

        @if($quote->deposit_target_cents > 0)
        <div class="terms">
            <h4>Condiciones de pago:</h4>
            <p>Anticipo inicial ({{ number_format($quote->deposit_percent, 2, ',', '.') }}%): {{ number_format($quote->deposit_target_cents / 100, 2, ',', '.') }} €. El saldo se solicitará al finalizar el trabajo.</p>
        </div>
        @endif

        @if($quote->terms_conditions)
        <div class="terms">
            <h4>Condiciones:</h4>
            <p>{{ nl2br($quote->terms_conditions) }}</p>
        </div>
        @endif

        <div class="footer">
            {{ config('platform.name') }}<br>
            Generado el {{ now()->format('d/m/Y H:i') }} - Presupuesto válido hasta {{ $quote->valid_until->format('d/m/Y') }}
        </div>
    </div>
</body>
</html>