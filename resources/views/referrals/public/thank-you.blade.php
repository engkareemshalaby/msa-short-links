@extends('layouts.crm-public')

@section('title', __('Student registered'))

@section('content')
<div class="form-section thank-card">
    <div class="thank-icon">✓</div>
    <h1>{{ __('Student registered successfully') }}</h1>
    <p>{{ __('Keep this referral number for future reference.') }}</p>
    <div class="reference-code">{{ $reference }}</div>
    <a class="button primary" href="{{ route('student-referrals.create', $partner->access_token) }}">＋ {{ __('Register another student') }}</a>
</div>
@endsection

@push('head')<style>.reference-code{display:inline-block;margin:8px 0 25px;padding:13px 20px;border-radius:12px;background:#edf5ea;color:#315f27;font-size:20px;font-weight:800;letter-spacing:.04em;direction:ltr}</style>@endpush
