@extends('layouts.app')

@section('title', __('Contact stages'))
@section('subtitle', __('Manage the contact pipeline without changing the legacy status data.'))

@section('content')
<div class="management-layout">
    <section class="card table-card"><div class="card-header"><div><h2>{{ __('Pipeline stages') }}</h2><p>{{ __('The legacy status remains stored but is no longer shown to users.') }}</p></div></div><div class="table-wrap"><table class="data-table">
        <thead><tr><th>{{ __('Stage') }}</th><th>{{ __('Contacts') }}</th><th>{{ __('Edit') }}</th><th>{{ __('Actions') }}</th></tr></thead>
        <tbody>@forelse($stages as $stage)<tr>
            <td><span class="stage-preview {{ $stage->is_active ? '' : 'inactive' }}" style="--stage-color:{{ $stage->color }}"><i></i>{{ $stage->name }}@unless($stage->is_active)<small>{{ __('Inactive') }}</small>@endunless</span></td>
            <td>{{ number_format($stage->contacts_count) }}</td>
            <td>@can('crm.event_contacts.manage')<form class="inline-edit" method="POST" action="{{ route('crm.event-contact-stages.update', $stage) }}">@csrf @method('PUT')<input name="name" value="{{ $stage->name }}" required maxlength="60"><input type="color" name="color" value="{{ $stage->color }}" required><input class="position" type="number" name="position" value="{{ $stage->position }}" min="0" max="999" required title="{{ __('Order') }}"><label class="active-check"><input type="checkbox" name="is_active" value="1" @checked($stage->is_active)>{{ __('Active') }}</label><button class="button small">{{ __('Save') }}</button></form>@endcan</td>
            <td>@can('crm.event_contacts.manage')<form method="POST" action="{{ route('crm.event-contact-stages.destroy', $stage) }}" onsubmit="return confirm(@js(__('Delete this unused stage?')))">@csrf @method('DELETE')<button class="button small danger" @disabled($stage->contacts_count)>{{ __('Delete') }}</button></form>@endcan</td>
        </tr>@empty<tr><td colspan="4"><div class="empty-state"><strong>{{ __('No stages yet') }}</strong></div></td></tr>@endforelse</tbody>
    </table></div></section>

    @can('crm.event_contacts.manage')
    <aside class="card sticky-panel"><div class="card-header"><div><h2>{{ __('Create stage') }}</h2><p>{{ __('Add another step to the contact pipeline.') }}</p></div></div><div class="card-body"><form method="POST" action="{{ route('crm.event-contact-stages.store') }}" class="form-grid">@csrf
        <label class="field full"><span>{{ __('Stage name') }}</span><input name="name" value="{{ old('name') }}" required maxlength="60"></label>
        <label class="field"><span>{{ __('Color') }}</span><input type="color" name="color" value="{{ old('color', '#538F3F') }}" required></label>
        <label class="field"><span>{{ __('Order') }}</span><input type="number" name="position" value="{{ old('position', ($stages->max('position') ?? 0) + 1) }}" min="0" max="999" required></label>
        <label class="checkbox full"><input type="checkbox" name="is_active" value="1" checked><span>{{ __('Active') }}</span></label>
        <div class="form-footer"><button class="button primary">＋ {{ __('Create stage') }}</button></div>
    </form></div></aside>
    @endcan
</div>
@endsection

@push('head')<style>.management-layout{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:20px;align-items:start}.sticky-panel{position:sticky;top:112px}.stage-preview{display:inline-flex;align-items:center;gap:7px;font-weight:700}.stage-preview i{width:11px;height:11px;border-radius:50%;background:var(--stage-color)}.stage-preview.inactive{opacity:.5}.stage-preview small{font-size:8px;color:var(--muted)}.inline-edit{display:flex;align-items:center;gap:6px}.inline-edit input[name=name]{min-width:125px;padding:8px}.inline-edit input[type=color]{width:40px;height:35px;padding:2px}.inline-edit .position{width:62px;padding:8px}.active-check{display:flex;align-items:center;gap:4px;font-size:10px}.active-check input{width:auto}.form-footer{grid-column:1/-1}@media(max-width:1000px){.management-layout{grid-template-columns:1fr}.sticky-panel{position:static}.inline-edit{flex-wrap:wrap}}</style>@endpush
