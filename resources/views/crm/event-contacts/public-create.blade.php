@extends('layouts.crm-public')
@section('title', __('Iraq Exhibition Contact Registration'))
@section('content')
<main class="iraq-contact-page">
    <section class="iraq-contact-hero" aria-labelledby="iraq-contact-title">
        <div class="iraq-contact-hero-copy">
            <span class="iraq-contact-eyebrow">MSA UNIVERSITY <span aria-hidden="true">/</span> BAGHDAD 2026</span>
            <h1 id="iraq-contact-title">{{ __('Iraq Exhibition Contact Registration') }}</h1>
            <p>{{ __('Share your contact details with the MSA University team at the Iraq Exhibition.') }}</p>
        </div>
        <picture class="iraq-contact-banner">
            <source media="(max-width: 700px)" srcset="{{ asset(app()->getLocale() === 'ar' ? 'images/iraq-exhibition-ar.jpeg' : 'images/iraq-exhibition-en.jpeg') }}">
            <img src="{{ asset(app()->getLocale() === 'ar' ? 'images/iraq-exhibition-ar.jpeg' : 'images/iraq-exhibition-en.jpeg') }}" alt="{{ __('Iraq Exhibition') }}" width="1600" height="400">
        </picture>
    </section>

    <section class="iraq-contact-card" aria-labelledby="iraq-contact-form-title">
        <div class="iraq-contact-card-head">
            <div><span class="iraq-contact-step">01 / 01</span><h2 id="iraq-contact-form-title">{{ __('Your contact details') }}</h2><p>{{ __('Complete the fields below so our team can reach you.') }}</p></div>
            <span class="iraq-contact-required">* {{ __('Required') }}</span>
        </div>
        <form method="POST" action="{{ route('crm.event-contacts.register.store') }}">
            @csrf
            <div class="iraq-contact-fields">
                <label class="iraq-contact-field"><span>{{ __('Name') }} <b>*</b></span><input name="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" required placeholder="{{ __('Your full name') }}" @error('name') aria-invalid="true" @enderror></label>
                <label class="iraq-contact-field"><span>{{ __('Email address') }} <b>*</b></span><input type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required placeholder="name@example.com" @error('email') aria-invalid="true" @enderror></label>
                <label class="iraq-contact-field"><span>{{ __('Phone number') }} <small>{{ __('Optional') }}</small></span><input type="tel" name="phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel" dir="ltr" placeholder="+964 7XX XXX XXXX" @error('phone') aria-invalid="true" @enderror></label>
                <label class="iraq-contact-field"><span>{{ __('Organization') }} <small>{{ __('Optional') }}</small></span><input name="organization_name" value="{{ old('organization_name') }}" maxlength="255" autocomplete="organization" @error('organization_name') aria-invalid="true" @enderror></label>
                <label class="iraq-contact-field"><span>{{ __('Job title') }} <small>{{ __('Optional') }}</small></span><input name="job_title" value="{{ old('job_title') }}" maxlength="255" autocomplete="organization-title" @error('job_title') aria-invalid="true" @enderror></label>
                <label class="iraq-contact-field iraq-contact-notes"><span>{{ __('Notes') }} <small>{{ __('Optional') }}</small></span><textarea name="notes" maxlength="2000" rows="4" placeholder="{{ __('Anything you would like our team to know?') }}" @error('notes') aria-invalid="true" @enderror>{{ old('notes') }}</textarea></label>
            </div>
            <div class="iraq-contact-honeypot" aria-hidden="true"><label>Fax<input name="company_fax" tabindex="-1" autocomplete="off"></label></div>
            <div class="iraq-contact-footer">
                <label class="iraq-contact-consent"><input type="checkbox" name="consent" value="1" @checked(old('consent')) required><span>{{ __('I agree to be contacted by MSA University using the details provided.') }}</span></label>
                @if($errors->any())<div class="iraq-contact-errors" role="alert"><strong>{{ __('Please check the following fields:') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <button class="iraq-contact-submit" type="submit">{{ __('Submit registration') }} <span aria-hidden="true">{{ app()->getLocale() === 'ar' ? '?' : '?' }}</span></button>
            </div>
        </form>
    </section>
</main>
@endsection
@push('head')
<style>
.iraq-contact-page{max-width:1040px;margin:0 auto;padding:32px 24px 76px;color:#102b46}.iraq-contact-hero{background:linear-gradient(135deg,#072841,#123b5c);border-radius:22px;overflow:hidden;box-shadow:0 15px 35px rgba(7,40,65,.13)}.iraq-contact-hero-copy{padding:30px 36px 26px;color:#fff}.iraq-contact-eyebrow{display:inline-flex;gap:9px;align-items:center;color:#bfe5ae;font-size:11px;font-weight:800;letter-spacing:.12em}.iraq-contact-hero h1{font-size:clamp(27px,3.4vw,42px);line-height:1.18;letter-spacing:-.025em;margin:13px 0 10px;color:#fff}.iraq-contact-hero p{max-width:650px;color:#d9e7ed;font-size:15px;line-height:1.7;margin:0}.iraq-contact-banner{display:block;background:#edf4f6}.iraq-contact-banner img{display:block;width:100%;height:auto}.iraq-contact-card{position:relative;margin:-1px auto 0;background:#fff;border:1px solid #e1e9e4;border-radius:0 0 22px 22px;box-shadow:0 16px 42px rgba(7,40,65,.07);overflow:hidden}.iraq-contact-card-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;padding:32px 36px 23px;border-bottom:1px solid #e8eee9}.iraq-contact-step{color:#538f3f;font-size:11px;font-weight:900;letter-spacing:.15em}.iraq-contact-card h2{font-size:23px;line-height:1.25;margin:8px 0 5px;color:#072841}.iraq-contact-card-head p{margin:0;color:#647680;font-size:13px;line-height:1.6}.iraq-contact-required{white-space:nowrap;color:#68777f;font-size:12px}.iraq-contact-fields{padding:28px 36px 12px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:23px 20px}.iraq-contact-field{display:flex;flex-direction:column;gap:8px;min-width:0;color:#173b50;font-size:13px;font-weight:700}.iraq-contact-field span{display:flex;align-items:center;gap:6px}.iraq-contact-field b{color:#bd473d}.iraq-contact-field small{font-size:11px;color:#7b8a91;font-weight:500}.iraq-contact-field input,.iraq-contact-field textarea{box-sizing:border-box;width:100%;min-height:49px;border:1px solid #d5e0da;border-radius:11px;background:#fbfdfb;color:#173b50;font:inherit;font-size:14px;font-weight:400;padding:12px 15px;outline:none;transition:border-color .15s,box-shadow .15s}.iraq-contact-field textarea{min-height:112px;resize:vertical;line-height:1.6}.iraq-contact-field input:focus,.iraq-contact-field textarea:focus{border-color:#538f3f;box-shadow:0 0 0 3px rgba(83,143,63,.13);background:#fff}.iraq-contact-field [aria-invalid=true]{border-color:#bd473d}.iraq-contact-notes{grid-column:1/-1}.iraq-contact-footer{margin:15px 36px 34px;padding-top:23px;border-top:1px solid #e8eee9}.iraq-contact-consent{display:flex;align-items:flex-start;gap:10px;color:#445c68;font-size:13px;line-height:1.6;cursor:pointer}.iraq-contact-consent input{width:18px;height:18px;margin:2px 0 0;flex:none;accent-color:#538f3f}.iraq-contact-submit{margin-top:23px;border:0;border-radius:11px;background:#538f3f;color:#fff;min-height:51px;padding:13px 24px;display:inline-flex;align-items:center;justify-content:space-between;gap:30px;font:inherit;font-size:14px;font-weight:800;cursor:pointer;box-shadow:0 7px 18px rgba(83,143,63,.2)}.iraq-contact-submit:hover{background:#437a31}.iraq-contact-submit:focus-visible{outline:3px solid #072841;outline-offset:3px}.iraq-contact-errors{margin-top:16px;border:1px solid #f0ceca;background:#fff7f6;color:#9c302a;padding:13px 16px;border-radius:10px;font-size:13px}.iraq-contact-errors ul{margin:7px 0 0;padding-inline-start:22px}.iraq-contact-honeypot{position:absolute!important;left:-10000px!important;width:1px!important;height:1px!important;overflow:hidden!important}@media(max-width:700px){.iraq-contact-page{padding:16px 14px 48px}.iraq-contact-hero{border-radius:17px}.iraq-contact-hero-copy{padding:25px 23px 24px}.iraq-contact-hero h1{font-size:29px}.iraq-contact-hero p{font-size:13px}.iraq-contact-banner{display:block}.iraq-contact-card{border-radius:0 0 17px 17px}.iraq-contact-card-head{padding:25px 21px 20px;align-items:flex-start;flex-direction:column;gap:8px}.iraq-contact-card h2{font-size:21px}.iraq-contact-fields{grid-template-columns:1fr;padding:24px 21px 8px;gap:19px}.iraq-contact-footer{margin:14px 21px 26px}.iraq-contact-submit{width:100%}}
</style>
@endpush
