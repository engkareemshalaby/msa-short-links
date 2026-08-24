<?php

namespace App\Console\Commands;

use App\Models\ShortLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class CheckLinkHealth extends Command
{
    protected $signature = 'links:check-health {--id= : Check one link ID only}';
    protected $description = 'Check short-link destinations and record their availability';

    public function handle(): int
    {
        $query = ShortLink::query()->where('is_active', true);
        if ($this->option('id')) $query->whereKey($this->option('id'));

        $query->orderBy('id')->chunkById(100, function ($links) {
            foreach ($links as $link) $this->check($link);
        });
        return self::SUCCESS;
    }

    private function check(ShortLink $link): void
    {
        try {
            $response = Http::timeout(12)->connectTimeout(5)->head($link->destination_url);
            if ($response->status() === 405) $response = Http::timeout(12)->connectTimeout(5)->get($link->destination_url);
            $healthy = $response->successful() || $response->redirect();
            $status = $response->status();
        } catch (\Throwable $exception) {
            $healthy = false;
            $status = null;
        }

        $wasBroken = $link->health_status === 'broken';
        $link->forceFill(['health_status' => $healthy ? 'healthy' : 'broken', 'last_status_code' => $status, 'last_checked_at' => now()])->save();

        if (! $healthy && ! $wasBroken && config('mail.health_alert_email')) {
            Mail::raw("The destination for {$link->short_url} is unavailable.\nDestination: {$link->destination_url}\nHTTP status: ".($status ?? 'No response'), function ($message) {
                $message->to(config('mail.health_alert_email'))->subject('MSA Go link health alert');
            });
        }
    }
}
