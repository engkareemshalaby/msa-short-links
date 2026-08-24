<?php

namespace App\Services;

use App\Models\ShortLink;
use Illuminate\Http\Request;

class SmartTargetResolver
{
    public function destination(ShortLink $link, Request $request): string
    {
        $device = $this->device((string) $request->userAgent());
        $language = strtolower(substr((string) $request->getPreferredLanguage(), 0, 2));
        $country = strtoupper((string) ($request->header('CF-IPCountry') ?: $request->header('X-Country-Code')));

        foreach ($link->smartTargets()->where('is_active', true)->get() as $target) {
            $actual = match ($target->condition_type) { 'device' => $device, 'language' => $language, 'country' => $country, default => null };
            if ($actual && strcasecmp($actual, $target->condition_value) === 0) return $target->destination_url;
        }
        return $link->destination_url;
    }

    private function device(string $agent): string
    {
        if (preg_match('/ipad|tablet|kindle|silk/i', $agent)) return 'tablet';
        if (preg_match('/mobile|iphone|ipod|android/i', $agent)) return 'mobile';
        return 'desktop';
    }
}
