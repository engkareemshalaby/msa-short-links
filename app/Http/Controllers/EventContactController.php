<?php

namespace App\Http\Controllers;

use App\Models\EventContact;
use App\Models\EventContactStage;
use App\Models\EventContactTag;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventContactController extends Controller
{
    public function index(Request $request): View
    {
        return view('crm.event-contacts.index', $this->indexData($request));
    }

    public function publicIndex(Request $request): View
    {
        return view('crm.event-contacts.public-index', $this->indexData($request));
    }

    private function indexData(Request $request): array
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'stage_id' => ['nullable', 'integer', 'exists:event_contact_stages,id'],
            'event' => ['nullable', 'string', 'max:255'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', 'exists:event_contact_tags,id'],
        ]);

        $query = EventContact::query()
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query
                ->where('event_contacts.name', 'like', "%{$search}%")
                ->orWhere('event_contacts.organization_name', 'like', "%{$search}%")
                ->orWhere('event_contacts.job_title', 'like', "%{$search}%")
                ->orWhere('event_contacts.primary_email', 'like', "%{$search}%")
                ->orWhere('event_contacts.emails', 'like', "%{$search}%")
                ->orWhere('event_contacts.primary_phone', 'like', "%{$search}%")
                ->orWhere('event_contacts.phones', 'like', "%{$search}%")
                ->orWhere('event_contacts.extra_data', 'like', "%{$search}%")))
            ->when(array_key_exists('stage_id', $filters), fn ($query) => $filters['stage_id']
                ? $query->where('event_contacts.event_contact_stage_id', $filters['stage_id'])
                : $query)
            ->when($filters['event'] ?? null, fn ($query, string $event) => $query->where('event_contacts.event_name', $event))
            ->when($filters['tag_ids'] ?? null, fn ($query, array $tagIds) => $query->whereHas(
                'tags', fn ($query) => $query->whereIn('event_contact_tags.id', $tagIds)
            ));

        $contacts = (clone $query)->with(['tags', 'stage'])->latest('event_contacts.created_at')->paginate(20)->withQueryString();
        $exhibitionChart = (clone $query)->select('event_contacts.event_name', DB::raw('COUNT(*) as total'))
            ->groupBy('event_contacts.event_name')->orderByDesc('total')->limit(10)->get();
        $tagChart = (clone $query)->join('event_contact_tag_assignments', 'event_contacts.id', '=', 'event_contact_tag_assignments.event_contact_id')
            ->join('event_contact_tags', 'event_contact_tags.id', '=', 'event_contact_tag_assignments.event_contact_tag_id')
            ->select('event_contact_tags.name', 'event_contact_tags.color', DB::raw('COUNT(*) as total'))
            ->groupBy('event_contact_tags.id', 'event_contact_tags.name', 'event_contact_tags.color')->orderByDesc('total')->limit(10)->get();

        return [
            'contacts' => $contacts,
            'contactStats' => [
                'total' => (clone $query)->count(),
                'exhibitions' => (clone $query)->distinct()->count('event_contacts.event_name'),
                'tagged' => (clone $query)->whereHas('tags')->count(),
                'tags' => (clone $query)->join('event_contact_tag_assignments', 'event_contacts.id', '=', 'event_contact_tag_assignments.event_contact_id')
                    ->distinct()->count('event_contact_tag_assignments.event_contact_tag_id'),
            ],
            'exhibitionChart' => $exhibitionChart,
            'tagChart' => $tagChart,
            'events' => EventContact::query()->distinct()->orderBy('event_name')->pluck('event_name'),
            'tags' => EventContactTag::query()->withCount('contacts')->orderBy('name')->get(),
            'stages' => EventContactStage::query()->where('is_active', true)->orderBy('position')->orderBy('name')->get(),
        ];
    }

    public function show(EventContact $contact): View
    {
        $contact->load(['tags', 'stage']);

        return view('crm.event-contacts.show', [
            'contact' => $contact,
            'stages' => EventContactStage::query()->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $contact->event_contact_stage_id))->orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('crm.event-contacts.create', [
            'tags' => EventContactTag::query()->orderBy('name')->get(),
            'stages' => EventContactStage::query()->where('is_active', true)->orderBy('position')->orderBy('name')->get(),
            'defaultStageId' => EventContactStage::query()->where('name', 'Awareness')->value('id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $emails, $phones, $tagIds] = $this->validatedContact($request);
        $contact = EventContact::create($data + [
            'import_key' => 'manual:'.Str::uuid(),
            'primary_email' => $emails[0],
            'emails' => $emails,
            'primary_phone' => $phones[0] ?? null,
            'phones' => $phones,
            'event_contact_stage_id' => $data['event_contact_stage_id'] ?? EventContactStage::query()->where('name', 'Awareness')->value('id'),
            'raw_data' => ['name' => $data['name'], 'emails' => $emails, 'phones' => $phones, 'event_name' => $data['event_name'], 'source' => $data['source']],
        ]);
        $contact->tags()->sync($tagIds);
        AuditLogger::log('created', $contact, 'Created event contact', [], $contact->only(['name', 'emails', 'phones', 'event_name', 'source', 'status']), $request);

        return redirect()->route('crm.event-contacts.show', $contact)->with('success', __('Event contact created successfully.'));
    }

    public function edit(EventContact $contact): View
    {
        $contact->load(['tags', 'stage']);

        return view('crm.event-contacts.edit', [
            'contact' => $contact,
            'tags' => EventContactTag::query()->orderBy('name')->get(),
            'stages' => EventContactStage::query()->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $contact->event_contact_stage_id))->orderBy('position')->orderBy('name')->get(),
            'defaultStageId' => null,
        ]);
    }

    public function update(Request $request, EventContact $contact): RedirectResponse
    {
        [$data, $emails, $phones, $tagIds] = $this->validatedContact($request);
        $old = $contact->only(['name', 'organization_name', 'job_title', 'emails', 'phones', 'event_name', 'source', 'status', 'notes', 'extra_data']);
        $contact->update($data + ['primary_email' => $emails[0], 'emails' => $emails, 'primary_phone' => $phones[0] ?? null, 'phones' => $phones]);
        $contact->tags()->sync($tagIds);
        AuditLogger::log('updated', $contact, 'Updated event contact', $old, $contact->only(['name', 'organization_name', 'job_title', 'emails', 'phones', 'event_name', 'source', 'status', 'notes', 'extra_data']), $request);

        return redirect()->route('crm.event-contacts.show', $contact)->with('success', __('Event contact updated successfully.'));
    }

    public function updateFollowUp(Request $request, EventContact $contact): RedirectResponse
    {
        $data = $request->validate([
            'event_contact_stage_id' => ['nullable', 'integer', 'exists:event_contact_stages,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $old = $contact->only(['event_contact_stage_id', 'notes']);
        $contact->update($data);
        AuditLogger::log('updated', $contact, 'Updated event contact follow-up', $old, $contact->only(['event_contact_stage_id', 'notes']), $request);

        return back()->with('success', __('Event contact updated successfully.'));
    }

    public function destroy(Request $request, EventContact $contact): RedirectResponse
    {
        AuditLogger::log('deleted', $contact, 'Deleted event contact', $contact->only(['name', 'emails', 'phones', 'event_name', 'source', 'status']), [], $request);
        $contact->delete();

        return redirect()->route('crm.event-contacts.index')->with('success', __('Event contact deleted successfully.'));
    }

    private function validatedContact(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'emails_text' => ['required', 'string', 'max:3000'],
            'phones_text' => ['nullable', 'string', 'max:1000'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', 'exists:event_contact_tags,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'source' => ['required', 'string', 'max:100'],
            'event_contact_stage_id' => ['nullable', 'integer', 'exists:event_contact_stages,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'extra_data' => ['nullable', 'array', 'max:20'],
            'extra_data.*.key' => ['nullable', 'string', 'max:80', 'required_with:extra_data.*.value'],
            'extra_data.*.value' => ['nullable', 'string', 'max:2000', 'required_with:extra_data.*.key'],
        ]);
        $emails = collect(preg_split('/[\s,;]+/', trim($data['emails_text'])) ?: [])
            ->filter()->map(fn (string $email) => mb_strtolower(trim($email)))->unique()->values()->all();
        Validator::make(['emails' => $emails], [
            'emails' => ['required', 'array', 'min:1', 'max:10'],
            'emails.*' => ['required', 'email', 'max:255', 'distinct'],
        ])->validate();
        $phones = collect(preg_split('/\R+/', trim((string) ($data['phones_text'] ?? ''))) ?: [])
            ->map(fn (string $phone) => trim($phone))->filter()->unique()->values()->all();
        Validator::make(['phones' => $phones], [
            'phones' => ['array', 'max:10'],
            'phones.*' => ['required', 'string', 'max:40', 'regex:/^\+?[0-9][0-9\s().-]{5,38}$/', 'distinct'],
        ])->validate();
        $tagIds = array_map('intval', $data['tag_ids'] ?? []);
        $data['extra_data'] = collect($data['extra_data'] ?? [])
            ->map(fn (array $item): array => ['key' => trim((string) ($item['key'] ?? '')), 'value' => trim((string) ($item['value'] ?? ''))])
            ->filter(fn (array $item): bool => $item['key'] !== '' && $item['value'] !== '')
            ->values()->all() ?: null;
        unset($data['emails_text'], $data['phones_text'], $data['tag_ids']);
        $data['name'] = trim($data['name']);
        $data['organization_name'] = filled($data['organization_name'] ?? null) ? trim($data['organization_name']) : null;
        $data['job_title'] = filled($data['job_title'] ?? null) ? trim($data['job_title']) : null;
        $data['event_name'] = trim($data['event_name']);
        $data['source'] = Str::snake(trim($data['source']));

        return [$data, $emails, $phones, $tagIds];
    }
}
