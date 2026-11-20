<x-filament::page>
    {{-- Demo Mode Banner --}}
    @php
        $groqKey = config('services.groq.api_key', env('GROQ_API_KEY', ''));
        $isOpenAIConfigured = !empty(env('OPENAI_API_KEY', ''));
        $isDemoMode = empty($groqKey) && !$isOpenAIConfigured;
    @endphp

    @if($isDemoMode)
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
            <div class="flex items-start space-x-3">
                <x-heroicon-o-information-circle class="w-5 h-5 text-amber-500 mt-0.5"/>
                <div>
                    <p class="font-semibold text-amber-800">Demo Mode Active</p>
                    <p class="text-sm text-amber-700 mt-1">
                        No API key configured. The assistant generates demo content.
                        To get real AI-generated content, configure your Groq API key (free).
                    </p>
                    <div class="mt-2 flex items-center space-x-2">
                        <code class="text-xs bg-amber-100 px-2 py-1 rounded">GROQ_API_KEY=gsk_...</code>
                        <a href="https://console.groq.com/keys" target="_blank" class="text-xs text-amber-600 underline">Get free key →</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Sidebar: Configuration --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Provider Selection --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center space-x-2">
                        <x-filament::icon icon="heroicon-o-cpu-chip" class="w-5 h-5 text-primary-500"/>
                        <span>AI Provider</span>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    {{-- Provider --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                        <select wire:model.live="selectedProvider" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach($this->getProviders() as $key => $provider)
                                <option value="{{ $key }}">{{ $provider['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Model --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                        <select wire:model.live="selectedModel" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach($this->getModels() as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Temperature --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Temperature: {{ $temperature }}
                            <span class="text-gray-400 text-xs">(0=strict, 1=creative)</span>
                        </label>
                        <input type="range" wire:model.live="temperature" min="0" max="2" step="0.1"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>

                    {{-- Max Tokens --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Max tokens: {{ $maxTokens }}
                        </label>
                        <input type="range" wire:model.live="maxTokens" min="256" max="8192" step="256"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>
                </div>
            </x-filament::section>

            {{-- Quick Prompts --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center space-x-2">
                        <x-filament::icon icon="heroicon-o-bolt" class="w-5 h-5 text-amber-500"/>
                        <span>Quick Templates</span>
                    </div>
                </x-slot>

                <div class="space-y-2">
                    @foreach($this->getPrompts() as $key => $prompt)
                        <button wire:click="$set('selectedPrompt', '{{ $key }}')"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm border transition {{ $selectedPrompt === $key ? 'bg-primary-50 border-primary-300 text-primary-700 font-semibold' : 'bg-white border-gray-200 hover:bg-gray-50' }}">
                            <div class="flex items-center space-x-2">
                                <x-filament::icon :icon="$prompt['icon']" class="w-4 h-4"/>
                                <span>{{ $prompt['name'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </x-filament::section>

            {{-- API Keys Status --}}
            <x-filament::section>
                <x-slot name="heading">API Keys Status</x-slot>
                @php
                    $groqKey = config('services.groq.api_key', env('GROQ_API_KEY', ''));
                    $openaiKey = env('OPENAI_API_KEY', '');
                    $anthropicKey = env('ANTHROPIC_API_KEY', '');
                    $deepseekKey = env('DEEPSEEK_API_KEY', '');
                @endphp
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span>Groq</span>
                        @if(!empty($groqKey))
                            <span class="text-green-600 font-semibold">✓ Configured</span>
                        @else
                            <span class="text-red-500">✗ Not configured</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span>OpenAI</span>
                        @if(!empty($openaiKey))
                            <span class="text-green-600 font-semibold">✓ Configured</span>
                        @else
                            <span class="text-red-500">✗ Not configured</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Anthropic</span>
                        @if(!empty($anthropicKey))
                            <span class="text-green-600 font-semibold">✓ Configured</span>
                        @else
                            <span class="text-red-500">✗ Not configured</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span>DeepSeek</span>
                        @if(!empty($deepseekKey))
                            <span class="text-green-600 font-semibold">✓ Configured</span>
                        @else
                            <span class="text-red-500">✗ Not configured</span>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Main: Input & Output --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Input --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center space-x-2">
                        <x-filament::icon icon="heroicon-o-pencil" class="w-5 h-5 text-primary-500"/>
                        <span>Input</span>
                        @if($selectedPrompt)
                            <x-filament::badge color="primary">
                                {{ $this->getPrompts()[$selectedPrompt]['name'] ?? '' }}
                            </x-filament::badge>
                        @endif
                    </div>
                </x-slot>

                <div class="space-y-4">
                    <textarea wire:model="userInput" rows="8"
                              placeholder="Type or paste text for the AI to work with.&#10;&#10;Examples:&#10;• Product name to generate description&#10;• Blog article topic&#10;• Text to translate&#10;• Product info for technical spec"
                              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">{{ strlen($userInput ?? '') }} characters</span>
                        <x-filament::button wire:click="generate"
                                            :loading="$isGenerating"
                                            :disabled="empty($userInput)"
                                            icon="heroicon-o-sparkles"
                                            size="lg">
                            {{ $isGenerating ? 'Generating...' : 'Generate content' }}
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>

            {{-- Output --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center space-x-2">
                        <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-green-500"/>
                        <span>Generated Result</span>
                    </div>
                </x-slot>

                @if($aiResponse)
                    <div class="space-y-4">
                        <div class="bg-gray-50 border rounded-xl p-6 text-sm text-gray-800 leading-relaxed" style="min-height: 200px;">
                            {!! $aiResponse !!}
                        </div>

                        <div class="flex items-center space-x-3">
                            <button onclick="navigator.clipboard.writeText(this.closest('[data-response]').querySelector('[data-content]').textContent); document.getElementById('copyFeedback').classList.remove('hidden'); setTimeout(() => document.getElementById('copyFeedback').classList.add('hidden'), 2000);"
                                    data-response
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-semibold">
                                <x-heroicon-o-clipboard class="w-4 h-4 mr-1"/>
                                Copy to clipboard
                            </button>
                            <span id="copyFeedback" class="hidden text-green-600 text-sm font-semibold">✓ Copied</span>
                        </div>

                        <div data-content class="hidden">{{ $aiResponse }}</div>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-400">
                        <x-heroicon-o-sparkles class="w-12 h-12 mx-auto mb-3 opacity-50"/>
                        <p class="text-lg font-medium">Generated result will appear here</p>
                        <p class="text-sm mt-1">Select a template, enter your text, and click "Generate content"</p>
                    </div>
                @endif
            </x-filament::section>
        </div>
    </div>
</x-filament::page>
