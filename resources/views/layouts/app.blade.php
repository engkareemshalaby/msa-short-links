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
<div class="app-shell" id="appShell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img class="brand-logo" src="{{ asset('images/msa-logo.png') }}" alt="MSA University">
            <span class="nav-text"><strong>MSA Go</strong><small>{{ __('Short Link Manager') }}</small></span>
        </a>
        <nav class="nav-list" aria-label="{{ __('Main navigation') }}">
            @can('dashboard.view')
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="{{ __('Dashboard') }}"><span>⌂</span><span class="nav-text">{{ __('Dashboard') }}</span></a>
            @endcan
            @can('links.view')
                <a href="{{ route('links.index') }}" class="nav-item {{ request()->routeIs('links.*') ? 'active' : '' }}" title="{{ __('Short Links') }}"><span>↗</span><span class="nav-text">{{ __('Short Links') }}</span></a>
            @endcan
            @can('links.create')
                <a href="{{ route('campaigns.index') }}" class="nav-item {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" title="{{ __('Campaigns') }}"><span>◇</span><span class="nav-text">{{ __('Campaigns') }}</span></a>
                <a href="{{ route('tags.index') }}" class="nav-item {{ request()->routeIs('tags.*') ? 'active' : '' }}" title="{{ __('Tags') }}"><span>●</span><span class="nav-text">{{ __('Tags') }}</span></a>
            @endcan
            @can('analytics.view')
                <a href="{{ route('analytics.index') }}" class="nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}" title="{{ __('Analytics') }}"><span>⌁</span><span class="nav-text">{{ __('Analytics') }}</span></a>
                <a href="{{ route('documentation') }}" class="nav-item {{ request()->routeIs('documentation') ? 'active' : '' }}" title="{{ __('Documentation') }}"><span>?</span><span class="nav-text">{{ __('Documentation') }}</span></a>
            @endcan
            @can('users.manage')
                <div class="nav-label"><span class="nav-text">{{ __('Administration') }}</span></div>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}" title="{{ __('Users') }}"><span>◎</span><span class="nav-text">{{ __('Users') }}</span></a>
            @endcan
            @role('Super Admin')
                <a href="{{ route('roles.index') }}" class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}" title="{{ __('Roles & Permissions') }}"><span>◇</span><span class="nav-text">{{ __('Roles & Permissions') }}</span></a>
                {{-- API keys and retargeting pixels are intentionally hidden from the navigation for now. --}}
            @endrole
            @can('audit.view')
                <a href="{{ route('audit.index') }}" class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}" title="{{ __('Activity Log') }}"><span>≡</span><span class="nav-text">{{ __('Activity Log') }}</span></a>
            @endcan
        </nav>
        @can('crm.submissions.view')
            <nav class="nav-list partner-nav" aria-label="{{ __('Partner system') }}">
                <div class="nav-label"><span class="nav-text">{{ __('Partner system') }}</span></div>
                <a href="{{ route('crm.submissions.index') }}" class="nav-item {{ request()->routeIs('crm.submissions.*') ? 'active' : '' }}" title="{{ __('Partner applications') }}"><span>◫</span><span class="nav-text">{{ __('Partner applications') }}</span></a>
                <a href="{{ route('crm.student-referrals.index') }}" class="nav-item {{ request()->routeIs('crm.student-referrals.*') ? 'active' : '' }}" title="{{ __('Student referrals') }}"><span>♙</span><span class="nav-text">{{ __('Student referrals') }}</span></a>
                <a href="{{ route('crm.exhibition.index') }}" class="nav-item {{ request()->routeIs('crm.exhibition.*') ? 'active' : '' }}" title="{{ __('Jordan exhibition') }}"><span>✦</span><span class="nav-text">{{ __('Jordan exhibition') }}</span></a>
                <a href="{{ route('crm.partners.index') }}" class="nav-item {{ request()->routeIs('crm.partners.*') ? 'active' : '' }}" title="{{ __('Partner accounts') }}"><span>◎</span><span class="nav-text">{{ __('Partner accounts') }}</span></a>
            </nav>
        @endcan
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="{{ __('Collapse sidebar') }}" title="{{ __('Collapse sidebar') }}"><span>‹</span><span class="nav-text">{{ __('Collapse') }}</span></button>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <button class="mobile-menu" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
            <div class="page-heading"><h1>@yield('title', __('Dashboard'))</h1><p>@yield('subtitle')</p></div>
            <div class="top-actions">
                <a class="lang-switch" href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}">{{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}</a>
                @can('links.create')<a class="button primary" href="{{ route('links.create') }}">＋ {{ __('New link') }}</a>@endcan
                <details class="account-menu">
                    <summary class="account-trigger" aria-label="{{ __('Account menu') }}"><span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span></summary>
                    <div class="account-dropdown">
                        <div class="account-card"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->roles->first()?->name ?? __('User') }}</small></div>
                        <a href="{{ route('profile.edit') }}">{{ __('My profile') }}</a>
                        <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">{{ __('Sign out') }}</button></form>
                    </div>
                </details>
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

const appShell = document.getElementById('appShell');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';

if (sidebarCollapsed) {
    appShell.classList.add('sidebar-collapsed');
}

sidebarToggle?.addEventListener('click', () => {
    appShell.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebar-collapsed', appShell.classList.contains('sidebar-collapsed'));
});
</script>
@stack('scripts')
</body>
</html>
