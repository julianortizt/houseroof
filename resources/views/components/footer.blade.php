<footer class="bg-gray-900 text-gray-300">
    {{-- Main Footer --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @php
            $productsModuleEnabled = app(\App\Services\PlatformModules::class)->enabled('products');
            $legalPages = \App\Models\Page::query()
                ->whereIn('slug', ['privacy-policy', 'cookie-policy', 'terms-of-sale', 'legal-notice'])
                ->pluck('slug')
                ->all();
            $legalLinks = [
                'privacy-policy' => __('Política de Privacidad'),
                'cookie-policy' => __('Aviso de Cookies'),
                'terms-of-sale' => __('Condiciones de Venta'),
                'legal-notice' => __('Aviso Legal'),
            ];
            $footerColumns = $productsModuleEnabled
                ? (count($legalPages) ? 'lg:grid-cols-4' : 'lg:grid-cols-3')
                : (count($legalPages) ? 'lg:grid-cols-3' : 'lg:grid-cols-2');
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 {{ $footerColumns }} gap-12">

            {{-- Company Info --}}
            <div>
                <a href="{{ route('home') }}" class="inline-block mb-6">
                    <img src="{{ asset('images/logo-white.png') }}" alt="{{ config('platform.public_name') }}" class="h-16 w-auto" width="280" height="140">
                </a>
                <p class="text-sm leading-relaxed mb-4">
                    {{ app()->getLocale() === 'es'
                        ? 'Instalación de pavimentos de resina, construcción y venta de productos para proyectos en Málaga y Costa Blanca.'
                        : 'Resin flooring installation, construction and product sales for projects in Malaga and Costa Blanca.' }}
                </p>
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <span class="flex items-center rounded bg-gray-800 px-2 py-1 text-xs">
                        <span class="font-semibold">{{ app()->getLocale() === 'es' ? 'RESINA' : 'RESIN' }}</span>
                        <span class="ml-1">{{ app()->getLocale() === 'es' ? 'Pavimentos' : 'Flooring' }}</span>
                    </span>
                    <span class="flex items-center rounded bg-gray-800 px-2 py-1 text-xs">
                        <span class="font-semibold">{{ app()->getLocale() === 'es' ? 'OBRA' : 'BUILD' }}</span>
                        <span class="ml-1">{{ app()->getLocale() === 'es' ? 'Construcción' : 'Construction' }}</span>
                    </span>
                </div>
            </div>

            @if(app(\App\Services\PlatformModules::class)->enabled('products'))
            <div>
                <h3 class="text-white font-bold uppercase tracking-wider mb-4">{{ __('Productos') }}</h3>
                @php
                    $footerCategories = \App\Models\Category::active()
                        ->roots()
                        ->orderBy('sort_order')
                        ->get();
                @endphp
                <ul class="space-y-2 text-sm">
                    @foreach($footerCategories as $category)
                        <li><a href="{{ route('shop.category', $category) }}" class="hover:text-amber-500 transition-colors">{{ $category->name }}</a></li>
                    @endforeach
                    <li><a href="{{ route('shop.index') }}" class="hover:text-amber-500 transition-colors font-semibold">{{ __('Ver todo el catálogo') }} →</a></li>
                </ul>
            </div>
            @endif

            {{-- Company --}}
            <div>
                <h3 class="text-white font-bold uppercase tracking-wider mb-4">{{ __('Empresa') }}</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('projects.index') }}" class="hover:text-amber-500 transition-colors">{{ app()->getLocale() === 'es' ? 'Proyectos' : 'Projects' }}</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-amber-500 transition-colors">{{ app()->getLocale() === 'es' ? 'Servicios' : 'Services' }}</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-amber-500 transition-colors">{{ __('Blog') }}</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-amber-500 transition-colors">{{ __('Contacto') }}</a></li>
                    <li><a href="{{ route('quote.create') }}" class="hover:text-amber-500 transition-colors">{{ __('Solicitar Cotización') }}</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            @if($legalPages)
            <div>
                <h3 class="text-white font-bold uppercase tracking-wider mb-4">{{ __('Legal') }}</h3>
                <ul class="space-y-2 text-sm">
                    @foreach($legalLinks as $slug => $label)
                        @if(in_array($slug, $legalPages, true))
                            <li><a href="{{ route('legal.page', $slug) }}" class="hover:text-amber-500 transition-colors">{{ $label }}</a></li>
                        @endif
                    @endforeach
                </ul>

            </div>
            @endif
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between text-sm">
            @php $footerCopyright = \App\Models\Setting::get('footer_copyright', '© ' . date('Y') . ' ' . config('platform.public_name') . '. ' . __('All rights reserved.')); @endphp
            <p>@if (! \Illuminate\Support\Str::startsWith(ltrim($footerCopyright), ['©', '&copy;']))&copy; @endif{{ $footerCopyright }}</p>
            @php
                $socialLinks = \App\Models\Setting::getMany([
                    'social_instagram', 'social_facebook', 'social_youtube',
                    'social_linkedin', 'social_tiktok', 'social_twitter',
                ], '');
            @endphp
            <div class="flex items-center space-x-4 mt-4 sm:mt-0" role="list" aria-label="Redes sociales">
                @if($socialLinks['social_instagram'])
                    <a href="{{ e($socialLinks['social_instagram']) }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-500 transition-colors" role="listitem" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                @endif
                @if($socialLinks['social_facebook'])
                    <a href="{{ e($socialLinks['social_facebook']) }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-500 transition-colors" role="listitem" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                @endif
                @if($socialLinks['social_youtube'])
                    <a href="{{ e($socialLinks['social_youtube']) }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-500 transition-colors" role="listitem" aria-label="YouTube">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                @endif
                @if($socialLinks['social_linkedin'])
                    <a href="{{ e($socialLinks['social_linkedin']) }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-500 transition-colors" role="listitem" aria-label="LinkedIn">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                @endif
                @if($socialLinks['social_tiktok'])
                    <a href="{{ e($socialLinks['social_tiktok']) }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-500 transition-colors" role="listitem" aria-label="TikTok">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                    </a>
                @endif
                @if($socialLinks['social_twitter'])
                    <a href="{{ e($socialLinks['social_twitter']) }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-500 transition-colors" role="listitem" aria-label="X / Twitter">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
</footer>
