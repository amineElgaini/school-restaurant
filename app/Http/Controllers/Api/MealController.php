<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index()
    {
        return Meal::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:entrée,plat principal,dessert,boisson',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $meal = Meal::create($validated);

        return response()->json($meal, 201);
    }

    public function show(Meal $meal)
    {
        return $meal;
    }

    public function update(Request $request, Meal $meal)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:entrée,plat principal,dessert,boisson',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $meal->update($validated);

        return response()->json($meal);
    }

    public function destroy(Meal $meal)
    {
        $meal->delete();

        return response()->json(null, 204);
    }
}
