<!doctype html>
<html lang="{{ $messageLocale === 'ar' ? 'ar' : 'en' }}" dir="{{ $messageLocale === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $messageLocale === 'ar' ? 'تم استلام بيانات التواصل | معرض العراق 2026' : 'We received your contact details | Iraq Exhibition 2026' }}</title></head>
<body style="margin:0;padding:0;background:#eef2f5;color:#17324a;font-family:Tahoma,Arial,sans-serif;line-height:1.8">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef2f5"><tr><td align="center" style="padding:28px 12px">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#fff;border-radius:16px;overflow:hidden">
<tr><td align="center" style="background:#102b46;padding:25px 20px;border-bottom:5px solid #538f3f"><img src="{{ asset('images/msa-logo.png') }}" alt="MSA University" width="96" style="display:block;width:96px;height:auto;margin:0 auto 10px;border:0"><div dir="ltr" style="color:#fff;font-size:13px;letter-spacing:1px">MSA UNIVERSITY · BAGHDAD 2026</div></td></tr>
<tr><td style="padding:30px 32px;text-align:{{ $messageLocale === 'ar' ? 'right' : 'left' }}">
@if($messageLocale === 'ar')
<p style="margin:0 0 16px;font-size:16px">عزيزي/عزيزتي {{ $contactName }}،</p>
<h1 style="margin:0 0 16px;font-size:22px;color:#102b46">تم استلام بيانات التواصل الخاصة بك</h1>
<p style="margin:0 0 16px;font-size:15px">شكرًا لتسجيل بياناتك في معرض العراق 2026 واهتمامك بجامعة أكتوبر للعلوم الحديثة والآداب (MSA).</p>
<p style="margin:0 0 16px;font-size:15px">تم حفظ بياناتك بنجاح، وقد يتواصل معك فريقنا إذا احتجنا إلى متابعة استفسارك.</p>
<p style="margin:0;font-size:15px">مع خالص التحية،<br>فريق جامعة MSA</p>
@else
<p style="margin:0 0 16px;font-size:16px">Dear {{ $contactName }},</p>
<h1 style="margin:0 0 16px;font-size:22px;color:#102b46">We received your contact details</h1>
<p style="margin:0 0 16px;font-size:15px">Thank you for registering your details at the Iraq Exhibition 2026 and for your interest in October University for Modern Sciences and Arts (MSA).</p>
<p style="margin:0 0 16px;font-size:15px">Your details have been saved successfully. Our team may contact you if we need to follow up on your enquiry.</p>
<p style="margin:0;font-size:15px">Warm regards,<br>MSA University Team</p>
@endif
</td></tr>
</table></td></tr></table>
</body></html>
