@extends('layouts.app')

@section('title', __('Edit partner account'))
@section('subtitle', __('Update the partner profile and portal sign-in details.'))

@section('content')
@php
    $submission = $partner->crmSubmission;
    $programs = old('interested_programs', $submission?->interested_programs ?? []);
    $countryOptions = ['Egypt', 'Saudi Arabia', 'United Arab Emirates', 'Qatar', 'Kuwait', 'Jordan', 'Oman', 'Bahrain', 'Iraq', 'Palestine', 'Lebanon', 'Syria', 'Libya', 'Sudan', 'Yemen', 'Algeria', 'Morocco', 'Tunisia'];
    $recruitmentCountries = old('recruitment_countries', $submission?->recruitment_countries ?? []);
@endphp

<form class="card form-card partner-edit-card" method="POST" action="{{ route('crm.partners.update', $partner) }}">
    @csrf
    @method('PUT')
    <div class="card-header"><div><h2>{{ __('Agency & contact') }}</h2><p>{{ __('These details appear on the partner account and application record.') }}</p></div></div>
    <div class="card-body">
        <div class="form-grid">
            <label class="field"><span>{{ __('Agency name') }}</span><input name="agency_name" value="{{ old('agency_name', $submission?->agency_name ?? $partner->name) }}" required></label>
            <label class="field"><span>{{ __('Country') }}</span><select name="country"><option value="">{{ __('Choose a country') }}</option>@foreach($countryOptions as $country)<option value="{{ $country }}" @selected(old('country', $submission?->country) === $country)>{{ __($country) }}</option>@endforeach</select></label>
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
                <div class="field full"><span>{{ __('Countries you recruit students from') }}</span><details class="multi-select" data-multi-select><summary><span data-multi-label data-placeholder="{{ __('Choose one or more countries') }}">{{ __('Choose one or more countries') }}</span><b>⌄</b></summary><div class="multi-options">@foreach($countryOptions as $country)<label><input type="checkbox" name="recruitment_countries[]" value="{{ $country }}" @checked(in_array($country, $recruitmentCountries, true))><span>{{ __($country) }}</span></label>@endforeach</div></details><small>{{ __('You can select more than one country.') }}</small></div>
                <label class="field"><span>{{ __('Approximate students recruited annually') }}</span><select name="annual_students_range"><option value="">{{ __('Choose a range') }}</option>@foreach(['1-25','26-50','51-100','101-250','251+'] as $range)<option value="{{ $range }}" @selected(old('annual_students_range', $submission->annual_students_range) === $range)>{{ $range }}</option>@endforeach</select></label>
                <div class="field"><span>{{ __('Do you work with universities in Egypt?') }}</span><div class="inline-options"><label class="choice"><input type="radio" name="works_with_egyptian_universities" value="1" @checked((string) old('works_with_egyptian_universities', (int) $submission->works_with_egyptian_universities) === '1')>{{ __('Yes') }}</label><label class="choice"><input type="radio" name="works_with_egyptian_universities" value="0" @checked((string) old('works_with_egyptian_universities', (int) $submission->works_with_egyptian_universities) === '0')>{{ __('No') }}</label></div></div>
                <label class="field full conditional-universities" id="universitiesField"><span>{{ __('Universities you currently work with') }}</span><textarea name="current_universities">{{ old('current_universities', $submission->current_universities) }}</textarea></label>
                <label class="field"><span>{{ __('Expected MSA students in the first 12 months') }}</span><select name="expected_msa_students_range"><option value="">{{ __('Choose a range') }}</option>@foreach(['1-10','11-25','26-50','51-100','101+'] as $range)<option value="{{ $range }}" @selected(old('expected_msa_students_range', $submission->expected_msa_students_range) === $range)>{{ $range }}</option>@endforeach</select></label>
                <div class="field full"><span>{{ __('Interested faculties / programs') }} <small class="optional-label">{{ __('Optional') }}</small></span><details class="multi-select" data-multi-select><summary><span data-multi-label data-placeholder="{{ __('Choose one or more programs') }}">{{ __('Choose one or more programs') }}</span><b>⌄</b></summary><div class="multi-options">@foreach(['Dentistry','Pharmacy','Biotechnology','Engineering','Computer Science','Arts & Design','Management Sciences','Languages','Other'] as $program)<label><input type="checkbox" name="interested_programs[]" value="{{ $program }}" @checked(in_array($program, $programs, true))><span>{{ __($program) }}</span></label>@endforeach</div></details></div>
                <label class="field full"><span>{{ __('Additional notes') }}</span><textarea name="notes">{{ old('notes', $submission->notes) }}</textarea></label>
            </div>
        </div>

        <div class="card-header"><div><h2>{{ __('Commercial proposal') }}</h2><p>{{ __('Requested commission and exclusive student discount.') }}</p></div></div>
        <div class="card-body">
            <div class="form-grid">
                <input type="hidden" name="commission_type" value="percentage">
                <label class="field"><span>{{ __('Requested commission percentage') }}</span><input type="number" name="commission_value" value="{{ old('commission_value', $submission->commission_value) }}" min="0" max="100" step="0.01"><small>{{ __('Enter a percentage from 0 to 100.') }}</small></label>
                <label class="field"><span>{{ __('Percentage calculated on') }}</span><select name="commission_basis"><option value="">{{ __('Choose one') }}</option><option value="installment" @selected(old('commission_basis', $submission->commission_basis) === 'installment')>{{ __('One tuition installment') }}</option><option value="academic_year" @selected(old('commission_basis', $submission->commission_basis) === 'academic_year')>{{ __('One academic year') }}</option></select></label>
                <label class="field"><span>{{ __('Minimum exclusive student discount') }}</span><input type="number" name="exclusive_discount_percent" value="{{ old('exclusive_discount_percent', $submission->exclusive_discount_percent) }}" min="0" max="30" step="0.01"><small>{{ __('Enter a percentage from 0 to 30. This is the minimum exclusive discount you can commit to.') }}</small></label>
            </div>
        </div>
    @endif

    <div class="card-body form-footer"><a class="button" href="{{ route('crm.partners.index') }}">{{ __('Cancel') }}</a><button class="button primary">{{ __('Save changes') }}</button></div>
</form>
@endsection

@push('head')
<style>
    .partner-edit-card{max-width:1040px;overflow:hidden;counter-reset:section;border-radius:17px}.partner-edit-card:has(.multi-select[open]){overflow:visible}.partner-edit-card .card-header{border-top:1px solid var(--line);padding:20px 24px;justify-content:flex-start;gap:14px;counter-increment:section}.partner-edit-card .card-header:before{content:counter(section);display:grid;place-items:center;width:31px;height:31px;border-radius:9px;background:#edf5ea;color:#3f7032;font-weight:800;flex:0 0 auto}.partner-edit-card .card-header:first-child{border-top:0}.partner-edit-card .card-body{padding:24px}.partner-edit-card .form-grid{gap:20px}.partner-edit-card .form-footer{margin-top:0;background:#072841}.partner-edit-card .form-footer .button:not(.primary){color:#fff;background:transparent;border-color:rgba(255,255,255,.3)}.optional-label{display:inline!important;margin-inline-start:6px;color:var(--muted);font-weight:500!important}.multi-select{position:relative}.multi-select summary{list-style:none;display:flex;align-items:center;justify-content:space-between;width:100%;border:1px solid #dfe4eb;border-radius:10px;padding:11px 13px;background:#fff;font-size:13px;cursor:pointer}.multi-select summary::-webkit-details-marker{display:none}.multi-select[open] summary{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}.multi-select summary b{transition:transform .15s}.multi-select[open] summary b{transform:rotate(180deg)}.multi-options{position:absolute;z-index:50;inset-inline:0;top:calc(100% + 6px);display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:4px;max-height:260px;overflow:auto;padding:8px;background:#fff;border:1px solid #dfe4eb;border-radius:11px;box-shadow:0 12px 30px rgba(7,40,65,.14)}.multi-options label{display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:8px;font-size:12px;cursor:pointer}.multi-options label:hover,.multi-options label:has(input:checked){background:var(--primary-soft)}.multi-options input{width:16px!important;height:16px;accent-color:var(--primary)}@media(max-width:700px){.multi-options{grid-template-columns:1fr}}
</style>
@endpush

@push('scripts')
<script>
const editForm=document.querySelector('.partner-edit-card');
const egyptRadios=editForm.querySelectorAll('[name="works_with_egyptian_universities"]');
const universitiesField=document.getElementById('universitiesField');
function syncUniversities(){universitiesField.style.display=[...egyptRadios].find(input=>input.checked)?.value==='1'?'block':'none'}
function syncMultiSelect(menu){const selected=[...menu.querySelectorAll('input:checked')];const label=menu.querySelector('[data-multi-label]');label.textContent=selected.length?selected.map(input=>input.nextElementSibling.textContent.trim()).join(', '):label.dataset.placeholder}
document.querySelectorAll('[data-multi-select]').forEach(menu=>{syncMultiSelect(menu);menu.querySelectorAll('input').forEach(input=>input.addEventListener('change',()=>syncMultiSelect(menu)))});
document.addEventListener('click',event=>document.querySelectorAll('[data-multi-select][open]').forEach(menu=>{if(!menu.contains(event.target))menu.removeAttribute('open')}));
egyptRadios.forEach(input=>input.addEventListener('change',syncUniversities));
syncUniversities();
</script>
@endpush
