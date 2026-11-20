@extends('layouts.app')

@section('title', __('Verify email') . ' — ' . config('platform.public_name'))

@section('content')
<main class="min-h-[65vh] bg-gray-50 px-4 py-16">
    <section class="mx-auto max-w-lg rounded-2xl bg-white p-8 text-center shadow-lg">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Verify your email address') }}</h1>
        <p class="mt-3 text-gray-600">{{ __('Before showing your data, we need to confirm you have access to this email.') }}</p>

        @if (session('status') === 'verification-link-sent')
            <p class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ __('We sent a new verification link.') }}</p>
        @endif

        <form class="mt-6" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="rounded-lg bg-amber-600 px-5 py-3 font-semibold text-white hover:bg-amber-700">{{ __('Resend verification link') }}</button>
        </form>

        <form class="mt-4" method="POST" action="{{ route('customer.logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 underline">{{ __('Sign out') }}</button>
        </form>
    </section>
</main>
@endsection
