<?php

namespace App\Services;

use App\Models\ShortLink;
use App\Models\Visit;
use Illuminate\Http\Request;

class ExhibitionAttribution
{
    private const CLICK_KEY = 'exhibition_source_click';

    private const FORM_PATHS = [
        '/crm/jordan-exhibition',
        '/crm/iraq-exhibition',
        '/crm/iraq-exhibition/ar',
        '/crm/exhibition-registration',
        '/crm/exhibition-registration/ar',
    ];

    public function rememberClick(Request $request, ShortLink $link, Visit $visit, string $destination): void
    {
        if (strcasecmp((string) parse_url($destination, PHP_URL_HOST), $request->getHost()) !== 0
            || ! in_array(parse_url($destination, PHP_URL_PATH), self::FORM_PATHS, true)) {
            return;
        }

        $request->session()->put(self::CLICK_KEY, [
            'destination' => $destination,
            'source_short_link_id' => $link->id,
            'source_visit_id' => $visit->id,
            'source_short_code' => $link->code,
            'source_referrer' => $visit->referer,
        ]);
    }

    public function rememberLanding(Request $request): void
    {
        $key = $this->formKey($request);
        $landingUrl = $request->fullUrl();
        $click = $request->session()->get(self::CLICK_KEY);
        $matchedClick = is_array($click) && $this->sameDestination($click['destination'] ?? '', $landingUrl);
        $previous = $request->session()->get($key);

        if ($matchedClick) {
            $request->session()->forget(self::CLICK_KEY);
        } elseif (is_array($previous) && ($previous['source_landing_url'] ?? null) === $landingUrl) {
            return;
        }

        $data = [
            'source_landing_url' => mb_substr($landingUrl, 0, 5000),
            'source_referrer' => mb_substr((string) ($matchedClick ? $click['source_referrer'] : $request->headers->get('referer')), 0, 2000) ?: null,
        ];

        foreach (['ref' => 150, 'bref' => 150, 'sem' => 100, 'utm_source' => 255, 'utm_medium' => 255, 'utm_campaign' => 255, 'utm_content' => 255] as $parameter => $limit) {
            $value = $request->query($parameter);
            $data['source_'.$parameter] = is_string($value) && $value !== '' ? mb_substr($value, 0, $limit) : null;
        }

        if ($matchedClick) {
            $data += [
                'source_short_link_id' => $click['source_short_link_id'],
                'source_visit_id' => $click['source_visit_id'],
                'source_short_code' => $click['source_short_code'],
            ];
        }

        $request->session()->put($key, $data);
    }

    public function forRegistration(Request $request): array
    {
        return array_replace($request->session()->get($this->formKey($request), []), [
            'source_form_locale' => app()->getLocale(),
        ]);
    }

    public function forgetRegistration(Request $request): void
    {
        $request->session()->forget($this->formKey($request));
    }

    private function formKey(Request $request): string
    {
        $path = match (true) {
            $request->is('crm/exhibition-registration/ar') => 'crm/exhibition-registration',
            $request->is('crm/iraq-exhibition/ar') => 'crm/iraq-exhibition',
            default => trim($request->path(), '/'),
        };

        return 'exhibition_source_'.str_replace('/', '_', $path);
    }

    private function sameDestination(string $destination, string $landingUrl): bool
    {
        if (strcasecmp((string) parse_url($destination, PHP_URL_HOST), (string) parse_url($landingUrl, PHP_URL_HOST)) !== 0
            || parse_url($destination, PHP_URL_PATH) !== parse_url($landingUrl, PHP_URL_PATH)) {
            return false;
        }

        parse_str((string) parse_url($destination, PHP_URL_QUERY), $destinationQuery);
        parse_str((string) parse_url($landingUrl, PHP_URL_QUERY), $landingQuery);

        return $destinationQuery == $landingQuery;
    }
}
