@extends('layouts.crm-public')

@section('title', __('Register a student'))

@section('content')
<div class="crm-hero referral-hero">
    <span class="crm-eyebrow">{{ __('MSA University student referral') }}</span>
    <h1>{{ __('Register a student') }}</h1>
    <p>{{ __('Partner: :name', ['name' => $partner->name]) }}</p>
</div>

@if($errors->any())
    <div class="crm-errors"><div class="alert danger"><strong>{{ __('Please review the highlighted information.') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif

<form class="crm-form referral-form" method="POST" action="{{ route('student-referrals.store', $partner->access_token) }}" enctype="multipart/form-data">
    @csrf
    <label class="honeypot" aria-hidden="true">Company fax<input name="company_fax" tabindex="-1" autocomplete="off"></label>

    <section class="form-section">
        <div class="form-section-head"><span class="section-number">1</span><div><h2>{{ __('Student details') }}</h2><p>{{ __('Only a few details are needed to register the referral.') }}</p></div></div>
        <div class="form-section-body"><div class="form-grid">
            <label class="field full"><span>{{ __('Student full name') }} <b class="required">*</b></span><input name="student_name" value="{{ old('student_name') }}" maxlength="255" required autocomplete="name"></label>
            <label class="field"><span>{{ __('Mobile / WhatsApp') }} <b class="required">*</b></span><input type="tel" name="mobile" value="{{ old('mobile') }}" required autocomplete="tel" inputmode="tel"></label>
            <label class="field"><span>{{ __('Nationality') }} <b class="required">*</b></span><input name="nationality" value="{{ old('nationality') }}" maxlength="120" required></label>
            <label class="field full"><span>{{ __('Requested faculty / program') }} <b class="required">*</b></span><select name="desired_program" required><option value="">{{ __('Choose a program') }}</option>@foreach(\App\Models\StudentReferral::PROGRAMS as $program)<option value="{{ $program }}" @selected(old('desired_program') === $program)>{{ __($program) }}</option>@endforeach</select></label>
        </div></div>
    </section>

    <section class="form-section">
        <div class="form-section-head"><span class="section-number">2</span><div><h2>{{ __('Optional document') }}</h2><p>{{ __('The passport can be added now or provided later.') }}</p></div></div>
        <div class="form-section-body"><div class="form-grid">
            <label class="field full"><span>{{ __('Passport copy') }}</span><input type="file" name="passport" accept=".jpg,.jpeg,.png,.pdf"><small>{{ __('JPG, PNG or PDF, up to 5 MB.') }}</small></label>
        </div></div>
    </section>

    <div class="submit-panel"><div>
        <label class="checkbox privacy-check"><input type="checkbox" name="consent" value="1" @checked(old('consent')) required><span>{{ __('I confirm that the student agreed to share these details with MSA University for admission follow-up.') }} <b class="required">*</b></span></label>
        <p>{{ __('The partner name and submission time are recorded automatically.') }}</p>
    </div><button class="button primary" type="submit">{{ __('Register student') }} <span>→</span></button></div>
</form>
@endsection

@push('head')
<style>.referral-hero{padding-bottom:18px}.referral-form{max-width:780px}.referral-hero{max-width:780px}.referral-form .form-section:focus-within{border-color:#c6d9c0;box-shadow:0 9px 28px rgba(83,143,63,.08)}</style>
@endpush
