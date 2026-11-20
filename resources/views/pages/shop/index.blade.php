@extends('layouts.app')

@section('meta_description', __('meta_shop_desc'))
@section('meta_keywords', __('meta_shop_kw'))
@section('title', __('Tienda'))
@section('meta_description', __('Catálogo de productos: resinas, aglutinantes, kits y herramientas para pavimentos decorativos'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Tienda') }}</h1>
        @isset($category)
            <p class="text-gray-600 mt-2">{{ __('Categoría:') }} {{ $category->name }}</p>
        @endisset
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Sidebar Filters --}}
        <div class="lg:w-64 flex-shrink-0">
            <div class="bg-white border rounded-xl p-6 sticky top-24">
                <h3 class="font-bold text-gray-900 mb-4">{{ __('Categorías') }}</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('shop.index') }}"
                           class="block py-1 text-sm {{ !request('category') ? 'text-amber-600 font-semibold' : 'text-gray-600 hover:text-amber-600' }}">
                            {{ __('Todos los productos') }}
                        </a>
                    </li>
                    @foreach($categories as $cat)
                        <li>
                            <a href="{{ route('shop.index', ['category' => $cat->id]) }}"
                               class="block py-1 text-sm {{ (isset($category) && $category->id == $cat->id) ? 'text-amber-600 font-semibold' : 'text-gray-600 hover:text-amber-600' }}">
                                {{ $cat->name }}
                                <span class="text-gray-400">({{ $cat->products_count }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <hr class="my-4">

                <h3 class="font-bold text-gray-900 mb-3">{{ __('Ordenar por') }}</h3>
                <select onchange="window.location = this.value" class="w-full text-sm border-gray-300 rounded-lg">
                    <option value="{{ route('shop.index', array_merge(request()->query(), ['sort' => ''])) }}">{{ __('Más recientes') }}</option>
                    <option value="{{ route('shop.index', array_merge(request()->query(), ['sort' => 'price_asc'])) }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('Precio: menor a mayor') }}</option>
                    <option value="{{ route('shop.index', array_merge(request()->query(), ['sort' => 'price_desc'])) }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('Precio: mayor a menor') }}</option>
                    <option value="{{ route('shop.index', array_merge(request()->query(), ['sort' => 'name'])) }}" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ __('Nombre A-Z') }}</option>
                </select>
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="flex-1">
            @if($products->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <a href="{{ route('shop.show', $product) }}" class="group bg-white border rounded-2xl overflow-hidden hover:shadow-lg transition relative {{ $product->is_coming_soon ? 'opacity-80' : '' }}">
                            {{-- Coming Soon Badge --}}
                            @if($product->is_coming_soon)
                                <div class="absolute top-3 left-3 z-10">
                                    <span class="inline-flex items-center bg-gray-900 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ __('Próximamente') }}
                                    </span>
                                </div>
                            @endif
                            <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
                                @if($product->getMainImageUrl())
                                    <img src="{{ $product->getMainImageUrl() }}" alt="{{ $product->name }}"
                                         class="w-full h-full object-contain p-2 group-hover:scale-105 transition duration-300 {{ $product->is_coming_soon ? 'grayscale-[30%]' : '' }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="text-xs text-amber-600 font-semibold uppercase tracking-wider mb-1">{{ $product->category->name ?? '' }}</p>
                                <h3 class="font-bold text-gray-900 group-hover:text-amber-600 transition">{{ $product->name }}</h3>
                                @if($product->short_description)
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $product->short_description }}</p>
                                @endif
                                <div class="mt-3">
                                    @if($product->is_coming_soon)
                                        <span class="text-xs bg-amber-50 text-amber-700 font-semibold px-2 py-1 rounded-full">{{ __('Próximamente') }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <p class="text-gray-500 text-lg">{{ __('No se encontraron productos.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
