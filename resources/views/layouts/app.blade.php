@php
    $styleOptions = config('platform.styles');
    $siteStyle = (string) \App\Models\Setting::get('platform_style', 'professional');
    if (! array_key_exists($siteStyle, $styleOptions)) {
        $siteStyle = 'professional';
    }
    $stylePalette = $styleOptions[$siteStyle];
    $siteFavicon = \App\Support\SiteLogo::favicon();

    $defaultHomeTemplate = config('platform.default_home_template', 'roof');
    $homeTemplates = config('platform.home_templates');
    $homeTemplate = (string) \App\Models\Setting::get('homepage_template', $defaultHomeTemplate);
    if (! array_key_exists($homeTemplate, $homeTemplates)) {
        $homeTemplate = $defaultHomeTemplate;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth" data-platform-style="{{ $siteStyle }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if($siteFavicon)<link rel="icon" type="image/png" href="{{ $siteFavicon }}">@endif
    @php
        $antiCloneEnabled = (bool) config('antidown.guard_enabled', true);
        $antiCloneHosts = config('antidown.allowed_hosts', []);
    @endphp
    @if ($antiCloneEnabled)
    {{-- Anti-clone guard: refuses to render the page on an unauthorized domain.
         Copies of the site deployed elsewhere show an unauthorized-copy notice. --}}
    <script>
    (function () {
        var allowed = @json($antiCloneHosts);
        var host = (window.location.hostname || '').toLowerCase();
        var ok = false;
        for (var i = 0; i < allowed.length; i++) {
            var a = String(allowed[i]).toLowerCase().replace(/^\./, '');
            if (host === a || host.slice(-(a.length + 1)) === '.' + a) { ok = true; break; }
        }
        if (!ok) {
            var render = function () {
                if (!document.body) { return; }
                document.body.innerHTML = '<main style="font-family:system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0f172a;color:#fff;text-align:center;padding:1rem"><div><h1 style="font-size:1.5rem;margin-bottom:.75rem">Copia no autorizada · Unauthorized copy</h1><p style="color:#f59e0b;font-weight:700">House Roof</p><p style="margin:.5rem 0">Este sitio es una copia no autorizada del original.<br>This website is an unauthorized copy of the original.</p><p style="font-size:.875rem;color:#94a3b8">El contenido es propiedad intelectual del titular.</p></div></main>';
            };
            document.addEventListener('DOMContentLoaded', render);
            if (document.body) { render(); }
        }
    })();
    </script>
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $ogLocaleMap = [
            'es' => 'es_ES', 'en' => 'en_GB', 'de' => 'de_DE',
            'it' => 'it_IT', 'fr' => 'fr_FR', 'pt' => 'pt_PT', 'nl' => 'nl_NL',
        ];
        $seoLocales = ['es', 'en', 'de', 'it', 'fr', 'pt', 'nl'];
        $canonicalUrl = url()->current();

        $seoTitle = \App\Models\Setting::get('seo_title', '');
        $seoDesc = \App\Models\Setting::get('seo_description', '');
        $seoKeywords = \App\Models\Setting::get('seo_keywords', '');
        $rawOg = \App\Models\Setting::get('seo_og_image', '');
        if ($rawOg) {
            if (\Illuminate\Support\Str::startsWith($rawOg, ['http', '/storage/'])) {
                $ogImageFallback = $rawOg;
            } else {
                $ogImageFallback = \Illuminate\Support\Facades\Storage::disk('public')->exists($rawOg) ? upload_url($rawOg) : asset($rawOg);
            }
        } else {
            $ogImageFallback = upload_url('site/og-default.jpg');
        }

    @endphp

    {{-- El título va después del bloque de código: antes se evaluaba como primer
         uso de $seoTitle y el ajuste SEO del panel nunca llegaba a la etiqueta.
         (Ojo: nunca escribir la arroba de una directiva dentro de un comentario
         Blade; el compilador la trata como directiva real y se traga todo el
         bloque siguiente.) --}}
    <title>@yield('title', ($seoTitle ?: config('platform.public_name') . ' — ' . __('Professional Services')))</title>

    {{-- SEO Meta Tags --}}
    <meta name="description" content="@yield('meta_description', ($seoDesc ?: __('meta_default_desc')))">
    <meta name="robots" content="index, follow">
    <meta name="author" content="{{ config('platform.public_name') }}">
    <meta name="keywords" content="@yield('meta_keywords', ($seoKeywords ?: __('meta_home_kw')))">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', ($seoTitle ?: config('platform.public_name')))">
    <meta property="og:description" content="@yield('meta_description', ($seoDesc ?: __('meta_default_desc')))">
    <meta property="og:image" content="@yield('og_image', ($ogImageFallback ?? upload_url('site/og-default.jpg')))">
    <meta property="og:locale" content="{{ $ogLocaleMap[app()->getLocale()] ?? 'en_GB' }}">
    <meta property="og:site_name" content="{{ config('platform.public_name') }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', ($seoTitle ?: config('platform.public_name')))">
    <meta name="twitter:description" content="@yield('meta_description', ($seoDesc ?: __('meta_default_desc')))">
    <meta name="twitter:image" content="@yield('og_image', ($ogImageFallback ?? upload_url('site/og-default.jpg')))">

    {{-- Canonical URL (language-neutral; serves default English content) --}}
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Alternate language versions (deterministic ?lang= URLs for crawlers) --}}
    @foreach($seoLocales as $altLocale)
        <link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $canonicalUrl . '?lang=' . $altLocale }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">

    {{-- Preconnect --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800|material-symbols-outlined" rel="stylesheet" />

    {{-- Tailwind / Vite assets --}}
    @vite(['resources/css/app.css'])

    {{-- Brand Colors from Settings --}}
    @php
        $colorPrimary = \App\Models\Setting::get('color_primary', '#D97706');
        $colorSecondary = \App\Models\Setting::get('color_secondary', '#1F2937');
        $colorAccent = \App\Models\Setting::get('color_accent', '#F59E0B');
    @endphp
    <style>
        :root {
            --color-primary: {{ $colorPrimary }};
            --color-secondary: {{ $colorSecondary }};
            --color-accent: {{ $colorAccent }};
            --platform-primary: {{ $stylePalette['primary'] }};
            --platform-primary-hover: {{ $stylePalette['primary_hover'] }};
            --platform-surface: {{ $stylePalette['surface'] }};
            --platform-canvas: {{ $stylePalette['canvas'] }};
            --platform-text: {{ $stylePalette['text'] }};
            --platform-muted: {{ $stylePalette['muted'] }};
        }

        html[data-platform-style] .bg-amber-600 { background-color: var(--platform-primary) !important; }
        html[data-platform-style] .text-amber-600,
        html[data-platform-style] .text-amber-700 { color: var(--platform-primary) !important; }
        html[data-platform-style] .border-amber-500,
        html[data-platform-style] .border-amber-600 { border-color: var(--platform-primary) !important; }
        html[data-platform-style] [class~="hover:bg-amber-700"]:hover { background-color: var(--platform-primary-hover) !important; }
        html[data-platform-style] [class~="hover:text-amber-700"]:hover,
        html[data-platform-style] [class~="hover:text-amber-600"]:hover { color: var(--platform-primary-hover) !important; }

        html[data-platform-style="modern_dark"] body { background: {{ $stylePalette['canvas'] }}; color: {{ $stylePalette['text'] }}; }
        html[data-platform-style="modern_dark"] nav,
        html[data-platform-style="modern_dark"] .bg-white { background-color: {{ $stylePalette['surface'] }} !important; }
        html[data-platform-style="modern_dark"] .bg-gray-50,
        html[data-platform-style="modern_dark"] .bg-gray-100 { background-color: {{ $stylePalette['canvas'] }} !important; }
        html[data-platform-style="modern_dark"] .text-gray-900,
        html[data-platform-style="modern_dark"] .text-gray-800 { color: {{ $stylePalette['text'] }} !important; }
        html[data-platform-style="modern_dark"] .text-gray-700,
        html[data-platform-style="modern_dark"] .text-gray-600,
        html[data-platform-style="modern_dark"] .text-gray-500 { color: {{ $stylePalette['muted'] }} !important; }
        html[data-platform-style="modern_dark"] .border-gray-100,
        html[data-platform-style="modern_dark"] .border-gray-200,
        html[data-platform-style="modern_dark"] .border { border-color: #334155 !important; }
        html[data-platform-style="modern_dark"] .bg-amber-50,
        html[data-platform-style="modern_dark"] .bg-amber-100 { background-color: #164e63 !important; }
        html[data-platform-style="modern_dark"] .text-amber-100 { color: #cffafe !important; }

        html[data-platform-style="portfolio"] .rounded-2xl { border-radius: 1rem !important; }
        html[data-platform-style="minimal"] .rounded-2xl,
        html[data-platform-style="minimal"] .rounded-xl { border-radius: 0.5rem !important; }
        html[data-platform-style="minimal"] .shadow-lg,
        html[data-platform-style="minimal"] .shadow-xl { box-shadow: 0 1px 3px rgb(15 23 42 / 0.08) !important; }

        main[data-home-template="portfolio"],
        main[data-home-template="services"] { display: flex; flex-direction: column; }
        main[data-home-template="portfolio"] > section,
        main[data-home-template="services"] > section { order: 10; }
        main[data-home-template="portfolio"] #home-hero { order: 0; }
        main[data-home-template="portfolio"] #home-trust { order: 1; }
        main[data-home-template="portfolio"] #home-value { order: 2; }
        main[data-home-template="portfolio"] #home-projects { order: 3; }
        main[data-home-template="portfolio"] #home-services { order: 4; }
        main[data-home-template="portfolio"] #home-products { order: 5; }
        main[data-home-template="portfolio"] #home-blog { order: 6; }
        main[data-home-template="portfolio"] #home-cta { order: 7; }
        main[data-home-template="services"] #home-hero { order: 0; }
        main[data-home-template="services"] #home-trust { order: 1; }
        main[data-home-template="services"] #home-services { order: 2; }
        main[data-home-template="services"] #home-value { order: 3; }
        main[data-home-template="services"] #home-products { order: 4; }
        main[data-home-template="services"] #home-projects { order: 5; }
        main[data-home-template="services"] #home-blog { order: 6; }
        main[data-home-template="services"] #home-cta { order: 7; }
    </style>

    @livewireStyles

</head>
<body class="bg-white text-gray-900 antialiased font-sans">

    {{-- Tarpit for website downloaders: invisible to humans, followed by mirror tools (HTTrack, wget --mirror…). --}}
    <a href="/site-archive" rel="nofollow" aria-hidden="true" tabindex="-1"
       style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden">404</a>

    {{-- Las páginas de medición piden `workspace`: el editor del MapsCalculator
         necesita la ventana entera. Con navegación, pie y los widgets flotantes
         (chat y WhatsApp, abajo a la derecha) el área de dibujo quedaba reducida
         a un tercio de la pantalla y los botones flotantes tapaban los controles
         del mapa. --}}
    @unless($workspace ?? false)
        {{-- Cookie Consent Banner --}}
        @livewire('cookie-consent')

        {{-- Navigation --}}
        <x-navigation />
    @endunless

    {{-- Main Content --}}
    <main
        data-platform-style="{{ $siteStyle }}"
        data-home-template="{{ request()->routeIs('home') ? $homeTemplate : 'standard' }}"
        class="{{ ($workspace ?? false) ? 'min-h-screen bg-gray-50' : '' }}">
        @yield('content')
    </main>

    @unless($workspace ?? false)
        {{-- Footer --}}
        <x-footer />

        {{-- AI Chat Widget (Bottom Right) --}}
        @livewire('ai-chat-widget')

        {{-- WhatsApp Floating Button (Pure Blade + Alpine) --}}
        <x-whatsapp-button />
    @endunless

    {{-- Scripts --}}
    @vite(['resources/js/app.js'])
    @livewireScripts

    @stack('scripts')
    @yield('scripts')
    @stack('scripts-bottom')

    {{-- JSON-LD Structured Data --}}
    @if(isset($jsonLd))
        <script type="application/ld+json">
            {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endif

    {{-- Organization JSON-LD (always present) --}}
    @php
        $jsonLdService = app(App\Services\JsonLdService::class);
    @endphp
    <script type="application/ld+json">
        {!! json_encode($jsonLdService->organization(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($jsonLdService->website(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($jsonLdService->localBusiness(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($jsonLdService->service(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    {{-- Avisos (toasts) del sistema. Los componentes del MapsCalculator emiten
         `notify` desde el servidor y hasta ahora nadie los escuchaba: los
         mensajes de guardado y los errores eran invisibles. --}}
    {{-- `w-[min(...)]` no lo genera Tailwind: los avisos ocupaban todo el ancho. --}}
    <div id="app-toasts" class="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-[calc(100vw_-_2rem)] max-w-[22rem]"></div>

    <script>
        // CSRF token for AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                window.Livewire.directive('token', (el) => {
                    el.setAttribute('wire:snippets', meta.getAttribute('content'));
                });
            }
        });

        (function () {
            const palette = {
                success: { bg: 'bg-green-50', border: 'border-green-500', text: 'text-green-800', icon: '✓' },
                error:   { bg: 'bg-red-50',   border: 'border-red-500',   text: 'text-red-800',   icon: '!' },
                warning: { bg: 'bg-amber-50', border: 'border-amber-500', text: 'text-amber-800', icon: '!' },
                info:    { bg: 'bg-blue-50',  border: 'border-blue-500',  text: 'text-blue-800',  icon: 'i' },
            };

            function showToast(message, type) {
                if (!message) return;

                const host = document.getElementById('app-toasts');
                if (!host) return;

                const style = palette[type] || palette.info;
                const el = document.createElement('div');
                el.className = 'flex items-start gap-3 px-4 py-3 rounded-lg shadow-lg border-l-4 bg-white '
                    + style.border + ' ' + style.bg + ' ' + style.text
                    + ' transition-all duration-300 translate-x-4 opacity-0';
                el.innerHTML = '<span class="font-bold">' + style.icon + '</span><span class="text-sm"></span>';
                el.lastElementChild.textContent = String(message);
                host.appendChild(el);

                requestAnimationFrame(() => {
                    el.classList.remove('translate-x-4', 'opacity-0');
                });

                setTimeout(() => {
                    el.classList.add('opacity-0', 'translate-x-4');
                    setTimeout(() => el.remove(), 300);
                }, 5000);
            }

            // Livewire puede mandar el detalle como objeto, como array de
            // parámetros o anidado; se normaliza antes de leerlo.
            function normalize(detail) {
                if (Array.isArray(detail)) {
                    return detail[0] && typeof detail[0] === 'object' ? detail[0] : {};
                }
                if (detail && typeof detail === 'object') {
                    if (detail.message) return detail;
                    const first = Object.values(detail)[0];
                    if (first && typeof first === 'object' && first.message) return first;
                }
                return {};
            }

            function downloadFile(payload) {
                if (!payload || !payload.content) return;

                const blob = new Blob([payload.content], { type: payload.mimeType || 'application/octet-stream' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = payload.filename || 'descarga.json';
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            }

            window.addEventListener('notify', (event) => {
                const data = normalize(event.detail);
                showToast(data.message, data.type || 'info');
            });

            window.addEventListener('downloadFile', (event) => {
                const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                downloadFile(detail);
            });

            // Los componentes que abren WhatsApp (botón flotante y panel del
            // proyecto) disparaban `openWhatsApp` y nadie lo escuchaba: el
            // enlace se generaba pero no se abría nada.
            window.addEventListener('openWhatsApp', (event) => {
                const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                if (detail && detail.url) window.open(detail.url, '_blank', 'noopener');
            });

            @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'error', 'info' => 'info'] as $key => $type)
                @if(session($key))
                    showToast(@json(session($key)), @json($type));
                @endif
            @endforeach
        })();
    </script>
</body>
</html>
