<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolePermissions = [
            'dashboard.view', 'links.view', 'links.create', 'links.update', 'links.delete',
            'analytics.view', 'audit.view', 'users.manage', 'roles.manage',
        ];
        $crmViewPermissions = [
            'crm.submissions.view', 'crm.exhibitions.view', 'crm.event_contacts.view',
            'crm.partners.view', 'crm.referrals.view',
        ];
        $crmManagePermissions = [
            'crm.submissions.manage', 'crm.exhibitions.manage', 'crm.event_contacts.manage',
            'crm.partners.manage', 'crm.referrals.manage',
        ];
        foreach ([...$rolePermissions, ...$crmViewPermissions, ...$crmManagePermissions] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $administrator = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $analyst = Role::firstOrCreate(['name' => 'Analyst', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Partner', 'guard_name' => 'web']);
        $crmAdmin = Role::firstOrCreate(['name' => 'CRM Admin', 'guard_name' => 'web']);
        $crmStaff = Role::firstOrCreate(['name' => 'CRM Staff', 'guard_name' => 'web']);

        $superAdmin->syncPermissions($rolePermissions);
        $administrator->syncPermissions(['dashboard.view', 'links.view', 'links.create', 'links.update', 'links.delete', 'analytics.view', 'audit.view']);
        $analyst->syncPermissions(['dashboard.view', 'links.view', 'analytics.view']);
        $crmAdmin->syncPermissions([...$crmViewPermissions, ...$crmManagePermissions]);
        $crmStaff->syncPermissions($crmViewPermissions);

        $admin = User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@msago.local')],
            ['name' => env('ADMIN_NAME', 'MSA Administrator'), 'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!'))]
        );
        $admin->syncRoles([$superAdmin]);
        $admin->givePermissionTo([...$crmViewPermissions, ...$crmManagePermissions]);
    }
}
