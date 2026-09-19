@extends('layouts.app')
@section('title', __('Edit exhibition contact'))
@section('subtitle', __('Update contact details while preserving the original imported record.'))
@section('content')
@include('crm.event-contacts._form')
@endsection
