<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', ['users' => User::with('roles')->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('users.form', ['user' => new User, 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'], 'roles.*' => ['exists:roles,name'],
        ]);
        $user = User::create($data);
        $user->syncRoles($data['roles'] ?? []);
        AuditLogger::log('created', $user, 'Created user '.$user->email, [], ['name' => $user->name, 'email' => $user->email, 'roles' => $data['roles'] ?? []]);

        return redirect()->route('users.index')->with('success', __('User created successfully.'));
    }

    public function edit(User $user): View
    {
        return view('users.form', ['user' => $user, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'], 'roles.*' => ['exists:roles,name'],
        ]);
        $old = ['name' => $user->name, 'email' => $user->email, 'roles' => $user->getRoleNames()->all()];
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);
        $user->syncRoles($data['roles'] ?? []);
        AuditLogger::log('updated', $user, 'Updated user '.$user->email, $old, ['name' => $user->name, 'email' => $user->email, 'roles' => $data['roles'] ?? []]);

        return redirect()->route('users.index')->with('success', __('User updated successfully.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, __('You cannot delete your own account.'));
        AuditLogger::log('deleted', $user, 'Deleted user '.$user->email, ['name' => $user->name, 'email' => $user->email]);
        $user->delete();

        return back()->with('success', __('User deleted successfully.'));
    }
}
