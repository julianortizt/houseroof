@extends('layouts.app')

@section('title', __('mapscalculator.create_title'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    @include('mapscalculator.partials.nav')

    <div class="mb-8">
        <a href="{{ route('mapscalculator.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-2 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Volver
        </a>
        <h1 class="text-3xl font-bold text-gray-900">{{ __('mapscalculator.create_page_title') }}</h1>
        <p class="text-gray-500 mt-1">{{ __('mapscalculator.create_subtitle') }}</p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('mapscalculator.project.store') }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-6" x-data="{ selectedType: '{{ old('type', 'interior') }}', showNewClient: {{ old('new_client_name') ? 'true' : 'false' }} }">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('mapscalculator.client') }} *</label>

            <template x-if="!showNewClient">
                <select name="client_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">{{ __('mapscalculator.select_client') }}</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected((string) old('client_id', request('client')) === (string) $client->id)>{{ $client->name }} {{ $client->email ? '(' . $client->email . ')' : '' }} {{ $client->phone ? '| ' . $client->phone : '' }}</option>
                    @endforeach
                </select>
            </template>

            <div x-show="showNewClient" class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-1">
                <input type="text" name="new_client_name" x-bind:required="showNewClient" value="{{ old('new_client_name') }}" placeholder="{{ __('mapscalculator.client_name_ph') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <input type="email" name="new_client_email" value="{{ old('new_client_email') }}" placeholder="{{ __('mapscalculator.email_optional') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <input type="text" name="new_client_phone" value="{{ old('new_client_phone') }}" placeholder="{{ __('mapscalculator.phone_optional') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <button type="button" @click="showNewClient = !showNewClient" class="mt-2 text-sm font-medium text-primary-600 hover:text-primary-700 flex items-center gap-1">
                <svg x-show="!showNewClient" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span x-show="!showNewClient">{{ __('mapscalculator.create_new_client') }}</span>
                <span x-show="showNewClient">{{ __('mapscalculator.use_existing_client') }}</span>
            </button>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('mapscalculator.project_name') }} *</label>
            <input type="text" name="name" required value="{{ old('name') }}" placeholder="{{ __('mapscalculator.project_name_ph') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('mapscalculator.project_type') }} *</label>
            <div class="grid grid-cols-3 gap-3">
                <label class="relative cursor-pointer">
                    <input type="radio" name="type" value="interior" required x-model="selectedType" class="sr-only peer">
                    <div class="p-4 border-2 rounded-lg text-center transition-all duration-200" :class="selectedType === 'interior' ? 'border-primary-500 bg-primary-50 ring-2 ring-primary-500' : 'border-gray-200 hover:border-gray-300'">
                        <svg class="w-8 h-8 mx-auto mb-2" :class="selectedType === 'interior' ? 'text-primary-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                        <div class="font-medium text-gray-900">{{ __('mapscalculator.type_interior') }}</div>
                        <div class="text-xs text-gray-500">{{ __('mapscalculator.interior_desc') }}</div>
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="type" value="exterior" x-model="selectedType" class="sr-only peer">
                    <div class="p-4 border-2 rounded-lg text-center transition-all duration-200" :class="selectedType === 'exterior' ? 'border-primary-500 bg-primary-50 ring-2 ring-primary-500' : 'border-gray-200 hover:border-gray-300'">
                        <svg class="w-8 h-8 mx-auto mb-2" :class="selectedType === 'exterior' ? 'text-primary-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <div class="font-medium text-gray-900">{{ __('mapscalculator.type_exterior') }}</div>
                        <div class="text-xs text-gray-500">{{ __('mapscalculator.exterior_desc') }}</div>
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="type" value="mixto" x-model="selectedType" class="sr-only peer">
                    <div class="p-4 border-2 rounded-lg text-center transition-all duration-200" :class="selectedType === 'mixto' ? 'border-primary-500 bg-primary-50 ring-2 ring-primary-500' : 'border-gray-200 hover:border-gray-300'">
                        <svg class="w-8 h-8 mx-auto mb-2" :class="selectedType === 'mixto' ? 'text-primary-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <div class="font-medium text-gray-900">{{ __('mapscalculator.type_mixto') }}</div>
                        <div class="text-xs text-gray-500">{{ __('mapscalculator.mixto_desc') }}</div>
                    </div>
                </label>
            </div>
            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-4 border-t flex justify-end gap-3">
            <a href="{{ route('mapscalculator.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">{{ __('mapscalculator.cancel') }}</a>
            <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('mapscalculator.create_project') }}
            </button>
        </div>
    </form>
</div>
@endsection