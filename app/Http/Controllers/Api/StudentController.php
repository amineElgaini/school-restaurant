<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuMeal;
use App\Models\Reservation;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StudentController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('role:student'),
            new Middleware('permission:submit_complaints', only: ['submitComplaint'])
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/student/reservations",
     *     summary="Show reserved meals for the current student",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function reservations(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $query = Reservation::with(['menuMeal.meal.mealType'])
            ->where('user_id', auth()->id());

        $query->whereHas('menuMeal', function ($q) use ($request) {
            $q->whereDate('served_at', $request->input('date'));
        });

        return $query->get();
    }

    /**
     * @OA\Post(
     *     path="/api/student/reservations",
     *     summary="Reserve a meal",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"menu_meal_id"},
     *             @OA\Property(property="menu_meal_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Reservation created"),
     *     @OA\Response(response=400, description="Already reserved a meal of this type for this date"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function reserve(Request $request)
    {
        $validated = $request->validate([
            'menu_meal_id' => 'required|exists:menu_meals,id',
        ]);

        $menuMeal = MenuMeal::with('meal.mealType')->findOrFail($validated['menu_meal_id']);

        // Students can only reserve during the week BEFORE the served week (i.e. reserve for next week, not current week).
        $servedAt = Carbon::parse($menuMeal->served_at);
        $nextWeekStart = Carbon::now()->addWeek()->startOfWeek();
        $nextWeekEnd = Carbon::now()->addWeek()->endOfWeek();

        if (! $servedAt->betweenIncluded($nextWeekStart, $nextWeekEnd)) {
            return response()->json([
                'message' => 'Reservations are only allowed for next week.',
            ], 422);
        }

        // Check if user already reserved a meal of the same type for this date
        $alreadyReservedType = Reservation::where('user_id', auth()->id())
            ->whereHas('menuMeal', function ($query) use ($menuMeal) {
                $query->whereDate('served_at', $menuMeal->served_at)
                    ->whereHas('meal', function ($q) use ($menuMeal) {
                        $q->where('meal_type_id', $menuMeal->meal->meal_type_id);
                    });
            })
            ->exists();

        if ($alreadyReservedType) {
            return response()->json(['message' => 'You have already reserved a meal of this type for this date'], 400);
        }

        $reservation = Reservation::create([
            'user_id' => auth()->id(),
            'menu_meal_id' => $validated['menu_meal_id'],
        ]);

        return response()->json($reservation->load('menuMeal.meal.mealType'), 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/student/reservations/{reservation}",
     *     summary="Remove a reserved meal",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="reservation",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Reservation removed"),
     *     @OA\Response(response=403, description="Unauthorized (not your reservation)"),
     *     @OA\Response(response=404, description="Reservation not found")
     * )
     */
    public function removeReservation(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reservation->delete();

        return response()->json(['message' => 'Reservation removed'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/student/complaints",
     *     summary="Submit a complaint",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"description"},
     *             @OA\Property(property="description", type="string", example="The meal was cold.")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Complaint submitted"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function submitComplaint(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string',
            'description' => 'required|string',
        ]);

        $complaint = Complaint::create([
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'description' => $validated['description'],
        ]);

        return response()->json($complaint, 201);
    }
}
