@if($messageLocale === 'ar')
مبروك يا {{ $studentName }}!

تم استلام تسجيلك في معرض جامعة MSA بالعراق (بغداد).

خطوتك التالية: أنشئ حسابك على منصة ادرس في مصر لبدء إجراءات التقديم:
https://admission.study-in-egypt.gov.eg/signup

سيتواصل معك فريق القبول عبر البريد الإلكتروني لإرشادك في الخطوات القادمة.
رقم تسجيلك: {{ $referenceCode }}
@else
Congratulations, {{ $studentName }}!

We received your registration at the MSA University exhibition in Baghdad, Iraq.

Your next step: create an account on the Study in Egypt platform to begin the application process:
https://admission.study-in-egypt.gov.eg/signup

Our Admissions Team will email you with guidance on your next steps.
Your registration reference: {{ $referenceCode }}
@endif
