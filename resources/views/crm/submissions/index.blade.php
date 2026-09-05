@extends('layouts.app')

@section('title', __('Partner applications'))
@section('subtitle', __('Review agency applications submitted through the public form.'))

@section('content')
<div class="stats-grid crm-stats">
    <div class="stat-card"><div class="stat-label"><span>{{ __('Total applications') }}</span><span class="stat-icon">◎</span></div><div class="stat-value">{{ number_format($counts->sum()) }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('New') }}</span><span class="stat-icon">＋</span></div><div class="stat-value">{{ number_format($counts['new'] ?? 0) }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('Reviewed') }}</span><span class="stat-icon">✓</span></div><div class="stat-value">{{ number_format($counts['reviewed'] ?? 0) }}</div></div>
    <div class="stat-card"><div class="stat-label"><span>{{ __('Archived') }}</span><span class="stat-icon">□</span></div><div class="stat-value">{{ number_format($counts['archived'] ?? 0) }}</div></div>
</div>

<div class="toolbar">
    <form class="filter-row" method="GET">
        <label class="field"><span>{{ __('Search') }}</span><input class="input" name="search" value="{{ request('search') }}" placeholder="{{ __('Agency, contact, email or phone') }}"></label>
        <label class="field"><span>{{ __('Status') }}</span><select name="status"><option value="">{{ __('All statuses') }}</option>@foreach(\App\Models\CrmSubmission::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __(ucfirst($status)) }}</option>@endforeach</select></label>
        <button class="button" type="submit">{{ __('Filter') }}</button>
        @if(request()->hasAny(['search','status']))<a class="button ghost" href="{{ route('crm.submissions.index') }}">{{ __('Clear') }}</a>@endif
    </form>
    <a class="button primary" href="{{ route('crm.submissions.export') }}">↓ {{ __('Export CSV') }}</a>
</div>

<div class="card table-card"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>{{ __('Agency') }}</th><th>{{ __('Contact') }}</th><th>{{ __('Country') }}</th><th>{{ __('Commercial proposal') }}</th><th>{{ __('Status') }}</th><th>{{ __('Submitted') }}</th><th></th></tr></thead>
    <tbody>@forelse($submissions as $submission)<tr>
        <td><a class="link-title" href="{{ route('crm.submissions.show', $submission) }}">{{ $submission->agency_name }}</a><small class="link-destination">{{ $submission->website ?: __('No website provided') }}</small></td>
        <td><strong>{{ $submission->contact_name }}</strong><small class="link-destination">{{ $submission->email }} · {{ $submission->mobile }}</small></td>
        <td>{{ $submission->country }}@if($submission->city)<small class="link-destination">{{ $submission->city }}</small>@endif</td>
        <td>@if($submission->commission_type === 'fixed_usd')<strong>${{ number_format((float)$submission->commission_value, 2) }}</strong><small class="link-destination">{{ __('per enrolled student') }}</small>@else<strong>{{ number_format((float)$submission->commission_value, 2) }}%</strong><small class="link-destination">{{ __($submission->commission_basis === 'installment' ? 'One tuition installment' : 'One academic year') }}</small>@endif</td>
        <td><span class="badge {{ $submission->status === 'new' ? 'warning' : ($submission->status === 'reviewed' ? 'success' : 'muted') }}">{{ __(ucfirst($submission->status)) }}</span></td>
        <td>{{ $submission->created_at->format('M d, Y') }}<small class="link-destination">{{ $submission->created_at->format('H:i') }}</small></td>
        <td><a class="button small" href="{{ route('crm.submissions.show', $submission) }}">{{ __('View') }}</a></td>
    </tr>@empty<tr><td colspan="7"><div class="empty-state"><strong>{{ __('No applications found') }}</strong>{{ __('New partner applications will appear here.') }}</div></td></tr>@endforelse</tbody>
</table></div>@if($submissions->hasPages())<div class="pagination">{{ $submissions->links() }}</div>@endif</div>
@endsection

@push('head')<style>.crm-stats{grid-template-columns:repeat(4,1fr)}.filter-row{flex:1}.filter-row .field:first-child{min-width:280px}@media(max-width:900px){.crm-stats{grid-template-columns:1fr 1fr}}@media(max-width:600px){.crm-stats{grid-template-columns:1fr}.filter-row .field{width:100%;min-width:0!important}}</style>@endpush
