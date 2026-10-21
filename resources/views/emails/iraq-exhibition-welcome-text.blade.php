@if($messageLocale === 'ar')
@include('emails.partials.iraq-welcome-ar-text')

English version below
---------------------

@include('emails.partials.iraq-welcome-en-text')
@else
@include('emails.partials.iraq-welcome-en-text')

النسخة العربية بالأسفل
---------------------

@include('emails.partials.iraq-welcome-ar-text')
@endif

رقم التسجيل / Registration reference: {{ $referenceCode }}
