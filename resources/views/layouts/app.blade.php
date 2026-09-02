<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#072841">
    <title>@yield('title', __('Dashboard')) · MSA Go</title>
    <link rel="icon" href="{{ asset('images/msa-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rtl-fixes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">
    @stack('head')
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img class="brand-logo" src="{{ asset('images/msa-logo.png') }}" alt="MSA University">
            <span><strong>MSA Go</strong><small>{{ __('Short Link Manager') }}</small></span>
        </a>
        <nav class="nav-list">
            @can('dashboard.view')
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span>⌂</span>{{ __('Dashboard') }}</a>
            @endcan
            @can('links.view')
                <a href="{{ route('links.index') }}" class="nav-item {{ request()->routeIs('links.*') ? 'active' : '' }}"><span>↗</span>{{ __('Short Links') }}</a>
            @endcan
            @can('links.create')
                <a href="{{ route('campaigns.index') }}" class="nav-item {{ request()->routeIs('campaigns.*') ? 'active' : '' }}"><span>◇</span>{{ __('Campaigns') }}</a>
                <a href="{{ route('tags.index') }}" class="nav-item {{ request()->routeIs('tags.*') ? 'active' : '' }}"><span>●</span>{{ __('Tags') }}</a>
            @endcan
            @can('analytics.view')
                <a href="{{ route('analytics.index') }}" class="nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}"><span>⌁</span>{{ __('Analytics') }}</a>
                <a href="{{ route('documentation') }}" class="nav-item {{ request()->routeIs('documentation') ? 'active' : '' }}"><span>?</span>{{ __('Documentation') }}</a>
            @endcan
            @can('users.manage')
                <div class="nav-label">{{ __('Administration') }}</div>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}"><span>◎</span>{{ __('Users') }}</a>
            @endcan
            @role('Super Admin')
                <a href="{{ route('roles.index') }}" class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}"><span>◇</span>{{ __('Roles & Permissions') }}</a>
                {{-- API keys and retargeting pixels are intentionally hidden from the navigation for now. --}}
            @endrole
            @can('audit.view')
                <a href="{{ route('audit.index') }}" class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}"><span>≡</span>{{ __('Activity Log') }}</a>
            @endcan
        </nav>
        <div class="sidebar-footer">
            <div class="user-chip"><span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->roles->first()?->name ?? __('User') }}</small></span></div>
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="icon-button" title="{{ __('Sign out') }}">⇥</button></form>
        </div>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <button class="mobile-menu" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
            <div class="page-heading"><h1>@yield('title', __('Dashboard'))</h1><p>@yield('subtitle')</p></div>
            <div class="top-actions">
                <a class="lang-switch" href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}">{{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}</a>
                @can('links.create')<a class="button primary" href="{{ route('links.create') }}">＋ {{ __('New link') }}</a>@endcan
            </div>
        </header>
        <section class="content">
            @if(session('success'))<div class="alert success">✓ {{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert danger"><strong>{{ __('Please fix the following errors:') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </section>
    </main>
</div>
<script>
async function copyText(text) {
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return;
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.cssText = 'position:fixed;opacity:0;pointer-events:none;';
    document.body.appendChild(textarea);
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length);

    const copied = document.execCommand('copy');
    textarea.remove();

    if (!copied) {
        throw new Error('Clipboard copy failed');
    }
}

document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
    const original = button.textContent;

    try {
        await copyText(button.dataset.copy);
        button.textContent = @json(__('Copied!'));
    } catch (error) {
        button.textContent = @json(__('Copy failed'));
    }

    setTimeout(() => button.textContent = original, 1600);
}));
</script>
@stack('scripts')
</body>
</html>
