<?php

namespace App\Livewire;

use App\Models\Setting;
use App\Models\Page;
use Livewire\Component;

class CookieConsent extends Component
{
    public bool $showBanner = false;
    public bool $analyticsAccepted = false;
    public bool $marketingAccepted = false;

    // Settings from admin
    public bool $enabled = true;
    public string $message = '';
    public string $privacyUrl = '';
    public string $acceptText = '';
    public string $declineText = '';

    public function mount(): void
    {
        $this->enabled = Setting::get('cookies_enabled', '1') === '1';
        $this->message = Setting::get('cookies_message', __('We use cookies to improve your experience. By continuing to browse, you accept our use of cookies.'));
        $this->privacyUrl = Setting::get('cookies_privacy_url', '/legal/cookie-policy');
        if (
            in_array($this->privacyUrl, ['/legal/cookie-policy', url('/legal/cookie-policy')], true)
            && ! Page::query()->where('slug', 'cookie-policy')->exists()
        ) {
            $this->privacyUrl = '';
        }
        $this->acceptText = Setting::get('cookies_accept_text', __('Aceptar'));
        $this->declineText = Setting::get('cookies_decline_text', __('Rechazar'));

        $this->showBanner = $this->enabled && !request()->hasCookie('cookie_consent');
    }

    public function acceptAll(): void
    {
        $this->analyticsAccepted = true;
        $this->marketingAccepted = true;
        $this->savePreferences();
    }

    public function rejectAll(): void
    {
        $this->analyticsAccepted = false;
        $this->marketingAccepted = false;
        $this->savePreferences();
    }

    public function savePreferences(): void
    {
        $preferences = [
            'necessary' => true,
            'analytics' => $this->analyticsAccepted,
            'marketing' => $this->marketingAccepted,
            'accepted_at' => now()->toIso8601String(),
        ];

        cookie()->queue(
            cookie('cookie_consent', json_encode($preferences), 365 * 24 * 60)
        );
        $this->dispatch('cookie-consent-saved');

        $this->showBanner = false;
    }

    public function render()
    {
        return view('livewire.cookie-consent');
    }
}
