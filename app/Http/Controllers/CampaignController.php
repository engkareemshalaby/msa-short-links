<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = Campaign::query()
            ->with([
                'links' => fn ($query) => $query->withCount([
                    'visits' => fn ($visits) => $visits->where('is_bot', false),
                ])->latest(),
            ])
            ->withCount('links')
            ->latest()
            ->get();

        $campaigns->each(function (Campaign $campaign): void {
            $campaign->total_visits = $campaign->links->sum('visits_count');
        });

        return view('campaigns.index', [
            'campaigns' => $campaigns,
            'totalLinks' => $campaigns->sum('links_count'),
            'totalVisits' => $campaigns->sum('total_visits'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('tracking_url')) { $query = parse_url((string) $request->input('tracking_url'), PHP_URL_QUERY); parse_str((string) $query, $params); foreach (['utm_source','utm_medium','utm_campaign','utm_content','ref','bref','sem'] as $field) if (! filled($request->input($field)) && isset($params[$field])) $request->merge([$field => $params[$field]]); }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'utm_source' => ['nullable', 'string', 'max:100'],
            'utm_medium' => ['nullable', 'string', 'max:100'],
            'utm_campaign' => ['nullable', 'string', 'max:100'],
            'utm_content' => ['nullable', 'string', 'max:100'], 'ref' => ['nullable','string','max:150'], 'bref' => ['nullable','string','max:150'], 'sem' => ['nullable','string','max:100'],
            'tracking_url' => ['nullable','url:http,https','max:5000'],
        ]);
        $campaign = Campaign::create($data + ['created_by' => $request->user()->id]);
        AuditLogger::log('created', $campaign, 'Created campaign '.$campaign->name);

        return back()->with('success', __('Campaign created successfully.'));
    }

}
