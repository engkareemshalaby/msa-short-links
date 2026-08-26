<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Tag;
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
            'tags' => $this->tagsWithPerformance(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'utm_source' => ['nullable', 'string', 'max:100'],
            'utm_medium' => ['nullable', 'string', 'max:100'],
            'utm_campaign' => ['nullable', 'string', 'max:100'],
        ]);
        $campaign = Campaign::create($data + ['created_by' => $request->user()->id]);
        AuditLogger::log('created', $campaign, 'Created campaign '.$campaign->name);

        return back()->with('success', __('Campaign created successfully.'));
    }

    public function tag(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:tags,name'],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
        Tag::create($data);

        return back()->with('success', __('Tag created successfully.'));
    }

    private function tagsWithPerformance()
    {
        $tags = Tag::query()
            ->with([
                'links' => fn ($query) => $query->withCount([
                    'visits' => fn ($visits) => $visits->where('is_bot', false),
                ])->latest(),
            ])
            ->withCount('links')
            ->orderBy('name')
            ->get();

        $tags->each(function (Tag $tag): void {
            $tag->total_visits = $tag->links->sum('visits_count');
        });

        return $tags;
    }
}
