<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class GeneralExhibitionParentWelcome extends GeneralExhibitionWelcome
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->messageLocale === 'ar'
            ? 'خطوات التحاق نجلكم/نجلتكم بجامعة MSA'
            : 'Your Child’s Next Steps to Join MSA University');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.general-exhibition-welcome',
            text: 'emails.general-exhibition-welcome-text',
            with: ['isParentEmail' => true],
        );
    }
}
