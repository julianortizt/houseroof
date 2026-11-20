<div>
@if($showBanner)
@php
    $whatsappPosition = \App\Models\Setting::get('whatsapp_position', 'bottom-left');
    $consentSpacer = $whatsappPosition === 'bottom-left' ? 'padding-left: 5rem;' : 'padding-right: 5rem;';
@endphp
<div class="fixed bottom-0 left-0 right-0 z-[90] bg-gray-900/95 backdrop-blur-sm text-white border-t border-gray-700" x-data>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3" style="{{ $consentSpacer }}">
            <div class="flex items-center space-x-3 text-sm">
                <span class="text-lg">🍪</span>
                <p class="text-gray-300">
                    {{ $message }}
                    @if($privacyUrl)
                        <a href="{{ $privacyUrl }}" class="text-amber-400 hover:underline font-semibold" target="_blank">
                            {{ __('Más info') }}
                        </a>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button wire:click="rejectAll"
                        class="px-4 py-1.5 border border-gray-600 rounded-lg text-xs font-semibold hover:bg-gray-800 transition">
                    {{ $declineText ?: __('Rechazar') }}
                </button>
                <button wire:click="acceptAll"
                        class="px-4 py-1.5 bg-amber-600 rounded-lg text-xs font-bold hover:bg-amber-700 transition">
                    {{ $acceptText ?: __('Aceptar todas') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>
