@extends('layouts.app')

@section('title', __('Partner accounts'))
@section('subtitle', __('Create a simple sign-in for each partner.'))

@section('content')
<div class="card partner-create">
    <div><h2>{{ __('Add a partner') }}</h2><p>{{ __('Create the login details that you will share with the partner.') }}</p></div>
    <form method="POST" action="{{ route('crm.partners.store') }}">@csrf
        <label class="field"><span>{{ __('Partner name') }}</span><input name="name" required maxlength="255" placeholder="{{ __('Example: Global Education') }}"></label>
        <label class="field"><span>{{ __('Email address') }}</span><input type="email" name="email" required></label>
        <label class="field"><span>{{ __('Temporary password') }}</span><input type="password" name="password" required minlength="8"></label>
        <label class="field"><span>{{ __('Confirm password') }}</span><input type="password" name="password_confirmation" required minlength="8"></label>
        <button class="button primary" type="submit">＋ {{ __('Create account') }}</button>
    </form>
</div>

<div class="card table-card"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>{{ __('Partner') }}</th><th>{{ __('Login email') }}</th><th>{{ __('Students') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
    <tbody>@forelse($partners as $partner)
        <tr>
            <td><strong>{{ $partner->name }}</strong><small class="link-destination">{{ $partner->code }}</small></td>
            <td dir="ltr">{{ $partner->user?->email ?? __('No account linked') }}</td>
            <td><a href="{{ route('crm.student-referrals.index', ['partner' => $partner->id]) }}">{{ number_format($partner->student_referrals_count) }}</a></td>
            <td><span class="badge {{ $partner->is_active ? 'success' : 'muted' }}">{{ $partner->is_active ? __('Active') : __('Disabled') }}</span></td>
            <td><div class="row-actions">
                <form method="POST" action="{{ route('crm.partners.toggle', $partner) }}">@csrf @method('PATCH')<button class="button small" type="submit">{{ $partner->is_active ? __('Disable') : __('Enable') }}</button></form>
            </div></td>
        </tr>
    @empty<tr><td colspan="5"><div class="empty-state"><strong>{{ __('No partner accounts yet') }}</strong>{{ __('Create the first partner account above.') }}</div></td></tr>@endforelse</tbody>
</table></div>@if($partners->hasPages())<div class="pagination">{{ $partners->links() }}</div>@endif</div>
@endsection

@push('head')<style>.partner-create{display:flex;align-items:end;justify-content:space-between;gap:25px;margin-bottom:22px}.partner-create h2{margin:0 0 5px}.partner-create p{margin:0;color:#68777f;font-size:12px}.partner-create form{display:grid;grid-template-columns:repeat(4,minmax(130px,1fr)) auto;align-items:end;gap:10px;flex:1}.row-actions{display:flex;gap:6px}@media(max-width:1000px){.partner-create{align-items:stretch;flex-direction:column}.partner-create form{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.partner-create form{grid-template-columns:1fr}}</style>@endpush
