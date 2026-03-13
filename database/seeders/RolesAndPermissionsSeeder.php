<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Roles
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
        $staffRole = \App\Models\Role::firstOrCreate(['name' => 'staff']);
        $studentRole = \App\Models\Role::firstOrCreate(['name' => 'student']);

        // 2. Create Permissions
        $permissions = [
            'plan_weekly_menu' => ['name' => 'Plan Weekly Menu', 'slug' => 'plan_weekly_menu'],
            'manage_menu' => ['name' => 'Manage Menu', 'slug' => 'manage_menu'],
            'view_reservations' => ['name' => 'View Reservations', 'slug' => 'view_reservations'],
            'view_statistics' => ['name' => 'View Statistics', 'slug' => 'view_statistics'],
            'submit_reclamation' => ['name' => 'Submit Reclamation', 'slug' => 'submit_reclamation'],
        ];

        foreach ($permissions as $slug => $data) {
            $permissionModels[$slug] = \App\Models\Permission::firstOrCreate(['slug' => $slug], $data);
        }

        // 3. Assign Permissions to Roles
        $adminRole->permissions()->sync(array_values(array_map(fn($p) => $p->id, $permissionModels)));
        
        $staffRole->permissions()->sync([
            $permissionModels['plan_weekly_menu']->id,
            $permissionModels['manage_menu']->id,
            $permissionModels['view_reservations']->id,
            $permissionModels['view_statistics']->id,
        ]);

        $studentRole->permissions()->sync([
            $permissionModels['submit_reclamation']->id,
        ]);

        // 4. Create Specific Test Users for Role Use Cases
        $this->createUser('Admin User', 'admin@example.com', $adminRole);
        $this->createUser('Staff Member', 'staff@example.com', $staffRole);
        $this->createUser('Student User', 'student@example.com', $studentRole);

        // 5. Create Specific Test Users for Permission Use Cases (Direct Permission Assignment)
        $this->createUser('Staff Planner', 'staff_plan_menu@example.com', $staffRole, $permissionModels['plan_weekly_menu']);
        $this->createUser('Staff Manager', 'staff_manage_menu@example.com', $staffRole, $permissionModels['manage_menu']);
        $this->createUser('Staff Reservations', 'staff_view_reservations@example.com', $staffRole, $permissionModels['view_reservations']);
        $this->createUser('Staff Analyst', 'staff_view_statistics@example.com', $staffRole, $permissionModels['view_statistics']);
        $this->createUser('Student Reporter', 'student_submit_reclamation@example.com', $studentRole, $permissionModels['submit_reclamation']);
    }

    private function createUser($name, $email, $role, $permission = null)
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role_id' => $role->id,
            ]
        );

        if ($permission) {
            $user->permissions()->sync([$permission->id]);
        }

        return $user;
    }
}
