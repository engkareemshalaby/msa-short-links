<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ExhibitionCampaignMessage extends Mailable
{
    public function __construct(public string $mailSubject, public string $bodyHtml) {}

    public function build(): static
    {
        return $this->subject($this->mailSubject)
            ->view('emails.exhibition-campaign')
            ->text('emails.exhibition-campaign-text');
    }
}
