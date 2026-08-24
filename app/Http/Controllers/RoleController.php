<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('roles.index', ['roles' => Role::with('permissions')->withCount('users')->get()]);
    }

    public function create(): View
    {
        return view('roles.form', ['role' => new Role, 'permissions' => Permission::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);
        AuditLogger::log('created', $role, 'Created role '.$role->name, [], $data);

        return redirect()->route('roles.index')->with('success', __('Role created successfully.'));
    }

    public function edit(Role $role): View
    {
        return view('roles.form', ['role' => $role, 'permissions' => Permission::orderBy('name')->get()]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->name === 'Super Admin', 403, __('The Super Admin role is protected.'));
        $data = $this->validated($request, $role);
        $old = ['name' => $role->name, 'permissions' => $role->getPermissionNames()->all()];
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);
        AuditLogger::log('updated', $role, 'Updated role '.$role->name, $old, $data);

        return redirect()->route('roles.index')->with('success', __('Role updated successfully.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->name === 'Super Admin' || $role->users()->exists(), 422, __('This role cannot be deleted.'));
        AuditLogger::log('deleted', $role, 'Deleted role '.$role->name, ['name' => $role->name]);
        $role->delete();

        return back()->with('success', __('Role deleted successfully.'));
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role?->id)],
            'permissions' => ['array'], 'permissions.*' => ['exists:permissions,name'],
        ]);
    }
}
