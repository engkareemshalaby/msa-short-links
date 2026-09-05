<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#072841">
    <title>@yield('title') · MSA University</title>
    <link rel="icon" href="{{ asset('images/msa-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">
    <style>
        html,body{overflow-x:hidden}body{background:#f3f6f4}.crm-public-header{background:#072841;color:#fff}.crm-header-inner{max-width:1040px;margin:auto;padding:25px 24px;display:flex;align-items:center;justify-content:space-between;gap:20px}.crm-header-inner img{width:190px;max-width:55%;filter:brightness(0) invert(1)}.crm-language{border:1px solid rgba(255,255,255,.3);border-radius:10px;padding:9px 13px;font-size:12px;font-weight:700}.crm-hero{max-width:1040px;margin:auto;padding:50px 24px 24px}.crm-eyebrow{color:#538f3f;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.crm-hero h1{font-size:36px;letter-spacing:-.04em;line-height:1.2;margin:10px 0}.crm-hero p{color:#62717a;max-width:720px;line-height:1.75;margin:0}.crm-form{max-width:1040px;margin:0 auto;padding:12px 24px 70px}.crm-form input[type=url],.crm-form input[type=email],.crm-form input[type=tel]{direction:ltr;text-align:left}.form-section{background:#fff;border:1px solid #e1e8e3;border-radius:17px;margin-bottom:18px;overflow:hidden;box-shadow:0 6px 22px rgba(7,40,65,.04)}.form-section-head{padding:20px 24px;border-bottom:1px solid #e8ede9;display:flex;gap:14px;align-items:flex-start}.section-number{display:grid;place-items:center;width:31px;height:31px;border-radius:9px;background:#edf5ea;color:#3f7032;font-weight:800;flex:0 0 auto}.form-section-head h2{font-size:16px;margin:0 0 4px}.form-section-head p{font-size:11px;color:#68777f;margin:0}.form-section-body{padding:24px}.form-section .form-grid{gap:20px}.required{color:#b43d3d}.choice-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.choice{display:flex;align-items:flex-start;gap:9px;border:1px solid #e1e8e3;border-radius:11px;padding:12px;font-size:12px;cursor:pointer}.choice:has(input:checked){border-color:#538f3f;background:#edf5ea}.choice input{margin-top:2px;accent-color:#538f3f}.inline-options{display:flex;gap:10px;flex-wrap:wrap}.inline-options .choice{min-width:130px}.conditional{display:none}.submit-panel{background:#072841;color:#fff;border-radius:17px;padding:25px;display:flex;align-items:center;justify-content:space-between;gap:25px}.submit-panel p{color:rgba(255,255,255,.7);font-size:11px;line-height:1.6;margin:6px 0 0}.submit-panel .button{min-width:190px;padding:14px 20px}.privacy-check{align-items:flex-start;line-height:1.55}.honeypot{position:absolute!important;left:-10000px!important;width:1px!important;height:1px!important;overflow:hidden!important}.crm-errors{max-width:1040px;margin:0 auto 5px;padding:0 24px}.thank-card{max-width:610px;margin:90px auto;padding:45px;text-align:center}.thank-icon{width:65px;height:65px;border-radius:50%;display:grid;place-items:center;background:#edf5ea;color:#538f3f;font-size:30px;margin:0 auto 20px}.thank-card h1{margin:0 0 12px}.thank-card p{color:#62717a;line-height:1.7}@media(max-width:700px){.crm-hero{padding-top:35px}.crm-hero h1{font-size:28px}.form-section-body,.form-section-head{padding:18px}.choice-grid{grid-template-columns:1fr}.submit-panel{align-items:stretch;flex-direction:column}.submit-panel .button{width:100%}}
    </style>
    @stack('head')
</head>
<body>
<header class="crm-public-header"><div class="crm-header-inner"><img src="{{ asset('images/msa-logo-wide.png') }}" alt="MSA University"><a class="crm-language" href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a></div></header>
@yield('content')
@stack('scripts')
</body>
</html>
