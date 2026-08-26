<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#072841"><title>{{ __('Link unavailable') }} · MSA Go</title><link rel="icon" href="{{ asset('images/msa-logo.png') }}" type="image/png"><style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f5f8f6;color:#072841;font-family:Inter,Tajawal,Arial,sans-serif}.box{width:min(420px,calc(100% - 40px));background:#fff;border:1px solid #dce5df;border-radius:16px;padding:34px;box-sizing:border-box;text-align:center}.logo{width:72px;height:72px;object-fit:contain}h1{font-size:24px;margin:16px 0 8px}p{color:#637384;line-height:1.6}</style></head>
<body><main class="box"><img class="logo" src="{{ asset('images/msa-logo.png') }}" alt="MSA University"><h1>{{ __('Link unavailable') }}</h1><p>{{ $reason }}</p></main></body>
</html>
