<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Configuración');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Language');
    }
    public function getTitle(): string
    {
        return __('admin.Cambiar Idioma');
    }
    protected static ?string $slug = 'language';
    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.language-switcher';

    public ?string $currentLocale = null;

    public function mount(): void
    {
        $this->currentLocale = Session::get('admin_locale', 'en');
    }

    public function switchLocale(string $locale): void
    {
        $availableLocales = ['en', 'es'];

        if (in_array($locale, $availableLocales)) {
            Session::put('admin_locale', $locale);
            \Illuminate\Support\Facades\App::setLocale($locale);
            $this->currentLocale = $locale;
        }

        // Use Livewire redirect to avoid CSRF/expiration issues
        $this->redirect(route('filament.admin.pages.language'), navigate: true);
    }
}
