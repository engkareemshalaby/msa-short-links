<?php

namespace App\Http\Controllers;

use App\Models\EventContactTag;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventContactTagController extends Controller
{
    public function index(): View
    {
        return view('crm.event-contact-tags.index', [
            'tags' => EventContactTag::query()->withCount('contacts')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tag = EventContactTag::create($this->validated($request));
        AuditLogger::log('created', $tag, 'Created event contact tag', [], $tag->only(['name', 'color']), $request);

        return back()->with('success', __('Tag created successfully.'));
    }

    public function update(Request $request, EventContactTag $tag): RedirectResponse
    {
        $old = $tag->only(['name', 'color']);
        $tag->update($this->validated($request, $tag));
        AuditLogger::log('updated', $tag, 'Updated event contact tag', $old, $tag->only(['name', 'color']), $request);

        return back()->with('success', __('Tag updated successfully.'));
    }

    public function destroy(Request $request, EventContactTag $tag): RedirectResponse
    {
        AuditLogger::log('deleted', $tag, 'Deleted event contact tag', $tag->only(['name', 'color']), [], $request);
        $tag->delete();

        return back()->with('success', __('Tag deleted successfully.'));
    }

    private function validated(Request $request, ?EventContactTag $tag = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('event_contact_tags', 'name')->ignore($tag)],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
    }
}
