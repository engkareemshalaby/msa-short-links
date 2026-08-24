@extends('layouts.app')
@section('title', __('Users'))
@section('subtitle', __('Manage staff access without exposing permission complexity.'))
@section('content')
<div class="toolbar"><div></div><a class="button primary" href="{{ route('users.create') }}">＋ {{ __('Add user') }}</a></div>
<div class="card table-card"><div class="table-wrap"><table class="data-table"><thead><tr><th>{{ __('User') }}</th><th>{{ __('Role') }}</th><th>{{ __('Joined') }}</th><th></th></tr></thead><tbody>@foreach($users as $user)<tr><td><div class="user-chip"><span class="avatar">{{ mb_strtoupper(mb_substr($user->name,0,1)) }}</span><span><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span></div></td><td>@forelse($user->roles as $role)<span class="badge purple">{{ $role->name }}</span>@empty<span class="badge muted">{{ __('No role') }}</span>@endforelse</td><td>{{ $user->created_at->format('M d, Y') }}</td><td><div class="actions"><a class="button small" href="{{ route('users.edit',$user) }}">{{ __('Edit') }}</a>@if(!auth()->user()->is($user))<form method="POST" action="{{ route('users.destroy',$user) }}" onsubmit="return confirm(@js(__('Delete this user?')))" >@csrf @method('DELETE')<button class="button small danger">{{ __('Delete') }}</button></form>@endif</div></td></tr>@endforeach</tbody></table></div></div>
@endsection
