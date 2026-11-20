{{-- AI Chat Widget --}}
<div>
@if($enabled)
<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-[100] flex flex-col-reverse items-end">
    {{-- Floating Button (FIRST in DOM = renders at bottom) --}}
    <button @click="open = !open"
            class="w-16 h-16 rounded-full bg-amber-500 hover:bg-amber-600 text-white shadow-2xl flex items-center justify-center transition-all duration-300 border-4 border-white ml-auto"
            :class="open ? '' : 'hover:scale-110'"
            style="box-shadow: 0 4px 20px rgba(245, 158, 11, 0.5);">
        <template x-if="!open">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </template>
        <template x-if="open">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </template>
    </button>

    {{-- Chat Window (SECOND in DOM = positioned ABOVE button with margin) --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-3 bg-white rounded-2xl shadow-2xl border overflow-hidden flex flex-col w-96 max-w-[calc(100vw-3rem)]"
         style="height: 500px;">

        {{-- Header --}}
        <div class="bg-amber-500 text-white px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <img src="{{ upload_url('site/rino-asistente.png') }}" alt="Rino" class="w-10 h-10 rounded-full object-cover border-2 border-white/30">
                <div>
                    <p class="font-bold text-sm">{{ config('platform.public_name') }} {{ __('AI Assistant') }}</p>
                    <p class="text-xs text-amber-100">{{ __('Virtual Assistant') }}</p>
                </div>
            </div>
            <button @click="open = false" class="text-white/80 hover:text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
            @foreach($messages as $msg)
                @if($msg['role'] === 'user')
                    <div class="flex justify-end">
                        <div class="bg-amber-600 text-white rounded-2xl rounded-br-md px-4 py-2.5 max-w-[80%]">
                            <p class="text-sm">{!! nl2br(e($msg['content'])) !!}</p>
                        </div>
                    </div>
                @else
                    <div class="flex justify-start">
                        <div class="bg-white border rounded-2xl rounded-bl-md px-4 py-2.5 max-w-[85%] shadow-sm">
                            <div class="text-sm text-gray-800 prose prose-sm">{!! nl2br($msg['content']) !!}</div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if($isLoading)
                <div class="flex justify-start">
                    <div class="bg-white border rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-amber-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                            <div class="w-2 h-2 bg-amber-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-amber-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Input --}}
        <div class="border-t bg-white px-4 py-3">
            <form wire:submit.prevent="send" class="flex items-center space-x-2">
                <input type="text"
                       wire:model="message"
                       placeholder="{{ __('Type your question...') }}"
                       class="flex-1 border border-gray-200 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <button type="submit"
                        class="w-9 h-9 bg-amber-600 hover:bg-amber-700 text-white rounded-full flex items-center justify-center transition"
                        @if($isLoading) disabled @endif>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
            <p class="text-xs text-gray-400 text-center mt-1.5">🤖 {{ __('AI Assistant') }} — {{ config('platform.public_name') }}</p>
        </div>
    </div>
</div>
@endif
</div>
