@extends('layouts.app')
@section('title', $page->title)

@section('content')
<section class="platform-page-hero">
    <div class="platform-page-hero__inner max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
        <span class="platform-page-kicker">{{ app()->getLocale() === 'es' ? 'Información' : 'Information' }}</span>
        <h1 class="platform-page-heading">{{ $page->title }}</h1>
    </div>
</section>

<section class="platform-page-body py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="platform-card prose prose-lg max-w-none p-6 text-gray-700 sm:p-10">
            {!! $page->content !!}
        </div>
    </div>
</section>
@endsection
