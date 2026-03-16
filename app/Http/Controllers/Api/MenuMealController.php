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
            new Middleware('permission:manage_menu', except: ['index']),
        ];
    }

    public function index(Request $request) // Show meals for a date
    {
        $request->validate(['date' => 'required|date']);

        $date = $request->input('date');

        return MenuMeal::with('meal')
            ->whereDate('reservation_date', $date)
            ->get();
    }

    public function store(Request $request) // Add meal to menu
    {
        $request->validate([
            'meal_id' => 'required|exists:meals,id',
            'reservation_date' => 'required|date',
        ]);

        // Check if the meal already exists for this date
        $exists = MenuMeal::where('meal_id', $request->meal_id)
            ->where('reservation_date', $request->reservation_date)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This meal is already in the menu for this date'
            ], 400);
        }

        // Create new MenuMeal
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
