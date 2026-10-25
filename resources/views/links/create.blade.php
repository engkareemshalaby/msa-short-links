@extends('layouts.app')
@section('title', __('Create short link'))
@section('subtitle', __('Generate a secure six-digit code or choose a memorable custom slug.'))
@section('content')
@if($source)<div class="alert success">{{ __('Duplicating :name. A unique copy name and slug are ready to edit. Campaign and tags are copied; other options use their defaults.', ['name' => $source->display_title]) }}</div>@endif
<div class="card form-card"><div class="card-header"><div><h2>{{ __('Link details') }}</h2><p>{{ __('The destination can be changed later without changing the short link.') }}</p></div></div><div class="card-body"><form method="POST" action="{{ route('links.store') }}">@csrf @include('links.partials.form',['link'=>null])</form></div></div>@endsection
