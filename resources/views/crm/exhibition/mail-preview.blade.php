@extends('layouts.app')
@section('title', __('Review email'))
@section('subtitle', __('Confirm the audience and message before sending.'))
@section('content')
<div class="card"><div class="card-body" style="max-width:900px">
    <h2>{{ __('This will send :count emails', ['count' => number_format($recipientCount)]) }}</h2>
    <p>{{ number_format($registrationCount) }} {{ __('matching registrations') }} · {{ $draft['include_parents'] ? __('Students and parents/guardians') : __('Students only') }} · {{ __('Duplicate email addresses are counted once.') }}</p>
    <p>{{ __('Messages are queued with a 10 second interval. The queue worker must be running.') }}</p>
    <hr>
    <p><strong>{{ __('Subject') }}:</strong> {{ $draft['subject'] }}</p>
    <div class="mail-preview-body">{!! $draft['body_html'] !!}</div>
    <form method="POST" action="{{ route('crm.exhibition.mail.send') }}" style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
        @csrf<input type="hidden" name="token" value="{{ $draft['token'] }}">
        <a class="button" href="{{ route('crm.exhibition.mail.compose', $draft['filters']) }}">{{ __('Edit email') }}</a>
        <button class="button primary" type="submit" @disabled($recipientCount === 0)>{{ __('Confirm sending') }}</button>
    </form>
</div></div>
@endsection
@push('head')<style>.mail-preview-body{border:1px solid #d6dfe5;padding:20px;border-radius:8px;line-height:1.7;overflow-wrap:anywhere}.mail-preview-body img{max-width:100%;height:auto}</style>@endpush
