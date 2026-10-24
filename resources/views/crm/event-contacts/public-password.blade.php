@extends('layouts.crm-public')

@section('title', __('Exhibition contacts'))

@section('content')
<main class="crm-form">
    <div class="card thank-card" style="text-align:start">
        <h1>{{ __('Exhibition contacts') }}</h1>
        <p>{{ __('Enter the viewing password to open the read-only contacts page.') }}</p>
        <form method="POST" action="{{ route('crm.event-contacts.public.unlock') }}" style="margin-top:24px">
            @csrf
            <label class="field"><span>{{ __('Password') }}</span><input type="password" name="password" required autocomplete="current-password" autofocus></label>
            @error('password')<p role="alert" style="color:#b43d3d">{{ $message }}</p>@enderror
            <button class="button primary" type="submit" style="margin-top:18px">{{ __('View contacts') }}</button>
        </form>
    </div>
</main>
@endsection
