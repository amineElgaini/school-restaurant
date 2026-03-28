<?php

namespace Database\Seeders;

use App\Models\Meal;
use App\Models\MealType;
use App\Models\MenuMeal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainCourse = MealType::where('slug', 'main_course')->first();
        $starter = MealType::where('slug', 'starter')->first();
        $dessert = MealType::where('slug', 'dessert')->first();

        $meals = [
            [
                'name' => 'Royal Couscous',
                'meal_type_id' => $mainCourse->id,
                'description' => 'Traditional couscous with vegetables and meat.',
            ],
            [
                'name' => 'Caesar Salad',
                'meal_type_id' => $starter->id,
                'description' => 'Fresh salad with croutons and Caesar dressing.',
            ],
            [
                'name' => 'Apple Pie',
                'meal_type_id' => $dessert->id,
                'description' => 'Sweet and fruity dessert.',
            ],
            [
                'name' => 'Pizza Margherita',
                'meal_type_id' => $mainCourse->id,
                'description' => 'Classic pizza with tomato and mozzarella.',
            ],
        ];

        foreach ($meals as $mealData) {
            $meal = Meal::firstOrCreate(['name' => $mealData['name']], $mealData);
            
            // Create a MenuMeal for today for each meal to make testing easy
            MenuMeal::firstOrCreate([
                'meal_id' => $meal->id,
                'served_at' => now()->toDateString(),
            ]);
        }
    }
}
