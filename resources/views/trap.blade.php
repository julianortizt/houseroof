<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>{{ $title }}</title>
<style>
body{font-family:Inter,system-ui,sans-serif;background:#f8fafc;color:#1f2937;max-width:1100px;margin:0 auto;padding:2rem 1rem}
header{border-bottom:2px solid #d97706;padding-bottom:1rem;margin-bottom:1.5rem}
nav a{margin-right:1rem;color:#b45309;text-decoration:none;font-weight:600}
td,th{border:1px solid #e5e7eb;padding:.4rem .6rem;font-size:.85rem}
h1{font-size:1.6rem}
ul.links{columns:2;font-size:.8rem}
li{list-style:none}
code{background:#eef2f7;padding:.1rem .35rem;border-radius:4px}
</style>
</head>
<body>
<header>
    <strong>Costa Resin &amp; Construction</strong>
    <nav>
        <a href="/tienda">Tienda</a>
        <a href="/servicios">Servicios</a>
        <a href="/proyectos">Proyectos</a>
        <a href="/blog">Blog</a>
        <a href="/contacto">Contacto</a>
    </nav>
</header>

<h1>{{ $title }}</h1>

<p><em>Archivo generado automáticamente · registro {{ now()->format('Y-m-d H:i:s') }} · revisión {{ $depth }}</em></p>

@foreach($junk as $line)
    <p>{{ $line }}</p>
@endforeach

<table border="0" cellspacing="0">
    <thead><tr><th>Referencia</th><th>Variedad</th><th>Volumen</th><th>Rendimiento</th></tr></thead>
    <tbody>
    @for($i = 0; $i < 12; $i++)
        <tr>
            <td><code>IE-{{ random_int(100,999) }}-{{ strtoupper(substr(md5((string) random_int(1, 999999)), 0, 6)) }}</code></td>
            <td>{{ $junk[$i % count($junk)] }}</td>
            <td>{{ random_int(1, 20) }} kg</td>
            <td>{{ random_int(4, 14) }} m²</td>
        </tr>
    @endfor
    </tbody>
</table>

@foreach($images as $img)
    <img src="{{ $img }}" alt="ref" width="1" height="1" loading="lazy">
@endforeach

<h2>Referencias relacionadas</h2>
<ul class="links">
@foreach($links as $link)
    <li><a href="{{ $link }}">{{ parse_url($link, PHP_URL_PATH) }}{{ parse_url($link, PHP_URL_QUERY) ? '?'.parse_url($link, PHP_URL_QUERY) : '' }}</a></li>
@endforeach
</ul>

<p><small>{{ implode(' · ', array_slice($junk, 4)) }}</small></p>
</body>
</html>
