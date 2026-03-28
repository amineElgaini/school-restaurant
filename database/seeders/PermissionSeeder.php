<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Manage Users', 'slug' => 'manage_users'],
            ['name' => 'Manage Meals', 'slug' => 'manage_meals'],
            ['name' => 'Manage Menu', 'slug' => 'manage_menu'],
            ['name' => 'View Reservations', 'slug' => 'view_reservations'],
            ['name' => 'Reserve Meals', 'slug' => 'reserve_meals'],
            ['name' => 'Submit Complaints', 'slug' => 'submit_complaints'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                ['name' => $permission['name']]
            );
        }
    }
}