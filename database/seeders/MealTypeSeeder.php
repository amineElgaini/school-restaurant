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
            ['name' => 'Entree', 'slug' => 'entree'],
            ['name' => 'Plat Principal', 'slug' => 'plat_principal'],
            ['name' => 'Dessert', 'slug' => 'dessert'],
            ['name' => 'Boisson', 'slug' => 'boisson'],
        ];

        foreach ($types as $type) {
            \App\Models\MealType::firstOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
