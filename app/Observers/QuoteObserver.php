<?php

namespace App\Observers;

use App\Models\Quote;
use App\Mail\QuoteReceived;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class QuoteObserver
{
    public function created(Quote $quote): void
    {
        try {
            $email = Config::get('mail.from.address', 'hello@TU_DOMINIO.com');
            Mail::to($email)->send(new QuoteReceived($quote));
        } catch (\Exception $e) {
            \Log::error('Error sending quote notification email: ' . $e->getMessage());
        }
    }
}
