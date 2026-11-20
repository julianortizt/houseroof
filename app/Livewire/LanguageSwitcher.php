<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public string $currentLocale;

    public array $availableLocales = [
        'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
        'en' => ['name' => 'English (UK)', 'flag' => '🇬🇧'],
    ];

    public function mount(): void
    {
        $this->currentLocale = app()->getLocale();
    }

    public function updatedCurrentLocale(): void
    {
        $this->switchLocale($this->currentLocale);
    }

    public function switchLocale(string $locale): void
    {
        if (array_key_exists($locale, $this->availableLocales)) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
            $this->currentLocale = $locale;
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
