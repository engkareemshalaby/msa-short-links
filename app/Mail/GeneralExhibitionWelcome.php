<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralExhibitionWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $studentName,
        public string $referenceCode,
        public string $messageLocale,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->messageLocale === 'ar'
            ? 'خطواتك القادمة للانضمام إلى جامعة MSA'
            : 'Your Next Steps to Join MSA University');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.general-exhibition-welcome', text: 'emails.general-exhibition-welcome-text');
    }
}
