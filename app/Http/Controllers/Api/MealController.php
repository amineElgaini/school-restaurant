<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MealController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_meals'),
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/meals",
     *     summary="List all meals",
     *     tags={"Meals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index()
    {
        return Meal::with('mealType')->get();
    }

    /**
     * @OA\Post(
     *     path="/api/meals",
     *     summary="Create a new meal",
     *     tags={"Meals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","meal_type_id"},
     *                 @OA\Property(property="name", type="string", example="Beef Steak"),
     *                 @OA\Property(property="meal_type_id", type="integer", example=1),
     *                 @OA\Property(property="description", type="string", example="Grilled beef with vegetables"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Meal created successfully"
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'meal_type_id' => 'required|exists:meal_types,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('meals', 'public');
            $validated['image'] = $path;
        }

        $meal = Meal::create($validated);

        return response()->json($meal, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/meals/{meal}",
     *     summary="Get meal details",
     *     tags={"Meals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="meal",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(response=404, description="Meal not found")
     * )
     */
    public function show(Meal $meal)
    {
        return $meal;
    }

    /**
     * @OA\Patch(
     *     path="/api/meals/{meal}",
     *     summary="Update a meal",
     *     tags={"Meals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="meal",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="meal_type_id", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Meal updated successfully"
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="Meal not found")
     * )
     */
    public function update(Request $request, Meal $meal)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'meal_type_id' => 'sometimes|exists:meal_types,id',
            'description' => 'nullable|string',
        ]);

        $meal->update($validated);

        return response()->json($meal);
    }

    /**
     * @OA\Delete(
     *     path="/api/meals/{meal}",
     *     summary="Delete a meal",
     *     tags={"Meals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="meal",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Meal deleted"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Meal not found")
     * )
     */
    public function destroy(Meal $meal)
    {
        $meal->delete();

        return response()->json(null, 204);
    }
}
