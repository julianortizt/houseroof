@extends('installer.layout')

@section('content')
    <h2>✅ Instalación completada</h2>
    <p class="sub">{{ config('platform.public_name') }} se ha instalado correctamente.</p>

    <div class="alert ok">El instalador ha sido bloqueado. Para volver a instalar, borra el archivo <code>.installed</code> de la raíz.</div>

    <div class="confirm-row"><span>Web pública</span><span><a href="{{ $app_url }}" target="_blank">{{ $app_url }}</a></span></div>
    <div class="confirm-row"><span>Panel admin</span><span><a href="{{ $app_url }}/admin" target="_blank">{{ $app_url }}/admin</a></span></div>

    <div class="alert info" style="margin-top:18px">
        <strong>Pasos posteriores recomendados:</strong>
        <ul style="margin:8px 0 0 18px">
            <li>Activa SSL en el panel de Hostinger (Dominios → SSL).</li>
            <li>Verifica que el dominio apunta a <code>/public_html/public</code>.</li>
            <li>Cambia la contraseña del administrador tras el primer acceso.</li>
            <li>Configura el contenido y las plantillas desde el panel <code>/admin</code>.</li>
        </ul>
    </div>

    <div class="actions">
        <a href="{{ $app_url }}/admin" class="btn btn-primary">Ir al panel →</a>
    </div>
@endsection
