@extends('layouts.crm-public')
@section('title', __('Iraq Exhibition Registration Received'))
@section('content')
<main class="iraq-contact-confirmation">
    <div class="iraq-contact-confirmation-mark" aria-hidden="true">&#10003;</div>
    <span class="iraq-contact-confirmation-eyebrow">MSA UNIVERSITY / BAGHDAD 2026</span>
    <h1>{{ __('Iraq Exhibition Registration Received') }}</h1>
    <p>{{ __('Thank you for sharing your details at the Iraq Exhibition. Our team will contact you if follow-up is needed.') }}</p>
    <a href="{{ route('crm.event-contacts.register') }}">{{ __('Register another contact') }}</a>
</main>
@endsection
@push('head')
<style>.iraq-contact-confirmation{box-sizing:border-box;max-width:640px;margin:80px auto;padding:46px 36px;text-align:center;background:#fff;border:1px solid #e1e9e4;border-radius:22px;box-shadow:0 16px 42px rgba(7,40,65,.08)}.iraq-contact-confirmation-mark{width:68px;height:68px;display:grid;place-items:center;margin:0 auto 24px;border-radius:50%;background:#eaf4e5;color:#538f3f;font-size:32px;font-weight:800}.iraq-contact-confirmation-eyebrow{color:#538f3f;font-size:11px;font-weight:900;letter-spacing:.1em}.iraq-contact-confirmation h1{margin:12px 0;color:#072841;font-size:clamp(26px,4vw,35px);line-height:1.25}.iraq-contact-confirmation p{color:#58707e;line-height:1.75;margin:0 auto 24px;max-width:460px}.iraq-contact-confirmation a{color:#416e32;font-size:13px;font-weight:700;text-decoration:underline}@media(max-width:700px){.iraq-contact-confirmation{margin:24px 14px;padding:38px 23px;border-radius:17px}}</style>
@endpush
