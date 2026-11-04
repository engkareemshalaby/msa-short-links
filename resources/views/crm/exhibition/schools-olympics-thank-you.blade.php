@extends('layouts.crm-public')

@section('title', 'Registration received · MSA Schools Olympics')

@section('content')
<main class="crm-form"><section class="form-section thank-card"><div class="thank-icon">✓</div><span class="crm-eyebrow">MSA Schools Olympics</span><h1>Registration received</h1><p dir="rtl">شكرًا لتسجيلك. سيقوم فريق القبول بجامعة MSA بالتواصل معك قريبًا في الموعد الذي اخترته.</p><a class="button primary" href="{{ route('crm.schools-olympics.create') }}">Register another student</a></section></main>
@endsection
