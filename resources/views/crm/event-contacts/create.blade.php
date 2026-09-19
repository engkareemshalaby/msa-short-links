@extends('layouts.app')
@section('title', __('Add exhibition contact'))
@section('subtitle', __('Add a contact collected from an exhibition or another offline source.'))
@section('content')
@include('crm.event-contacts._form')
@endsection
