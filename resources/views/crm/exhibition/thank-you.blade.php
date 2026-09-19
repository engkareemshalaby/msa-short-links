@extends('layouts.crm-public')
@section('title', __('Registration received'))
@section('content')
<main class="crm-form"><section class="form-section thank-card"><div class="thank-icon">✓</div><span class="crm-eyebrow">{{ __('Registration received') }}</span><h1>{{ __('Thank you for your interest in MSA University!') }}</h1><p>{{ __('Our Admissions Team will contact you shortly with personalized guidance for your next step.') }}</p><div class="next-step"><strong>{{ __('Your next step') }}</strong><p>{{ __('Complete your application by registering on the Study in Egypt platform.') }}</p><a class="button primary" href="https://admission.study-in-egypt.gov.eg/signup" target="_blank" rel="noopener noreferrer">{{ __('Register on Study in Egypt') }} ↗</a></div><a class="secondary-link" href="{{ route('crm.jordan.create') }}">{{ __('Register another student') }}</a></section></main>
@endsection
@push('head')<style>.next-step{margin:24px auto 16px;padding:22px;background:#f4f8f2;border:1px solid #d9e7d5;border-radius:15px}.next-step>strong{display:block;color:#072841;font-size:16px}.next-step p{margin:7px 0 17px}.secondary-link{display:inline-block;color:#538f3f;font-size:12px;font-weight:700;margin-top:5px}</style>@endpush
@if(session('registration_reference'))
    @push('scripts')
        @if(is_string(config('services.meta.crm_pixel_id')) && preg_match('/^\d+$/', config('services.meta.crm_pixel_id')))
            <script>fbq('track', 'CompleteRegistration', {content_name: 'Jordan Exhibition'});</script>
        @endif
    @endpush
@endif
