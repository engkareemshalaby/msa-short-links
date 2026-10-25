<?php

namespace App\Services;

use Illuminate\Http\Request;

class RegistrationClientContext
{
    public function capture(Request $request): array
    {
        $agent = mb_substr((string) $request->userAgent(), 0, 1000);
        $ip = (string) $request->ip();

        return [
            'ip_address' => $ip ?: null,
            'ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
            'user_agent' => $agent ?: null,
            'device_type' => $this->device($agent),
            'browser' => $this->browser($agent),
            'platform' => $this->platform($agent),
            'browser_language' => mb_substr((string) $request->getPreferredLanguage(), 0, 20) ?: null,
            'connection_country' => $this->header($request, ['CF-IPCountry', 'X-Country-Code'], 2, true),
            'connection_region' => $this->header($request, ['CF-Region', 'X-Region'], 100),
            'connection_city' => $this->header($request, ['CF-IPCity', 'X-City'], 100),
            'client_hints' => array_filter([
                'brands' => $request->header('Sec-CH-UA'),
                'mobile' => $request->header('Sec-CH-UA-Mobile'),
                'platform' => $request->header('Sec-CH-UA-Platform'),
                'platform_version' => $request->header('Sec-CH-UA-Platform-Version'),
                'model' => $request->header('Sec-CH-UA-Model'),
            ], fn ($value) => is_string($value) && $value !== ''),
        ];
    }

    private function header(Request $request, array $names, int $limit, bool $uppercase = false): ?string
    {
        foreach ($names as $name) {
            $value = trim((string) $request->header($name));
            if ($value !== '') {
                $value = $uppercase ? strtoupper($value) : $value;

                return mb_substr($value, 0, $limit);
            }
        }

        return null;
    }

    private function device(string $agent): string
    {
        return match (true) {
            preg_match('/bot|crawl|spider|slurp|preview/i', $agent) === 1 => 'Bot',
            preg_match('/ipad|tablet|kindle|silk/i', $agent) === 1 => 'Tablet',
            preg_match('/mobile|iphone|ipod|android/i', $agent) === 1 => 'Mobile',
            default => 'Desktop',
        };
    }

    private function browser(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'OPR/') => 'Opera',
            str_contains($agent, 'SamsungBrowser/') => 'Samsung Internet',
            str_contains($agent, 'Chrome/') || str_contains($agent, 'CriOS/') => 'Chrome',
            str_contains($agent, 'Firefox/') || str_contains($agent, 'FxiOS/') => 'Firefox',
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
