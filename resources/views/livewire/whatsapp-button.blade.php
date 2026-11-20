{{-- WhatsApp Floating Button --}}
<div>
@if($enabled)
<div class="fixed {{ $position === 'bottom-left' ? 'bottom-6 left-6' : 'bottom-6 right-6' }} z-[94]"
     x-data="{
        open: false,
        phone: '{{ $phone }}',
        color: '{{ $color }}',
        userMessage: '',
        get url() {
            let text = this.userMessage || '{{ addslashes($welcomeMessage) }}';
            return 'https://wa.me/' + this.phone + '?text=' + encodeURIComponent(text);
        }
     }">

    {{-- Expandable chat box --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-3 bg-white rounded-2xl shadow-2xl border border-gray-200 w-80 overflow-hidden">

        {{-- Header --}}
        <div class="text-white px-4 py-3 flex items-center justify-between"
             :style="'background-color:' + color">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <div>
                    <p class="font-bold text-sm">{{ config('platform.public_name') }}</p>
                    <p class="text-xs text-white/80">{{ __('Quick reply') }}</p>
                </div>
            </div>
            <button @click="open = false" class="text-white/80 hover:text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Chat bubble --}}
        <div class="bg-gray-50 px-4 py-3">
            <div class="bg-white rounded-xl p-3 shadow-sm text-sm text-gray-700 relative">
                {{ $welcomeMessage ?: __('Hello! How can we help you?') }}
                <div class="absolute -top-2 left-4 w-4 h-4 bg-white transform rotate-45 -mt-1"></div>
            </div>
        </div>

        {{-- Input area --}}
        <div class="px-4 py-3 border-t border-gray-100">
            <div class="flex items-center space-x-2">
                <input type="text"
                       x-model="userMessage"
                       placeholder="{{ __('Type your message...') }}"
                       class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:border-transparent">
                <a :href="url" target="_blank" rel="noopener noreferrer"
                   class="text-white rounded-full p-2 transition inline-flex items-center justify-center hover:opacity-90"
                   :style="'background-color:' + color">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Floating Button --}}
    <button @click="open = !open"
            class="text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 p-4 group"
            :style="'background-color:' + color">
        <svg x-show="!open" class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        <svg x-show="open" x-cloak class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    {{-- Pulse animation --}}
    <div x-show="!open" class="absolute -top-1 -right-1 w-4 h-4 rounded-full animate-ping" :style="'background-color:' + color"></div>
</div>
@endif
</div>
