<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'HOD', 'Accountant', 'Director', 'CEO'
        ];
        $permissions = ['view', 'create', 'update', 'delete'];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach ($roles as $role) {
           $role = Role::create(['name' => $role]);
           $role->givePermissionTo($permissions);
        }

    }
}
