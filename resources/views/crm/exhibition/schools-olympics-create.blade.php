@extends('layouts.crm-public')
@section('title', __('schools_olympics.title'))
@push('head')
<meta name="description" content="{{ __('schools_olympics.description') }}">
<link rel="canonical" href="{{ app()->getLocale() === 'ar' ? route('crm.schools-olympics.create.ar') : route('crm.schools-olympics.create') }}">
<style>.olympics-header{max-width:1040px;margin:28px auto 0;padding:0 24px}.olympics-header img{display:block;width:100%;height:auto;border-radius:16px}.olympics-hero{max-width:992px;margin:12px auto 10px;padding:28px 32px;border-radius:18px;background:linear-gradient(120deg,#092c48,#17456a 65%,#387b4d);color:white}.olympics-hero p{max-width:850px;font-size:clamp(18px,2vw,24px);font-weight:700;line-height:1.5;margin:0}.olympics-form .choice-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.olympics-form .choice,.olympics-form .field span{font-size:15px}.olympics-form input[type=email],.olympics-form input[type=tel],.olympics-form input[name=student_name]{min-height:48px;font-size:16px}@media(max-width:700px){.olympics-header{margin:16px auto 0;padding:0 16px}.olympics-header img{border-radius:12px}.olympics-hero{margin:12px 16px 0;padding:22px}.olympics-form .choice-grid,.olympics-form .form-grid.two{grid-template-columns:1fr}}</style>
@endpush
@section('content')
<div class="olympics-header"><img src="{{ asset('images/olympics-header.jpeg') }}" alt="MSA International Schools' Olympics" width="1600" height="400" fetchpriority="high"></div>
<section class="olympics-hero"><p>{{ __('schools_olympics.description') }}</p></section>
@if($errors->any())<div class="crm-errors"><div class="alert danger"><strong>{{ __('schools_olympics.error') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
<form class="crm-form olympics-form" method="POST" action="{{ route(app()->getLocale() === 'ar' ? 'crm.schools-olympics.store.ar' : 'crm.schools-olympics.store') }}">@csrf
<input class="honeypot" name="company_fax" tabindex="-1" autocomplete="off">
@include('crm.exhibition.partials.client-context-fields')
<section class="form-section"><div class="form-section-head"><span class="section-number">1</span><h2>{{ __('schools_olympics.email') }} <b class="required">*</b></h2></div><div class="form-section-body"><label class="field"><span>{{ __('schools_olympics.email') }}</span><input type="email" name="student_email" value="{{ old('student_email') }}" required autocomplete="email" placeholder="name@example.com"></label></div></section>
<section class="form-section"><div class="form-section-head"><span class="section-number">2</span><h2>{{ __('schools_olympics.role') }} <b class="required">*</b></h2></div><div class="form-section-body choice-grid">@foreach(['parent','student'] as $role)<label class="choice"><input type="radio" name="registrant_role" value="{{ $role }}" @checked(old('registrant_role') === $role) required><span>{{ __('schools_olympics.'.$role) }}</span></label>@endforeach</div></section>
<section class="form-section"><div class="form-section-head"><span class="section-number">3</span><h2>{{ __('schools_olympics.contact_details') }}</h2></div><div class="form-section-body form-grid two">
@foreach(['student_name' => 'full_name', 'student_mobile' => 'mobile', 'whatsapp_number' => 'whatsapp', 'parent_mobile' => 'parent_mobile'] as $name => $label)
<label class="field {{ in_array($name,['student_name','parent_mobile']) ? 'full' : '' }}"><span>{{ __('schools_olympics.'.$label) }} @if($name !== 'whatsapp_number')<b class="required">*</b>@endif</span><input type="{{ $name === 'student_name' ? 'text' : 'tel' }}" name="{{ $name }}" value="{{ old($name) }}" @if($name !== 'whatsapp_number') required @endif></label>
@endforeach
</div></section>
<section class="form-section"><div class="form-section-head"><span class="section-number">4</span><h2>{{ __('schools_olympics.certificate') }} <b class="required">*</b></h2></div><div class="form-section-body choice-grid">@foreach(\App\Models\ExhibitionRegistration::SCHOOLS_OLYMPICS_CERTIFICATES as $certificate)<label class="choice"><input type="radio" name="certificate_type" value="{{ $certificate }}" @checked(old('certificate_type') === $certificate) required><span>{{ __('schools_olympics.certificates.'.$certificate) }}</span></label>@endforeach</div></section>
<section class="form-section"><div class="form-section-head"><span class="section-number">5</span><div><h2>{{ __('schools_olympics.faculties') }} <b class="required">*</b></h2><p><span id="faculty-count">0</span> / 4</p></div></div><div class="form-section-body choice-grid">@foreach(\App\Models\ExhibitionRegistration::FACULTIES as $faculty)<label class="choice"><input type="checkbox" name="interested_faculties[]" value="{{ $faculty }}" @checked(in_array($faculty, old('interested_faculties', [])))><span>{{ __($faculty) }}</span></label>@endforeach</div></section>
<div class="submit-panel"><p>{{ __('schools_olympics.submit_note') }}</p><button class="button primary" type="submit">{{ __('schools_olympics.submit') }}</button></div>
</form>
@endsection
@push('scripts')
@include('crm.exhibition.partials.client-context-script')
<script>
const faculties = [...document.querySelectorAll('input[name="interested_faculties[]"]')];
function updateFaculties(){const count=faculties.filter(input=>input.checked).length;document.getElementById('faculty-count').textContent=count;faculties.forEach(input=>input.disabled=!input.checked&&count>=4)}
faculties.forEach(input=>input.addEventListener('change',updateFaculties));updateFaculties();
</script>
@endpush
