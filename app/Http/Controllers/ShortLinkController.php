<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Models\Campaign;
use App\Models\RetargetingPixel;
use App\Models\Tag;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShortLinkController extends Controller
{
    private const RESERVED = ['login', 'logout', 'dashboard', 'links', 'users', 'roles', 'audit', 'locale', 'up'];

    public function index(Request $request): View
    {
        $links = ShortLink::with('creator')->withCount([
            'visits' => fn ($query) => $query->where('is_bot', false),
        ])->when($request->search, fn ($query, $search) => $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('destination_url', 'like', "%{$search}%");
        }))->latest()->paginate(15)->withQueryString();

        return view('links.index', compact('links'));
    }

    public function create(): View
    {
        return view('links.create', $this->growthData());
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('custom_code')) {
            $request->merge(['custom_code' => strtolower((string) $request->input('custom_code'))]);
        }
        $data = $this->validated($request);

        $link = DB::transaction(function () use ($data, $request): ShortLink {
            $campaignId = $this->resolveCampaignId($data, $request->user()->id);
            $code = $data['code_type'] === 'custom' ? strtolower($data['custom_code']) : $this->randomCode();
            $link = ShortLink::create([
                'title' => $data['title'] ?? null,
                'code' => $code,
                'destination_url' => $this->destinationWithCampaignUtm($data['destination_url'], $campaignId),
                'access_password' => filled($data['access_password'] ?? null) ? Hash::make($data['access_password']) : null,
                'code_type' => $data['code_type'],
                'is_active' => $request->boolean('is_active', true),
                'expires_at' => $data['expires_at'] ?? null,
                'max_visits' => $data['max_visits'] ?? null,
                'campaign_id' => $campaignId,
                'retargeting_enabled' => $request->boolean('retargeting_enabled'),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
            $link->tags()->sync($this->resolveTagIds($data));
            $link->pixels()->sync($data['pixel_ids'] ?? []);

            return $link;
        });

        AuditLogger::log('created', $link, 'Created short link '.$link->code, [], $link->only(['title', 'code', 'destination_url', 'code_type', 'is_active', 'expires_at']));

        return redirect()->route('links.show', $link)->with('success', __('Short link created successfully.'));
    }

    public function show(ShortLink $link): View
    {
        $link->load('creator', 'updater', 'campaign', 'tags', 'pixels')->loadCount(['visits' => fn ($query) => $query->where('is_bot', false)]);

        return view('links.show', compact('link'));
    }

    public function edit(ShortLink $link): View
    {
        return view('links.edit', array_merge(compact('link'), $this->growthData()));
    }

    public function update(Request $request, ShortLink $link): RedirectResponse
    {
        $request->merge(['code' => strtolower((string) $request->input('code'))]);
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'destination_url' => ['required', 'url:http,https', 'max:5000'],
            'code' => ['required', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9_-]{1,49}$/', Rule::unique('short_links', 'code')->ignore($link->id), Rule::notIn(self::RESERVED)],
            'expires_at' => ['nullable', 'date'],
            'max_visits' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'is_active' => ['nullable', 'boolean'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'pixel_ids' => ['nullable', 'array'],
            'pixel_ids.*' => ['integer', 'exists:retargeting_pixels,id'],
            'retargeting_enabled' => ['nullable', 'boolean'],
            'access_password' => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
        ]);

        $old = $link->only(['title', 'code', 'destination_url', 'is_active', 'expires_at']);
        $link->update([
            'title' => $data['title'] ?? null,
            'destination_url' => $this->destinationWithCampaignUtm($data['destination_url'], $data['campaign_id'] ?? null),
            'code' => strtolower($data['code']),
            'expires_at' => $data['expires_at'] ?? null,
            'max_visits' => $data['max_visits'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'updated_by' => $request->user()->id,
            'campaign_id' => $data['campaign_id'] ?? null,
            'retargeting_enabled' => $request->boolean('retargeting_enabled'),
            'access_password' => filled($data['access_password'] ?? null) ? Hash::make($data['access_password']) : $link->access_password,
        ]);
        $link->tags()->sync($data['tag_ids'] ?? []);
        $link->pixels()->sync($data['pixel_ids'] ?? []);

        AuditLogger::log('updated', $link, 'Updated short link '.$link->code, $old, $link->only(array_keys($old)));

        return redirect()->route('links.show', $link)->with('success', __('Short link updated successfully.'));
    }

    public function destroy(ShortLink $link): RedirectResponse
    {
        AuditLogger::log('deleted', $link, 'Deleted short link '.$link->code, $link->only(['title', 'code', 'destination_url']));
        $link->delete();

        return redirect()->route('links.index')->with('success', __('Short link archived successfully.'));
    }

    public function toggle(Request $request, ShortLink $link): RedirectResponse
    {
        $old = ['is_active' => $link->is_active];
        $link->update(['is_active' => ! $link->is_active, 'updated_by' => $request->user()->id]);
        AuditLogger::log('status_changed', $link, 'Changed status for '.$link->code, $old, ['is_active' => $link->is_active]);

        return back()->with('success', __('Link status updated.'));
    }

    private function validated(Request $request): array
    {
        if ($request->filled('new_campaign_tracking_url')) { $query = parse_url((string) $request->input('new_campaign_tracking_url'), PHP_URL_QUERY); parse_str((string) $query, $params); foreach (['utm_source'=>'new_campaign_utm_source','utm_medium'=>'new_campaign_utm_medium','utm_campaign'=>'new_campaign_utm_campaign','utm_content'=>'new_campaign_utm_content','ref'=>'new_campaign_ref','bref'=>'new_campaign_bref','sem'=>'new_campaign_sem'] as $source=>$target) if (! filled($request->input($target)) && isset($params[$source])) $request->merge([$target=>$params[$source]]); }
        return $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'destination_url' => ['required', 'url:http,https', 'max:5000'],
            'code_type' => ['required', Rule::in(['random', 'custom'])],
            'custom_code' => [
                Rule::requiredIf($request->input('code_type') === 'custom'), 'nullable',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9_-]{1,49}$/', 'unique:short_links,code', Rule::notIn(self::RESERVED),
            ],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'max_visits' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'is_active' => ['nullable', 'boolean'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'pixel_ids' => ['nullable', 'array'],
            'pixel_ids.*' => ['integer', 'exists:retargeting_pixels,id'],
            'retargeting_enabled' => ['nullable', 'boolean'],
            'access_password' => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
            'new_campaign_name' => ['nullable', 'string', 'max:100'],
            'new_campaign_description' => ['nullable', 'string', 'max:1000'],
            'new_campaign_utm_source' => ['nullable', 'string', 'max:100'],
            'new_campaign_utm_medium' => ['nullable', 'string', 'max:100'],
            'new_campaign_utm_campaign' => ['nullable', 'string', 'max:100'],
            'new_campaign_utm_content' => ['nullable','string','max:100'], 'new_campaign_ref' => ['nullable','string','max:150'], 'new_campaign_bref' => ['nullable','string','max:150'], 'new_campaign_sem' => ['nullable','string','max:100'],
            'new_campaign_tracking_url' => ['nullable','url:http,https','max:5000'],
            'new_tags' => ['nullable', 'string', 'max:500'],
            'new_tag_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
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

    private function growthData(): array
    {
        return [
            'campaigns' => Campaign::query()->where('is_active', true)->orderBy('name')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
            'pixels' => RetargetingPixel::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function destinationWithCampaignUtm(string $destination, ?int $campaignId): string
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
        if (isset($parts['port'])) $base = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').':'.$parts['port'].($parts['path'] ?? '');

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
