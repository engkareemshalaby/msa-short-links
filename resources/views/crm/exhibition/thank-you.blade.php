@extends('layouts.crm-public')
@section('title', __('Registration received'))
@section('content')
<main class="crm-form"><section class="form-section thank-card"><div class="thank-icon">✓</div><span class="crm-eyebrow">{{ __('Registration received') }}</span><h1>{{ __('Thank you for your interest in MSA University!') }}</h1><p>{{ __('Our Admissions Team will contact you shortly with personalized guidance for your next step.') }}</p>@if(session('registration_reference'))<div class="reference-box"><small>{{ __('Your reference number') }}</small><strong dir="ltr">{{ session('registration_reference') }}</strong></div>@endif<a class="button primary" href="{{ route('crm.jordan.create') }}">{{ __('Register another student') }}</a></section></main>
@endsection
@push('head')<style>.reference-box{background:#f3f7f1;border:1px solid #dce8d8;border-radius:13px;padding:14px;margin:22px auto;max-width:310px}.reference-box small,.reference-box strong{display:block}.reference-box small{color:#708078;margin-bottom:5px}.reference-box strong{color:#072841;letter-spacing:.08em}</style>@endpush
