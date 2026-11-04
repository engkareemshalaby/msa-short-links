@extends('layouts.app')
@section('title', __('Exhibition registrations'))
@section('subtitle', __('Follow up with students who registered at exhibitions.'))
@section('content')
<section class="exhibition-overview" aria-label="{{ __('exhibition_list.overview') }}">
    <div class="overview-heading"><div><span class="overview-eyebrow">{{ __('exhibition_list.eyebrow') }}</span><h2>{{ __('exhibition_list.overview') }}</h2><p>{{ __('exhibition_list.summary_hint') }}</p></div><a class="button" href="{{ route('crm.exhibition.analytics') }}">{{ __('Registration analytics') }} ↗</a></div>
    <div class="stats-grid exhibition-summary">
        @foreach([
            [__('Total registrations'), $statusCounts->sum(), 'total'],
            [__('New'), $statusCounts['new'] ?? 0, 'new'],
            [__('Contacted'), $statusCounts['contacted'] ?? 0, 'contacted'],
            [__('Qualified'), $statusCounts['qualified'] ?? 0, 'qualified'],
            [__('Applied'), $statusCounts['applied'] ?? 0, 'applied'],
            [__('Closed'), $statusCounts['closed'] ?? 0, 'closed'],
            [__('Last 7 days'), $recentCount, 'recent'],
            [__('Parent / Guardian'), $parentCount, 'parents'],
        ] as [$label, $count, $tone])
        <div class="stat-card summary-card summary-{{ $tone }}"><div class="stat-label">{{ $label }}</div><div class="stat-value">{{ number_format($count) }}</div></div>
        @endforeach
    </div>
</section>

<section class="card exhibition-filter-panel">
    <div class="card-header"><div><h2>{{ __('exhibition_list.filter_title') }}</h2><p>{{ __('exhibition_list.filter_hint') }}</p></div>@if(request()->except('page'))<a class="button small" href="{{ route('crm.exhibition.index') }}">{{ __('Clear') }}</a>@endif</div>
    <div class="card-body"><form class="exhibition-filter-grid" method="GET" action="{{ route('crm.exhibition.index') }}">
        <label class="field search-field"><span>{{ __('Search') }}</span><input name="search" value="{{ request('search') }}" placeholder="{{ __('Name, email, phone or reference') }}"></label>
        <label class="field"><span>{{ __('Exhibition location') }}</span><select name="exhibition_location"><option value="">{{ __('All exhibitions') }}</option>@foreach($exhibitionLocations as $location)<option value="{{ $location }}" @selected(request('exhibition_location') === $location)>{{ __($location) }}</option>@endforeach</select></label>
        <label class="field"><span>{{ __('Registrant') }}</span><select name="registrant_role"><option value="">{{ __('All registrants') }}</option><option value="student" @selected(request('registrant_role')==='student')>{{ __('Student') }}</option><option value="parent" @selected(request('registrant_role')==='parent')>{{ __('Parent / Guardian') }}</option></select></label>
        <label class="field"><span>{{ __('Certificate type') }}</span><select name="certificate_type"><option value="">{{ __('All certificates') }}</option>@foreach($certificateTypes as $certificate)<option value="{{ $certificate }}" @selected(request('certificate_type')===$certificate)>{{ __($certificate) }}</option>@endforeach</select></label>
        <label class="field"><span>{{ __('Interested faculty') }}</span><select name="faculty"><option value="">{{ __('All faculties') }}</option>@foreach(\App\Models\ExhibitionRegistration::FACULTIES as $faculty)<option value="{{ $faculty }}" @selected(request('faculty')===$faculty)>{{ __($faculty) }}</option>@endforeach</select></label>
        <label class="field"><span>{{ __('Connection country') }}</span><select name="connection_country"><option value="">{{ __('All connection countries') }}</option>@foreach($connectionCountries as $country)<option value="{{ $country }}" @selected(request('connection_country') === $country)>{{ $country }}</option>@endforeach</select></label>
        <label class="field"><span>{{ __('Device type') }}</span><select name="device_type"><option value="">{{ __('All devices') }}</option>@foreach(['Mobile', 'Desktop', 'Tablet'] as $device)<option value="{{ $device }}" @selected(request('device_type') === $device)>{{ __($device) }}</option>@endforeach</select></label>
        <label class="field"><span>{{ __('From date') }}</span><input type="date" name="date_from" value="{{ request('date_from') }}"></label>
        <label class="field"><span>{{ __('To date') }}</span><input type="date" name="date_to" value="{{ request('date_to') }}"></label>
        <div class="filter-actions"><button class="button primary" type="submit">{{ __('Apply filters') }}</button></div>
    </form></div>
</section>
<div class="registration-toolbar">
    <div class="toolbar-group"><span class="toolbar-label">{{ __('exhibition_list.actions') }}</span><div class="toolbar-buttons">@can('crm.exhibitions.manage')<a class="button primary" href="{{ route('crm.exhibition.mail.compose', request()->query()) }}">✉ {{ __('Send mail') }}</a>@endcan<a class="button" href="{{ route('crm.exhibition.export', request()->query()) }}">↓ {{ __('Export Excel CSV') }}</a></div></div>
    <div class="toolbar-group"><span class="toolbar-label">{{ __('exhibition_list.forms') }}</span><div class="toolbar-buttons"><a class="button" href="{{ route('crm.general.create') }}" target="_blank" rel="noopener noreferrer">{{ __('General form') }} ↗</a><a class="button" href="{{ route('crm.jordan.create') }}" target="_blank" rel="noopener noreferrer">{{ __('Jordan form') }} ↗</a><a class="button" href="{{ route('crm.iraq.create') }}" target="_blank" rel="noopener noreferrer">{{ __('Iraq form') }} ↗</a><a class="button olympics-button" href="{{ route('crm.schools-olympics.create') }}" target="_blank" rel="noopener noreferrer">{{ __('exhibition_list.olympics_form') }} ↗</a></div></div>
</div>
<div class="card table-card"><div class="table-wrap"><table class="data-table"><thead><tr><th>{{ __('Student') }}</th><th>{{ __('Exhibition location') }}</th><th>{{ __('Registrant') }}</th><th>{{ __('Academic background') }}</th><th>{{ __('Interested faculties') }}</th><th>{{ __('Status') }}</th><th>{{ __('Submitted') }}</th><th>{{ __('Actions') }}</th></tr></thead><tbody>@forelse($registrations as $registration)<tr><td><a class="student-detail-link" href="{{ route('crm.exhibition.show', $registration) }}">{{ $registration->student_name }}</a><small class="link-destination" dir="ltr">{{ $registration->student_mobile }}</small></td><td>{{ __($registration->exhibition_location) }}</td><td>{{ __(ucfirst($registration->registrant_role)) }}</td><td>{{ __($registration->certificate_type === 'Other' ? $registration->certificate_type_other : $registration->certificate_type) }}<small class="link-destination">{{ $registration->current_result }}</small></td><td><div class="mini-tags">@foreach($registration->interested_faculties as $faculty)<span>{{ __($faculty) }}</span>@endforeach</div></td><td><form method="POST" action="{{ route('crm.exhibition.update', $registration) }}">@csrf @method('PATCH')<select class="status-select" name="status" onchange="this.form.submit()">@foreach(\App\Models\ExhibitionRegistration::STATUSES as $status)<option value="{{ $status }}" @selected($registration->status === $status)>{{ __(ucfirst($status)) }}</option>@endforeach</select></form></td><td>{{ $registration->created_at->format('M d, Y') }}<small class="link-destination">{{ $registration->created_at->format('H:i') }}</small></td><td><div class="exhibition-row-actions"><a class="button small" href="{{ route('crm.exhibition.show', $registration) }}">{{ __('View form') }}</a>@can('crm.exhibitions.manage')<form method="POST" action="{{ route('crm.exhibition.destroy', $registration) }}" onsubmit="return confirm(@js(__('Are you sure you want to delete this registration?')))">@csrf @method('DELETE')<button class="button small danger" type="submit">{{ __('Delete form') }}</button></form>@endcan</div></td></tr>@empty<tr><td colspan="8"><div class="empty-state"><strong>{{ __('No registrations found') }}</strong>{{ __('New exhibition registrations will appear here.') }}</div></td></tr>@endforelse</tbody></table></div>{{ $registrations->onEachSide(1)->links('pagination.msa') }}</div>
@endsection
@push('head')
<style>
.exhibition-overview{margin-bottom:22px}
.overview-heading{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:16px}
.overview-eyebrow{display:block;color:#538f3f;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;margin-bottom:5px}
.overview-heading h2{font-size:21px;letter-spacing:-.035em;margin:0 0 4px;color:#102d48}
.overview-heading p{font-size:12px;color:#6c7880;margin:0}
.exhibition-summary{grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin:0}
.summary-card{position:relative;min-height:112px;padding:20px 22px;overflow:hidden;border-color:#dde7e2;box-shadow:0 6px 20px rgba(7,40,65,.035)}
.summary-card:before{content:"";position:absolute;inset-block:0;inset-inline-start:0;width:4px;background:#b2c8b0}
.summary-card .stat-label{font-size:12px;color:#60727b}
.summary-card .stat-value{font-size:32px;margin:12px 0 0;color:#092c48}
.summary-total{background:linear-gradient(135deg,#0d304b,#1b4c66);border-color:#0d304b}
.summary-total:before{background:#8ac478}
.summary-total .stat-label,.summary-total .stat-value{color:#fff}
.summary-new:before{background:#d5a64a}.summary-contacted:before{background:#5c9b73}.summary-qualified:before{background:#4c8c91}.summary-applied:before{background:#4c769e}.summary-closed:before{background:#a7b0bb}.summary-recent:before{background:#6d9bcb}.summary-parents:before{background:#a184bd}
.exhibition-filter-panel{margin-bottom:18px;overflow:hidden}
.exhibition-filter-panel .card-header{background:#fbfdfb}
.exhibition-filter-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px}
.exhibition-filter-grid .search-field{grid-column:span 2}
.exhibition-filter-grid .filter-actions{display:flex;align-items:end}
.exhibition-filter-grid .filter-actions .button{min-height:42px;width:100%}
.exhibition-filter-grid .field input,.exhibition-filter-grid .field select{min-height:42px}
.registration-toolbar{display:flex;justify-content:space-between;align-items:stretch;flex-wrap:wrap;gap:14px;margin:0 0 18px}
.toolbar-group{display:flex;flex-direction:column;gap:9px;min-width:0}
.toolbar-label{font-size:10px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:#71818a}
.toolbar-buttons{display:flex;flex-wrap:wrap;gap:8px}
.toolbar-buttons .button{min-height:40px;white-space:nowrap}
.toolbar-buttons .button:not(.primary):not(.olympics-button){background:#fff;border-color:#dbe6df}
.toolbar-buttons .button:not(.primary):not(.olympics-button):hover{border-color:#92bc89;background:#f4faf1}
.olympics-button{background:#173957;color:#fff;border-color:#173957}
.olympics-button:hover{background:#245879;color:#fff}
.student-detail-link{color:#072841;font-weight:700;text-decoration:none}
.student-detail-link:hover,.student-detail-link:focus{text-decoration:underline;color:#3f7032}
.status-select{min-width:115px;padding:8px}
.exhibition-row-actions{display:flex;flex-direction:column;align-items:stretch;gap:6px}
.exhibition-row-actions .button{width:100%;text-align:center}
.exhibition-row-actions form{margin:0}
.mini-tags{display:flex;gap:5px;flex-wrap:wrap;min-width:180px}
.mini-tags span{background:#edf5ea;color:#3f7032;padding:4px 7px;border-radius:8px;font-size:9px}
@media(max-width:1150px){.exhibition-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.exhibition-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:650px){.overview-heading{align-items:start;flex-direction:column}.exhibition-summary{gap:9px}.summary-card{min-height:100px;padding:15px}.summary-card .stat-value{font-size:27px}.exhibition-filter-grid{grid-template-columns:1fr}.exhibition-filter-grid .search-field{grid-column:auto}.registration-toolbar,.toolbar-group{width:100%}.toolbar-buttons{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.toolbar-buttons .button{white-space:normal;text-align:center}}
</style>
@endpush
