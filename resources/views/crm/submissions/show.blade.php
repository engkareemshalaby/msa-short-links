@extends('layouts.app')

@section('title', $submission->agency_name)
@section('subtitle', __('Submitted :date', ['date' => $submission->created_at->format('M d, Y · H:i')]))

@section('content')
<div class="detail-grid crm-detail">
    <div>
        <section class="card detail-section"><div class="card-header"><div><h2>{{ __('Agency details') }}</h2><p>{{ __('Organization and primary contact information.') }}</p></div><span class="badge {{ $submission->status === 'reviewed' ? 'success' : ($submission->status === 'archived' ? 'muted' : 'warning') }}">{{ __(ucfirst($submission->status)) }}</span></div><div class="card-body"><div class="meta-list">
            <div class="meta-item"><span>{{ __('Agency name') }}</span><strong>{{ $submission->agency_name }}</strong></div>
            <div class="meta-item"><span>{{ __('Location') }}</span><strong>{{ collect([$submission->city,$submission->country])->filter()->join(', ') }}</strong></div>
            <div class="meta-item"><span>{{ __('Website') }}</span><strong>@if($submission->website)<a class="text-link" href="{{ $submission->website }}" target="_blank" rel="noopener noreferrer">{{ $submission->website }} ↗</a>@else—@endif</strong></div>
            <div class="meta-item"><span>{{ __('Source') }}</span><strong>{{ $submission->source ?: '—' }}</strong></div>
            <div class="meta-item"><span>{{ __('Contact person') }}</span><strong>{{ $submission->contact_name }}{{ $submission->job_title ? ' · '.$submission->job_title : '' }}</strong></div>
            <div class="meta-item"><span>{{ __('Email address') }}</span><strong><a class="text-link" href="mailto:{{ $submission->email }}">{{ $submission->email }}</a></strong></div>
            <div class="meta-item"><span>{{ __('Mobile / WhatsApp') }}</span><strong><a class="text-link" href="tel:{{ $submission->mobile }}">{{ $submission->mobile }}</a></strong></div>
        </div></div></section>

        <section class="card detail-section"><div class="card-header"><div><h2>{{ __('Recruitment profile') }}</h2><p>{{ __('Current reach and expected MSA recruitment.') }}</p></div></div><div class="card-body"><div class="meta-list">
            <div class="meta-item"><span>{{ __('Recruitment countries') }}</span><strong>{{ implode(', ', $submission->recruitment_countries) }}</strong></div>
            <div class="meta-item"><span>{{ __('Students recruited annually') }}</span><strong>{{ $submission->annual_students_range }}</strong></div>
            <div class="meta-item"><span>{{ __('Works with Egyptian universities') }}</span><strong>{{ $submission->works_with_egyptian_universities ? __('Yes') : __('No') }}</strong></div>
            <div class="meta-item"><span>{{ __('Expected MSA students') }}</span><strong>{{ $submission->expected_msa_students_range }} / {{ __('first 12 months') }}</strong></div>
            @if($submission->current_universities)<div class="meta-item full"><span>{{ __('Current universities') }}</span><strong class="preline">{{ $submission->current_universities }}</strong></div>@endif
            <div class="meta-item full"><span>{{ __('Interested faculties / programs') }}</span><div class="tag-row">@foreach($submission->interested_programs as $program)<span class="badge purple">{{ __($program) }}</span>@endforeach</div></div>
            @if($submission->notes)<div class="meta-item full"><span>{{ __('Additional notes') }}</span><strong class="preline">{{ $submission->notes }}</strong></div>@endif
        </div></div></section>

        <section class="card detail-section commercial"><div class="card-header"><div><h2>{{ __('Commercial proposal') }}</h2><p>{{ __('Requested commission and exclusive student discount.') }}</p></div></div><div class="card-body commercial-grid">
            <div><span>{{ __('Requested commission') }}</span><strong>@if($submission->commission_type === 'fixed_usd')${{ number_format((float)$submission->commission_value, 2) }}<small>{{ __('per enrolled student') }}</small>@else{{ number_format((float)$submission->commission_value, 2) }}%<small>{{ __($submission->commission_basis === 'installment' ? 'of one tuition installment' : 'of one academic year') }}</small>@endif</strong></div>
            <div><span>{{ __('Minimum exclusive student discount') }}</span><strong>{{ number_format((float)$submission->exclusive_discount_percent, 2) }}%<small>{{ __('for students referred by this agency') }}</small></strong></div>
        </div></section>
    </div>

    <aside><section class="card sticky-panel"><div class="card-header"><div><h2>{{ __('Application status') }}</h2><p>{{ __('Opening a new application marks it as reviewed.') }}</p></div></div><div class="card-body"><form method="POST" action="{{ route('crm.submissions.update', $submission) }}">@csrf @method('PATCH')<label class="field"><span>{{ __('Status') }}</span><select name="status">@foreach(\App\Models\CrmSubmission::STATUSES as $status)<option value="{{ $status }}" @selected($submission->status === $status)>{{ __(ucfirst($status)) }}</option>@endforeach</select></label><div class="form-footer"><button class="button primary full">{{ __('Save status') }}</button></div></form><div class="review-meta"><span>{{ __('Received') }}</span><strong>{{ $submission->created_at->format('M d, Y · H:i') }}</strong>@if($submission->reviewed_at)<span>{{ __('First reviewed') }}</span><strong>{{ $submission->reviewed_at->format('M d, Y · H:i') }}</strong>@endif</div><a class="button full" href="{{ route('crm.submissions.index') }}">← {{ __('Back to applications') }}</a></div></section></aside>
</div>
@endsection

@push('head')<style>.crm-detail{grid-template-columns:minmax(0,2.2fr) minmax(270px,.8fr)}.detail-section{margin-bottom:18px}.detail-section .meta-item.full{grid-column:1/-1}.text-link{color:#3f7032;overflow-wrap:anywhere}.preline{white-space:pre-line;line-height:1.65}.tag-row{display:flex;gap:7px;flex-wrap:wrap}.commercial{border-color:#cee0c8}.commercial-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}.commercial-grid>div{background:#f6f9f5;border-radius:13px;padding:20px}.commercial-grid span,.commercial-grid small{display:block;color:var(--muted);font-size:10px}.commercial-grid strong{display:block;color:#072841;font-size:26px;margin-top:9px}.commercial-grid small{font-weight:500;margin-top:5px}.review-meta{display:grid;gap:5px;border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:17px 0;margin:18px 0}.review-meta span{color:var(--muted);font-size:10px;margin-top:7px}.review-meta strong{font-size:11px}@media(max-width:900px){.crm-detail{grid-template-columns:1fr}.sticky-panel{position:static}}@media(max-width:600px){.commercial-grid{grid-template-columns:1fr}}</style>@endpush
