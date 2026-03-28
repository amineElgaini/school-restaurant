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

    /**
     * @OA\Get(
     *     path="/api/menu-meals",
     *     summary="List meals scheduled for a specific date",
     *     tags={"Menu Meals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         description="The date to check (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2024-03-25")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function index(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $date = $request->input('date');

        return MenuMeal::with('meal')
            ->whereDate('served_at', $date)
            ->get();
    }

    /**
     * @OA\Post(
     *     path="/api/menu-meals",
     *     summary="Add a meal to the menu for a specific date",
     *     tags={"Menu Meals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"meal_id","served_at"},
     *             @OA\Property(property="meal_id", type="integer", example=1),
     *             @OA\Property(property="served_at", type="string", format="date", example="2024-03-25")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Meal added to menu successfully"
     *     ),
     *     @OA\Response(response=400, description="Meal already in menu for this date"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'meal_id' => 'required|exists:meals,id',
            'served_at' => 'required|date',
        ]);

        // Check if the meal already exists for this date
        $exists = MenuMeal::where('meal_id', $request->meal_id)
            ->where('served_at', $request->served_at)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This meal is already in the menu for this date'
            ], 400);
        }

        // Create new MenuMeal
        $menuMeal = MenuMeal::create([
            'meal_id' => $request->meal_id,
            'served_at' => $request->served_at,
        ]);

        return response()->json($menuMeal, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/menu-meals/{menu_meal}",
     *     summary="Remove a meal from the menu",
     *     tags={"Menu Meals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="menu_meal",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Meal removed from menu"
     *     ),
     *     @OA\Response(response=404, description="Menu meal not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(MenuMeal $menuMeal)
    {
        $menuMeal->delete();

        return response()->json([
            'message' => 'Meal removed from menu',
            'meal_id' => $menuMeal->meal_id,
            'served_at' => $menuMeal->served_at,
        ], 200);
    }
}
