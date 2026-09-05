@extends('layouts.crm-public')
@section('title', __('Application received'))
@section('content')
<main class="card thank-card"><div class="thank-icon">✓</div><h1>{{ __('Thank you') }}</h1><p>{{ __('Your partnership application has been received successfully. The MSA University partnerships team will review it and contact you if more information is needed.') }}</p></main>
@endsection
