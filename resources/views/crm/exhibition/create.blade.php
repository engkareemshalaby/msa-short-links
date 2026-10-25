@extends('layouts.crm-public')

@php($general = $general ?? false)
@section('title', $general ? __('General registration') : __('Jordan Exhibition Registration'))

@section('content')
@if($general)<div class="general-exhibition-banner"><img src="{{ asset(app()->getLocale() === 'ar' ? 'images/general-exhibition-ar.jpeg' : 'images/general-exhibition-en.jpeg') }}" alt="{{ __('Study at MSA University — international students') }}" width="1600" height="400"></div>@endif
<section class="jordan-hero">
    <div class="hero-copy"><span class="crm-eyebrow">{{ $general ? __('MSA University') : __('MSA University · Jordan') }}</span><h1>{{ __('Start your journey at MSA University') }}</h1><p>{{ __('Register your interest and our Admissions Team will contact you with personalized guidance about programs, requirements, scholarships, tuition fees, and the application process.') }}</p><div class="hero-points"><span>✓ {{ __('No application fee') }}</span><span>✓ {{ __('Takes about 3 minutes') }}</span><span>✓ {{ __('Personal admissions guidance') }}</span></div></div>
    <div class="hero-mark"><strong>MSA</strong><span>{{ $general ? __('General registration') : __('Jordan Exhibition') }}</span></div>
</section>

@if($errors->any())<div class="crm-errors"><div class="alert danger"><strong>{{ __('Please review the highlighted information.') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif

<form class="crm-form jordan-form" method="POST" action="{{ $general ? route('crm.general.store') : route('crm.jordan.store') }}">@csrf
    <input class="honeypot" name="company_fax" tabindex="-1" autocomplete="off">
    <section class="form-section"><div class="form-section-head"><span class="section-number">1</span><div><h2>{{ __('Who is registering?') }}</h2><p>{{ __('Tell us how you are connected to the student.') }}</p></div></div><div class="form-section-body">
        <div class="role-grid">@foreach(['student' => __('Student'), 'parent' => __('Parent / Guardian')] as $value => $label)<label class="role-card"><input type="radio" name="registrant_role" value="{{ $value }}" @checked(old('registrant_role', 'student') === $value) required><span class="role-icon">{{ $value === 'student' ? '♙' : '♡' }}</span><strong>{{ $label }}</strong><small>{{ $value === 'student' ? __('I am registering myself') : __('I am registering a student') }}</small></label>@endforeach</div>
    </div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">2</span><div><h2>{{ __('Student & contact details') }}</h2><p>{{ __('We will only use these details for admissions follow-up.') }}</p></div></div><div class="form-section-body"><div class="form-grid two">
        <label class="field full"><span>{{ __('Student full name') }} <b class="required">*</b></span><input name="student_name" value="{{ old('student_name') }}" required autocomplete="name" placeholder="{{ __('Enter the student’s full name') }}"></label>
        <label class="field"><span>{{ __('Student email address') }} <b class="required">*</b></span><input type="email" name="student_email" value="{{ old('student_email') }}" required autocomplete="email" placeholder="student@example.com"></label>
        <label class="field"><span>{{ __('Student mobile (WhatsApp preferred)') }} <b class="required">*</b></span><input type="tel" name="student_mobile" value="{{ old('student_mobile') }}" required autocomplete="tel" placeholder="{{ $general ? '+XXX XX XXX XXXX' : '+962 7X XXX XXXX' }}"></label>
        <label class="field"><span>{{ __('Parent / guardian email') }}</span><input type="email" name="parent_email" value="{{ old('parent_email') }}" placeholder="parent@example.com"></label>
        <label class="field"><span>{{ __('Parent / guardian mobile') }}</span><input type="tel" name="parent_mobile" value="{{ old('parent_mobile') }}" placeholder="{{ $general ? '+XXX XX XXX XXXX' : '+962 7X XXX XXXX' }}"></label>
    </div></div></section>

    <section class="form-section{{ $general ? ' general-academic-section' : '' }}"><div class="form-section-head"><span class="section-number">3</span><div><h2>{{ __('Academic background') }}</h2><p>{{ __('Share your current qualification and result.') }}</p></div></div><div class="form-section-body"><div class="form-grid two">
        @if($general)
            <label class="field"><span>{{ __('Certificate category') }} <b class="required">*</b></span><select name="certificate_category" id="certificate-category" required><option value="">{{ __('Select a certificate category') }}</option>@foreach(\App\Support\GeneralRegistrationCertificates::categories() as $category)<option value="{{ $category }}" @selected(old('certificate_category') === $category)>{{ __($category) }}</option>@endforeach</select></label>
            <label class="field"><span>{{ __('Certificate type') }} <b class="required">*</b></span><select name="certificate_type" id="certificate-type" data-initial="{{ old('certificate_type') }}" required disabled><option value="">{{ __('Select a certificate category first') }}</option></select></label>
            <div class="field country-picker"><label for="country-search">{{ __("What is the student's nationality?") }} <b class="required">*</b></label><div class="country-combobox"><input type="search" id="country-search" role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="country-options" value="{{ old('registration_country') ? __(old('registration_country')) : '' }}" required autocomplete="off" placeholder="{{ __('Search and choose a country') }}"><input type="hidden" name="registration_country" id="registration-country" value="{{ old('registration_country') }}"><div id="country-options" class="country-options" role="listbox" hidden>@foreach(\App\Support\GeneralRegistrationCertificates::countries() as $country)<button type="button" role="option" data-country="{{ $country }}" aria-selected="false">{{ __($country) }}</button>@endforeach</div></div></div>
            <label class="field other-country" @if(old('registration_country') !== 'Other') hidden @endif><span>{{ __('Please specify the country') }} <b class="required">*</b></span><input name="registration_country_other" value="{{ old('registration_country_other') }}" maxlength="120"></label>
            <template id="general-certificate-options">
                <optgroup data-category="Arab High School Certificates">@foreach(\App\Support\GeneralRegistrationCertificates::ARAB_CERTIFICATES as $certificate)<option value="{{ $certificate }}">{{ __($certificate) }}</option>@endforeach</optgroup>
                <optgroup data-category="International Systems">@foreach(\App\Support\GeneralRegistrationCertificates::INTERNATIONAL_CERTIFICATES as $certificate)<option value="{{ $certificate }}">{{ __($certificate) }}</option>@endforeach</optgroup>
                <optgroup data-category="Asian National Certificates">@foreach(\App\Support\GeneralRegistrationCertificates::ASIAN_CERTIFICATES as $certificate)<option value="{{ $certificate }}">{{ __($certificate) }}</option>@endforeach</optgroup>
            </template>
        @else
            <label class="field"><span>{{ __('Certificate type') }} <b class="required">*</b></span><select name="certificate_type" id="certificate-type" required><option value="">{{ __('Select a certificate') }}</option>@foreach(\App\Models\ExhibitionRegistration::CERTIFICATES as $certificate)<option value="{{ $certificate }}" @selected(old('certificate_type') === $certificate)>{{ __($certificate) }}</option>@endforeach</select></label>
            <label class="field other-certificate" @if(old('certificate_type') !== 'Other') hidden @endif><span>{{ __('Other certificate type') }} <b class="required">*</b></span><input name="certificate_type_other" value="{{ old('certificate_type_other') }}"></label>
        @endif
        <label class="field full"><span>{{ __('Current GPA / overall result') }} @if($general)<small>({{ __('Optional') }})</small>@else<b class="required">*</b>@endif</span><input name="current_result" value="{{ old('current_result') }}" @required(! $general) placeholder="{{ __('GPA, percentage, grades, or predicted result') }}"><small>{{ __('Enter the result according to your educational system.') }}</small></label>
    </div></div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">4</span><div><h2>{{ __('Faculties you are interested in') }}</h2><p>{{ __('Choose between 1 and 4 faculties.') }} <strong class="selection-count">0 / 4</strong></p></div></div><div class="form-section-body"><div class="faculty-grid">@foreach(\App\Models\ExhibitionRegistration::FACULTIES as $faculty)<label class="choice"><input type="checkbox" name="interested_faculties[]" value="{{ $faculty }}" @checked(in_array($faculty, old('interested_faculties', [])))><span>{{ __($faculty) }}</span></label>@endforeach</div></div></section>

    @unless($general)
    <section class="form-section"><div class="form-section-head"><span class="section-number">5</span><div><h2>{{ __('Additional information') }}</h2><p>{{ __('This helps our Admissions Team provide the right guidance for you.') }}</p></div></div><div class="form-section-body"><div class="egypt-question"><span>{{ __('Do you have relatives or acquaintances in Egypt?') }} <b class="required">*</b></span><div class="inline-options">@foreach(['1' => __('Yes'), '0' => __('No')] as $value => $label)<label class="choice"><input type="radio" name="has_relatives_or_acquaintances_in_egypt" value="{{ $value }}" @checked((string) old('has_relatives_or_acquaintances_in_egypt') === $value) required><span>{{ $label }}</span></label>@endforeach</div></div><div class="egypt-question accommodation-question" @if((string) old('has_relatives_or_acquaintances_in_egypt') !== '1') hidden @endif><span>{{ __('Is accommodation or a place to stay available in Egypt?') }} <b class="required">*</b></span><div class="inline-options">@foreach(['1' => __('Yes'), '0' => __('No')] as $value => $label)<label class="choice"><input type="radio" name="has_accommodation_in_egypt" value="{{ $value }}" @checked((string) old('has_accommodation_in_egypt') === $value)><span>{{ $label }}</span></label>@endforeach</div></div><div class="contact-block"><strong>{{ __('How should we contact you?') }}</strong><small>{{ __('Choose the easiest way for our Admissions Team to reach you.') }}</small><div class="inline-options">@foreach(['email' => __('Email'), 'whatsapp' => __('WhatsApp')] as $value => $label)<label class="choice"><input type="radio" name="preferred_contact_method" value="{{ $value }}" @checked(old('preferred_contact_method', 'whatsapp') === $value) required><span>{{ $label }}</span></label>@endforeach</div></div><div class="location-pill"><span>●</span><div><small>{{ __('Exhibition location') }}</small><strong>{{ __('Jordan') }}</strong></div></div></div></section>
    @endunless

    <section class="submit-panel"><div><label class="choice privacy-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>{{ __('I agree that MSA University may use these details to contact me about admissions and related opportunities.') }}</span></label><p>{{ __('Your information will be handled securely and will not be shared outside the admissions process.') }}</p></div><button class="button primary" type="submit">{{ __('Submit registration') }} <span>→</span></button></section>
</form>
@endsection

@push('head')<style>
.jordan-hero{max-width:1040px;margin:auto;padding:58px 24px 34px;display:grid;grid-template-columns:1fr 240px;align-items:center;gap:50px}.hero-copy h1{font-size:42px;line-height:1.12;letter-spacing:-.045em;margin:12px 0 16px;max-width:700px}.hero-copy>p{max-width:730px;color:#62717a;line-height:1.8}.hero-points{display:flex;gap:10px;flex-wrap:wrap;margin-top:23px}.hero-points span{font-size:11px;font-weight:700;color:#3f7032;background:#eaf3e7;padding:8px 11px;border-radius:100px}.hero-mark{aspect-ratio:1;border-radius:50%;background:linear-gradient(145deg,#0b3859,#061d30);box-shadow:0 20px 50px rgba(7,40,65,.2);display:grid;place-content:center;text-align:center;color:white;border:8px solid #fff;outline:1px solid #dce6df}.hero-mark strong{font-size:45px;letter-spacing:-.07em}.hero-mark span{font-size:11px;color:#b8d6ad}.jordan-form{padding-top:4px}.role-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.role-card{position:relative;border:1px solid #dfe7e1;border-radius:14px;padding:20px;display:grid;grid-template-columns:42px 1fr;column-gap:12px;cursor:pointer}.role-card input{position:absolute;opacity:0}.role-card:has(input:checked){border-color:#538f3f;background:#f1f7ef;box-shadow:0 0 0 2px rgba(83,143,63,.1)}.role-icon{grid-row:1/3;width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:#e6f0e3;color:#3f7032;font-size:20px}.role-card strong{font-size:13px}.role-card small{color:#74817a;margin-top:3px}.faculty-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.selection-count{color:#538f3f;margin-inline-start:8px}.egypt-question{padding:0 0 22px;border-bottom:1px solid #e6ece8}.egypt-question>span{display:block;font-size:12px;font-weight:700;margin-bottom:12px}.contact-block{padding-top:22px}.contact-block>strong,.contact-block>small{display:block}.contact-block>strong{font-size:12px}.contact-block>small{color:#74817a;font-size:10px;margin:4px 0 12px}.location-pill{margin-top:20px;padding:13px 16px;border-radius:12px;background:#f3f6f4;display:flex;align-items:center;gap:11px;width:max-content;min-width:210px}.location-pill>span{color:#538f3f}.location-pill small,.location-pill strong{display:block}.location-pill small{color:#7b8881;font-size:9px;text-transform:uppercase;letter-spacing:.06em}.location-pill strong{font-size:13px;margin-top:2px}.field small{color:#7b8881;margin-top:6px}@media(max-width:800px){.jordan-hero{grid-template-columns:1fr}.hero-mark{display:none}.faculty-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:560px){.jordan-hero{padding-top:38px}.hero-copy h1{font-size:32px}.hero-points{display:grid}.role-grid,.faculty-grid{grid-template-columns:1fr}.form-grid.two{grid-template-columns:1fr}.form-grid.two .full{grid-column:auto}}
.general-academic-section{overflow:visible;position:relative;z-index:2}.country-combobox{position:relative}.country-options{position:absolute;inset-inline:0;top:calc(100% + 4px);z-index:30;max-height:min(320px,45vh);overflow:auto;padding:4px;background:#fff;border:1px solid #dfe7e1;border-radius:10px;box-shadow:0 12px 30px rgba(7,40,65,.14)}.country-options[hidden],.other-country[hidden]{display:none!important}.country-options button{display:block;width:100%;border:0;background:#fff;text-align:start;padding:10px 12px;border-radius:7px;color:#072841;cursor:pointer}.country-options button:hover,.country-options button:focus{background:#edf5ea;outline:none}.country-options button[data-country="Other"]{font-weight:800;border-top:1px solid #dfe7e1}.country-options button[hidden]{display:none}
.general-exhibition-banner{max-width:992px;margin:28px auto 0;overflow:hidden;border-radius:16px;box-shadow:0 6px 22px rgba(7,40,65,.08)}.general-exhibition-banner img{display:block;width:100%;height:auto}@media(max-width:560px){.general-exhibition-banner{margin:16px 16px 0;border-radius:10px}}
</style>@endpush

@push('scripts')<script>
const certificate=document.getElementById('certificate-type');const other=document.querySelector('.other-certificate');if(other){const otherInput=other.querySelector('input');function toggleOther(){const visible=certificate.value==='Other';other.hidden=!visible;otherInput.required=visible}certificate.addEventListener('change',toggleOther);toggleOther()}
const faculties=[...document.querySelectorAll('input[name="interested_faculties[]"]')];const count=document.querySelector('.selection-count');function updateFaculties(){const selected=faculties.filter(i=>i.checked);count.textContent=`${selected.length} / 4`;faculties.forEach(i=>i.disabled=!i.checked&&selected.length>=4)}faculties.forEach(i=>i.addEventListener('change',updateFaculties));updateFaculties();
const relatives=[...document.querySelectorAll('input[name="has_relatives_or_acquaintances_in_egypt"]')];const accommodation=document.querySelector('.accommodation-question');if(accommodation){const accommodationInputs=[...accommodation.querySelectorAll('input')];function toggleAccommodation(){const show=relatives.some(input=>input.checked&&input.value==='1');accommodation.hidden=!show;accommodationInputs.forEach(input=>{input.required=show;if(!show)input.checked=false})}relatives.forEach(input=>input.addEventListener('change',toggleAccommodation));toggleAccommodation()}
const country=document.getElementById('registration-country');if(country){
    const picker=document.querySelector('.country-picker');
    const search=document.getElementById('country-search');
    const list=document.getElementById('country-options');
    const countryOptions=[...list.querySelectorAll('[data-country]')];
    const certificateOptions=document.getElementById('general-certificate-options').content;
    const certificateCategory=document.getElementById('certificate-category');
    const otherCountry=document.querySelector('.other-country');
    const otherCountryInput=otherCountry.querySelector('input');
    const chooseCategory=@js(__('Select a certificate category first'));
    const chooseCertificate=@js(__('Select a certificate'));
    const invalidCountry=@js(__('Choose a country from the list'));
    const countries=new Set(countryOptions.map(option=>option.dataset.country));
    function updateCertificates(initial=false){
        const selected=initial?certificate.dataset.initial:certificate.value;
        const group=[...certificateOptions.querySelectorAll('optgroup')].find(group=>group.dataset.category===certificateCategory.value);
        certificate.replaceChildren(new Option(group?chooseCertificate:chooseCategory,''));
        if(group)group.querySelectorAll('option').forEach(option=>certificate.append(option.cloneNode(true)));
        certificate.disabled=!group;
        if([...certificate.options].some(option=>option.value===selected))certificate.value=selected;
        else certificate.value='';
    }
    function updateOtherCountry(){const show=country.value==='Other';otherCountry.hidden=!show;otherCountryInput.required=show;if(!show)otherCountryInput.value=''}
    function closeOptions(){list.hidden=true;search.setAttribute('aria-expanded','false')}
    function openOptions(){
        const term=search.value.trim().toLocaleLowerCase();
        let matches=0;
        countryOptions.forEach(option=>{
            option.hidden=!(option.dataset.country.toLocaleLowerCase().includes(term)||option.textContent.toLocaleLowerCase().includes(term));
            if(!option.hidden)matches++;
        });
        list.hidden=matches===0;
        search.setAttribute('aria-expanded',String(matches>0));
    }
    function chooseOption(option){
        country.value=option.dataset.country;
        search.value=option.textContent.trim();
        search.setCustomValidity('');
        countryOptions.forEach(candidate=>candidate.setAttribute('aria-selected',String(candidate===option)));
        search.focus();closeOptions();updateOtherCountry();
    }
    search.addEventListener('focus',openOptions);
    search.addEventListener('input',()=>{
        country.value='';search.setCustomValidity(search.value?invalidCountry:'');
        countryOptions.forEach(option=>option.setAttribute('aria-selected','false'));
        updateOtherCountry();openOptions();
    });
    search.addEventListener('keydown',event=>{
        const visible=countryOptions.filter(option=>!option.hidden);
        if(event.key==='Escape'){closeOptions();return}
        if((event.key==='ArrowDown'||event.key==='Enter')&&visible.length){event.preventDefault();if(event.key==='Enter')chooseOption(visible[0]);else visible[0].focus()}
    });
    list.addEventListener('click',event=>{const option=event.target.closest('[data-country]');if(option)chooseOption(option)});
    list.addEventListener('keydown',event=>{
        const visible=countryOptions.filter(option=>!option.hidden);
        const index=visible.indexOf(document.activeElement);
        if(event.key==='Escape'){event.preventDefault();closeOptions();search.focus()}
        if(event.key==='ArrowDown'||event.key==='ArrowUp'){event.preventDefault();visible[(index+(event.key==='ArrowDown'?1:visible.length-1))%visible.length]?.focus()}
    });
    document.addEventListener('click',event=>{if(!picker.contains(event.target))closeOptions()});
    if(!countries.has(country.value))search.setCustomValidity(search.value?invalidCountry:'');
    certificateCategory.addEventListener('change',()=>updateCertificates());
    updateOtherCountry();updateCertificates(true);
}
</script>@endpush
