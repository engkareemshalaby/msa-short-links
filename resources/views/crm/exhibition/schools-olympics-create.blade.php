@extends('layouts.crm-public')

@section('title', 'MSA Schools Olympics Registration')

@push('head')
<meta name="description" content="Register your interest in MSA University through MSA Schools Olympics.">
<link rel="canonical" href="{{ route('crm.schools-olympics.create') }}">
<style>
.olympics-hero{max-width:1040px;margin:28px auto 10px;padding:34px 38px;border-radius:18px;background:linear-gradient(120deg,#092c48,#17456a 65%,#387b4d);color:#fff}.olympics-hero h1{font-size:clamp(30px,5vw,48px);margin:12px 0}.olympics-hero p{max-width:750px;line-height:1.7;margin:0}.olympics-hero .eyebrow{color:#a9d899;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.olympics-form .form-section-head h2{font-size:18px}.olympics-form .field>span{font-size:15px}.olympics-form .choice{font-size:15px;min-height:48px}.olympics-form .choice-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.olympics-form input[type=text],.olympics-form input[type=email],.olympics-form input[type=tel]{min-height:48px;font-size:16px}.olympics-form .form-grid.two{gap:16px}.olympics-form .form-section-head{align-items:center}.olympics-form .form-section-head p{font-size:13px;line-height:1.5}.olympics-form .submit-panel{margin-top:24px}@media(max-width:700px){.olympics-hero{margin:16px 16px 0;padding:28px 22px}.olympics-form .choice-grid,.olympics-form .form-grid.two{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<section class="olympics-hero">
    <span class="eyebrow">MSA University · Schools Olympics</span>
    <h1>MSA Schools Olympics Registration</h1>
    <p dir="rtl">من فضلك قم بتسجيل بياناتك لإبداء رغبتك في الالتحاق بجامعة MSA للعام الأكاديمي 2026/2027، وسيقوم فريق القبول بالتواصل معك قريبًا لتوضيح الخطوات التالية.</p>
</section>

@if($errors->any())<div class="crm-errors"><div class="alert danger"><strong>يرجى مراجعة البيانات المطلوبة.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif

<form class="crm-form olympics-form" method="POST" action="{{ route('crm.schools-olympics.store') }}">@csrf
    <input class="honeypot" name="company_fax" tabindex="-1" autocomplete="off">
    @include('crm.exhibition.partials.client-context-fields')
    <section class="form-section"><div class="form-section-head"><span class="section-number">1</span><div><h2>عنوان بريد إلكتروني <b class="required">*</b></h2><p>Email address</p></div></div><div class="form-section-body"><div class="form-grid"><label class="field"><span>بريدك الإلكتروني</span><input type="email" name="student_email" value="{{ old('student_email') }}" required autocomplete="email" placeholder="name@example.com"></label></div></div></section>
    <section class="form-section"><div class="form-section-head"><span class="section-number">2</span><div><h2>صفة القائم بالتسجيل - Role of the Registrant <b class="required">*</b></h2></div></div><div class="form-section-body"><div class="choice-grid">@foreach(['parent' => 'Parent - ولي أمر', 'student' => 'Student - طالب'] as $value => $label)<label class="choice"><input type="radio" name="registrant_role" value="{{ $value }}" @checked(old('registrant_role') === $value) required><span>{{ $label }}</span></label>@endforeach</div></div></section>
    <section class="form-section"><div class="form-section-head"><span class="section-number">3</span><div><h2>بيانات التواصل - Contact details</h2></div></div><div class="form-section-body"><div class="form-grid two">
        <label class="field full"><span>الاسم الرباعي - Full Name <b class="required">*</b></span><input name="student_name" value="{{ old('student_name') }}" required autocomplete="name"></label>
        <label class="field"><span>رقم الهاتف المحمول - Mobile Number <b class="required">*</b></span><input type="tel" name="student_mobile" value="{{ old('student_mobile') }}" required autocomplete="tel"></label>
        <label class="field"><span>رقم تطبيق الواتساب - WhatsApp Number</span><input type="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}"></label>
        <label class="field full"><span>رقم هاتف ولي الأمر - Parent/Guardian’s Mobile Number <b class="required">*</b></span><input type="tel" name="parent_mobile" value="{{ old('parent_mobile') }}" required></label>
    </div></div></section>
    <section class="form-section"><div class="form-section-head"><span class="section-number">4</span><div><h2>نوع الشهادة الدراسية - Certificate Type <b class="required">*</b></h2></div></div><div class="form-section-body"><div class="choice-grid">
        @foreach(['Egyptian Thanaweya Amma' => 'الثانوية العامة المصرية - Egyptian Thanaweya Amma', 'IGCSE / British Certificate' => 'الشهادة البريطانية - IGCSE / British Certificate', 'American Diploma' => 'الدبلومة الأمريكية - American Diploma', 'Arab Equivalent Certificate' => 'شهادة الدول العربية - Arab Equivalent Certificate', 'Other' => 'شهادة دولية أخرى - Other', 'Al-Azhar Secondary Certificate' => 'الثانوية الأزهرية - Al-Azhar Secondary Certificate'] as $value => $label)<label class="choice"><input type="radio" name="certificate_type" value="{{ $value }}" @checked(old('certificate_type') === $value) required><span>{{ $label }}</span></label>@endforeach
    </div></div></section>
    <section class="form-section"><div class="form-section-head"><span class="section-number">5</span><div><h2>حدد بحد أقصى 4 كليات - Select up to 4 faculties you would like to join <b class="required">*</b></h2><p><span id="faculty-count">0</span> / 4</p></div></div><div class="form-section-body"><div class="choice-grid">@foreach(\App\Models\ExhibitionRegistration::FACULTIES as $faculty)<label class="choice"><input type="checkbox" name="interested_faculties[]" value="{{ $faculty }}" @checked(in_array($faculty, old('interested_faculties', [])))><span>{{ $faculty }}</span></label>@endforeach</div></div></section>
    <section class="form-section"><div class="form-section-head"><span class="section-number">6</span><div><h2>Please select the most suitable time for our Admissions Team to contact you <b class="required">*</b></h2><p>حدد الميعاد المناسب للتواصل معكم هاتفيًا</p></div></div><div class="form-section-body"><div class="choice-grid">@foreach(\App\Models\ExhibitionRegistration::SCHOOLS_OLYMPICS_CONTACT_TIMES as $time)<label class="choice"><input type="radio" name="preferred_contact_time" value="{{ $time }}" @checked(old('preferred_contact_time') === $time) required><span>{{ $time }}</span></label>@endforeach</div></div></section>
    <div class="submit-panel"><p>سيتواصل معك فريق القبول في الموعد الذي اخترته.</p><button class="button primary" type="submit">إرسال · Submit</button></div>
</form>
@endsection

@push('scripts')
@include('crm.exhibition.partials.client-context-script')
<script>
const faculties = [...document.querySelectorAll('input[name="interested_faculties[]"]')];
function updateFaculties() {
    const selected = faculties.filter(input => input.checked).length;
    document.getElementById('faculty-count').textContent = selected;
    faculties.forEach(input => input.disabled = !input.checked && selected >= 4);
}
faculties.forEach(input => input.addEventListener('change', updateFaculties));
updateFaculties();
</script>
@endpush
