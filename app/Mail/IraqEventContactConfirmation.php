<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IraqEventContactConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $contactName,
        public string $messageLocale,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->messageLocale === 'ar'
            ? 'تم استلام بيانات التواصل | معرض العراق 2026'
            : 'We received your contact details | Iraq Exhibition 2026');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.iraq-event-contact-confirmation',
            text: 'emails.iraq-event-contact-confirmation-text',
        );
    }
}
