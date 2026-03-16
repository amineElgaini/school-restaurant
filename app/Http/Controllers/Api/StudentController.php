<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuMeal;
use App\Models\Reservation;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StudentController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('role:student'),
            new Middleware('permission:submit_reclamation', only: ['submitReclamation'])
        ];
    }

    /**
     * Show reserved meals for the current student, optionally for a specific date.
     */
    public function reservations(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $query = Reservation::with(['menuMeal.meal'])
            ->where('user_id', auth()->id());

        $query->whereHas('menuMeal', function ($q) use ($request) {
            $q->where('reservation_date', $request->query('date'));
        });

        return $query->get();
    }

    /**
     * Reserve a meal.
     */
    public function reserve(Request $request)
    {
        $validated = $request->validate([
            'menu_meal_id' => 'required|exists:menu_meals,id',
        ]);

        $menuMeal = MenuMeal::with('meal.type')->findOrFail($validated['menu_meal_id']);
        
        // Check if user already reserved a meal of the same type for this date
        $alreadyReservedType = Reservation::where('user_id', auth()->id())
            ->whereHas('menuMeal', function ($query) use ($menuMeal) {
                $query->where('reservation_date', $menuMeal->reservation_date)
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

        return response()->json($reservation->load('menuMeal.meal.type'), 201);
    }

    /**
     * Remove a reserved meal.
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
     * Submit a reclamation.
     */
    public function submitReclamation(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        $complaint = Complaint::create([
            'user_id' => auth()->id(),
            'description' => $validated['description'],
        ]);

        return response()->json($complaint, 201);
    }
}
