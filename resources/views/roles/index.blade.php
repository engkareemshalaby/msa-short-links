@extends('layouts.app')

@section('title', __('Roles & Permissions'))
@section('subtitle', __('Define access once, then assign simple roles to staff.'))

@section('content')
    <div class="toolbar">
        <div></div>
        <a class="button primary" href="{{ route('roles.create') }}">＋ {{ __('Add role') }}</a>
    </div>

    <div class="card table-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Permissions') }}</th>
                        <th>{{ __('Users') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td><strong>{{ $role->name }}</strong></td>
                            <td>
                                <div style="display:flex;gap:5px;flex-wrap:wrap">
                                    @foreach ($role->permissions->take(5) as $permission)
                                        <span class="badge muted">{{ $permission->name }}</span>
                                    @endforeach

                                    @if ($role->permissions->count() > 5)
                                        <span class="badge purple">+{{ $role->permissions->count() - 5 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td>
                                <div class="actions">
                                    @if ($role->name !== 'Super Admin')
                                        <a class="button small" href="{{ route('roles.edit', $role) }}">{{ __('Edit') }}</a>

                                        @if ($role->users_count === 0)
                                            <form method="POST" action="{{ route('roles.destroy', $role) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="button small danger">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
