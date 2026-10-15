@extends('layouts.crm-public')
@section('title', app()->getLocale() === 'ar' ? 'تم التسجيل بنجاح' : 'Registration Successfully Received')
@section('content')
<main class="crm-form"><section class="form-section thank-card"><div class="thank-icon">✓</div>
@if(app()->getLocale() === 'ar')
    <h1>تم التسجيل بنجاح ✓</h1>
    <p class="welcome">مرحبًا بكم في جامعة أكتوبر للعلوم الحديثة والآداب (MSA)! 🎓</p>
    <p>يسعدنا تسجيلكم واهتمامكم بالانضمام إلى جامعة MSA، أعلى توثيق وشراكة بريطانية في مصر.</p>
    <div class="next-step"><strong>📩 الخطوة التالية:</strong><p>يرجى التوجه إلى البريد الإلكتروني الذي تم تسجيله في الاستمارة، حيث تم إرسال رسالة رسمية من جامعة MSA تتضمن الخطوات التالية لاستكمال عملية التسجيل والتقديم.</p><p>كما يمكنكم مراجعة صندوق الوارد وSpam / Junk للتأكد من وصول الرسالة، ثم اتباع التعليمات الواردة بها.</p></div>
    <p class="closing">نتطلع إلى الترحيب بكم في مجتمع MSA! 💙</p>
@else
    <h1>Registration Successfully Received ✓</h1>
    <p class="welcome">Welcome to October University for Modern Sciences and Arts (MSA)! 🎓</p>
    <p>We are delighted to have you register with MSA University — Egypt’s highest level of accreditation and British partnership.</p>
    <div class="next-step"><strong>📩 What’s next?</strong><p>Please check the email address you provided in the form, as an official email from MSA University has been sent to you with the next steps to complete your registration and application.</p><p>Please check your Inbox and Spam/Junk folder, then follow the instructions provided in the email.</p></div>
    <p class="closing">We look forward to welcoming you to the MSA community! 💙</p>
@endif
<a class="secondary-link" href="{{ route('crm.iraq.create') }}">{{ __('Register another student') }}</a></section></main>
@endsection
@push('head')<style>.thank-card{max-width:700px}.thank-card h1{font-size:clamp(25px,4vw,34px);color:#072841}.thank-card .welcome{font-size:19px;font-weight:700;color:#173a50}.next-step{margin:26px auto 20px;padding:23px;background:#f4f8f2;border:1px solid #d9e7d5;border-radius:15px;text-align:start}.next-step>strong{display:block;color:#072841;font-size:18px}.next-step p{margin:10px 0 0}.thank-card .closing{color:#3f7032;font-weight:700}.secondary-link{display:inline-block;color:#538f3f;font-size:13px;font-weight:700;margin-top:10px}</style>@endpush
@if(session('registration_reference'))
    @push('scripts')
        @if(is_string(config('services.meta.crm_pixel_id')) && preg_match('/^\d+$/', config('services.meta.crm_pixel_id')))
            <script>fbq('track', 'CompleteRegistration', {content_name: 'Iraq Exhibition'});</script>
        @endif
    @endpush
@endif
