@extends('installer.layout')

@section('content')
    <h2>Confirmar instalación</h2>
    <p class="sub">Revisa los datos antes de instalar. Este proceso crea las tablas, los datos iniciales y el administrador.</p>

    @if(session('install_error'))
        <div class="alert err">La instalación falló: {{ session('install_error') }}</div>
    @endif

    <div class="confirm-row"><span>Sitio</span><span>{{ $site['app_name'] }}</span></div>
    <div class="confirm-row"><span>URL</span><span>{{ $site['app_url'] }}</span></div>
    <div class="confirm-row"><span>Base de datos</span><span>{{ $db['db_name'] }}</span></div>
    <div class="confirm-row"><span>Usuario DB</span><span>{{ $db['db_user'] }}</span></div>
    <div class="confirm-row"><span>Email admin</span><span>{{ $site['admin_email'] }}</span></div>
    <div class="confirm-row"><span>SMTP</span><span>{{ $site['mail_host'] }}:{{ $site['mail_port'] }}</span></div>

    <div class="alert info" style="margin-top:18px">
        El proceso puede tardar uno o dos minutos (migraciones y cachés de producción).
    </div>

    <form method="POST" action="{{ route('install.run') }}">
        @csrf
        <div class="actions">
            <a href="{{ route('install.site') }}" class="btn btn-ghost">← Atrás</a>
            <button type="submit" class="btn btn-primary">🚀 Instalar ahora</button>
        </div>
    </form>
@endsection
