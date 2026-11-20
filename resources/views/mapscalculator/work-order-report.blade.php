<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ __('mapscalculator.work_report') }} #{{ $workOrder->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .page { padding: 20mm 15mm; }
        .header { border-bottom: 3px solid #1e3a5f; padding-bottom: 14px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; color: #1e3a5f; }
        .header .sub { color: #666; font-size: 12px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
        .meta td { padding: 6px 10px; background: #f8f9fa; border: 1px solid #eceff2; font-size: 11px; }
        .meta .label { color: #666; width: 22%; font-weight: bold; }
        h2 { font-size: 13px; color: #1e3a5f; text-transform: uppercase; letter-spacing: .5px; margin: 22px 0 10px; border-bottom: 1px solid #dfe3e8; padding-bottom: 5px; }
        table.logs { width: 100%; border-collapse: collapse; }
        table.logs th { background: #1e3a5f; color: #fff; padding: 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.logs td { padding: 8px; border-bottom: 1px solid #eee; vertical-align: top; font-size: 10px; }
        .photos { margin-top: 8px; }
        .photo { display: inline-block; width: 30%; margin: 0 2% 10px 0; vertical-align: top; }
        .photo img { width: 100%; border: 1px solid #ddd; border-radius: 4px; }
        .photo .cap { font-size: 9px; color: #777; text-align: center; margin-top: 3px; }
        .totals { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 10px; }
        .totals .label { text-align: right; color: #555; }
        .totals .value { text-align: right; font-weight: bold; }
        .signature { margin-top: 45px; width: 100%; }
        .signature td { width: 50%; padding-top: 30px; border-top: 1px solid #999; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>{{ __('mapscalculator.work_report') }}</h1>
        <div class="sub">{{ config('platform.name') }} — {{ now()->format('d/m/Y') }}</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">{{ __('mapscalculator.project') }}</td>
            <td>{{ $workOrder->project->name }}</td>
            <td class="label">{{ __('mapscalculator.client') }}</td>
            <td>{{ $workOrder->project->client->name }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('mapscalculator.status') }}</td>
            <td>{{ $workOrder->status_label }} ({{ $workOrder->progress }}%)</td>
            <td class="label">{{ __('mapscalculator.assigned_to') }}</td>
            <td>{{ $workOrder->assignee?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('mapscalculator.scheduled_start') }}</td>
            <td>{{ $workOrder->scheduled_start?->format('d/m/Y') ?? '—' }}</td>
            <td class="label">{{ __('mapscalculator.scheduled_end') }}</td>
            <td>{{ $workOrder->scheduled_end?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('mapscalculator.actual_start') }}</td>
            <td>{{ $workOrder->actual_start?->format('d/m/Y') ?? '—' }}</td>
            <td class="label">{{ __('mapscalculator.actual_end') }}</td>
            <td>{{ $workOrder->actual_end?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('mapscalculator.total_area') }}</td>
            <td>{{ number_format($workOrder->project->total_area, 2, ',', '.') }} m²</td>
            <td class="label">{{ __('mapscalculator.perimeter') }}</td>
            <td>{{ number_format($workOrder->project->total_perimeter, 2, ',', '.') }} m</td>
        </tr>
    </table>

    <h2>{{ __('mapscalculator.measurements') }}</h2>
    <table class="logs">
        <thead>
        <tr>
            <th>{{ __('mapscalculator.label') }}</th>
            <th>{{ __('mapscalculator.type') }}</th>
            <th style="text-align:right">{{ __('mapscalculator.area') }}</th>
            <th style="text-align:right">{{ __('mapscalculator.perimeter') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($workOrder->project->measurements as $measurement)
            <tr>
                <td>{{ $measurement->label }}</td>
                <td>{{ __('mapscalculator.type_' . $measurement->type) }}</td>
                <td style="text-align:right">{{ number_format((float) $measurement->area_m2, 2, ',', '.') }} m²</td>
                <td style="text-align:right">{{ number_format((float) $measurement->perimeter_m, 2, ',', '.') }} m</td>
            </tr>
        @empty
            <tr><td colspan="4">—</td></tr>
        @endforelse
        </tbody>
    </table>

    @if($quote = $workOrder->project->latestQuote)
        <h2>{{ __('mapscalculator.quote') }} {{ $quote->formatted_number }}</h2>
        <table class="totals">
            <tr><td class="label">{{ __('mapscalculator.subtotal') }}</td><td class="value">{{ $quote->formatted_subtotal }}</td></tr>
            <tr><td class="label">{{ __('mapscalculator.vat') }} ({{ number_format((float) $quote->tax_percent, 2, ',', '.') }}%)</td><td class="value">{{ $quote->formatted_tax_amount }}</td></tr>
            <tr><td class="label"><strong>{{ __('mapscalculator.total') }}</strong></td><td class="value">{{ $quote->formatted_total }}</td></tr>
        </table>
    @endif

    <h2>{{ __('mapscalculator.work_log') }}</h2>
    <table class="logs">
        <thead>
        <tr>
            <th style="width:15%">{{ __('mapscalculator.date') }}</th>
            <th style="width:15%">{{ __('mapscalculator.log_type') }}</th>
            <th>{{ __('mapscalculator.log_description') }}</th>
            <th style="width:18%">{{ __('mapscalculator.user') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($workOrder->logs as $log)
            <tr>
                <td>{{ $log->date?->format('d/m/Y') }}</td>
                <td>{{ $log->type_label }}</td>
                <td>
                    {{ $log->description }}
                    @if(isset($photos[$log->id]))
                        <div class="photos">
                            <div class="photo">
                                <img src="{{ $photos[$log->id] }}" alt="">
                                <div class="cap">{{ $log->date?->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @endif
                </td>
                <td>{{ $log->creator?->name ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4">{{ __('mapscalculator.no_logs') }}</td></tr>
        @endforelse
        </tbody>
    </table>

    @if($workOrder->notes)
        <h2>{{ __('mapscalculator.notes') }}</h2>
        <p>{{ $workOrder->notes }}</p>
    @endif

    <table class="signature">
        <tr>
            <td>{{ __('mapscalculator.signature_client') }}</td>
            <td>{{ __('mapscalculator.signature_company') }}</td>
        </tr>
    </table>
</div>
</body>
</html>
