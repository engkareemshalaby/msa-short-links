<?php

namespace App\Services;

use App\Models\ShortLink;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitTracker
{
    public function record(ShortLink $link, Request $request): Visit
    {
        $agent = (string) $request->userAgent();
        $ip = (string) $request->ip();
        $referer = mb_substr((string) $request->headers->get('referer'), 0, 2000);
        $salt = (string) config('app.key');

        return Visit::create([
            'short_link_id' => $link->id,
            'visited_at' => now(),
            'ip_address' => $ip ?: null,
            'ip_hash' => $ip ? hash_hmac('sha256', $ip, $salt) : null,
            'visitor_hash' => hash_hmac('sha256', $ip.'|'.$agent, $salt),
            'user_agent' => mb_substr($agent, 0, 1000),
            'referer' => $referer ?: null,
            'referer_host' => $referer ? (parse_url($referer, PHP_URL_HOST) ?: null) : null,
            'device_type' => $this->device($agent),
            'browser' => $this->browser($agent),
            'platform' => $this->platform($agent),
            'language' => mb_substr((string) $request->getPreferredLanguage(), 0, 10),
            'query_string' => mb_substr((string) $request->getQueryString(), 0, 2000) ?: null,
            'is_bot' => (bool) preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp/i', $agent),
        ]);
    }

    private function device(string $agent): string
    {
        if (preg_match('/ipad|tablet|kindle|silk/i', $agent)) {
            return 'Tablet';
        }
        if (preg_match('/mobile|iphone|ipod|android/i', $agent)) {
            return 'Mobile';
        }
        if (preg_match('/bot|crawl|spider/i', $agent)) {
            return 'Bot';
        }

        return 'Desktop';
    }

    private function browser(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'OPR/') => 'Opera',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Other',
        };
    }

    private function platform(string $agent): string
    {
        return match (true) {
            preg_match('/Windows/i', $agent) === 1 => 'Windows',
            preg_match('/Android/i', $agent) === 1 => 'Android',
            preg_match('/iPhone|iPad|iPod/i', $agent) === 1 => 'iOS',
            preg_match('/Macintosh|Mac OS/i', $agent) === 1 => 'macOS',
            preg_match('/Linux/i', $agent) === 1 => 'Linux',
            default => 'Other',
        };
    }
}
