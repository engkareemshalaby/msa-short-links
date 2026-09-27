@extends('layouts.app')

@section('title', __('Contact tags'))
@section('subtitle', __('Manage labels used only for exhibition contacts.'))

@section('content')
<div class="management-layout">
    <section class="card table-card">
        <div class="card-header"><div><h2>{{ __('Contact tags') }}</h2><p>{{ __('Edit names and colors or remove unused labels.') }}</p></div></div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>{{ __('Tag') }}</th><th>{{ __('Contacts') }}</th><th>{{ __('Edit') }}</th><th>{{ __('Actions') }}</th></tr></thead>
            <tbody>@forelse($tags as $tag)<tr>
                <td><span class="tag-preview" style="--tag-color:{{ $tag->color }}"><i></i>{{ $tag->name }}</span></td>
                <td>{{ number_format($tag->contacts_count) }}</td>
                <td>@can('crm.event_contacts.manage')<form class="inline-edit" method="POST" action="{{ route('crm.event-contact-tags.update', $tag) }}">@csrf @method('PUT')<input name="name" value="{{ $tag->name }}" required maxlength="60"><input type="color" name="color" value="{{ $tag->color }}" required><button class="button small">{{ __('Save') }}</button></form>@else<span class="muted-text">—</span>@endcan</td>
                <td>@can('crm.event_contacts.manage')<form method="POST" action="{{ route('crm.event-contact-tags.destroy', $tag) }}" onsubmit="return confirm(@js(__('Deleting this tag will remove it from all contacts. Continue?')))">@csrf @method('DELETE')<button class="button small danger">{{ __('Delete') }}</button></form>@endcan</td>
            </tr>@empty<tr><td colspan="4"><div class="empty-state"><strong>{{ __('No contact tags yet') }}</strong></div></td></tr>@endforelse</tbody>
        </table></div>
    </section>

    @can('crm.event_contacts.manage')
    <aside class="card sticky-panel"><div class="card-header"><div><h2>{{ __('Create tag') }}</h2><p>{{ __('This tag will only be available for contacts.') }}</p></div></div><div class="card-body">
        <form method="POST" action="{{ route('crm.event-contact-tags.store') }}" class="form-grid">@csrf
            <label class="field full"><span>{{ __('Tag name') }}</span><input name="name" value="{{ old('name') }}" required maxlength="60" placeholder="VIP"></label>
            <label class="field full"><span>{{ __('Color') }}</span><div class="color-control"><input type="color" name="color" value="{{ old('color', '#538F3F') }}" required></div></label>
            <div class="form-footer"><button class="button primary">＋ {{ __('Create tag') }}</button></div>
        </form>
    </div></aside>
    @endcan
</div>
@endsection

@push('head')<style>.management-layout{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:20px;align-items:start}.sticky-panel{position:sticky;top:112px}.tag-preview{display:inline-flex;align-items:center;gap:8px;font-weight:700}.tag-preview i{width:11px;height:11px;border-radius:50%;background:var(--tag-color)}.inline-edit{display:flex;align-items:center;gap:7px}.inline-edit input[name=name]{min-width:150px;padding:8px 10px}.inline-edit input[type=color],.color-control input[type=color]{width:46px;height:36px;padding:2px;cursor:pointer}.form-footer{grid-column:1/-1}@media(max-width:900px){.management-layout{grid-template-columns:1fr}.sticky-panel{position:static}}</style>@endpush
