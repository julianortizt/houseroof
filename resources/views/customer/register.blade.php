@extends('layouts.app')

@section('title', __('Create customer account') . ' — ' . config('platform.public_name'))

@section('content')
<main class="min-h-[65vh] bg-gray-50 px-4 py-16">
    <section class="mx-auto max-w-md rounded-2xl bg-white p-8 shadow-lg">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Create customer account') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('Verify your email to access the information linked to your account.') }}</p>

        @if ($errors->any())
            <div class="mt-5 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form class="mt-6 space-y-4" method="POST" action="{{ route('customer.register.post') }}">
            @csrf
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                <input id="name" name="name" type="text" autocomplete="name" required maxlength="255" value="{{ old('name') }}" class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Email address') }}</label>
                <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Confirm password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-lg border-gray-300">
            </div>
            <button type="submit" class="w-full rounded-lg bg-amber-600 px-4 py-3 font-semibold text-white hover:bg-amber-700">{{ __('Create account') }}</button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            {{ __('Already have an account?') }}
            <a href="{{ route('customer.login') }}" class="font-semibold text-amber-700 hover:underline">{{ __('Sign in') }}</a>
        </p>
    </section>
</main>
@endsection
