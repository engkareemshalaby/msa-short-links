<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IraqExhibitionWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $studentName,
        public string $referenceCode,
        public string $messageLocale,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Registration Received | MSA University – تم استلام تسجيلك في معرض العراق 2026');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.iraq-exhibition-welcome', text: 'emails.iraq-exhibition-welcome-text');
    }
}


