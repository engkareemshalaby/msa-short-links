@extends('layouts.app')

@section('title', __('Edit partner account'))
@section('subtitle', __('Update the partner profile and portal sign-in details.'))

@section('content')
@php
    $submission = $partner->crmSubmission;
    $programs = old('interested_programs', $submission?->interested_programs ?? []);
@endphp

<form class="card form-card partner-edit-card" method="POST" action="{{ route('crm.partners.update', $partner) }}">
    @csrf
    @method('PUT')
    <div class="card-header"><div><h2>{{ __('Agency & contact') }}</h2><p>{{ __('These details appear on the partner account and application record.') }}</p></div></div>
    <div class="card-body">
        <div class="form-grid">
            <label class="field"><span>{{ __('Agency name') }}</span><input name="agency_name" value="{{ old('agency_name', $submission?->agency_name ?? $partner->name) }}" required></label>
            <label class="field"><span>{{ __('Country') }}</span><input name="country" value="{{ old('country', $submission?->country) }}"></label>
            <label class="field"><span>{{ __('City') }}</span><input name="city" value="{{ old('city', $submission?->city) }}"></label>
            <label class="field"><span>{{ __('Website or social media profile') }}</span><input type="url" name="website" value="{{ old('website', $submission?->website) }}" placeholder="https://"></label>
            <label class="field"><span>{{ __('Contact person name') }}</span><input name="contact_name" value="{{ old('contact_name', $submission?->contact_name ?? $partner->user?->name) }}" required></label>
            <label class="field"><span>{{ __('Job title') }}</span><input name="job_title" value="{{ old('job_title', $submission?->job_title) }}"></label>
            <label class="field"><span>{{ __('Mobile / WhatsApp') }}</span><input name="mobile" value="{{ old('mobile', $submission?->mobile) }}"></label>
            <label class="field"><span>{{ __('Email address') }}</span><input type="email" name="email" value="{{ old('email', $partner->user?->email ?? $submission?->email) }}" required></label>
            <label class="field"><span>{{ __('New password') }}</span><input type="password" name="password"><small>{{ __('Leave empty to keep the current password.') }}</small></label>
            <label class="field"><span>{{ __('Confirm password') }}</span><input type="password" name="password_confirmation"></label>
        </div>
    </div>

    @if($submission)
        <div class="card-header"><div><h2>{{ __('Recruitment profile') }}</h2><p>{{ __('Current reach and expected MSA recruitment.') }}</p></div></div>
        <div class="card-body">
            <div class="form-grid">
                <label class="field full"><span>{{ __('Countries you recruit students from') }}</span><textarea name="recruitment_countries">{{ old('recruitment_countries', implode(', ', $submission->recruitment_countries ?? [])) }}</textarea></label>
                <label class="field"><span>{{ __('Approximate students recruited annually') }}</span><select name="annual_students_range"><option value="">{{ __('Choose a range') }}</option>@foreach(['1-25','26-50','51-100','101-250','251+'] as $range)<option value="{{ $range }}" @selected(old('annual_students_range', $submission->annual_students_range) === $range)>{{ $range }}</option>@endforeach</select></label>
                <div class="field"><span>{{ __('Do you work with universities in Egypt?') }}</span><div class="inline-options"><label class="choice"><input type="radio" name="works_with_egyptian_universities" value="1" @checked((string) old('works_with_egyptian_universities', (int) $submission->works_with_egyptian_universities) === '1')>{{ __('Yes') }}</label><label class="choice"><input type="radio" name="works_with_egyptian_universities" value="0" @checked((string) old('works_with_egyptian_universities', (int) $submission->works_with_egyptian_universities) === '0')>{{ __('No') }}</label></div></div>
                <label class="field full"><span>{{ __('Universities you currently work with') }}</span><textarea name="current_universities">{{ old('current_universities', $submission->current_universities) }}</textarea></label>
                <label class="field"><span>{{ __('Expected MSA students in the first 12 months') }}</span><select name="expected_msa_students_range"><option value="">{{ __('Choose a range') }}</option>@foreach(['1-10','11-25','26-50','51-100','101+'] as $range)<option value="{{ $range }}" @selected(old('expected_msa_students_range', $submission->expected_msa_students_range) === $range)>{{ $range }}</option>@endforeach</select></label>
                <div class="field full"><span>{{ __('Interested faculties / programs') }}</span><div class="choice-grid">@foreach(['Dentistry','Pharmacy','Biotechnology','Engineering','Computer Science','Arts & Design','Management Sciences','Languages','Other'] as $program)<label class="choice"><input type="checkbox" name="interested_programs[]" value="{{ $program }}" @checked(in_array($program, $programs, true))>{{ __($program) }}</label>@endforeach</div></div>
                <label class="field full"><span>{{ __('Additional notes') }}</span><textarea name="notes">{{ old('notes', $submission->notes) }}</textarea></label>
            </div>
        </div>

        <div class="card-header"><div><h2>{{ __('Commercial proposal') }}</h2><p>{{ __('Requested commission and exclusive student discount.') }}</p></div></div>
        <div class="card-body">
            <div class="form-grid">
                <label class="field"><span>{{ __('Preferred commission model') }}</span><select name="commission_type"><option value="fixed_usd" @selected(old('commission_type', $submission->commission_type) === 'fixed_usd')>{{ __('Fixed amount in USD') }}</option><option value="percentage" @selected(old('commission_type', $submission->commission_type) === 'percentage')>{{ __('Percentage') }}</option></select></label>
                <label class="field"><span>{{ __('Requested commission value') }}</span><input type="number" name="commission_value" value="{{ old('commission_value', $submission->commission_value) }}" min="0" step="0.01"></label>
                <label class="field"><span>{{ __('Percentage calculated on') }}</span><select name="commission_basis"><option value="">{{ __('Choose one') }}</option><option value="installment" @selected(old('commission_basis', $submission->commission_basis) === 'installment')>{{ __('One tuition installment') }}</option><option value="academic_year" @selected(old('commission_basis', $submission->commission_basis) === 'academic_year')>{{ __('One academic year') }}</option></select></label>
                <label class="field"><span>{{ __('Minimum exclusive student discount') }}</span><input type="number" name="exclusive_discount_percent" value="{{ old('exclusive_discount_percent', $submission->exclusive_discount_percent) }}" min="0" max="100" step="0.01"></label>
            </div>
        </div>
    @endif

    <div class="card-body form-footer"><a class="button" href="{{ route('crm.partners.index') }}">{{ __('Cancel') }}</a><button class="button primary">{{ __('Save changes') }}</button></div>
</form>
@endsection

@push('head')<style>.partner-edit-card{max-width:980px;overflow:hidden}.partner-edit-card .card-header{border-top:1px solid var(--line)}.partner-edit-card .card-header:first-child{border-top:0}.partner-edit-card .form-footer{margin-top:0}</style>@endpush
