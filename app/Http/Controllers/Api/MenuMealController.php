<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuMeal;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MenuMealController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_menu'),
        ];
    }

   public function index(Request $request) // Show meals for a date
    {
        $request->validate(['date' => 'required|date']);

        $date = $request->query('date');
        return MenuMeal::with('meal')->where('reservation_date', $date)->get();
    }

    public function store(Request $request) // Add meal to menu
    {
        $request->validate([
            'meal_id' => 'required|exists:meals,id',
            'reservation_date' => 'required|date',
        ]);

        $menuMeal = MenuMeal::create([
            'meal_id' => $request->meal_id,
            'reservation_date' => $request->reservation_date,
        ]);

        return response()->json($menuMeal, 201);
    }

    public function destroy(MenuMeal $menuMeal) // Remove meal from menu
    {
        $menuMeal->delete();

        return response()->json([
            'message' => 'Meal removed from menu',
            'meal_id' => $menuMeal->meal_id,
            'reservation_date' => $menuMeal->reservation_date,
        ], 200);
    }
}
