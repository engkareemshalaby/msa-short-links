@extends('layouts.app')

@section('title', __('Exhibition contact details'))
@section('subtitle', __('Review the original contact and record follow-up progress.'))

@section('content')
<div class="detail-grid"><main><section class="card"><div class="card-header"><div><h2>{{ $contact->name }}</h2><p>{{ __($contact->event_name) }}</p></div></div><div class="card-body meta-list">
    <div class="meta-item"><span>{{ __('Name') }}</span><strong>{{ $contact->name }}</strong></div>
    <div class="meta-item"><span>{{ __('Exhibition') }}</span><strong>{{ __($contact->event_name) }}</strong></div>
    <div class="meta-item"><span>{{ __('Emails') }}</span>@foreach($contact->emails as $email)<a class="contact-email" href="mailto:{{ $email }}" dir="ltr">{{ $email }}</a>@endforeach</div>
    <div class="meta-item"><span>{{ __('Phone numbers') }}</span>@forelse($contact->phones ?? [] as $phone)<a class="contact-email" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" dir="ltr">{{ $phone }}</a>@empty<strong>{{ __('Not provided') }}</strong>@endforelse</div>
    <div class="meta-item"><span>{{ __('Tags') }}</span><div class="contact-tags">@forelse($contact->tags as $tag)<span style="--tag-color:{{ $tag->color }}"><i></i>{{ $tag->name }}</span>@empty<strong>{{ __('Not provided') }}</strong>@endforelse</div></div>
    <div class="meta-item"><span>{{ __('Source') }}</span><strong>{{ __(ucwords(str_replace('_', ' ', $contact->source))) }}</strong></div>
    <div class="meta-item"><span>{{ __('Imported') }}</span><strong>{{ $contact->created_at->format('M d, Y · H:i') }}</strong></div><div class="meta-item"><span>{{ __('Notes') }}</span><strong>{{ $contact->notes ?: __('Not provided') }}</strong></div>
</div></section></main>
<aside><section class="card"><div class="card-header"><div><h2>{{ __('Follow-up') }}</h2><p>{{ __('Update the pipeline stage and keep follow-up notes.') }}</p></div></div><div class="card-body"><form method="POST" action="{{ route('crm.event-contacts.follow-up', $contact) }}">@csrf @method('PATCH')
    <label class="field"><span>{{ __('Stage') }}</span><select name="event_contact_stage_id"><option value="">{{ __('Not assigned') }}</option>@foreach($stages as $stage)<option value="{{ $stage->id }}" @selected($contact->event_contact_stage_id === $stage->id)>{{ $stage->name }}</option>@endforeach</select></label>
    <label class="field notes-field"><span>{{ __('Notes') }}</span><textarea name="notes" rows="7">{{ old('notes', $contact->notes) }}</textarea></label>
    <div class="form-footer"><button class="button primary full">{{ __('Save') }}</button></div>
</form><a class="button full edit-button" href="{{ route('crm.event-contacts.edit', $contact) }}">{{ __('Edit contact') }}</a><form method="POST" action="{{ route('crm.event-contacts.destroy', $contact) }}" onsubmit="return confirm(@js(__('Are you sure you want to delete this contact?')))" >@csrf @method('DELETE')<button class="button danger full delete-button" type="submit">{{ __('Delete contact') }}</button></form><a class="button full back-button" href="{{ route('crm.event-contacts.index') }}">← {{ __('Back to contacts') }}</a></div></section></aside></div>
@endsection

@push('head')<style>.contact-email{display:block;color:#3f7032;margin:4px 0;overflow-wrap:anywhere}.contact-tags{display:flex;flex-wrap:wrap;gap:6px}.contact-tags span{display:flex;align-items:center;gap:6px;background:#f2f5f3;padding:5px 8px;border-radius:10px;font-size:10px}.contact-tags i{width:8px;height:8px;border-radius:50%;background:var(--tag-color)}.notes-field{margin-top:18px}.edit-button{margin-top:14px}.delete-button,.back-button{margin-top:10px}</style>@endpush
