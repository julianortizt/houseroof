<div class="ml-2" x-data="{ dark: document.documentElement.classList.contains('dark') }">
    <button
        type="button"
        title="{{ __('Toggle theme') }}"
        aria-label="{{ __('Toggle theme') }}"
        class="fi-icon-btn fi-size-md flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
        @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
    >
        <x-heroicon-o-sun x-show="dark" class="h-5 w-5" />
        <x-heroicon-o-moon x-show="!dark" class="h-5 w-5" />
    </button>
</div>
