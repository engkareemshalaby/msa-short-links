@extends('layouts.app')
@section('title', __('Bulk import links'))
@section('subtitle', __('Create many short links from a CSV file.'))
@section('content')
<div class="card"><div class="card-header"><div><h2>{{ __('Import CSV') }}</h2><p>{{ __('Required column: destination_url. Optional columns: title, code.') }}</p></div></div><div class="card-body"><form method="POST" action="{{ route('links.bulk.store') }}" enctype="multipart/form-data">@csrf <label class="field"><span>{{ __('CSV file') }}</span><input type="file" name="csv" accept=".csv,text/csv" required><small>destination_url,title,code</small></label><div class="form-footer"><a class="button" href="{{ route('links.index') }}">{{ __('Cancel') }}</a><button class="button primary">{{ __('Import links') }}</button></div></form></div></div>
@endsection
