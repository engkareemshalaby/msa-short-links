@if($messageLocale === 'ar')
@include('emails.partials.iraq-welcome-ar-text')
@else
@include('emails.partials.iraq-welcome-en-text')
@endif

{{ $messageLocale === 'ar' ? 'رقم التسجيل:' : 'Registration reference:' }} {{ $referenceCode }}
