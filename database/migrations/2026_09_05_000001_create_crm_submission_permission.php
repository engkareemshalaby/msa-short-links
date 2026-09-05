<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'crm.submissions.view',
            'guard_name' => 'web',
        ]);

        $email = config('crm.admin_email');
        if ($email && ($user = User::query()->where('email', $email)->first())) {
            $user->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->where('name', 'crm.submissions.view')->where('guard_name', 'web')->delete();
    }
};
