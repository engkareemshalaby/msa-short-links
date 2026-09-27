<?php

namespace App\Http\Controllers;

use App\Models\EventContactStage;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventContactStageController extends Controller
{
    public function index(): View
    {
        return view('crm.event-contact-stages.index', [
            'stages' => EventContactStage::query()->withCount('contacts')->orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $stage = EventContactStage::create($this->validated($request));
        AuditLogger::log('created', $stage, 'Created event contact stage', [], $stage->only(['name', 'color', 'position', 'is_active']), $request);

        return back()->with('success', __('Stage created successfully.'));
    }

    public function update(Request $request, EventContactStage $stage): RedirectResponse
    {
        $old = $stage->only(['name', 'color', 'position', 'is_active']);
        $stage->update($this->validated($request, $stage));
        AuditLogger::log('updated', $stage, 'Updated event contact stage', $old, $stage->only(['name', 'color', 'position', 'is_active']), $request);

        return back()->with('success', __('Stage updated successfully.'));
    }

    public function destroy(Request $request, EventContactStage $stage): RedirectResponse
    {
        if ($stage->contacts()->exists()) {
            return back()->withErrors(['stage' => __('This stage is assigned to contacts and cannot be deleted. Disable it instead.')]);
        }

        AuditLogger::log('deleted', $stage, 'Deleted event contact stage', $stage->only(['name', 'color', 'position', 'is_active']), [], $request);
        $stage->delete();

        return back()->with('success', __('Stage deleted successfully.'));
    }

    private function validated(Request $request, ?EventContactStage $stage = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('event_contact_stages', 'name')->ignore($stage)],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'position' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
