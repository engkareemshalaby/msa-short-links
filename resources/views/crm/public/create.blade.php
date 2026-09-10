@extends('layouts.crm-public')

@section('title', __('Partner application'))

@section('content')
<div class="crm-hero">
    <span class="crm-eyebrow">{{ __('MSA University partnerships') }}</span>
    <h1>{{ __('Become an MSA recruitment partner') }}</h1>
    <p>{{ __('Tell us about your agency and student recruitment experience. The partnerships team will review your information and contact you if there is a suitable opportunity.') }}</p>
</div>

@if($errors->any())
    <div class="crm-errors"><div class="alert danger"><strong>{{ __('Please review the highlighted information.') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif

<form class="crm-form" method="POST" action="{{ route('crm.store') }}">
    @csrf
    @php($countryOptions = ['Egypt', 'Saudi Arabia', 'United Arab Emirates', 'Qatar', 'Kuwait', 'Jordan', 'Oman', 'Bahrain', 'Iraq', 'Palestine', 'Lebanon', 'Syria', 'Libya', 'Sudan', 'Yemen', 'Algeria', 'Morocco', 'Tunisia'])
    <input type="hidden" name="source" value="{{ old('source', request('source', request('utm_source', 'direct'))) }}">
    <label class="honeypot" aria-hidden="true">Company fax<input name="company_fax" tabindex="-1" autocomplete="off"></label>

    <section class="form-section"><div class="form-section-head"><span class="section-number">1</span><div><h2>{{ __('Agency details') }}</h2><p>{{ __('Basic information about your organization.') }}</p></div></div><div class="form-section-body"><div class="form-grid">
        <label class="field"><span>{{ __('Agency name') }} <b class="required">*</b></span><input name="agency_name" value="{{ old('agency_name') }}" maxlength="255" required autocomplete="organization"></label>
        <label class="field"><span>{{ __('Country') }} <b class="required">*</b></span><select name="country" required autocomplete="country-name"><option value="">{{ __('Choose a country') }}</option>@foreach($countryOptions as $country)<option value="{{ $country }}" @selected(old('country') === $country)>{{ __($country) }}</option>@endforeach</select></label>
        <label class="field"><span>{{ __('City') }}</span><input name="city" value="{{ old('city') }}" maxlength="120" autocomplete="address-level2"></label>
        <label class="field"><span>{{ __('Website or social media profile') }}</span><input type="url" name="website" value="{{ old('website') }}" placeholder="https://"></label>
    </div></div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">2</span><div><h2>{{ __('Primary contact') }}</h2><p>{{ __('Who should our partnerships team contact?') }}</p></div></div><div class="form-section-body"><div class="form-grid">
        <label class="field"><span>{{ __('Contact person name') }} <b class="required">*</b></span><input name="contact_name" value="{{ old('contact_name') }}" required autocomplete="name"></label>
        <label class="field"><span>{{ __('Job title') }}</span><input name="job_title" value="{{ old('job_title') }}" maxlength="150" autocomplete="organization-title"></label>
        <label class="field"><span>{{ __('Mobile / WhatsApp') }} <b class="required">*</b></span><input type="tel" name="mobile" value="{{ old('mobile') }}" required autocomplete="tel" inputmode="tel"></label>
        <label class="field"><span>{{ __('Email address') }} <b class="required">*</b></span><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
    </div></div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">3</span><div><h2>{{ __('Partner portal sign-in') }}</h2><p>{{ __('You will use this password if your partner account is approved.') }}</p></div></div><div class="form-section-body"><div class="form-grid">
        <label class="field"><span>{{ __('Password') }} <b class="required">*</b></span><input type="password" name="password" required minlength="8" autocomplete="new-password"><small>{{ __('At least 8 characters.') }}</small></label>
        <label class="field"><span>{{ __('Confirm password') }} <b class="required">*</b></span><input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
    </div></div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">4</span><div><h2>{{ __('Recruitment experience') }}</h2><p>{{ __('Help us understand your reach and current activity.') }}</p></div></div><div class="form-section-body"><div class="form-grid">
        <div class="field full"><span>{{ __('Countries you recruit students from') }} <b class="required">*</b></span><details class="multi-select" data-multi-select data-required-group><summary><span data-multi-label data-placeholder="{{ __('Choose one or more countries') }}">{{ __('Choose one or more countries') }}</span><b>⌄</b></summary><div class="multi-options">@foreach($countryOptions as $country)<label><input type="checkbox" name="recruitment_countries[]" value="{{ $country }}" @checked(in_array($country, old('recruitment_countries', []), true))><span>{{ __($country) }}</span></label>@endforeach</div></details><small>{{ __('You can select more than one country.') }}</small></div>
        <label class="field"><span>{{ __('Approximate students recruited annually') }} <b class="required">*</b></span><select name="annual_students_range" required><option value="">{{ __('Choose a range') }}</option>@foreach(['1-25','26-50','51-100','101-250','251+'] as $range)<option value="{{ $range }}" @selected(old('annual_students_range') === $range)>{{ $range }}</option>@endforeach</select></label>
        <div class="field"><span>{{ __('Do you work with universities in Egypt?') }} <b class="required">*</b></span><div class="inline-options"><label class="choice"><input type="radio" name="works_with_egyptian_universities" value="1" @checked(old('works_with_egyptian_universities') === '1') required>{{ __('Yes') }}</label><label class="choice"><input type="radio" name="works_with_egyptian_universities" value="0" @checked(old('works_with_egyptian_universities') === '0') required>{{ __('No') }}</label></div></div>
        <label class="field full conditional" id="universitiesField"><span>{{ __('Universities you currently work with') }} <b class="required">*</b></span><textarea name="current_universities">{{ old('current_universities') }}</textarea></label>
    </div></div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">5</span><div><h2>{{ __('Partnership opportunity') }}</h2><p>{{ __('Your realistic expectations for the first year with MSA.') }}</p></div></div><div class="form-section-body"><div class="form-grid">
        <label class="field"><span>{{ __('Expected MSA students in the first 12 months') }} <b class="required">*</b></span><select name="expected_msa_students_range" required><option value="">{{ __('Choose a range') }}</option>@foreach(['1-10','11-25','26-50','51-100','101+'] as $range)<option value="{{ $range }}" @selected(old('expected_msa_students_range') === $range)>{{ $range }}</option>@endforeach</select></label>
        <div></div>
        <div class="field full"><span>{{ __('Interested faculties / programs') }} <small class="optional-label">{{ __('Optional') }}</small></span><details class="multi-select" data-multi-select><summary><span data-multi-label data-placeholder="{{ __('Choose one or more programs') }}">{{ __('Choose one or more programs') }}</span><b>⌄</b></summary><div class="multi-options">@foreach(['Dentistry','Pharmacy','Biotechnology','Engineering','Computer Science','Arts & Design','Management Sciences','Languages','Other'] as $program)<label><input type="checkbox" name="interested_programs[]" value="{{ $program }}" @checked(in_array($program, old('interested_programs', []), true))><span>{{ __($program) }}</span></label>@endforeach</div></details></div>
        <label class="field full"><span>{{ __('Additional notes') }}</span><textarea name="notes" maxlength="5000">{{ old('notes') }}</textarea></label>
    </div></div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">6</span><div><h2>{{ __('Commercial proposal') }}</h2><p>{{ __('Tell us the commission you request and the exclusive student discount you can offer.') }}</p></div></div><div class="form-section-body"><div class="form-grid">
        <input type="hidden" name="commission_type" value="percentage">
        <label class="field"><span>{{ __('Requested commission percentage') }} <b class="required">*</b></span><input type="number" name="commission_value" value="{{ old('commission_value') }}" min="0" max="100" step="0.01" required inputmode="decimal"><small>{{ __('Enter a percentage from 0 to 100.') }}</small></label>
        <label class="field"><span>{{ __('Percentage calculated on') }} <b class="required">*</b></span><select name="commission_basis" required><option value="">{{ __('Choose one') }}</option><option value="installment" @selected(old('commission_basis') === 'installment')>{{ __('One tuition installment') }}</option><option value="academic_year" @selected(old('commission_basis') === 'academic_year')>{{ __('One academic year') }}</option></select></label>
        <label class="field full"><span>{{ __('Minimum exclusive discount for students referred by your agency') }} <b class="required">*</b></span><input type="number" name="exclusive_discount_percent" value="{{ old('exclusive_discount_percent') }}" min="0" max="30" step="0.01" required inputmode="decimal"><small>{{ __('Enter a percentage from 0 to 30. This is the minimum exclusive discount you can commit to.') }}</small></label>
    </div></div></section>

        <div class="submit-panel"><div><label class="checkbox privacy-check"><input type="checkbox" name="consent" value="1" @checked(old('consent')) required><span>{{ __('I consent to MSA University storing this information and contacting me regarding partnership opportunities.') }} <b class="required">*</b></span></label><p>{{ __('Submitting this form does not constitute approval or a partnership agreement.') }}</p></div><div class="submit-actions"><button class="button primary" type="submit">{{ __('Submit application') }} <span>→</span></button></div></div>
</form>
@endsection

@push('head')
<style>
    .crm-hero{padding-bottom:30px}.crm-hero:after{content:"";display:block;width:58px;height:4px;border-radius:10px;background:#538f3f;margin-top:24px}.crm-form{padding-top:0}.form-progress{position:relative;display:grid;grid-template-columns:repeat(3,1fr);background:#fff;border:1px solid #e1e8e3;border-radius:16px;padding:18px 28px;margin-bottom:25px;box-shadow:0 8px 25px rgba(7,40,65,.05)}.progress-line{position:absolute;top:34px;inset-inline:calc(16.66% + 22px);height:3px;background:#e6ece8;border-radius:10px;overflow:hidden}.progress-line span{display:block;width:0;height:100%;background:#538f3f;transition:width .3s ease}.progress-step{position:relative;z-index:1;border:0;background:transparent;display:flex;align-items:center;justify-content:center;gap:10px;color:#87938c;cursor:default}.progress-step>span{display:grid;place-items:center;width:34px;height:34px;border-radius:50%;background:#eef2ef;border:3px solid #fff;box-shadow:0 0 0 1px #dfe6e1;font-size:12px;font-weight:800;transition:.2s}.progress-step strong{font-size:11px}.progress-step.active,.progress-step.complete{color:#072841}.progress-step.active>span,.progress-step.complete>span{background:#538f3f;color:#fff;box-shadow:0 0 0 1px #538f3f}.progress-step.complete{cursor:pointer}.step-panel{animation:stepIn .25s ease}.crm-form.steps-ready .step-panel{display:none}.crm-form.steps-ready .step-panel.active{display:block}.step-intro{margin:0 0 18px;padding:0 5px}.step-intro>span{display:block;color:#538f3f;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.step-intro h2{font-size:23px;letter-spacing:-.025em;margin:6px 0}.step-intro p{color:#68777f;font-size:12px;margin:0}.step-actions{display:flex;align-items:center;justify-content:space-between;gap:15px;background:#fff;border:1px solid #e1e8e3;border-radius:14px;padding:15px 18px}.step-actions>span{color:#7b8881;font-size:10px}.step-next{min-width:145px}.step-back{min-width:110px}.submit-actions{display:flex;align-items:center;gap:9px;flex:0 0 auto}.submit-back{color:#fff;background:transparent;border-color:rgba(255,255,255,.3)}.form-section{transition:border-color .2s,box-shadow .2s}.form-section:focus-within{border-color:#c6d9c0;box-shadow:0 9px 28px rgba(83,143,63,.08)}.field input,.field select,.field textarea{transition:border-color .15s,box-shadow .15s,background .15s}.field input:user-invalid,.field select:user-invalid,.field textarea:user-invalid{border-color:#dca0a0;background:#fffafa}.choice{transition:border-color .15s,background .15s,transform .15s}.choice:hover{border-color:#b9cbb4;transform:translateY(-1px)}@keyframes stepIn{from{opacity:0;transform:translateY(7px)}to{opacity:1;transform:none}}@media(max-width:700px){.form-progress{padding:14px 8px}.progress-line{top:30px;inset-inline:calc(16.66% + 16px)}.progress-step{flex-direction:column;gap:6px}.progress-step>span{width:30px;height:30px}.progress-step strong{font-size:9px;line-height:1.25}.step-intro h2{font-size:20px}.step-actions{align-items:stretch}.step-actions>span{display:none}.step-actions .button{min-width:0;flex:1}.submit-actions{width:100%}.submit-actions .button{min-width:0;flex:1}.submit-panel{padding:20px}}
    .form-section:has(.multi-select[open]){overflow:visible;position:relative;z-index:30}.optional-label{display:inline!important;margin-inline-start:6px;color:#7b8881;font-weight:500!important}.multi-select{position:relative}.multi-select summary{list-style:none;display:flex;align-items:center;justify-content:space-between;width:100%;border:1px solid #dfe4eb;border-radius:10px;padding:11px 13px;background:#fff;color:#172033;font-size:13px;cursor:pointer}.multi-select summary::-webkit-details-marker{display:none}.multi-select[open] summary{border-color:#538f3f;box-shadow:0 0 0 3px #edf5ea}.multi-select summary b{font-size:16px;transition:transform .15s}.multi-select[open] summary b{transform:rotate(180deg)}.multi-options{position:absolute;z-index:20;inset-inline:0;top:calc(100% + 6px);display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:4px;max-height:260px;overflow:auto;padding:8px;background:#fff;border:1px solid #dfe4eb;border-radius:11px;box-shadow:0 12px 30px rgba(7,40,65,.14)}.multi-options label{display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:8px;font-size:12px;cursor:pointer}.multi-options label:hover,.multi-options label:has(input:checked){background:#edf5ea}.multi-options input{width:16px!important;height:16px;accent-color:#538f3f}.multi-select.invalid summary{border-color:#b43d3d;background:#fffafa}@media(max-width:700px){.multi-options{grid-template-columns:1fr}}
</style>
@endpush

@push('scripts')
<script>
const egyptRadios=document.querySelectorAll('[name="works_with_egyptian_universities"]');
const universitiesField=document.getElementById('universitiesField');
const crmForm=document.querySelector('.crm-form');
function syncConditionalFields(){
    const works=[...egyptRadios].find(input=>input.checked)?.value==='1';
    universitiesField.style.display=works?'block':'none';
    universitiesField.querySelector('textarea').required=works;
}
function syncMultiSelect(menu){
    const selected=[...menu.querySelectorAll('input:checked')];
    const label=menu.querySelector('[data-multi-label]');
    label.textContent=selected.length?selected.map(input=>input.nextElementSibling.textContent.trim()).join(', '):label.dataset.placeholder;
    menu.classList.remove('invalid');
}
document.querySelectorAll('[data-multi-select]').forEach(menu=>{
    syncMultiSelect(menu);
    menu.querySelectorAll('input').forEach(input=>input.addEventListener('change',()=>syncMultiSelect(menu)));
});
document.addEventListener('click',event=>document.querySelectorAll('[data-multi-select][open]').forEach(menu=>{if(!menu.contains(event.target))menu.removeAttribute('open')}));
crmForm.addEventListener('submit',event=>{
    const missing=[...document.querySelectorAll('[data-required-group]')].find(menu=>!menu.querySelector('input:checked'));
    if(missing){event.preventDefault();missing.classList.add('invalid');missing.open=true;missing.scrollIntoView({behavior:'smooth',block:'center'});}
});
egyptRadios.forEach(input=>input.addEventListener('change',syncConditionalFields));
syncConditionalFields();
</script>
@endpush
