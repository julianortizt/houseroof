<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quote $quote,
        public bool $attachPdf = true
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Presupuesto ' . $this->quote->formatted_number . ' - ' . config('platform.public_name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.quote-sent',
            with: [
                'quote' => $this->quote,
            ]
        );
    }

    public function attachments(): array
    {
        if (!$this->attachPdf) {
            return [];
        }

        $pdf = Pdf::loadView('admin.quotes.pdf', [
            'quote' => $this->quote->load('items.product', 'items.variant')
        ]);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                $this->quote->formatted_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}