@extends('layouts.app')

@section('title', __('Student referrals'))
@section('subtitle', __('Review students registered through partner links.'))

@section('content')
<div class="stats-grid referral-stats">
    <div class="stat-card"><div class="stat-label"><span>{{ __('Total students') }}</span><span class="stat-icon">◎</span></div><div class="stat-value">{{ number_format($counts->sum()) }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('New') }}</span><span class="stat-icon">＋</span></div><div class="stat-value">{{ number_format($counts['new'] ?? 0) }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('Contacted') }}</span><span class="stat-icon">✓</span></div><div class="stat-value">{{ number_format($counts['contacted'] ?? 0) }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('Applied on Study in Egypt') }}</span><span class="stat-icon">⌁</span></div><div class="stat-value">{{ number_format($counts['applied_on_study_in_egypt'] ?? 0) }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('Enrolled') }}</span><span class="stat-icon">★</span></div><div class="stat-value">{{ number_format($counts['enrolled'] ?? 0) }}</div></div>
</div>

<div class="toolbar">
    <form class="filter-row" method="GET">
        <label class="field"><span>{{ __('Search') }}</span><input name="search" value="{{ request('search') }}" placeholder="{{ __('Name, phone or reference') }}"></label>
        <label class="field"><span>{{ __('Partner') }}</span><select name="partner"><option value="">{{ __('All partners') }}</option>@foreach($partners as $partner)<option value="{{ $partner->id }}" @selected((string)request('partner') === (string)$partner->id)>{{ $partner->name }}</option>@endforeach</select></label>
        <label class="field"><span>{{ __('Status') }}</span><select name="status"><option value="">{{ __('All statuses') }}</option>@foreach(\App\Models\StudentReferral::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __($status === 'applied_on_study_in_egypt' ? 'Applied on Study in Egypt' : ucfirst($status)) }}</option>@endforeach</select></label>
        <button class="button" type="submit">{{ __('Filter') }}</button>
        @if(request()->hasAny(['search','partner','status']))<a class="button ghost" href="{{ route('crm.student-referrals.index') }}">{{ __('Clear') }}</a>@endif
    </form>
    <div class="top-actions"><a class="button" href="{{ route('crm.partners.index') }}">{{ __('Manage partner accounts') }}</a><a class="button primary" href="{{ route('crm.student-referrals.create') }}">＋ {{ __('Register a student') }}</a></div>
</div>

<div class="card table-card"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>{{ __('Student') }}</th><th>{{ __('Partner') }}</th><th>{{ __('Nationality') }}</th><th>{{ __('Program') }}</th><th>{{ __('Passport') }}</th><th>{{ __('Status') }}</th><th>{{ __('Submitted') }}</th></tr></thead>
    <tbody>@forelse($referrals as $referral)<tr>
        <td><strong>{{ $referral->student_name }}</strong><small class="link-destination" dir="ltr">{{ $referral->mobile }}</small>@if($referral->email)<small class="link-destination">{{ $referral->email }}</small>@endif<small class="link-destination">{{ $referral->reference_code }}</small></td>
        <td>{{ $referral->partner->name }}</td>
        <td>{{ __($referral->nationality) }}</td>
        <td>{{ __($referral->desired_program) }}</td>
        <td>@if($referral->passport_path)<a class="button small" href="{{ route('crm.student-referrals.passport', $referral) }}">↓ {{ __('Download') }}</a>@else<span class="muted-text">{{ __('Not provided') }}</span>@endif</td>
        <td><form method="POST" action="{{ route('crm.student-referrals.update', $referral) }}">@csrf @method('PATCH')<select class="status-select" name="status" onchange="this.form.submit()">@foreach(\App\Models\StudentReferral::STATUSES as $status)<option value="{{ $status }}" @selected($referral->status === $status)>{{ __($status === 'applied_on_study_in_egypt' ? 'Applied on Study in Egypt' : ucfirst($status)) }}</option>@endforeach</select></form>@if($referral->status === 'applied_on_study_in_egypt' && $referral->studyInEgyptUpdatedBy)<small class="link-destination">{{ __('Updated by :name', ['name' => $referral->studyInEgyptUpdatedBy->name]) }}</small><small class="link-destination">{{ $referral->study_in_egypt_updated_at?->format('M d, Y H:i') }}</small>@endif</td>
        <td>{{ $referral->created_at->format('M d, Y') }}<small class="link-destination">{{ $referral->created_at->format('H:i') }}</small></td>
    </tr>@empty<tr><td colspan="7"><div class="empty-state"><strong>{{ __('No student referrals found') }}</strong>{{ __('Students registered by partners will appear here.') }}</div></td></tr>@endforelse</tbody>
</table></div>{{ $referrals->onEachSide(1)->links('pagination.msa') }}</div>
@endsection

@push('head')<style>.referral-stats{grid-template-columns:repeat(5,1fr)}.filter-row{flex:1}.filter-row .field:first-child{min-width:230px}.status-select{min-width:120px;padding:8px}.muted-text{color:#87938c;font-size:12px}@media(max-width:1100px){.referral-stats{grid-template-columns:repeat(3,1fr)}}@media(max-width:900px){.referral-stats{grid-template-columns:1fr 1fr}}@media(max-width:600px){.referral-stats{grid-template-columns:1fr}.filter-row .field{width:100%;min-width:0!important}}</style>@endpush
