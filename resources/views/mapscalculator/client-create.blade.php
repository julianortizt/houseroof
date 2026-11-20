@extends('layouts.app')

@section('title', __('mapscalculator.new_client_title'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    @include('mapscalculator.partials.nav')

    <div class="mb-8">
        <a href="{{ route('mapscalculator.client.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-2 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ __('mapscalculator.back') }}
        </a>
        <h1 class="text-3xl font-bold text-gray-900">{{ __('mapscalculator.new_client') }}</h1>
        <p class="text-gray-500 mt-1">{{ __('mapscalculator.new_client_subtitle') }}</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('mapscalculator.client.store') }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('mapscalculator.name') }} *</label>
            <input type="text" name="name" required value="{{ old('name') }}" placeholder="{{ __('mapscalculator.client_name_ph') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('mapscalculator.company') }}</label>
                <input type="text" name="company" value="{{ old('company') }}" placeholder="{{ __('mapscalculator.company_ph') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('mapscalculator.nif') }}</label>
                <input type="text" name="nif" value="{{ old('nif') }}" placeholder="NIF / CIF..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('mapscalculator.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('mapscalculator.email_ph') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('mapscalculator.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="{{ __('mapscalculator.phone_ph') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('mapscalculator.address') }}</label>
            <textarea name="address" rows="2" placeholder="{{ __('mapscalculator.address_ph') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('address') }}</textarea>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('mapscalculator.client.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.cancel') }}</a>
            <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium">
                {{ __('mapscalculator.create_client_assign') }}
            </button>
        </div>
    </form>
</div>
@endsection