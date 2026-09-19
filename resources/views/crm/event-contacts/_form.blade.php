@php($editing = isset($contact))
<div class="card form-card"><div class="card-body"><form method="POST" action="{{ $editing ? route('crm.event-contacts.update', $contact) : route('crm.event-contacts.store') }}">@csrf @if($editing) @method('PUT') @endif
    <div class="form-grid">
        <label class="field"><span>{{ __('Name') }}</span><input name="name" required maxlength="255" value="{{ old('name', $contact->name ?? '') }}"></label>
        <label class="field"><span>{{ __('Exhibition') }}</span><input name="event_name" required maxlength="255" value="{{ old('event_name', $contact->event_name ?? '') }}" placeholder="Nigeria Exhibition"></label>
        <label class="field full"><span>{{ __('Emails') }}</span><textarea name="emails_text" required rows="4" placeholder="name@example.com&#10;another@example.com">{{ old('emails_text', isset($contact) ? implode("\n", $contact->emails) : '') }}</textarea><small>{{ __('Enter one email per line. The first email will be the primary email.') }}</small></label>
        <label class="field"><span>{{ __('Source') }}</span><input name="source" required maxlength="100" value="{{ old('source', isset($contact) ? ucwords(str_replace('_', ' ', $contact->source)) : 'Business Card') }}"></label>
        <label class="field"><span>{{ __('Status') }}</span><select name="status">@foreach(\App\Models\EventContact::STATUSES as $status)<option value="{{ $status }}" @selected(old('status', $contact->status ?? 'new') === $status)>{{ __(ucfirst($status)) }}</option>@endforeach</select></label>
        <label class="field full"><span>{{ __('Notes') }}</span><textarea name="notes" rows="6">{{ old('notes', $contact->notes ?? '') }}</textarea></label>
    </div>
    <div class="form-footer"><a class="button" href="{{ $editing ? route('crm.event-contacts.show', $contact) : route('crm.event-contacts.index') }}">{{ __('Cancel') }}</a><button class="button primary" type="submit">{{ $editing ? __('Save changes') : __('Add contact') }}</button></div>
</form></div></div>
