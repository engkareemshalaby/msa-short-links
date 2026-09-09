@extends('layouts.app')

@section('title', __('Partner accounts'))
@section('subtitle', __('Approve partner applications and manage their portal access.'))

@section('content')
<div class="card table-card pending-partners">
    <div class="card-header"><div><h2>{{ __('Applications ready for partner account') }}</h2><p>{{ __('Partners submit full details and password from the public application form.') }}</p></div><a class="button ghost" href="{{ route('crm.new') }}">{{ __('Open application form') }}</a></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>{{ __('Agency') }}</th><th>{{ __('Contact') }}</th><th>{{ __('Country') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
        <tbody>@forelse($pendingSubmissions as $submission)
            <tr>
                <td><strong>{{ $submission->agency_name }}</strong><small class="link-destination">{{ $submission->website ?: __('No website provided') }}</small></td>
                <td><strong>{{ $submission->contact_name }}</strong><small class="link-destination" dir="ltr">{{ $submission->email }} · {{ $submission->mobile }}</small></td>
                <td>{{ $submission->country }}@if($submission->city), {{ $submission->city }}@endif</td>
                <td><span class="badge {{ $submission->status === 'new' ? 'warning' : 'purple' }}">{{ __(ucfirst($submission->status)) }}</span></td>
                <td><div class="row-actions">
                    <a class="button small" href="{{ route('crm.submissions.show', $submission) }}">{{ __('View') }}</a>
                    <form method="POST" action="{{ route('crm.partners.store', $submission) }}">@csrf<button class="button small primary" type="submit">{{ __('Create account') }}</button></form>
                </div></td>
            </tr>
        @empty<tr><td colspan="5"><div class="empty-state"><strong>{{ __('No pending partner applications') }}</strong>{{ __('New full partner applications will appear here.') }}</div></td></tr>@endforelse</tbody>
    </table></div>
</div>

<div class="card table-card"><div class="card-header"><div><h2>{{ __('Active partner accounts') }}</h2><p>{{ __('Edit partner details, password, and account status.') }}</p></div></div><div class="table-wrap"><table class="data-table">
    <thead><tr><th>{{ __('Partner') }}</th><th>{{ __('Login email') }}</th><th>{{ __('Students') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
    <tbody>@forelse($partners as $partner)
        <tr>
            <td><strong>{{ $partner->name }}</strong><small class="link-destination">{{ $partner->crmSubmission?->country ?? $partner->code }}</small></td>
            <td dir="ltr">{{ $partner->user?->email ?? __('No account linked') }}</td>
            <td><a href="{{ route('crm.student-referrals.index', ['partner' => $partner->id]) }}">{{ number_format($partner->student_referrals_count) }}</a></td>
            <td><span class="badge {{ $partner->is_active ? 'success' : 'muted' }}">{{ $partner->is_active ? __('Active') : __('Disabled') }}</span></td>
            <td><div class="row-actions">
                <a class="button small" href="{{ route('crm.partners.edit', $partner) }}">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('crm.partners.toggle', $partner) }}">@csrf @method('PATCH')<button class="button small" type="submit">{{ $partner->is_active ? __('Disable') : __('Enable') }}</button></form>
            </div></td>
        </tr>
    @empty<tr><td colspan="5"><div class="empty-state"><strong>{{ __('No partner accounts yet') }}</strong>{{ __('Approve a partner application above to create the first account.') }}</div></td></tr>@endforelse</tbody>
</table></div>@if($partners->hasPages())<div class="pagination">{{ $partners->links() }}</div>@endif</div>
@endsection

@push('head')<style>.pending-partners{margin-bottom:22px}.row-actions{display:flex;align-items:center;gap:6px;justify-content:flex-end}.row-actions form{margin:0}@media(max-width:700px){.card-header{align-items:stretch;flex-direction:column}.card-header .button{width:100%}.row-actions{justify-content:flex-start;flex-wrap:wrap}}</style>@endpush
