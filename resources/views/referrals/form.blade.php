@extends('layouts.app')
@section('title', __('Register a student'))
@section('subtitle', __('Register a student and assign the responsible partner.'))
@section('content')
<form class="card form-card" method="POST" action="{{ route('crm.student-referrals.store') }}" enctype="multipart/form-data">@csrf
<div class="card-header"><div><h2>{{ __('Student details') }}</h2><p>{{ __('The partner assignment is required for follow-up and reporting.') }}</p></div></div>
<div class="card-body"><div class="form-grid">
<label class="field full"><span>{{ __('Responsible partner') }} *</span><select name="recruitment_partner_id" required><option value="">{{ __('Choose a partner') }}</option>@foreach($partners as $partner)<option value="{{ $partner->id }}" @selected((string)old('recruitment_partner_id') === (string)$partner->id)>{{ $partner->name }}</option>@endforeach</select></label>
<label class="field full"><span>{{ __('Student full name') }} *</span><input name="student_name" value="{{ old('student_name') }}" required></label>
<label class="field"><span>{{ __('Mobile / WhatsApp') }} *</span><input name="mobile" value="{{ old('mobile') }}" required></label><label class="field"><span>{{ __('Student email') }} ({{ __('Optional') }})</span><input type="email" name="email" value="{{ old('email') }}"></label>
<label class="field"><span>{{ __('Nationality') }} *</span><select name="nationality" required><option value="">{{ __('Choose a country') }}</option>@foreach(\App\Models\StudentReferral::COUNTRIES as $country)<option value="{{ $country }}" @selected(old('nationality') === $country)>{{ __($country) }}</option>@endforeach</select></label>
<label class="field"><span>{{ __('Requested faculty / program') }} *</span><select name="desired_program" required><option value="">{{ __('Choose a program') }}</option>@foreach(\App\Models\StudentReferral::PROGRAMS as $program)<option value="{{ $program }}" @selected(old('desired_program') === $program)>{{ __($program) }}</option>@endforeach</select></label>
<label class="field"><span>{{ __('Passport copy') }}</span><input type="file" name="passport" accept=".jpg,.jpeg,.png,.pdf"></label>
<label class="checkbox full"><input type="checkbox" name="consent" value="1" @checked(old('consent')) required><span>{{ __('I confirm that the student agreed to share these details with MSA University for admission follow-up.') }} *</span></label><input type="text" name="company_fax" class="sr-only" tabindex="-1" autocomplete="off">
</div></div><div class="card-body form-footer"><a class="button" href="{{ route('crm.student-referrals.index') }}">{{ __('Cancel') }}</a><button class="button primary">{{ __('Register student') }}</button></div></form>
@endsection
