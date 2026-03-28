<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class AssignableRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'admin' => [
            ],
            'staff' => [
                'manage_meals',
                'manage_menu',
            ],
            'student' => [
                'submit_complaints',
            ],
        ];

        foreach ($map as $roleSlug => $permissionSlugs) {
            $role = Role::where('slug', $roleSlug)->first();

            $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');

            $role->assignablePermissions()->sync($permissionIds);
        }
    }
}