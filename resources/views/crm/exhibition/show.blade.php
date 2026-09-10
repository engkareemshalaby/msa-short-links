@extends('layouts.app')

@section('title', __('Registration details'))
@section('subtitle', __('Review the complete Jordan exhibition registration form.'))

@section('content')
<div class="detail-grid exhibition-detail">
    <main>
        <section class="card detail-section">
            <div class="card-header"><div><h2>{{ __('Student details') }}</h2><p>{{ __('Personal and direct contact information.') }}</p></div><span class="reference-badge" dir="ltr">{{ $registration->reference_code }}</span></div>
            <div class="card-body meta-grid">
                <div class="meta-item"><span>{{ __('Student full name') }}</span><strong>{{ $registration->student_name }}</strong></div>
                <div class="meta-item"><span>{{ __('Student email address') }}</span><a class="text-link" href="mailto:{{ $registration->student_email }}" dir="ltr">{{ $registration->student_email }}</a></div>
                <div class="meta-item"><span>{{ __('Student mobile (WhatsApp preferred)') }}</span><a class="text-link" href="tel:{{ $registration->student_mobile }}" dir="ltr">{{ $registration->student_mobile }}</a></div>
                <div class="meta-item"><span>{{ __('Registrant') }}</span><strong>{{ __(ucfirst($registration->registrant_role)) }}</strong></div>
            </div>
        </section>

        <section class="card detail-section">
            <div class="card-header"><div><h2>{{ __('Parent / guardian details') }}</h2><p>{{ __('Additional contact details provided with the registration.') }}</p></div></div>
            <div class="card-body meta-grid">
                <div class="meta-item"><span>{{ __('Parent / guardian email') }}</span>@if($registration->parent_email)<a class="text-link" href="mailto:{{ $registration->parent_email }}" dir="ltr">{{ $registration->parent_email }}</a>@else<strong class="muted-text">{{ __('Not provided') }}</strong>@endif</div>
                <div class="meta-item"><span>{{ __('Parent / guardian mobile') }}</span>@if($registration->parent_mobile)<a class="text-link" href="tel:{{ $registration->parent_mobile }}" dir="ltr">{{ $registration->parent_mobile }}</a>@else<strong class="muted-text">{{ __('Not provided') }}</strong>@endif</div>
            </div>
        </section>

        <section class="card detail-section">
            <div class="card-header"><div><h2>{{ __('Academic background') }}</h2><p>{{ __('Certificate, current result, and faculty interests.') }}</p></div></div>
            <div class="card-body meta-grid">
                <div class="meta-item"><span>{{ __('Certificate type') }}</span><strong>{{ __($registration->certificate_type) }}</strong></div>
                @if($registration->certificate_type === 'Other')<div class="meta-item"><span>{{ __('Other certificate type') }}</span><strong>{{ $registration->certificate_type_other }}</strong></div>@endif
                <div class="meta-item full"><span>{{ __('Current GPA / overall result') }}</span><strong>{{ $registration->current_result }}</strong></div>
                <div class="meta-item full"><span>{{ __('Interested faculties') }}</span><div class="faculty-tags">@foreach($registration->interested_faculties as $faculty)<span>{{ __($faculty) }}</span>@endforeach</div></div>
            </div>
        </section>

        <section class="card detail-section">
            <div class="card-header"><div><h2>{{ __('Registration information') }}</h2><p>{{ __('Contact preference, exhibition, and submission time.') }}</p></div></div>
            <div class="card-body meta-grid">
                <div class="meta-item"><span>{{ __('Preferred contact method') }}</span><strong>{{ __(ucfirst($registration->preferred_contact_method)) }}</strong></div>
                <div class="meta-item"><span>{{ __('Relatives or acquaintances in Egypt') }}</span><strong>{{ is_null($registration->has_relatives_or_acquaintances_in_egypt) ? __('Not specified') : ($registration->has_relatives_or_acquaintances_in_egypt ? __('Yes') : __('No')) }}</strong></div>
                @if($registration->has_relatives_or_acquaintances_in_egypt)<div class="meta-item"><span>{{ __('Accommodation or a place to stay in Egypt') }}</span><strong>{{ is_null($registration->has_accommodation_in_egypt) ? __('Not specified') : ($registration->has_accommodation_in_egypt ? __('Yes') : __('No')) }}</strong></div>@endif
                <div class="meta-item"><span>{{ __('Exhibition location') }}</span><strong>{{ __($registration->exhibition_location) }}</strong></div>
                <div class="meta-item"><span>{{ __('Submitted') }}</span><strong>{{ $registration->created_at->format('M d, Y · H:i') }}</strong></div>
                <div class="meta-item"><span>{{ __('Last updated') }}</span><strong>{{ $registration->updated_at->format('M d, Y · H:i') }}</strong></div>
            </div>
        </section>
    </main>

    <aside><section class="card sticky-panel"><div class="card-header"><div><h2>{{ __('Registration status') }}</h2><p>{{ __('Update the admissions follow-up stage.') }}</p></div></div><div class="card-body"><form method="POST" action="{{ route('crm.exhibition.update', $registration) }}">@csrf @method('PATCH')<label class="field"><span>{{ __('Status') }}</span><select name="status">@foreach(\App\Models\ExhibitionRegistration::STATUSES as $status)<option value="{{ $status }}" @selected($registration->status === $status)>{{ __(ucfirst($status)) }}</option>@endforeach</select></label><div class="form-footer"><button class="button primary full">{{ __('Save status') }}</button></div></form><div class="side-meta"><span>{{ __('Reference') }}</span><strong dir="ltr">{{ $registration->reference_code }}</strong></div><a class="button full" href="{{ route('crm.exhibition.index') }}">← {{ __('Back to registrations') }}</a></div></section></aside>
</div>
@endsection

@push('head')<style>
.exhibition-detail{grid-template-columns:minmax(0,2.1fr) minmax(280px,.9fr)}.detail-section{margin-bottom:18px}.meta-item.full{grid-column:1/-1}.reference-badge{background:#edf5ea;color:#3f7032;border-radius:9px;padding:7px 10px;font-size:10px;font-weight:800;letter-spacing:.06em}.text-link{color:#3f7032;overflow-wrap:anywhere}.muted-text{color:#87938c;font-weight:500}.faculty-tags{display:flex;flex-wrap:wrap;gap:8px;margin-top:4px}.faculty-tags span{background:#edf5ea;color:#3f7032;border:1px solid #dce9d7;border-radius:9px;padding:7px 10px;font-size:10px;font-weight:600}.side-meta{display:grid;gap:5px;border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:17px 0;margin:18px 0}.side-meta span{color:var(--muted);font-size:10px}.side-meta strong{font-size:12px}@media(max-width:900px){.exhibition-detail{grid-template-columns:1fr}.sticky-panel{position:static}}@media(max-width:600px){.meta-grid{grid-template-columns:1fr}.meta-item.full{grid-column:auto}}
</style>@endpush
