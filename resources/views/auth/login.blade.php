<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Sign in') }} · MSA Go</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rtl-fixes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">
</head>
<body class="login-body">
<div class="login-visual"><div class="visual-content"><img class="visual-logo" src="{{ asset('images/msa-logo.png') }}" alt="MSA University"><h1>{{ __('Links that move at the speed of your team.') }}</h1><p>{{ __('Create trusted short links, understand every visit, and keep a complete activity history.') }}</p><div class="visual-metric"><strong>MSA Go</strong><span>{{ __('Secure · Trackable · Branded') }}</span></div></div></div>
<main class="login-panel">
    <a class="login-language" href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a>
    <form class="login-card" method="POST" action="{{ route('login.store') }}">@csrf
        <img class="login-official-logo" src="{{ asset('images/msa-logo-wide.png') }}" alt="MSA University"><h2>{{ __('Welcome back') }}</h2><p>{{ __('Sign in to manage your short links and analytics.') }}</p>
        @if($errors->any())<div class="alert danger">{{ $errors->first() }}</div>@endif
        <label class="field"><span>{{ __('Email address') }}</span><input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@msa.edu.eg"></label>
        <label class="field"><span>{{ __('Password') }}</span><input type="password" name="password" required placeholder="••••••••"></label>
        <label class="checkbox"><input type="checkbox" name="remember" value="1"><span>{{ __('Remember me') }}</span></label>
        <button class="button primary full" type="submit">{{ __('Sign in') }} <span>→</span></button>
        <small class="login-note">{{ __('Authorized staff access only.') }}</small>
    </form>
</main>
</body></html>
