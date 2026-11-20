{{-- Main Navigation --}}
<nav x-data="{ mobileOpen: false, scrolled: false }"
     x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)"
     :class="scrolled ? 'bg-white shadow-lg' : 'bg-white/90 backdrop-blur-md'"
     class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">

            <a href="{{ route('home') }}" class="flex-shrink-0 mr-8 flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('platform.public_name') }}" class="h-12 lg:h-16 w-auto" width="320" height="160">
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden lg:flex items-center gap-x-4 lg:gap-x-8">
                {{-- Home --}}
                <a href="{{ route('home') }}"
                   class="text-sm font-medium {{ request()->routeIs('home') ? 'text-amber-600' : 'text-gray-700 hover:text-amber-600' }} transition-colors whitespace-nowrap">
                    {{ __('Inicio') }}
                </a>

                @if(app(\App\Services\PlatformModules::class)->enabled('products'))
                @php
                    $navCategories = \Illuminate\Support\Facades\Cache::remember('nav:categories_with_products', 600, fn () =>
                        \App\Models\Category::active()
                            ->roots()
                            ->with(['products' => function ($q) {
                                $q->active()->limit(4)->select('id', 'category_id', 'slug', 'name');
                            }])
                            ->orderBy('sort_order')
                            ->get()
                    );
                @endphp
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <a href="{{ route('shop.index') }}" class="text-sm font-medium text-gray-700 hover:text-amber-600 transition-colors flex items-center whitespace-nowrap">
                        {{ __('Productos') }}
                        <svg class="w-3 h-3 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>

                    {{-- Level 1: Categories list --}}
                    <div x-show="open" x-cloak x-transition
                         x-onmouseenter="open = true" x-onmouseleave="open = false"
                         class="absolute top-full left-0 mt-0 bg-white rounded-b-xl shadow-2xl border z-50 w-60">
                        @foreach($navCategories as $cat)
                            <div class="relative" x-data="{ sub: false }" @mouseenter="sub = true" @mouseleave="sub = false">
                                <a href="{{ route('shop.category', $cat) }}"
                                   class="flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                    <span>{{ $cat->name }}</span>
                                    @if($cat->products->count())
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    @endif
                                </a>

                                {{-- Level 2: Products — appears to the RIGHT --}}
                                @if($cat->products->count())
                                    <div x-show="sub" x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 -translate-x-2"
                                         x-transition:enter-end="opacity-100 translate-x-0"
                                         x-onmouseenter="sub = true" x-onmouseleave="sub = false"
                                         class="absolute top-0 left-full bg-white rounded-r-xl shadow-2xl border z-50 w-72">
                                        <div class="px-4 py-2 border-b bg-gray-50">
                                            <a href="{{ route('shop.category', $cat) }}" class="text-xs font-bold text-amber-600 uppercase tracking-wider hover:text-amber-700">
                                                {{ $cat->name }}
                                            </a>
                                        </div>
                                        @foreach($cat->products as $product)
                                            <a href="{{ route('shop.show', $product) }}"
                                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors border-b border-gray-50 last:border-0">
                                                {{ $product->name }}
                                            </a>
                                        @endforeach
                                        <a href="{{ route('shop.category', $cat) }}"
                                           class="block px-4 py-2 text-sm font-semibold text-amber-600 hover:bg-amber-50 transition-colors">
                                            {{ __('Ver todos') }} →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <div class="border-t">
                            <a href="{{ route('shop.index') }}" class="block px-4 py-2.5 text-sm font-bold text-amber-600 hover:bg-amber-50 transition-colors">
                                {{ __('Ver todo el catálogo') }} →
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <a href="{{ route('services') }}"
                   class="text-sm font-medium {{ request()->routeIs('services') ? 'text-amber-600' : 'text-gray-700 hover:text-amber-600' }} transition-colors whitespace-nowrap">
                    {{ __('Servicios') }}
                </a>

                <a href="{{ route('projects.index') }}"
                   class="text-sm font-medium {{ request()->routeIs('projects.*') ? 'text-amber-600' : 'text-gray-700 hover:text-amber-600' }} transition-colors whitespace-nowrap">
                    {{ __('Proyectos') }}
                </a>

                <a href="{{ route('blog.index') }}"
                   class="text-sm font-medium {{ request()->routeIs('blog.*') ? 'text-amber-600' : 'text-gray-700 hover:text-amber-600' }} transition-colors whitespace-nowrap">
                    {{ __('Blog') }}
                </a>

                <a href="{{ route('about') }}"
                   class="text-sm font-medium {{ request()->routeIs('about') ? 'text-amber-600' : 'text-gray-700 hover:text-amber-600' }} transition-colors whitespace-nowrap">
                    {{ __('About Us') }}
                </a>

                <a href="{{ route('contact') }}"
                   class="text-sm font-medium {{ request()->routeIs('contact') ? 'text-amber-600' : 'text-gray-700 hover:text-amber-600' }} transition-colors whitespace-nowrap">
                    {{ __('Contacto') }}
                </a>

                @if(app(\App\Services\PlatformModules::class)->enabled('customer_portal'))
                    <a href="{{ auth('customer')->check() ? route('customer.portal') : route('customer.login') }}"
                       class="text-sm font-semibold text-amber-700 hover:text-amber-800 whitespace-nowrap">
                        {{ auth('customer')->check() ? __('My account') : __('Customer access') }}
                    </a>
                @endif
            </div>

            {{-- Right side: Search, Language, Cart, Mobile menu --}}
            <div class="flex items-center space-x-4">
                {{-- Search --}}
                @if(app(\App\Services\PlatformModules::class)->enabled('products'))
                <form action="{{ route('shop.search') }}" method="GET" class="hidden lg:block">
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Buscar...') }}"
                               class="w-32 lg:w-40 pl-9 pr-3 py-1.5 text-sm border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
                @endif
                {{-- Language Switcher --}}
                @include('livewire.language-switcher')

                {{-- Cart --}}
                @if(app(\App\Services\StripeCredentials::class)->productCheckoutReady())
                    @livewire('cart-widget')
                @endif

                {{-- Mobile Menu Button --}}
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-md text-gray-700 hover:text-amber-600">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Navigation --}}
    <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="lg:hidden bg-white border-t shadow-lg">
        <div class="px-4 py-4 space-y-1">
            <a href="{{ route('home') }}" class="block py-2 text-gray-700 hover:text-amber-600 font-semibold">{{ __('Inicio') }}</a>

            @if(app(\App\Services\PlatformModules::class)->enabled('products'))
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full py-2 text-gray-700 hover:text-amber-600 font-semibold">
                    {{ __('Productos') }}
                    <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak class="pl-4 space-y-1">
                    @foreach($navCategories as $cat)
                        <div x-data="{ sub: false }">
                            <button @click="sub = !sub" class="flex items-center justify-between w-full py-1 text-sm text-gray-600 hover:text-amber-600 font-medium">
                                {{ $cat->name }}
                                <svg :class="sub ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="sub" x-cloak class="pl-4 space-y-1 pb-1">
                                @foreach($cat->products as $product)
                                    <a href="{{ route('shop.show', $product) }}" class="block py-1 text-xs text-gray-500 hover:text-amber-600">{{ $product->name }}</a>
                                @endforeach
                                <a href="{{ route('shop.category', $cat) }}" class="block py-1 text-xs text-amber-600 font-semibold">{{ __('Ver todos') }} →</a>
                            </div>
                        </div>
                    @endforeach
                    <a href="{{ route('shop.index') }}" class="block py-1 text-sm text-amber-600 font-semibold">{{ __('View full catalogue') }} →</a>
                </div>
            </div>

            <a href="{{ route('shop.index') }}" class="block py-2 text-gray-700 hover:text-amber-600 font-semibold">{{ __('Tienda') }}</a>
            @endif
            <a href="{{ route('services') }}" class="block py-2 text-gray-700 hover:text-amber-600 font-semibold">{{ __('Servicios') }}</a>
            <a href="{{ route('projects.index') }}" class="block py-2 text-gray-700 hover:text-amber-600 font-semibold">{{ __('Proyectos') }}</a>
            <a href="{{ route('blog.index') }}" class="block py-2 text-gray-700 hover:text-amber-600 font-semibold">{{ __('Blog') }}</a>
            <a href="{{ route('about') }}" class="block py-2 text-gray-700 hover:text-amber-600 font-semibold">{{ __('About Us') }}</a>
            <a href="{{ route('contact') }}" class="block py-2 text-gray-700 hover:text-amber-600 font-semibold">{{ __('Contacto') }}</a>
            @if(app(\App\Services\PlatformModules::class)->enabled('customer_portal'))
                <a href="{{ auth('customer')->check() ? route('customer.portal') : route('customer.login') }}" class="block py-2 text-amber-700 hover:text-amber-800 font-semibold">
                    {{ auth('customer')->check() ? __('My account') : __('Customer access') }}
                </a>
            @endif
            <a href="{{ route('quote.create') }}" class="block mt-4 text-center bg-amber-600 text-white py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
                {{ __('Solicitar Cotización') }}
            </a>
        </div>
    </div>
</nav>

{{-- Spacer for fixed nav --}}
<div class="h-16 lg:h-20"></div>
