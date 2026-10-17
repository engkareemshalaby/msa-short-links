@extends('layouts.crm-public')

@section('title', __('Iraq Exhibition Registration'))

@push('head')
<meta name="description" content="{{ __('Register your interest in MSA University at the Iraq Exhibition. Our Admissions Team will guide you through programs, requirements, and the application process.') }}">
<link rel="canonical" href="{{ route('crm.iraq.create') }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="MSA University">
<meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_AR' : 'en_US' }}">
<meta property="og:url" content="{{ route('crm.iraq.create') }}">
<meta property="og:title" content="{{ __('Iraq Exhibition Registration') }} · MSA University">
<meta property="og:description" content="{{ __('Register your interest in MSA University at the Iraq Exhibition. Our Admissions Team will guide you through programs, requirements, and the application process.') }}">
<meta property="og:image" content="{{ asset(file_exists(public_path('images/iraq-exhibition-'.app()->getLocale().'.jpeg')) ? 'images/iraq-exhibition-'.app()->getLocale().'.jpeg' : 'images/msa-logo-wide.png') }}">
<meta property="og:image:alt" content="MSA University">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ __('Iraq Exhibition Registration') }} · MSA University">
<meta name="twitter:description" content="{{ __('Register your interest in MSA University at the Iraq Exhibition. Our Admissions Team will guide you through programs, requirements, and the application process.') }}">
<meta name="twitter:image" content="{{ asset(file_exists(public_path('images/iraq-exhibition-'.app()->getLocale().'.jpeg')) ? 'images/iraq-exhibition-'.app()->getLocale().'.jpeg' : 'images/msa-logo-wide.png') }}">
@endpush

@section('content')
@if(file_exists(public_path('images/iraq-exhibition-'.app()->getLocale().'.jpeg')))
<div class="iraq-banner"><img src="{{ asset('images/iraq-exhibition-'.app()->getLocale().'.jpeg') }}" alt="{{ __('Iraq Exhibition') }}" width="1600" height="400" fetchpriority="high"></div>
@endif
<section class="jordan-hero">
    <div class="hero-copy"><span class="crm-eyebrow">{{ __('MSA University · Iraq') }}</span><h1>{{ __('Start your journey at MSA University') }}</h1><p>{{ __('Register your interest and our Admissions Team will contact you with personalized guidance about programs, requirements, scholarships, tuition fees, and the application process.') }}</p><div class="hero-points"><span>✓ {{ __('No application fee') }}</span><span>✓ {{ __('Takes about 3 minutes') }}</span><span>✓ {{ __('Personal admissions guidance') }}</span></div></div>
    <div class="hero-mark"><img src="{{ asset('images/msa-logo.png') }}" alt="MSA University"><span>{{ __('Iraq Exhibition') }}</span></div>
</section>

@if($errors->any())<div class="crm-errors"><div class="alert danger"><strong>{{ __('Please review the highlighted information.') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif

<form class="crm-form jordan-form" method="POST" action="{{ route('crm.iraq.store') }}">@csrf
    <input class="honeypot" name="company_fax" tabindex="-1" autocomplete="off">
    <section class="form-section"><div class="form-section-head"><span class="section-number">1</span><div><h2>{{ __('Who is registering?') }}</h2><p>{{ __('Tell us how you are connected to the student.') }}</p></div></div><div class="form-section-body">
        <div class="role-grid">@foreach(['student' => __('Student'), 'parent' => __('Parent / Guardian')] as $value => $label)<label class="role-card"><input type="radio" name="registrant_role" value="{{ $value }}" @checked(old('registrant_role', 'student') === $value) required><span class="role-icon">{{ $value === 'student' ? '♙' : '♡' }}</span><strong>{{ $label }}</strong><small>{{ $value === 'student' ? __('I am registering myself') : __('I am registering a student') }}</small></label>@endforeach</div>
    </div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">2</span><div><h2>{{ __('Student & contact details') }}</h2><p>{{ __('We will only use these details for admissions follow-up.') }}</p></div></div><div class="form-section-body"><div class="form-grid two">
        <label class="field full"><span>{{ __('Student full name') }} <b class="required">*</b></span><input name="student_name" value="{{ old('student_name') }}" required autocomplete="name" placeholder="{{ __('Enter the student’s full name') }}"></label>
        <label class="field"><span>{{ __('Student email address') }} <b class="required">*</b></span><input type="email" name="student_email" value="{{ old('student_email') }}" required autocomplete="email" placeholder="student@example.com"></label>
        <label class="field"><span>{{ __('Student mobile (WhatsApp preferred)') }} <b class="required">*</b></span><input type="tel" name="student_mobile" value="{{ old('student_mobile') }}" required autocomplete="tel" placeholder="+964 7XX XXX XXXX"></label>
        <label class="field"><span>{{ __('Parent / guardian email') }}</span><input type="email" name="parent_email" value="{{ old('parent_email') }}" placeholder="parent@example.com"></label>
        <label class="field"><span>{{ __('Parent / guardian mobile') }}</span><input type="tel" name="parent_mobile" value="{{ old('parent_mobile') }}" placeholder="+964 7XX XXX XXXX"></label>
    </div></div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">3</span><div><h2>{{ __('Academic background') }}</h2><p>{{ __('Share your current qualification and result.') }}</p></div></div><div class="form-section-body"><div class="form-grid two">
        <label class="field"><span>{{ __('Certificate type') }} <b class="required">*</b></span><select name="certificate_type" id="certificate-type" required><option value="">{{ __('Select a certificate') }}</option>@foreach(\App\Models\ExhibitionRegistration::IRAQ_CERTIFICATES as $certificate)<option value="{{ $certificate }}" @selected(old('certificate_type') === $certificate)>{{ __($certificate) }}</option>@endforeach</select><small id="certificate-description" aria-live="polite"></small></label>
        <label class="field other-certificate" @if(old('certificate_type') !== 'Other') hidden @endif><span>{{ __('Other certificate type') }}</span><input name="certificate_type_other" value="{{ old('certificate_type_other') }}"></label>
        <label class="field full"><span>{{ __('Current GPA / overall result') }} ({{ __('Optional') }})</span><input name="current_result" value="{{ old('current_result') }}" placeholder="{{ __('GPA, percentage, grades, or predicted result') }}"><small>{{ __('Enter the result according to your educational system.') }}</small></label>
    </div></div></section>

    <section class="form-section"><div class="form-section-head"><span class="section-number">4</span><div><h2>{{ __('Faculties you are interested in') }}</h2><p>{{ __('Choose between 1 and 4 faculties.') }} <strong class="selection-count">0 / 4</strong></p></div></div><div class="form-section-body"><div class="faculty-grid">@foreach(\App\Models\ExhibitionRegistration::FACULTIES as $faculty)<label class="choice"><input type="checkbox" name="interested_faculties[]" value="{{ $faculty }}" @checked(in_array($faculty, old('interested_faculties', [])))><span>{{ __($faculty) }}</span></label>@endforeach</div></div></section>


    <section class="submit-panel"><div><label class="choice privacy-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>{{ __('I agree that MSA University may use these details to contact me about admissions and related opportunities.') }}</span></label><p>{{ __('Your information will be handled securely and will not be shared outside the admissions process.') }}</p></div><button class="button primary" type="submit">{{ __('Submit registration') }} <span>→</span></button></section>
</form>
@endsection

@push('head')<style>
.jordan-form .submit-panel .privacy-check:has(input:checked){color:#072841}.jordan-form .submit-panel .privacy-check:has(input:checked) span{color:#072841}.iraq-banner{max-width:1040px;margin:24px auto 0;padding:0 24px}.iraq-banner img{display:block;width:100%;height:auto;border-radius:14px}@media(max-width:560px){.iraq-banner{margin-top:16px;padding:0 16px}.iraq-banner img{border-radius:10px}}
.jordan-hero{max-width:1040px;margin:auto;padding:58px 24px 34px;display:grid;grid-template-columns:1fr 240px;align-items:center;gap:50px}.hero-copy h1{font-size:42px;line-height:1.12;letter-spacing:-.045em;margin:12px 0 16px;max-width:700px}.hero-copy>p{max-width:730px;color:#62717a;line-height:1.8}.hero-points{display:flex;gap:10px;flex-wrap:wrap;margin-top:23px}.hero-points span{font-size:11px;font-weight:700;color:#3f7032;background:#eaf3e7;padding:8px 11px;border-radius:100px}.hero-mark{aspect-ratio:1;border-radius:50%;background:linear-gradient(145deg,#0b3859,#061d30);box-shadow:0 20px 50px rgba(7,40,65,.2);display:grid;place-content:center;text-align:center;color:white;border:8px solid #fff;outline:1px solid #dce6df}.hero-mark strong{font-size:45px;letter-spacing:-.07em}.hero-mark span{font-size:11px;color:#b8d6ad}.jordan-form{padding-top:4px}.role-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.role-card{position:relative;border:1px solid #dfe7e1;border-radius:14px;padding:20px;display:grid;grid-template-columns:42px 1fr;column-gap:12px;cursor:pointer}.role-card input{position:absolute;opacity:0}.role-card:has(input:checked){border-color:#538f3f;background:#f1f7ef;box-shadow:0 0 0 2px rgba(83,143,63,.1)}.role-icon{grid-row:1/3;width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:#e6f0e3;color:#3f7032;font-size:20px}.role-card strong{font-size:13px}.role-card small{color:#74817a;margin-top:3px}.faculty-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.selection-count{color:#538f3f;margin-inline-start:8px}.egypt-question{padding:0 0 22px;border-bottom:1px solid #e6ece8}.egypt-question>span{display:block;font-size:12px;font-weight:700;margin-bottom:12px}.contact-block{padding-top:22px}.contact-block>strong,.contact-block>small{display:block}.contact-block>strong{font-size:12px}.contact-block>small{color:#74817a;font-size:10px;margin:4px 0 12px}.location-pill{margin-top:20px;padding:13px 16px;border-radius:12px;background:#f3f6f4;display:flex;align-items:center;gap:11px;width:max-content;min-width:210px}.location-pill>span{color:#538f3f}.location-pill small,.location-pill strong{display:block}.location-pill small{color:#7b8881;font-size:9px;text-transform:uppercase;letter-spacing:.06em}.location-pill strong{font-size:13px;margin-top:2px}.field small{color:#7b8881;margin-top:6px}@media(max-width:800px){.jordan-hero{grid-template-columns:1fr}.hero-mark{display:none}.faculty-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:560px){.jordan-hero{padding-top:38px}.hero-copy h1{font-size:32px}.hero-points{display:grid}.role-grid,.faculty-grid{grid-template-columns:1fr}.form-grid.two{grid-template-columns:1fr}.form-grid.two .full{grid-column:auto}}
.hero-mark{padding:28px}.hero-mark img{display:block;width:100%;max-width:150px;height:auto;margin:0 auto}.hero-mark span{font-size:14px;margin-top:8px}.other-certificate[hidden]{display:none!important}.jordan-form{font-size:16px}.jordan-form .form-section-head h2{font-size:21px}.jordan-form .form-section-head p,.jordan-form .field span,.jordan-form .egypt-question>span,.jordan-form .contact-block>strong{font-size:16px}.jordan-form .choice,.jordan-form .role-card strong,.jordan-form .location-pill strong{font-size:16px}.jordan-form input:not([type=radio]):not([type=checkbox]),.jordan-form select{font-size:16px;min-height:48px}.jordan-form .role-card,.jordan-form .choice{min-height:52px}.jordan-form .field small,.jordan-form .contact-block>small,.jordan-form .submit-panel p{font-size:13px}@media(max-width:800px){.jordan-hero{display:flex;flex-direction:column;gap:24px}.hero-mark{display:grid;width:160px;flex:none;margin:0 auto;order:-1}.hero-copy{width:100%;min-width:0}}@media(max-width:560px){.jordan-form{padding-inline:16px}.jordan-form .form-section-body,.jordan-form .form-section-head{padding:20px}.jordan-form .inline-options{display:grid;grid-template-columns:1fr}.jordan-form .role-card{padding:16px}}@media(max-width:800px){.jordan-hero{padding-top:24px}.jordan-hero .hero-mark{display:none}} </style>@endpush

@push('scripts')<script>
@php $certificateDescriptions = [
    'Iraqi Secondary School Certificate (Sixth Preparatory)' => __('The official Iraqi secondary school certificate.'),
    'Cambridge IGCSE / A Level' => __('International British qualification. Equivalency and subject requirements may apply, including A Levels for your chosen program.'),
    'Pearson Edexcel International GCSE / A Level' => __('International British qualification. Equivalency and subject requirements may apply, including A Levels for your chosen program.'),
    'American High School Diploma' => __('Equivalency, transcripts, or SAT results may be requested where applicable.'),
    'International Baccalaureate (IB Diploma)' => __('International Baccalaureate diploma from an international school.'),
    'French Baccalaureate' => __('Foreign secondary qualification subject to equivalency review.'),
    'German Abitur' => __('Foreign secondary qualification subject to equivalency review.'),
    'Other' => __('Enter your certificate name. The admissions team will review its equivalency and requirements.'),
]; @endphp
const certificateDescriptions = @json($certificateDescriptions);
const certificate=document.getElementById('certificate-type');const other=document.querySelector('.other-certificate');const otherInput=other.querySelector('input');function toggleOther(){const visible=certificate.value==='Other';other.hidden=!visible;otherInput.required=visible;document.getElementById('certificate-description').textContent=certificateDescriptions[certificate.value]||''}certificate.addEventListener('change',toggleOther);toggleOther();
const faculties=[...document.querySelectorAll('input[name="interested_faculties[]"]')];const count=document.querySelector('.selection-count');function updateFaculties(){const selected=faculties.filter(i=>i.checked);count.textContent=`${selected.length} / 4`;faculties.forEach(i=>i.disabled=!i.checked&&selected.length>=4)}faculties.forEach(i=>i.addEventListener('change',updateFaculties));updateFaculties();

</script>@endpush




