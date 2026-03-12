<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuMeal;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        return Menu::with('meals')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'week_start_date' => 'required|date|after_or_equal:today',
            'week_end_date' => 'required|date|after:week_start_date',
        ]);

        $menu = Menu::create($validated);

        return response()->json($menu, 201);
    }

    public function show(Menu $menu)
    {
        return $menu->load('meals');
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'week_start_date' => 'sometimes|date',
            'week_end_date' => 'sometimes|date|after:week_start_date',
        ]);

        $menu->update($validated);

        return response()->json($menu);
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return response()->json(null, 204);
    }

    public function assignMeal(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'meal_id' => 'required|exists:meals,id',
            'reservation_date' => 'required|date|after_or_equal:' . $menu->week_start_date . '|before_or_equal:' . $menu->week_end_date,
        ]);

        $menuMeal = MenuMeal::create([
            'menu_id' => $menu->id,
            'meal_id' => $validated['meal_id'],
            'reservation_date' => $validated['reservation_date'],
        ]);

        return response()->json($menuMeal, 201);
    }

    public function deassignMeal(Request $request, Menu $menu, $menuMealId)
    {
        $menuMeal = MenuMeal::where('menu_id', $menu->id)->findOrFail($menuMealId);
        $menuMeal->delete();

        return response()->json(null, 204);
    }
}
