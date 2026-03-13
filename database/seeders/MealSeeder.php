<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platPrincipal = \App\Models\MealType::where('slug', 'plat_principal')->first();
        $entree = \App\Models\MealType::where('slug', 'entree')->first();
        $dessert = \App\Models\MealType::where('slug', 'dessert')->first();

        $meals = [
            [
                'name' => 'Couscous Royal',
                'meal_type_id' => $platPrincipal->id,
                'description' => 'Traditionnel couscous avec légumes et viande.',
            ],
            [
                'name' => 'Salade César',
                'meal_type_id' => $entree->id,
                'description' => 'Salade fraîche avec croûtons et sauce César.',
            ],
            [
                'name' => 'Tarte au Pommes',
                'meal_type_id' => $dessert->id,
                'description' => 'Dessert sucré et fruité.',
            ],
            [
                'name' => 'Pizza Margherita',
                'meal_type_id' => $platPrincipal->id,
                'description' => 'Pizza classique avec tomate et mozzarella.',
            ],
        ];

        foreach ($meals as $mealData) {
            $meal = \App\Models\Meal::firstOrCreate(['name' => $mealData['name']], $mealData);
            
            // Create a MenuMeal for today for each meal to make testing easy
            \App\Models\MenuMeal::firstOrCreate([
                'meal_id' => $meal->id,
                'reservation_date' => now()->toDateString(),
            ]);
        }
    }
}
