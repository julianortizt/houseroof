@extends('installer.layout')

@section('content')
    <h2>Verificación del servidor</h2>
    <p class="sub">Comprobamos que tu alojamiento en Hostinger cumple los requisitos.</p>

    @if(!$requirements['all_ok'])
        <div class="alert err">
            Faltan requisitos obligatorios. Corrígelos en el panel de Hostinger
            (PHP → versión 8.2 y extensiones) y recarga esta página.
        </div>
    @else
        <div class="alert ok">✅ Tu servidor cumple los requisitos obligatorios. Puedes continuar.</div>
    @endif

    <ul class="req-list">
        <li>
            <span>PHP {{ $requirements['php_version'] }} (se recomienda 8.2+)</span>
            <span class="badge {{ $requirements['php_ok'] ? 'ok' : 'err' }}">{{ $requirements['php_ok'] ? 'OK' : 'NO' }}</span>
        </li>

        @foreach($requirements['required'] as $ext)
            @php($loaded = $requirements['extensions'][$ext])
            <li>
                <span>Extensión PHP: {{ $ext }}</span>
                <span class="badge {{ $loaded ? 'ok' : 'err' }}">{{ $loaded ? 'OK' : 'FALTA' }}</span>
            </li>
        @endforeach

        @foreach($requirements['recommended'] as $ext)
            @php($loaded = $requirements['extensions'][$ext])
            <li>
                <span>Extensión PHP (recomendada): {{ $ext }}</span>
                <span class="badge {{ $loaded ? 'ok' : 'err' }}">{{ $loaded ? 'OK' : 'Opcional' }}</span>
            </li>
        @endforeach

        @foreach($requirements['writable'] as $dir => $ok)
            <li>
                <span>Permisos de escritura: {{ $dir }}</span>
                <span class="badge {{ $ok ? 'ok' : 'err' }}">{{ $ok ? 'OK' : 'NO' }}</span>
            </li>
        @endforeach
    </ul>

    <div class="actions">
        <a href="{{ route('install.database') }}" class="btn btn-primary">Continuar →</a>
    </div>
@endsection
