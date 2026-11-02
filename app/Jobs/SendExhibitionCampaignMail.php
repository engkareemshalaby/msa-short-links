<?php

namespace App\Jobs;

use App\Mail\ExhibitionCampaignMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendExhibitionCampaignMail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $recipientId) {}

    public function handle(): void
    {
        $recipient = DB::table('exhibition_mail_recipients')->find($this->recipientId);
        if (! $recipient || $recipient->status !== 'pending') {
            return;
        }
        $campaign = DB::table('exhibition_mail_campaigns')->find($recipient->campaign_id);
        if (! $campaign) {
            return;
        }

        Mail::to($recipient->email)->send(new ExhibitionCampaignMessage($campaign->subject, $campaign->body_html));
        DB::table('exhibition_mail_recipients')->where('id', $this->recipientId)->update([
            'status' => 'sent', 'sent_at' => now(), 'error' => null, 'updated_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        DB::table('exhibition_mail_recipients')->where('id', $this->recipientId)->update([
            'status' => 'failed', 'error' => mb_substr($exception->getMessage(), 0, 1000), 'updated_at' => now(),
        ]);
    }
}
