<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\RetargetingPixel;
use App\Models\ShortLink;
use App\Models\Tag;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ShortLinkCreator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShortLinkController extends Controller
{
    public const RESERVED = ['login', 'logout', 'dashboard', 'links', 'users', 'roles', 'audit', 'locale', 'up'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'tag_id' => ['nullable', 'integer', 'exists:tags,id'],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'expired'])],
            'archive' => ['nullable', Rule::in(['current', 'archived', 'all'])],
            'created_from' => ['nullable', 'date_format:Y-m-d'],
            'created_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:created_from'],
        ]);

        $archive = $filters['archive'] ?? 'current';
        $links = ShortLink::query()
            ->when($archive === 'archived', fn ($query) => $query->onlyTrashed())
            ->when($archive === 'all', fn ($query) => $query->withTrashed())
            ->with('creator')->withCount([
                'visits' => fn ($query) => $query->where('is_bot', false),
            ])->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('destination_url', 'like', "%{$search}%");
            }))
            ->when($filters['tag_id'] ?? null, fn ($query, $tagId) => $query->whereHas('tags', fn ($query) => $query->whereKey($tagId)))
            ->when($filters['campaign_id'] ?? null, fn ($query, $campaignId) => $query->where('campaign_id', $campaignId))
            ->when($filters['created_by'] ?? null, fn ($query, $userId) => $query->where('created_by', $userId))
            ->when($filters['created_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['created_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true)->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now())))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when(($filters['status'] ?? null) === 'expired', fn ($query) => $query->where('is_active', true)->where('expires_at', '<=', now()))
            ->latest()->paginate(15)->withQueryString();

        $tags = Tag::query()->orderBy('name')->get(['id', 'name']);
        $campaigns = Campaign::query()->orderBy('name')->get(['id', 'name']);
        $creators = User::query()->whereHas('shortLinks', fn ($query) => $query->withTrashed())->orderBy('name')->get(['id', 'name']);

        return view('links.index', compact('links', 'tags', 'campaigns', 'creators'));
    }

    public function create(Request $request): View
    {
        $defaults = $request->session()->get('link_defaults', []);
        $source = null;
        if ($request->filled('duplicate')) {
            abort_unless($request->user()->can('links.view'), 403);
            $source = ShortLink::with('tags')->findOrFail($request->integer('duplicate'));
            $defaults = $source->only(['destination_url', 'campaign_id']);
            $defaults['title'] = mb_substr($source->display_title, 0, 250).' copy';
            $defaults['code_type'] = 'custom';
            $defaults['custom_code'] = $this->duplicateCode($source->code);
            $defaults['tag_ids'] = $source->tags->modelKeys();
        }

        return view('links.create', array_merge($this->growthData($defaults['campaign_id'] ?? null), compact('defaults', 'source')));
    }

    private function duplicateCode(string $code): string
    {
        $candidate = substr($code, 0, 45).'-copy';
        if (! ShortLink::withTrashed()->where('code', $candidate)->exists()) {
            return $candidate;
        }

        $numbers = range(10, 99);
        shuffle($numbers);
        foreach ($numbers as $number) {
            $candidate = substr($code, 0, 43).'-copy'.$number;
            if (! ShortLink::withTrashed()->where('code', $candidate)->exists()) {
                return $candidate;
            }
        }

        abort(503, 'Unable to generate a unique duplicate slug.');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('custom_code')) {
            $request->merge(['custom_code' => strtolower((string) $request->input('custom_code'))]);
        }
        $data = $this->validated($request);

        $link = app(ShortLinkCreator::class)->create($data, $request->user()->id);

        if ($request->input('after_save') === 'another') {
            return redirect()->route('links.create')->with('success', __('Short link created successfully.').' '.$link->short_url)
                ->with('link_defaults', [
                    'campaign_id' => $link->campaign_id,
                    'tag_ids' => $link->tags->modelKeys(),
                    'code_type' => $link->code_type,
                ]);
        }

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
            'destination_url' => app(ShortLinkCreator::class)->destinationWithCampaignUtm($data['destination_url'], $data['campaign_id'] ?? null),
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

    public function forceDestroy(ShortLink $link): RedirectResponse
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($link) {
            AuditLogger::log('permanently_deleted', $link, 'Permanently deleted short link '.$link->code, $link->only(['title', 'code', 'destination_url']));
            $link->forceDelete();
        });

        return redirect()->route('links.index')->with('success', __('Short link permanently deleted.'));
    }

    public function restore(int $link): RedirectResponse
    {
        $link = ShortLink::onlyTrashed()->findOrFail($link);
        $link->restore();
        AuditLogger::log('restored', $link, 'Restored short link '.$link->code, [], $link->only(['title', 'code', 'destination_url']));

        return redirect()->route('links.index', ['archive' => 'archived'])->with('success', __('Short link restored successfully.'));
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
        if ($request->filled('new_campaign_tracking_url')) {
            $query = parse_url((string) $request->input('new_campaign_tracking_url'), PHP_URL_QUERY);
            parse_str((string) $query, $params);
            foreach (['utm_source' => 'new_campaign_utm_source', 'utm_medium' => 'new_campaign_utm_medium', 'utm_campaign' => 'new_campaign_utm_campaign', 'utm_content' => 'new_campaign_utm_content', 'ref' => 'new_campaign_ref', 'bref' => 'new_campaign_bref', 'sem' => 'new_campaign_sem'] as $source => $target) {
                if (! filled($request->input($target)) && isset($params[$source])) {
                    $request->merge([$target => $params[$source]]);
                }
            }
        }

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
            'new_campaign_utm_content' => ['nullable', 'string', 'max:100'], 'new_campaign_ref' => ['nullable', 'string', 'max:150'], 'new_campaign_bref' => ['nullable', 'string', 'max:150'], 'new_campaign_sem' => ['nullable', 'string', 'max:100'],
            'new_campaign_tracking_url' => ['nullable', 'url:http,https', 'max:5000'],
            'new_tags' => ['nullable', 'string', 'max:500'],
            'new_tag_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
    }

    private function growthData(?int $selectedCampaignId = null): array
    {
        return [
            'campaigns' => Campaign::query()->where(fn ($query) => $query->where('is_active', true)
                ->when($selectedCampaignId, fn ($query) => $query->orWhere('id', $selectedCampaignId)))->orderBy('name')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
            'pixels' => RetargetingPixel::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
