@extends('layouts.app')
@section('title', $post->title)

@section('content')
<section class="platform-page-hero">
    <div class="platform-page-hero__inner max-w-4xl px-4 sm:px-6 lg:px-8">
        <nav class="mb-6 text-sm text-gray-400" aria-label="{{ __('Navegación de ruta') }}">
            <a href="{{ route('home') }}" class="hover:text-white">{{ __('Inicio') }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('blog.index') }}" class="hover:text-white">{{ __('Blog') }}</a>
        </nav>
        @if($post->category)
            <span class="text-amber-500 font-semibold text-sm uppercase tracking-wider">{{ $post->category->name }}</span>
        @endif
        <h1 class="platform-page-heading mt-2 mb-4">{{ $post->title }}</h1>
        <div class="flex items-center text-gray-400 text-sm">
            <span>{{ __('Publicado') }} {{ $post->published_at?->format('d/m/Y') }}</span>
            @if($post->tags->count())
                <span class="mx-3">•</span>
                @foreach($post->tags as $tag)
                    <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="hover:text-amber-500 mr-2">#{{ $tag->name }}</a>
                @endforeach
            @endif
        </div>
    </div>
</section>

<article class="platform-page-body py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Featured Image --}}
        @if($post->getFirstMediaUrl())
            <div class="aspect-[16/9] rounded-2xl overflow-hidden mb-8">
                <img src="{{ $post->getFirstMediaUrl() }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        {{-- Content --}}
        <div class="prose prose-lg max-w-none text-gray-700">
            {!! $post->content !!}
        </div>

        {{-- Video --}}
        @if($post->video_url)
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Video') }}</h2>
                <div class="aspect-video rounded-xl overflow-hidden shadow-lg">
                    @if(Str::contains($post->video_url, 'youtube'))
                        @php $videoId = Yt::extract($post->video_url); @endphp
                        <iframe src="https://www.youtube.com/embed/{{ $videoId }}" frameborder="0" allowfullscreen class="w-full h-full"></iframe>
                    @elseif(Str::contains($post->video_url, 'vimeo'))
                        @php $vimeoId = last(explode('/', $post->video_url)); @endphp
                        <iframe src="https://player.vimeo.com/video/{{ $vimeoId }}" frameborder="0" allowfullscreen class="w-full h-full"></iframe>
                    @endif
                </div>
            </div>
        @endif
    </div>
</article>

{{-- Related Posts --}}
@if($relatedPosts->count())
<section class="platform-page-body py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">{{ __('Publicaciones relacionadas') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($relatedPosts as $rp)
                <a href="{{ route('blog.show', $rp) }}" class="platform-card platform-card--interactive group overflow-hidden">
                    <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                        @if($rp->getFirstMediaUrl())
                            <img src="{{ $rp->getFirstMediaUrl() }}" alt="{{ $rp->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @endif
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-amber-600 transition">{{ $rp->title }}</h3>
                        <p class="text-gray-600 text-sm mt-2 line-clamp-2">{{ $rp->excerpt }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
