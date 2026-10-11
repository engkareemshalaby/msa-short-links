<!doctype html>
<html lang="{{ $messageLocale === 'ar' ? 'ar' : 'en' }}" dir="{{ $messageLocale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $messageLocale === 'ar' ? 'تم استلام تسجيلك في معرض MSA بالعراق' : 'Your MSA Iraq exhibition registration' }}</title>
</head>
<body style="margin:0;padding:0;background:#eef2f0;color:#102c43;font-family:Arial,Tahoma,sans-serif;line-height:1.7">
<div style="display:none;font-size:1px;color:#eef2f0;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden">{{ $messageLocale === 'ar' ? 'تهانينا! سجّل الآن في منصة ادرس في مصر لاستكمال الخطوة التالية.' : 'Congratulations! Register on Study in Egypt for your next step.' }}</div>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#eef2f0"><tr><td align="center" style="padding:28px 12px">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="width:100%;max-width:600px;background:#ffffff;border-radius:18px;overflow:hidden">
<tr><td style="background:#102b46;padding:28px 28px 25px;text-align:center">
<img src="{{ asset('images/msa-logo.png') }}" alt="MSA University" width="96" style="display:block;width:96px;max-width:100%;height:auto;margin:0 auto 14px;border:0">
<div style="color:#e7b46a;font-size:12px;font-weight:bold;letter-spacing:1px">MSA UNIVERSITY · BAGHDAD</div>
</td></tr>
<tr><td style="padding:32px 30px 12px;text-align:{{ $messageLocale === 'ar' ? 'right' : 'left' }}">
<div style="display:inline-block;background:#e9f3e5;color:#3f7032;border-radius:100px;padding:6px 14px;font-size:13px;font-weight:bold">{{ $messageLocale === 'ar' ? '✓ تم استلام التسجيل' : '✓ Registration received' }}</div>
<h1 style="font-size:28px;line-height:1.35;color:#102b46;margin:18px 0 12px">{{ $messageLocale === 'ar' ? 'مبروك يا ' : 'Congratulations, ' }}{{ $studentName }}!</h1>
<p style="font-size:16px;color:#455967;margin:0 0 22px">{{ $messageLocale === 'ar' ? 'يسعدنا تسجيلك في معرض جامعة MSA بالعراق (بغداد). نتطلع لمساعدتك في رحلتك الدراسية.' : 'We are delighted you registered at the MSA University exhibition in Baghdad, Iraq. We look forward to helping you on your study journey.' }}</p>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f3f7f1;border:1px solid #dce9d8;border-radius:12px"><tr><td style="padding:20px">
<div style="color:#3f7032;font-size:13px;font-weight:bold;margin-bottom:7px">{{ $messageLocale === 'ar' ? 'خطوتك التالية' : 'YOUR NEXT STEP' }}</div>
<div style="font-size:18px;font-weight:bold;color:#102b46;margin-bottom:8px">{{ $messageLocale === 'ar' ? 'سجّل في منصة ادرس في مصر' : 'Register on Study in Egypt' }}</div>
<div style="font-size:14px;color:#455967">{{ $messageLocale === 'ar' ? 'أنشئ حسابك على المنصة الرسمية لبدء إجراءات التقديم.' : 'Create your account on the official platform to begin the application process.' }}</div>
</td></tr></table>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0"><tr><td bgcolor="#538f3f" style="border-radius:9px"><a href="https://admission.study-in-egypt.gov.eg/signup" style="display:inline-block;padding:13px 24px;color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none">{{ $messageLocale === 'ar' ? 'ابدأ التسجيل في ادرس في مصر ←' : 'Register on Study in Egypt →' }}</a></td></tr></table>
<p style="font-size:14px;color:#455967;margin:0 0 20px">{{ $messageLocale === 'ar' ? 'إذا لم يعمل الزر، افتح الرابط التالي:' : 'If the button does not work, open this link:' }}<br><a href="https://admission.study-in-egypt.gov.eg/signup" style="color:#3f7032;word-break:break-all">https://admission.study-in-egypt.gov.eg/signup</a></p>
<p style="font-size:15px;color:#455967;margin:0 0 20px">{{ $messageLocale === 'ar' ? 'سيتواصل معك فريق القبول عبر البريد الإلكتروني لإرشادك في الخطوات القادمة.' : 'Our Admissions Team will email you with guidance on your next steps.' }}</p>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-top:1px solid #e3ebe6"><tr><td style="padding:16px 0;color:#667984;font-size:13px">{{ $messageLocale === 'ar' ? 'رقم تسجيلك' : 'Your registration reference' }}</td><td dir="ltr" style="padding:16px 0;color:#102b46;font-size:15px;font-weight:bold;text-align:{{ $messageLocale === 'ar' ? 'left' : 'right' }}">{{ $referenceCode }}</td></tr></table>
</td></tr>
<tr><td style="background:#f8faf9;padding:20px 30px;text-align:center;color:#667984;font-size:12px">MSA University<br>{{ $messageLocale === 'ar' ? 'تم إرسال هذه الرسالة بعد تسجيلك في معرض العراق.' : 'This email was sent after your Iraq exhibition registration.' }}</td></tr>
</table>
</td></tr></table>
</body></html>
