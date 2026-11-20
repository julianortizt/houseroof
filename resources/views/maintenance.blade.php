@php
    $message = config('app.maintenance.message', 'Estamos realizando mejoras en el sitio. Volveremos pronto.');
    $retryAfter = config('app.maintenance.retry');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('platform.public_name') }} — {{ __('Maintenance') }}</title>
    <style>
        :root { --brand: #D97706; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #1F2937 0%, #111827 100%);
            color: #f9fafb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1rem;
            padding: 2.5rem;
            max-width: 32rem;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(217,119,6,0.15);
            color: #FBBF24;
            border: 1px solid rgba(251,191,36,0.4);
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
        h1 { font-size: 1.6rem; margin-bottom: 1rem; font-weight: 700; }
        p { color: #d1d5db; line-height: 1.6; font-size: 1rem; }
        .logo { font-weight: 800; font-size: 1.1rem; color: var(--brand); margin-bottom: 1.5rem; letter-spacing: -0.02em; }
        .pulse { width: 12px; height: 12px; border-radius: 9999px; background: #FBBF24; animation: pulse 1.4s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
        .retry { margin-top: 1.5rem; font-size: 0.8rem; color: #9CA3AF; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">COSTA RESIN & CONSTRUCTION</div>
        <div class="badge"><span class="pulse"></span> Mantenimiento</div>
        <h1>Estamos trabajando en mejoras</h1>
        <p>{{ $message }}</p>
        @if ($retryAfter)
        <div class="retry">Tiempo estimado de espera: {{ $retryAfter }} segundos.</div>
        @endif
    </div>
</body>
</html>
