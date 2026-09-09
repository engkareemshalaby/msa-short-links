<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ __('MSA Go is the smart link management platform for MSA University.') }}">
    <title>{{ __('Smart links, clearer insights') }} · MSA Go</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--navy:#072841;--navy-soft:#123b59;--green:#538f3f;--green-dark:#427532;--green-pale:#edf5ea;--ink:#102a3d;--muted:#667783;--line:#dde7e1;--paper:#fbfcfb;--white:#fff}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;min-height:100vh;color:var(--ink);background:var(--paper);font-family:'Inter',Arial,sans-serif;overflow-x:hidden}html[lang=ar] body{font-family:'Tajawal',Arial,sans-serif}a{color:inherit}
        .page{min-height:100vh;display:flex;flex-direction:column;position:relative;isolation:isolate}.page::before{content:'';position:fixed;z-index:-1;width:36rem;height:36rem;inset:-18rem auto auto -14rem;border-radius:50%;background:rgba(83,143,63,.08)}[dir=rtl] .page::before{inset:-18rem -14rem auto auto}.shell{width:min(1180px,calc(100% - 40px));margin-inline:auto}
        .topbar{padding:22px 0}.nav{display:flex;align-items:center;justify-content:space-between;gap:20px}.logo{display:block;width:clamp(180px,22vw,260px);height:auto}.nav-actions{display:flex;align-items:center;gap:10px}
        .language,.login-link{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:0 16px;border-radius:12px;font-size:.9rem;font-weight:700;text-decoration:none;transition:transform .22s ease,background-color .22s ease,color .22s ease,box-shadow .22s ease}.language{border:1px solid var(--line);background:var(--white);color:var(--navy)}.login-link{border:1px solid var(--navy);background:var(--navy);color:var(--white)}.language:hover,.login-link:hover{transform:translateY(-2px)}.language:hover{background:var(--green-pale);border-color:#bdd3b4}.login-link:hover{background:var(--navy-soft);box-shadow:0 10px 24px rgba(7,40,65,.18)}
        main{flex:1;display:flex;align-items:center;padding:34px 0 48px}.hero{display:grid;grid-template-columns:1.02fr .98fr;align-items:center;gap:clamp(40px,7vw,90px)}.copy{animation:rise-in .75s cubic-bezier(.2,.75,.25,1) both}
        .eyebrow{display:inline-flex;align-items:center;gap:9px;margin:0 0 22px;padding:8px 12px;border:1px solid #d8e8d2;border-radius:999px;background:var(--green-pale);color:var(--green-dark);font-size:.78rem;font-weight:800;letter-spacing:.04em}.eyebrow::before{content:'';width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 0 5px rgba(83,143,63,.12)}
        h1{margin:0;max-width:650px;color:var(--navy);font-size:clamp(2.55rem,5.3vw,5rem);line-height:1.05;letter-spacing:-.055em}[dir=rtl] h1{letter-spacing:-.035em;line-height:1.16}h1 span{color:var(--green)}.lead{max-width:600px;margin:24px 0 0;color:var(--muted);font-size:clamp(1rem,1.5vw,1.15rem);line-height:1.85}
        .cta-row{display:flex;align-items:center;flex-wrap:wrap;gap:14px;margin-top:34px}.primary-cta{display:inline-flex;align-items:center;justify-content:center;gap:12px;min-height:54px;padding:0 24px;border-radius:14px;background:var(--green);color:var(--white);font-weight:800;text-decoration:none;box-shadow:0 12px 30px rgba(83,143,63,.25);transition:transform .22s ease,background-color .22s ease,box-shadow .22s ease}.primary-cta:hover{transform:translateY(-3px);background:var(--green-dark);box-shadow:0 16px 34px rgba(83,143,63,.3)}.primary-cta svg{transition:transform .22s ease}.primary-cta:hover svg{transform:translateX(4px)}[dir=rtl] .primary-cta svg{transform:scaleX(-1)}[dir=rtl] .primary-cta:hover svg{transform:scaleX(-1) translateX(4px)}.access-note{color:var(--muted);font-size:.82rem}
        .visual{position:relative;min-height:430px;display:grid;place-items:center;animation:rise-in .75s .12s cubic-bezier(.2,.75,.25,1) both}.visual::before{content:'';position:absolute;width:82%;aspect-ratio:1;border-radius:50%;background:radial-gradient(circle,rgba(83,143,63,.17),rgba(83,143,63,0) 68%);animation:breathe 5s ease-in-out infinite}.illustration{width:min(100%,520px);height:auto;overflow:visible;filter:drop-shadow(0 24px 30px rgba(7,40,65,.11))}.orbit{transform-box:fill-box;transform-origin:center;animation:orbit 18s linear infinite}.float-a{animation:float 4.5s ease-in-out infinite}.float-b{animation:float 4.5s .9s ease-in-out infinite}.draw{stroke-dasharray:330;stroke-dashoffset:330;animation:draw 1.6s .45s ease forwards}.pulse{animation:dot-pulse 2.2s ease-in-out infinite;transform-box:fill-box;transform-origin:center}
        footer{border-top:1px solid var(--line);background:rgba(255,255,255,.72)}.footer-inner{min-height:72px;display:flex;align-items:center;justify-content:center;gap:9px;color:var(--muted);text-align:center;font-size:.88rem}.footer-inner svg{color:var(--green);flex:0 0 auto}
        @keyframes rise-in{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:translateY(0)}}@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}@keyframes orbit{to{transform:rotate(360deg)}}@keyframes draw{to{stroke-dashoffset:0}}@keyframes breathe{50%{transform:scale(1.07);opacity:.72}}@keyframes dot-pulse{50%{transform:scale(1.28);opacity:.7}}
        @media(max-width:860px){main{padding-top:34px}.hero{grid-template-columns:1fr;text-align:center;gap:22px}.copy{display:flex;flex-direction:column;align-items:center}.visual{min-height:350px;order:-1}.illustration{max-width:440px}.lead{max-width:660px}.cta-row{justify-content:center}}
        @media(max-width:560px){.shell{width:min(100% - 28px,1180px)}.topbar{padding:16px 0}.logo{width:154px}.language{width:44px;padding:0}.language span{display:none}.login-link{padding-inline:13px;font-size:.82rem}main{padding:18px 0 48px}.visual{min-height:285px}h1{font-size:clamp(2.25rem,12vw,3.3rem)}.lead{font-size:.98rem;line-height:1.75}.cta-row{margin-top:28px;flex-direction:column;width:100%}.primary-cta{width:100%}.footer-inner{min-height:78px;font-size:.78rem}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;scroll-behavior:auto!important;transition-duration:.01ms!important}}
    </style>
</head>
<body>
<div class="page">
    <header class="topbar">
        <nav class="shell nav" aria-label="{{ __('Main navigation') }}">
            <a href="{{ route('home') }}" aria-label="MSA Go"><img class="logo" src="{{ asset('images/msa-logo-wide.png') }}" alt="MSA University"></a>
            <div class="nav-actions">
                <a class="language" href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" aria-label="{{ __('Change language') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 12h17M12 3c2.2 2.45 3.32 5.45 3.32 9S14.2 18.55 12 21M12 3C9.8 5.45 8.68 8.45 8.68 12S9.8 18.55 12 21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                </a>
                <a class="login-link" href="{{ route('login') }}">{{ __('Sign in') }}</a>
            </div>
        </nav>
    </header>
    <main>
        <section class="shell hero" aria-labelledby="hero-title">
            <div class="copy">
                <p class="eyebrow">{{ __('MSA UNIVERSITY · DIGITAL PLATFORM') }}</p>
                <h1 id="hero-title">{{ __('Smart links,') }} <span>{{ __('clearer insights') }}</span></h1>
                <p class="lead">{{ __('MSA Go brings link creation, campaign organization, and visit analytics together in one secure, easy-to-use platform.') }}</p>
                <div class="cta-row">
                    <a class="primary-cta" href="{{ route('login') }}">{{ __('Go to sign in') }}<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M14 7l5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    <span class="access-note">{{ __('For authorized MSA staff') }}</span>
                </div>
            </div>
            <div class="visual" aria-hidden="true">
                <svg class="illustration" viewBox="0 0 620 520">
                    <defs><linearGradient id="panel" x1="125" y1="79" x2="488" y2="456" gradientUnits="userSpaceOnUse"><stop stop-color="#fff"/><stop offset="1" stop-color="#f2f7f0"/></linearGradient><linearGradient id="navyFill" x1="175" y1="130" x2="456" y2="405" gradientUnits="userSpaceOnUse"><stop stop-color="#123B59"/><stop offset="1" stop-color="#072841"/></linearGradient></defs>
                    <g class="orbit" opacity=".65"><circle cx="310" cy="260" r="225" fill="none" stroke="#D9E6DB" stroke-width="2" stroke-dasharray="7 12"/><circle class="pulse" cx="310" cy="35" r="7" fill="#538F3F"/><circle cx="512" cy="359" r="5" fill="#072841"/><circle cx="109" cy="157" r="5" fill="#8DB07F"/></g>
                    <rect x="102" y="70" width="416" height="380" rx="32" fill="url(#panel)" stroke="#DCE7DF" stroke-width="2"/>
                    <rect x="126" y="94" width="368" height="54" rx="17" fill="#fff" stroke="#E1E9E4"/><circle cx="153" cy="121" r="8" fill="#538F3F"/><rect x="171" y="113" width="104" height="8" rx="4" fill="#B9C6CD"/><rect x="171" y="128" width="66" height="6" rx="3" fill="#E0E7E3"/><rect x="435" y="108" width="36" height="26" rx="8" fill="#EDF5EA"/><path d="m447 121 5 5 9-11" fill="none" stroke="#538F3F" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="126" y="166" width="228" height="260" rx="22" fill="url(#navyFill)"/><circle cx="240" cy="268" r="64" fill="#fff" fill-opacity=".06" stroke="#fff" stroke-opacity=".15" stroke-width="2"/><path class="draw" d="M186 295c21-50 41-11 61-55 18-39 40 1 60-44" fill="none" stroke="#84B773" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="186" cy="295" r="7" fill="#fff"/><circle cx="247" cy="240" r="7" fill="#fff"/><circle class="pulse" cx="307" cy="196" r="8" fill="#84B773"/><rect x="158" y="349" width="164" height="13" rx="6.5" fill="#fff" fill-opacity=".9"/><rect x="158" y="375" width="112" height="8" rx="4" fill="#fff" fill-opacity=".26"/>
                    <g class="float-a"><rect x="374" y="166" width="120" height="112" rx="20" fill="#fff" stroke="#E0E8E3"/><circle cx="410" cy="205" r="18" fill="#EDF5EA"/><path d="M401 205h18m-9-9v18" stroke="#538F3F" stroke-width="2.5" stroke-linecap="round"/><rect x="396" y="236" width="76" height="8" rx="4" fill="#AAB9C2"/><rect x="408" y="253" width="52" height="6" rx="3" fill="#DFE7E2"/></g>
                    <g class="float-b"><rect x="374" y="296" width="120" height="130" rx="20" fill="#fff" stroke="#E0E8E3"/><path d="M400 386v-25M420 386v-44M440 386v-17M460 386v-58" stroke="#538F3F" stroke-width="10" stroke-linecap="round"/><rect x="396" y="318" width="62" height="8" rx="4" fill="#AAB9C2"/></g>
                    <g transform="translate(67 327) rotate(-8)"><rect width="111" height="54" rx="16" fill="#538F3F"/><path d="M26 27h36m-8-9 9 9-9 9" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><circle cx="84" cy="27" r="7" fill="#fff" fill-opacity=".4"/></g>
                </svg>
            </div>
        </section>
    </main>
    <footer><div class="shell footer-inner"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20h16M6 20V9l6-4 6 4v11M9 12h.01M15 12h.01M9 16h.01M15 16h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span>{{ __('@ E-Content Department at MSA University') }}</span></div></footer>
</div>
</body>
</html>
