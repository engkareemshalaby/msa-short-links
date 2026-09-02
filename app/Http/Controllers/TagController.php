<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
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

        return view('tags.index', [
            'tags' => $tags,
            'totalLinks' => $tags->sum('links_count'),
            'totalVisits' => $tags->sum('total_visits'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:tags,name'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
        Tag::create($data);

        return back()->with('success', __('Tag created successfully.'));
    }
}
