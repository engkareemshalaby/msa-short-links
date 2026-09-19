@extends('layouts.app')

@section('title', __('Exhibition contacts'))
@section('subtitle', __('Business-card contacts are stored separately from student registrations.'))

@section('content')
<div class="toolbar"><div></div><a class="button primary" href="{{ route('crm.event-contacts.create') }}">＋ {{ __('Add contact') }}</a></div>
<div class="card filter-card"><div class="card-body"><form class="filter-row" method="GET">
    <label class="field"><span>{{ __('Search') }}</span><input name="search" value="{{ request('search') }}" placeholder="{{ __('Name or email') }}"></label>
    <label class="field"><span>{{ __('Exhibition') }}</span><select name="event"><option value="">{{ __('All exhibitions') }}</option>@foreach($events as $event)<option value="{{ $event }}" @selected(request('event') === $event)>{{ __($event) }}</option>@endforeach</select></label>
    <label class="field"><span>{{ __('Status') }}</span><select name="status"><option value="">{{ __('All statuses') }}</option>@foreach(\App\Models\EventContact::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __(ucfirst($status)) }}</option>@endforeach</select></label>
    <button class="button primary" type="submit">{{ __('Apply filters') }}</button>@if(request()->hasAny(['search','event','status']))<a class="button" href="{{ route('crm.event-contacts.index') }}">{{ __('Clear') }}</a>@endif
</form></div></div>
<div class="card table-card event-contacts-table"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Emails') }}</th><th>{{ __('Exhibition') }}</th><th>{{ __('Source') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
    <tbody>@forelse($contacts as $contact)<tr>
        <td><strong>{{ $contact->name }}</strong></td>
        <td>@foreach($contact->emails as $email)<a class="contact-email" href="mailto:{{ $email }}" dir="ltr">{{ $email }}</a>@endforeach</td>
        <td>{{ __($contact->event_name) }}</td><td>{{ __(ucwords(str_replace('_', ' ', $contact->source))) }}</td>
        <td><span class="badge {{ $contact->status === 'new' ? 'warning' : ($contact->status === 'qualified' ? 'success' : 'purple') }}">{{ __(ucfirst($contact->status)) }}</span></td>
        <td><div class="row-actions"><a class="button small" href="{{ route('crm.event-contacts.show', $contact) }}">{{ __('View') }}</a><a class="button small" href="{{ route('crm.event-contacts.edit', $contact) }}">{{ __('Edit') }}</a><form method="POST" action="{{ route('crm.event-contacts.destroy', $contact) }}" onsubmit="return confirm(@js(__('Are you sure you want to delete this contact?')))" >@csrf @method('DELETE')<button class="button small danger" type="submit">{{ __('Delete') }}</button></form></div></td>
    </tr>@empty<tr><td colspan="6"><div class="empty-state"><strong>{{ __('No exhibition contacts found') }}</strong></div></td></tr>@endforelse</tbody>
</table></div>{{ $contacts->onEachSide(1)->links('pagination.msa') }}</div>
@endsection

@push('head')<style>.filter-card{margin-bottom:18px}.filter-row .field:first-child{flex:1;min-width:240px}.contact-email{display:block;color:#3f7032;margin:3px 0;white-space:nowrap}.row-actions{display:flex;align-items:center;justify-content:flex-end;gap:6px}.row-actions form{margin:0}</style>@endpush
