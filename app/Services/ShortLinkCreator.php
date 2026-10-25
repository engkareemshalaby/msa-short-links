<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\ShortLink;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ShortLinkCreator
{
    public function create(array $data, int $userId): ShortLink
    {
        $link = DB::transaction(function () use ($data, $userId): ShortLink {
            $campaignId = $this->resolveCampaignId($data, $userId);
            $code = $data['code_type'] === 'custom' ? strtolower($data['custom_code']) : $this->randomCode();
            $link = ShortLink::create([
                'title' => $data['title'] ?? null,
                'code' => $code,
                'destination_url' => $this->destinationWithCampaignUtm($data['destination_url'], $campaignId),
                'access_password' => filled($data['access_password'] ?? null) ? Hash::make($data['access_password']) : null,
                'code_type' => $data['code_type'],
                'is_active' => (bool) ($data['is_active'] ?? true),
                'expires_at' => $data['expires_at'] ?? null,
                'max_visits' => $data['max_visits'] ?? null,
                'campaign_id' => $campaignId,
                'retargeting_enabled' => (bool) ($data['retargeting_enabled'] ?? false),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $link->tags()->sync($this->resolveTagIds($data));
            $link->pixels()->sync($data['pixel_ids'] ?? []);

            return $link;
        });

        AuditLogger::log('created', $link, 'Created short link '.$link->code, [], $link->only(['title', 'code', 'destination_url', 'code_type', 'is_active', 'expires_at']));

        return $link;
    }

    private function resolveCampaignId(array $data, int $userId): ?int
    {
        if (! filled($data['new_campaign_name'] ?? null)) {
            return isset($data['campaign_id']) ? (int) $data['campaign_id'] : null;
        }

        return Campaign::create([
            'name' => trim($data['new_campaign_name']),
            'description' => $data['new_campaign_description'] ?? null,
            'utm_source' => $data['new_campaign_utm_source'] ?? null,
            'utm_medium' => $data['new_campaign_utm_medium'] ?? null,
            'utm_campaign' => $data['new_campaign_utm_campaign'] ?? null,
            'utm_content' => $data['new_campaign_utm_content'] ?? null, 'ref' => $data['new_campaign_ref'] ?? null, 'bref' => $data['new_campaign_bref'] ?? null, 'sem' => $data['new_campaign_sem'] ?? null,
            'is_active' => true,
            'created_by' => $userId,
        ])->id;
    }

    private function resolveTagIds(array $data): array
    {
        $tagIds = array_map('intval', $data['tag_ids'] ?? []);
        $names = preg_split('/[,،\r\n]+/u', (string) ($data['new_tags'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);

        foreach (array_unique(array_map('trim', $names ?: [])) as $name) {
            if ($name === '') {
                continue;
            }

            $tagIds[] = Tag::firstOrCreate(
                ['name' => mb_substr($name, 0, 60)],
                ['color' => $data['new_tag_color'] ?? '#072841'],
            )->id;
        }

        return array_values(array_unique($tagIds));
    }

    public function destinationWithCampaignUtm(string $destination, ?int $campaignId): string
    {
        if (! $campaignId || ! $campaign = Campaign::find($campaignId)) {
            return $destination;
        }

        $parts = parse_url($destination);
        parse_str($parts['query'] ?? '', $query);
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'ref', 'bref', 'sem'] as $field) {
            if ($campaign->{$field} && ! isset($query[$field])) {
                $query[$field] = $campaign->{$field};
            }
        }
        $base = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').($parts['path'] ?? '');
        if (isset($parts['port'])) {
            $base = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').':'.$parts['port'].($parts['path'] ?? '');
        }

        return $query ? $base.'?'.http_build_query($query).(isset($parts['fragment']) ? '#'.$parts['fragment'] : '') : $destination;
    }

    private function randomCode(): string
    {
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $code = (string) random_int(100000, 999999);
            if (! ShortLink::withTrashed()->where('code', $code)->exists()) {
                return $code;
            }
        }
        abort(503, 'Unable to generate a unique code.');
    }
}
