@extends('layouts.partner')
@section('title', __('My profile'))
@section('content')
@php($submission = $partner->crmSubmission)
<div class="partner-page-head"><div><h1>{{ __('My profile') }}</h1><p>{{ __('View and update your agency and contact details.') }}</p></div><a class="button ghost" href="{{ route('partner.referrals.index') }}">{{ __('Back to my students') }}</a></div>
<form class="partner-form" method="POST" action="{{ route('partner.profile.update') }}">@csrf @method('PUT')
<section class="form-section partner-card"><div class="form-section-head"><span class="section-number">1</span><div><h2>{{ __('Agency & contact') }}</h2><p>{{ __('Keep your contact information up to date.') }}</p></div></div><div class="form-section-body"><div class="form-grid">
<label class="field"><span>{{ __('Agency name') }}</span><input name="agency_name" value="{{ old('agency_name', $submission?->agency_name ?? $partner->name) }}" required></label>
<label class="field"><span>{{ __('Country') }}</span><select name="country"><option value="">{{ __('Choose a country') }}</option>@foreach(\App\Models\StudentReferral::COUNTRIES as $country)<option value="{{ $country }}" @selected(old('country', $submission?->country) === $country)>{{ __($country) }}</option>@endforeach</select></label>
<label class="field"><span>{{ __('City') }}</span><input name="city" value="{{ old('city', $submission?->city) }}"></label><label class="field"><span>{{ __('Website or social media profile') }}</span><input type="url" name="website" value="{{ old('website', $submission?->website) }}"></label>
<label class="field"><span>{{ __('Contact person name') }}</span><input name="contact_name" value="{{ old('contact_name', $submission?->contact_name ?? auth()->user()->name) }}" required></label><label class="field"><span>{{ __('Job title') }}</span><input name="job_title" value="{{ old('job_title', $submission?->job_title) }}"></label>
<label class="field"><span>{{ __('Mobile / WhatsApp') }}</span><input name="mobile" value="{{ old('mobile', $submission?->mobile) }}"></label><label class="field"><span>{{ __('Email address') }}</span><input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></label>
</div></div></section>
<section class="form-section partner-card"><div class="form-section-head"><span class="section-number">2</span><div><h2>{{ __('Password') }}</h2><p>{{ __('Leave empty to keep the current password.') }}</p></div></div><div class="form-section-body"><div class="form-grid"><label class="field"><span>{{ __('New password') }}</span><input type="password" name="password"></label><label class="field"><span>{{ __('Confirm password') }}</span><input type="password" name="password_confirmation"></label></div></div></section>
<div class="submit-panel"><span>{{ __('Only account and contact details can be changed here.') }}</span><button class="button primary">{{ __('Save changes') }}</button></div></form>
@endsection
