@if($isParentEmail ?? false)
@include('emails.partials.general-parent-welcome-'.($messageLocale === 'ar' ? 'ar' : 'en').'-text')
@elseif($messageLocale === 'ar')
@include('emails.partials.general-welcome-ar-text')
@else
@include('emails.partials.general-welcome-en-text')
@endif

{{ $messageLocale === 'ar' ? 'رقم التسجيل:' : 'Registration reference:' }} {{ $referenceCode }}
