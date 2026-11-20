@extends('installer.layout')

@section('content')
    <h2>Base de datos</h2>
    <p class="sub">Crea la base de datos en el panel de Hostinger (Bases de datos → MySQL) y escribe sus datos.</p>

    @if(session('db_error'))
        <div class="alert err">No se pudo conectar: {{ session('db_error') }}</div>
    @endif

    <form method="POST" action="{{ route('install.database.post') }}">
        @csrf

        <div class="grid2">
            <div>
                <label for="db_host">Servidor (host)</label>
                <input id="db_host" name="db_host" value="{{ $values['db_host'] }}" required>
            </div>
            <div>
                <label for="db_port">Puerto</label>
                <input id="db_port" name="db_port" value="{{ $values['db_port'] }}" required>
            </div>
        </div>

        <label for="db_name">Nombre de la base de datos</label>
        <input id="db_name" name="db_name" value="{{ $values['db_name'] }}" placeholder="u123456789_houseroof" required>

        <label for="db_user">Usuario de la base de datos</label>
        <input id="db_user" name="db_user" value="{{ $values['db_user'] }}" placeholder="u123456789_admin" required>

        <label for="db_password">Contraseña</label>
        <input id="db_password" name="db_password" type="password" value="{{ $values['db_password'] }}" placeholder="Deja en blanco si no tiene">

        <div class="actions">
            <a href="{{ route('install.welcome') }}" class="btn btn-ghost">← Atrás</a>
            <button type="submit" class="btn btn-primary">Probar y continuar →</button>
        </div>
    </form>
@endsection
