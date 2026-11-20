@extends('layouts.app')

@section('title', e($project->title))

@section('content')
<section class="platform-page-hero">
    <div class="platform-page-hero__inner px-4 sm:px-6 lg:px-8">
        <nav class="mb-6 text-sm text-gray-400" aria-label="{{ __('Navegación de ruta') }}">
            <a href="{{ route('home') }}" class="hover:text-white">{{ __('Inicio') }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('projects.index') }}" class="hover:text-white">{{ __('Proyectos') }}</a>
            <span class="mx-2">/</span>
            <span class="text-white">{{ e($project->title) }}</span>
        </nav>
        @if($project->category && isset($categories[$project->category]))
            <span class="platform-page-kicker">{{ $categories[$project->category] }}</span>
        @endif
        <h1 class="text-4xl font-bold">{{ e($project->title) }}</h1>
        @if($project->location)
            <p class="text-gray-300 mt-2 flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                {{ $project->location }}
            </p>
        @endif
    </div>
</section>

<section class="platform-page-body py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Main project content --}}
        @if($project->description)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16 items-start mb-12">
                <div class="prose prose-lg text-gray-700 max-w-none">{!! $project->description !!}</div>

                @if($project->media->count())
                    @php
                        $mainMedia = $project->media->first();
                    @endphp
                    <div class="aspect-[4/3] bg-gray-100 rounded-2xl overflow-hidden shadow-lg">
                        <img src="{{ $mainMedia->getUrl() }}" alt="{{ e($project->title) }}"
                             class="w-full h-full object-cover">
                    </div>
                @endif
            </div>
        @elseif($project->media->count())
            @php
                $mainMedia = $project->media->first();
            @endphp
            <div class="max-w-4xl mx-auto mb-12">
                <div class="aspect-[4/3] bg-gray-100 rounded-2xl overflow-hidden shadow-lg">
                    <img src="{{ $mainMedia->getUrl() }}" alt="{{ e($project->title) }}"
                         class="w-full h-full object-cover">
                </div>
            </div>
        @endif

        {{-- Additional gallery images --}}
        @if($project->media->count() > 1)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-12">
                @foreach($project->media->skip(1) as $media)
                    <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden">
                        <img src="{{ $media->getUrl() }}" alt="{{ e($project->title) }}"
                             class="w-full h-full object-cover hover:scale-105 transition duration-300 cursor-pointer">
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Video --}}
        @if($project->video_url)
            <div class="max-w-3xl mx-auto mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Video del proyecto') }}</h2>
                <div class="aspect-video rounded-xl overflow-hidden shadow-lg">
                    @php
                        $videoEmbedUrl = null;
                        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([^&\?\/]+)/', $project->video_url, $matches)) {
                            $videoEmbedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                        } elseif (preg_match('/vimeo\.com\/(\d+)/', $project->video_url, $matches)) {
                            $videoEmbedUrl = 'https://player.vimeo.com/video/' . $matches[1];
                        }
                    @endphp
                    @if($videoEmbedUrl)
                        <iframe src="{{ $videoEmbedUrl }}" frameborder="0" allowfullscreen class="w-full h-full"></iframe>
                    @else
                        <a href="{{ $project->video_url }}" target="_blank" rel="noopener" class="text-amber-600 hover:underline">{{ __('Ver video externo') }}</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
