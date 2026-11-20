@extends('layouts.app')

@section('meta_description', __('meta_blog_desc'))
@section('meta_keywords', __('meta_blog_kw'))
@section('title', __('Blog'))

@section('content')
<section class="platform-page-hero">
    <div class="platform-page-hero__inner px-4 sm:px-6 lg:px-8 text-center">
        <span class="platform-page-kicker">{{ app()->getLocale() === 'es' ? 'Ideas y recursos' : 'Insights and resources' }}</span>
        <h1 class="platform-page-heading">{{ __('Blog') }}</h1>
        <p class="platform-page-subtitle">
            {{ app()->getLocale() === 'es' ? 'Ideas, guías y novedades para mejorar el trabajo de tu equipo.' : 'Ideas, guides, and updates to help your team work better.' }}
        </p>
    </div>
</section>

<section class="platform-page-body py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar --}}
            <div class="lg:w-64 flex-shrink-0">
                <div class="platform-card p-6 sticky top-24">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('Categorías') }}</h3>
                    <ul class="space-y-2">
                        @foreach($categories as $cat)
                            @if($cat->blog_posts_count > 0)
                                <li>
                                    <a href="{{ route('blog.index', ['category' => $cat->id]) }}"
                                       class="block py-1 text-sm {{ request('category') == $cat->id ? 'text-amber-600 font-semibold' : 'text-gray-600 hover:text-amber-600' }}">
                                        {{ $cat->name }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Posts --}}
            <div class="flex-1">
                @if($posts->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($posts as $post)
                            <a href="{{ route('blog.show', $post) }}" class="platform-card platform-card--interactive group overflow-hidden">
                                <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                                    @if($post->getFirstMediaUrl())
                                        <img src="{{ $post->getFirstMediaUrl() }}" alt="{{ $post->title }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    @endif
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center space-x-2 text-xs text-gray-500 mb-2">
                                        @if($post->category)
                                            <span class="text-amber-600 font-semibold">{{ $post->category->name }}</span>
                                            <span>•</span>
                                        @endif
                                        <span>{{ $post->published_at?->format('d/m/Y') }}</span>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-amber-600 transition mb-2">{{ $post->title }}</h3>
                                    <p class="text-gray-600 text-sm line-clamp-3">{{ $post->excerpt }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $posts->links() }}</div>
                @else
                    <div class="text-center py-16">
                        <p class="text-gray-500 text-lg">{{ __('Próximamente publicaremos artículos interesantes.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
