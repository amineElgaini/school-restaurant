<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MealTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Starter', 'slug' => 'starter'],
            ['name' => 'Main Course', 'slug' => 'main_course'],
            ['name' => 'Dessert', 'slug' => 'dessert'],
            ['name' => 'Drink', 'slug' => 'drink'],
        ];

        foreach ($types as $type) {
            \App\Models\MealType::firstOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
