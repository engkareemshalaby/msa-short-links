<!doctype html>
<html lang="{{ $messageLocale === 'ar' ? 'ar' : 'en' }}" dir="{{ $messageLocale === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $messageLocale === 'ar' ? 'خطواتك القادمة | جامعة MSA' : 'Your Next Steps | MSA University' }}</title></head>
<body style="margin:0;padding:0;background:#eef2f5;color:#17324a;font-family:Tahoma,Arial,sans-serif;line-height:1.8">
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden">{{ $messageLocale === 'ar' ? 'تم استلام تسجيلك. خطوات التقديم والروابط داخل الرسالة.' : 'Your registration has been received. Application steps and links are inside.' }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef2f5"><tr><td align="center" style="padding:28px 12px"><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#fff;border-radius:16px;overflow:hidden">
<tr><td align="center" style="background:#102b46;padding:26px 24px;border-bottom:5px solid #b9a06a"><img src="{{ asset('images/msa-logo.png') }}" alt="MSA University" width="96" style="display:block;width:96px;height:auto;margin:0 auto 10px;border:0"><div dir="ltr" style="color:#fff;font-size:13px;letter-spacing:1px">MSA UNIVERSITY · BAGHDAD 2026</div></td></tr>
<tr><td style="padding:22px 30px 0;text-align:center"><div style="display:inline-block;background:#e9f3e5;color:#376b31;border-radius:20px;padding:4px 14px;font-size:13px;font-weight:bold">{{ $messageLocale === 'ar' ? 'تم استلام التسجيل بنجاح' : 'Registration received successfully' }}</div></td></tr>
@if($isParentEmail ?? false)
@include('emails.partials.iraq-parent-welcome-'.($messageLocale === 'ar' ? 'ar' : 'en'))
@elseif($messageLocale === 'ar')
@include('emails.partials.iraq-welcome-ar')
@else
@include('emails.partials.iraq-welcome-en')
@endif
<tr><td style="background:#f7f9fa;padding:18px 30px;text-align:center;color:#657889;font-size:12px">{{ $messageLocale === 'ar' ? 'رقم التسجيل:' : 'Registration reference:' }} <strong dir="ltr" style="color:#102b46">{{ $referenceCode }}</strong><br>MSA University · Iraq Exhibition 2026</td></tr>
</table></td></tr></table>
</body></html>
