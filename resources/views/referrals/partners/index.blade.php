@extends('layouts.app')

@section('title', __('Partner links'))
@section('subtitle', __('Create a private student registration link for each partner.'))

@section('content')
<div class="card partner-create">
    <div><h2>{{ __('Add a partner') }}</h2><p>{{ __('Enter the partner name and the private link will be created automatically.') }}</p></div>
    <form method="POST" action="{{ route('crm.partners.store') }}">@csrf
        <label class="field"><span>{{ __('Partner name') }}</span><input name="name" required maxlength="255" placeholder="{{ __('Example: Global Education') }}"></label>
        <button class="button primary" type="submit">＋ {{ __('Create link') }}</button>
    </form>
</div>

<div class="card table-card"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>{{ __('Partner') }}</th><th>{{ __('Private registration link') }}</th><th>{{ __('Students') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
    <tbody>@forelse($partners as $partner)
        @php($link = route('student-referrals.create', $partner->access_token))
        <tr>
            <td><strong>{{ $partner->name }}</strong><small class="link-destination">{{ $partner->code }}</small></td>
            <td><div class="copy-link"><input readonly value="{{ $link }}" dir="ltr"><button class="button small" type="button" data-copy="{{ $link }}">{{ __('Copy') }}</button></div></td>
            <td><a href="{{ route('crm.student-referrals.index', ['partner' => $partner->id]) }}">{{ number_format($partner->student_referrals_count) }}</a></td>
            <td><span class="badge {{ $partner->is_active ? 'success' : 'muted' }}">{{ $partner->is_active ? __('Active') : __('Disabled') }}</span></td>
            <td><div class="row-actions">
                <form method="POST" action="{{ route('crm.partners.toggle', $partner) }}">@csrf @method('PATCH')<button class="button small" type="submit">{{ $partner->is_active ? __('Disable') : __('Enable') }}</button></form>
                <form method="POST" action="{{ route('crm.partners.regenerate', $partner) }}" onsubmit="return confirm(@js(__('The old link will stop working. Continue?')))" >@csrf<button class="button small ghost" type="submit">{{ __('New link') }}</button></form>
            </div></td>
        </tr>
    @empty<tr><td colspan="5"><div class="empty-state"><strong>{{ __('No partner links yet') }}</strong>{{ __('Create the first private registration link above.') }}</div></td></tr>@endforelse</tbody>
</table></div>@if($partners->hasPages())<div class="pagination">{{ $partners->links() }}</div>@endif</div>
@endsection

@push('head')<style>.partner-create{display:flex;align-items:end;justify-content:space-between;gap:25px;margin-bottom:22px}.partner-create h2{margin:0 0 5px}.partner-create p{margin:0;color:#68777f;font-size:12px}.partner-create form{display:flex;align-items:end;gap:10px;min-width:430px}.partner-create .field{flex:1}.copy-link{display:flex;gap:7px;min-width:360px}.copy-link input{min-width:0;flex:1;font-size:11px}.row-actions{display:flex;gap:6px}@media(max-width:900px){.partner-create{align-items:stretch;flex-direction:column}.partner-create form{min-width:0}.copy-link{min-width:260px}}@media(max-width:600px){.partner-create form{align-items:stretch;flex-direction:column}}</style>@endpush
