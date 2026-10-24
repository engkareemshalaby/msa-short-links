<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class IraqExhibitionParentWelcome extends IraqExhibitionWelcome
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->messageLocale === 'ar'
            ? 'خطوات التحاق نجلكم/نجلتكم بجامعة MSA | معرض العراق للطلاب 2026'
            : 'Your Child’s Next Steps to Join MSA University | Iraq Exhibition 2026');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.iraq-exhibition-welcome',
            text: 'emails.iraq-exhibition-welcome-text',
            with: ['isParentEmail' => true],
        );
    }
}
