<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'admin' => [
                'manage_users',
            ],
            'staff' => [
                'view_reservations',
            ],
            'student' => [
                'reserve_meals',
            ],
        ];

        foreach ($map as $roleSlug => $permissionSlugs) {
            $role = Role::where('slug', $roleSlug)->first();

            $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');

            $role->permissions()->sync($permissionIds);
        }
    }
}