@extends('layouts.app')
@section('title', __('Email campaign'))
@section('subtitle', $campaign->subject)
@section('content')
<div class="stats-grid exhibition-summary">
    <div class="stat-card"><div class="stat-label">{{ __('Total emails') }}</div><div class="stat-value">{{ number_format($campaign->recipient_count) }}</div></div>
    <div class="stat-card"><div class="stat-label">{{ __('Queued') }}</div><div class="stat-value">{{ number_format($counts['pending'] ?? 0) }}</div></div>
    <div class="stat-card"><div class="stat-label">{{ __('Sent') }}</div><div class="stat-value">{{ number_format($counts['sent'] ?? 0) }}</div></div>
    <div class="stat-card"><div class="stat-label">{{ __('Failed') }}</div><div class="stat-value">{{ number_format($counts['failed'] ?? 0) }}</div></div>
</div>
<p>{{ __('Queue worker command: php artisan queue:work database --queue=exhibition-mail --tries=3') }}</p>
<p><a class="button" href="{{ route('crm.exhibition.index') }}">{{ __('Back to registrations') }}</a> <a class="button" href="{{ request()->url() }}">{{ __('Refresh status') }}</a></p>
@endsection
@push('head')<style>.exhibition-summary{grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:18px}@media(max-width:700px){.exhibition-summary{grid-template-columns:repeat(2,1fr)}}</style>@endpush
