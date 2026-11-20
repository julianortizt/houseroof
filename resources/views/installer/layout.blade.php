<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Installer') }} · {{ config('platform.public_name') }}</title>
    <style>
        :root {
            --brand: #d97706;
            --brand-dark: #b45309;
            --bg: #0f172a;
            --card: #ffffff;
            --text: #1e293b;
            --muted: #64748b;
            --ok: #16a34a;
            --err: #dc2626;
            --line: #e2e8f0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .wrap {
            width: 100%;
            max-width: 720px;
            background: var(--card);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }
        .header {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            color: #fff;
            padding: 28px 32px;
        }
        .header h1 { margin: 0; font-size: 22px; letter-spacing: .3px; }
        .header p { margin: 6px 0 0; opacity: .9; font-size: 14px; }
        .steps {
            display: flex;
            gap: 6px;
            padding: 16px 32px;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }
        .step {
            font-size: 12px;
            color: var(--muted);
            padding: 4px 10px;
            border-radius: 999px;
            background: #eef2f7;
            white-space: nowrap;
        }
        .step.active { background: var(--brand); color: #fff; font-weight: 600; }
        .step.done { background: #dcfce7; color: var(--ok); }
        .content { padding: 28px 32px; }
        h2 { margin: 0 0 4px; font-size: 19px; }
        .sub { color: var(--muted); font-size: 14px; margin: 0 0 20px; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 14px 0 6px; }
        input, select {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid var(--line);
            border-radius: 9px;
            font-size: 14px;
            color: var(--text);
        }
        input:focus, select:focus { outline: 2px solid var(--brand); border-color: var(--brand); }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .req-list { list-style: none; padding: 0; margin: 0; }
        .req-list li {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 14px; border: 1px solid var(--line); border-radius: 9px; margin-bottom: 8px; font-size: 14px;
        }
        .badge { font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
        .badge.ok { background: #dcfce7; color: var(--ok); }
        .badge.err { background: #fee2e2; color: var(--err); }
        .alert {
            padding: 12px 14px; border-radius: 9px; font-size: 14px; margin-bottom: 16px;
        }
        .alert.err { background: #fef2f2; color: var(--err); border: 1px solid #fecaca; }
        .alert.info { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .alert.ok { background: #f0fdf4; color: var(--ok); border: 1px solid #bbf7d0; }
        .actions { margin-top: 24px; display: flex; gap: 12px; }
        .btn {
            display: inline-block; text-align: center; text-decoration: none;
            padding: 12px 22px; border-radius: 9px; font-size: 14px; font-weight: 600; cursor: pointer; border: none;
        }
        .btn-primary { background: var(--brand); color: #fff; }
        .btn-primary:hover { background: var(--brand-dark); }
        .btn-ghost { background: #eef2f7; color: var(--text); }
        .confirm-row { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid var(--line); font-size: 14px; }
        .confirm-row span:first-child { color: var(--muted); }
        .confirm-row span:last-child { font-weight: 600; word-break: break-all; text-align: right; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 5px; font-size: 13px; }
        .foot { text-align: center; color: var(--muted); font-size: 12px; padding: 18px; border-top: 1px solid var(--line); }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>{{ config('platform.public_name') }}</h1>
            <p>Instalador visual para Hostinger</p>
        </div>

        @if(isset($steps) && isset($current))
            <div class="steps">
                @foreach($steps as $key => $label)
                    @if($key === $current)
                        <span class="step active">{{ $label }}</span>
                    @elseif(array_search($current, array_keys($steps)) > array_search($key, array_keys($steps)))
                        <span class="step done">✓ {{ $label }}</span>
                    @else
                        <span class="step">{{ $label }}</span>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="content">
            @yield('content')
        </div>

        <div class="foot">Laravel · Filament · MySQL · PHP 8.2+</div>
    </div>
</body>
</html>
