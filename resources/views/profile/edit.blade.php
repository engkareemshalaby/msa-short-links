@extends('layouts.app')
@section('title', __('My profile'))
@section('subtitle', __('Update your account details and password.'))
@section('content')
<div class="card form-card">
    <div class="card-header"><h2>{{ __('Profile details') }}</h2></div>
    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <label class="field"><span>{{ __('Full name') }}</span><input name="name" value="{{ old('name', $user->name) }}" required></label>
                <label class="field"><span>{{ __('Email address') }}</span><input type="email" name="email" value="{{ old('email', $user->email) }}" required></label>
                <label class="field"><span>{{ __('New password') }}</span><input type="password" name="password"><small>{{ __('Leave empty to keep the current password.') }}</small></label>
                <label class="field"><span>{{ __('Confirm password') }}</span><input type="password" name="password_confirmation"></label>
            </div>
            <div class="form-footer"><a class="button" href="{{ route('dashboard') }}">{{ __('Cancel') }}</a><button class="button primary">{{ __('Save changes') }}</button></div>
        </form>
    </div>
</div>
@endsection
