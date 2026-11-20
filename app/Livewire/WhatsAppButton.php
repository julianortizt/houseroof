<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class WhatsAppButton extends Component
{
    public string $phone = '';
    public string $userMessage = '';
    public bool $expanded = false;
    public bool $enabled = false;
    public string $position = 'bottom-right';
    public string $color = '#25D366';
    public string $welcomeMessage = '';

    public function mount(): void
    {
        $configuredPhone = Setting::get('site_whatsapp');
        $this->enabled = (bool) Setting::get('whatsapp_enabled', false) && filled($configuredPhone);
        $this->position = Setting::get('whatsapp_position', 'bottom-right');
        $this->color = Setting::get('whatsapp_color', '#25D366');
        $this->welcomeMessage = Setting::translated('whatsapp_message', __('Hello, how can I help you?'));
        $this->phone = $this->cleanPhone((string) $configuredPhone);
    }

    /**
     * Clean phone number: remove spaces, dashes, +, etc.
     */
    public function cleanPhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone) ?: '';
    }

    public function getWhatsAppUrl(string $message = ''): string
    {
        $text = trim((string) ($message !== '' ? $message : $this->welcomeMessage));
        $phone = $this->cleanPhone($this->phone);

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text !== '' ? $text : __('Hello, how can I help you?'));
    }

    public function toggle(): void
    {
        $this->expanded = !$this->expanded;
    }

    public function send(): void
    {
        $text = !empty($this->userMessage)
            ? $this->userMessage
            : $this->welcomeMessage;

        $this->dispatch('openWhatsApp', url: $this->getWhatsAppUrl($text));
    }

    /**
     * Get position CSS classes based on settings.
     */
    public function getPositionClasses(): string
    {
        return match ($this->position) {
            'bottom-left' => 'bottom-6 left-6',
            default => 'bottom-6 right-6',
        };
    }

    public function render()
    {
        return view('livewire.whatsapp-button');
    }
}
