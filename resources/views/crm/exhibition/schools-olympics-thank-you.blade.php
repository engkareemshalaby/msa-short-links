@extends('layouts.crm-public')
@section('title', __('schools_olympics.received'))
@section('content')
<main class="crm-form"><section class="form-section thank-card"><div class="thank-icon">✓</div><span class="crm-eyebrow">MSA Schools Olympics</span><h1>{{ __('schools_olympics.received') }}</h1><p>{{ __('schools_olympics.thanks') }}</p><a class="button primary" href="{{ route(app()->getLocale() === 'ar' ? 'crm.schools-olympics.create.ar' : 'crm.schools-olympics.create') }}">{{ __('schools_olympics.register_another') }}</a></section></main>
@endsection
