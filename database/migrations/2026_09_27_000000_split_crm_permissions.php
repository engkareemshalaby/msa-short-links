<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'crm.submissions.view', 'crm.submissions.manage',
            'crm.exhibitions.view', 'crm.exhibitions.manage',
            'crm.event_contacts.view', 'crm.event_contacts.manage',
            'crm.partners.view', 'crm.partners.manage',
            'crm.referrals.view', 'crm.referrals.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $allCrmPermissions = Permission::query()->whereIn('name', $permissions)->get();
        $viewPermissions = $allCrmPermissions->filter(fn (Permission $permission) => str_ends_with($permission->name, '.view'));

        $crmAdmin = Role::firstOrCreate(['name' => 'CRM Admin', 'guard_name' => 'web']);
        $crmStaff = Role::firstOrCreate(['name' => 'CRM Staff', 'guard_name' => 'web']);
        $crmAdmin->givePermissionTo($allCrmPermissions);
        $crmStaff->givePermissionTo($viewPermissions);

        $legacyPermission = Permission::findByName('crm.submissions.view', 'web');
        foreach ($legacyPermission->roles as $role) {
            $role->givePermissionTo($allCrmPermissions);
        }

        User::permission('crm.submissions.view')->each(
            fn (User $user) => $user->givePermissionTo($allCrmPermissions)
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally non-destructive: removing permissions could revoke production access.
    }
};
