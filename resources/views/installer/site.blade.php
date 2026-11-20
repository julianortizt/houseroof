@extends('installer.layout')

@section('content')
    <h2>Configuración del sitio</h2>
    <p class="sub">Datos de la web y la cuenta de administrador.</p>

    <form method="POST" action="{{ route('install.site.post') }}">
        @csrf

        <label for="app_name">Nombre del sitio</label>
        <input id="app_name" name="app_name" value="{{ $values['app_name'] }}" required>

        <label for="app_url">URL del sitio</label>
        <input id="app_url" name="app_url" value="{{ $values['app_url'] }}" placeholder="https://tudominio.com" required>

        <hr style="border:none;border-top:1px solid var(--line);margin:22px 0">

        <label for="admin_email">Email del administrador</label>
        <input id="admin_email" name="admin_email" type="email" value="{{ $values['admin_email'] }}" required>

        <label for="admin_password">Contraseña del administrador</label>
        <input id="admin_password" name="admin_password" type="password" required>
        <small style="color:var(--muted)">Mínimo 8 caracteres.</small>

        <hr style="border:none;border-top:1px solid var(--line);margin:22px 0">

        <p style="font-weight:600;margin:0 0 4px">Correo saliente (SMTP)</p>
        <small style="color:var(--muted)">Puedes dejarlo para configurar luego en el panel.</small>

        <div class="grid2">
            <div>
                <label for="mail_host">Servidor SMTP</label>
                <input id="mail_host" name="mail_host" value="{{ $values['mail_host'] }}" required>
            </div>
            <div>
                <label for="mail_port">Puerto</label>
                <input id="mail_port" name="mail_port" value="{{ $values['mail_port'] }}" required>
            </div>
        </div>

        <label for="mail_user">Usuario SMTP</label>
        <input id="mail_user" name="mail_user" value="{{ $values['mail_user'] }}" required>

        <label for="mail_password">Contraseña SMTP</label>
        <input id="mail_password" name="mail_password" type="password" value="{{ $values['mail_password'] }}">

        <div class="grid2">
            <div>
                <label for="mail_from">Email remitente</label>
                <input id="mail_from" name="mail_from" type="email" value="{{ $values['mail_from'] }}" placeholder="Opcional">
            </div>
            <div>
                <label for="mail_encryption">Cifrado</label>
                <select id="mail_encryption" name="mail_encryption">
                    <option value="tls" @if($values['mail_encryption']==='tls') selected @endif>TLS</option>
                    <option value="ssl" @if($values['mail_encryption']==='ssl') selected @endif>SSL</option>
                    <option value="null" @if($values['mail_encryption']==='null') selected @endif>Sin cifrado</option>
                </select>
            </div>
        </div>

        <div class="actions">
            <a href="{{ route('install.database') }}" class="btn btn-ghost">← Atrás</a>
            <button type="submit" class="btn btn-primary">Continuar →</button>
        </div>
    </form>
@endsection
