<x-filament-panels::page>
    <div class="max-w-4xl space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Platform modules</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Turn module access on or off without deleting existing data. Planned modules remain unavailable until their functionality is implemented.
            </p>

            <form wire:submit="save" class="mt-6 space-y-4">
                @foreach($this->getModules() as $key => $module)
                    <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <input
                            type="checkbox"
                            wire:model="moduleSettings.{{ $key }}"
                            class="mt-1 rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                        >
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-2 font-medium text-gray-950 dark:text-white">
                                {{ $module['label'] }}
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $module['status'] }}
                                </span>
                            </span>
                            <span class="mt-1 block text-sm text-gray-600 dark:text-gray-400">
                                {{ $module['description'] }}
                            </span>
                            @if($module['dependencies'])
                                <span class="mt-1 block text-xs text-gray-500 dark:text-gray-500">
                                    Requires: {{ implode(', ', $module['dependencies']) }}
                                </span>
                            @endif
                            @error("moduleSettings.$key")
                                <span class="mt-2 block text-sm text-danger-600">{{ $message }}</span>
                            @enderror
                        </span>
                    </label>
                @endforeach

                <x-filament::button type="submit">Save module settings</x-filament::button>
            </form>
        </div>
    </div>
</x-filament-panels::page>
