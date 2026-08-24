@extends('layouts.app')
@section('title', __('Edit short link'))
@section('subtitle', $link->short_url)
@section('content')<div class="card form-card"><div class="card-header"><div><h2>{{ __('Link details') }}</h2><p>{{ __('Update the destination, status or expiration date.') }}</p></div></div><div class="card-body"><form method="POST" action="{{ route('links.update',$link) }}">@csrf @method('PUT') @include('links.partials.form',['link'=>$link])</form></div></div>@endsection
