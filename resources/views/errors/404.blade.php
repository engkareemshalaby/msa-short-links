<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#072841">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Page not found') }} · MSA Go</title>
    <link rel="icon" href="{{ asset('images/msa-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--navy:#072841;--green:#538f3f;--cream:#f6f0df;--muted:#68777f;--line:#dfe6df}*{box-sizing:border-box}html{font-family:Inter,Tajawal,sans-serif;color:var(--navy);background:var(--cream)}html[dir=rtl]{font-family:Tajawal,Inter,sans-serif}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:28px;background:radial-gradient(circle at 15% 15%,rgba(83,143,63,.13),transparent 29%),radial-gradient(circle at 85% 80%,rgba(7,40,65,.09),transparent 28%),var(--cream)}a{text-decoration:none;color:inherit}.error-shell{width:min(100%,1040px);min-height:600px;display:grid;grid-template-columns:1.05fr .95fr;background:#fff;border:1px solid rgba(7,40,65,.11);border-radius:26px;overflow:hidden;box-shadow:0 24px 70px rgba(7,40,65,.12)}.error-copy{padding:58px;display:flex;flex-direction:column}.brand{display:flex;align-items:center;gap:12px;width:max-content}.brand img{width:48px;height:44px;object-fit:contain}.brand strong,.brand small{display:block}.brand strong{font-size:16px}.brand small{font-size:11px;color:var(--muted);margin-top:2px}.copy-body{margin:auto 0}.eyebrow{display:inline-flex;align-items:center;gap:8px;color:var(--green);font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.eyebrow:before{content:"";width:28px;height:3px;border-radius:8px;background:var(--green)}h1{font-size:clamp(34px,5vw,55px);line-height:1.08;letter-spacing:-.045em;margin:15px 0 18px}p{max-width:510px;color:var(--muted);font-size:15px;line-height:1.8;margin:0}.actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:30px}.button{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid var(--line);border-radius:11px;padding:12px 17px;background:#fff;font-size:13px;font-weight:700;cursor:pointer}.button.primary{background:var(--green);border-color:var(--green);color:#fff}.button:hover{transform:translateY(-1px);box-shadow:0 7px 18px rgba(7,40,65,.1)}.help{font-size:11px;color:#87938c;margin-top:20px}.error-visual{position:relative;display:grid;place-items:center;overflow:hidden;background:var(--navy);color:#fff}.error-visual:before,.error-visual:after{content:"";position:absolute;border:1px solid rgba(83,143,63,.42);border-radius:50%}.error-visual:before{width:430px;height:430px;inset-block-start:-160px;inset-inline-end:-140px}.error-visual:after{width:310px;height:310px;inset-block-end:-130px;inset-inline-start:-110px}.visual-content{position:relative;z-index:1;text-align:center}.code{font-size:clamp(100px,15vw,168px);font-weight:800;letter-spacing:-.09em;line-height:.85;color:#fff;text-shadow:0 10px 30px rgba(0,0,0,.18)}.route{width:190px;height:58px;margin:34px auto 0;position:relative}.route:before{content:"";position:absolute;inset-inline:8px;top:27px;border-top:3px dashed rgba(255,255,255,.3)}.route .start,.route .end{position:absolute;top:17px;width:22px;height:22px;border-radius:50%;border:5px solid var(--navy);box-shadow:0 0 0 2px rgba(255,255,255,.7)}.route .start{inset-inline-start:0;background:var(--green)}.route .end{inset-inline-end:0;background:#fff}.route .arrow{position:absolute;left:50%;top:8px;transform:translateX(-50%) rotate(-12deg);width:48px;height:40px;border-top:3px solid var(--green);border-radius:50%}.visual-label{margin-top:10px;color:rgba(255,255,255,.65);font-size:12px}.lang{margin-top:auto;width:max-content;color:var(--muted);font-size:12px;font-weight:700}.lang:hover{color:var(--green)}@media(max-width:760px){body{padding:16px}.error-shell{grid-template-columns:1fr;min-height:0}.error-copy{padding:30px;min-height:510px}.error-visual{grid-row:1;min-height:245px}.code{font-size:100px}.route{margin-top:22px}.copy-body{margin:42px 0}.actions{align-items:stretch;flex-direction:column}.button{width:100%}h1{font-size:34px}}
    </style>
</head>
<body>
<main class="error-shell">
    <section class="error-copy">
        <a class="brand" href="{{ route('home') }}"><img src="{{ asset('images/msa-logo.png') }}" alt="MSA University"><span><strong>MSA Go</strong><small>{{ __('Short Link Manager') }}</small></span></a>
        <div class="copy-body">
            <span class="eyebrow">{{ __('Link not found') }}</span>
            <h1>{{ __('This link did not lead anywhere.') }}</h1>
            <p>{{ __('The address may be incorrect, expired, inactive, or no longer available. Check the URL and try again.') }}</p>
            <div class="actions">
                <a class="button primary" href="{{ auth()->check() ? route('dashboard') : route('home') }}">{{ auth()->check() ? __('Go to dashboard') : __('Go to homepage') }} <span aria-hidden="true">→</span></a>
                <button class="button" type="button" onclick="history.length > 1 ? history.back() : location.href=@js(route('home'))">{{ __('Go back') }}</button>
            </div>
            <div class="help">{{ __('Error code: 404 · The requested page could not be found.') }}</div>
        </div>
        <a class="lang" href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a>
    </section>
    <aside class="error-visual" aria-hidden="true">
        <div class="visual-content"><div class="code">404</div><div class="route"><span class="start"></span><span class="arrow"></span><span class="end"></span></div><div class="visual-label">{{ __('A short detour. Let’s get you back.') }}</div></div>
    </aside>
</main>
</body>
</html>
