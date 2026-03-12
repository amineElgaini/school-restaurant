<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\MenuMeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index()
    {
        return Reservation::with(['user', 'menuMeal.meal', 'menuMeal.menu'])->get();
    }

    public function show(Reservation $reservation)
    {
        return $reservation->load(['user', 'menuMeal.meal', 'menuMeal.menu']);
    }

    public function stats()
    {
        $stats = MenuMeal::with('meal')
            ->withCount('reservations')
            ->get()
            ->map(function ($menuMeal) {
                return [
                    'meal_name' => $menuMeal->meal->name,
                    'meal_type' => $menuMeal->meal->type,
                    'reservation_date' => $menuMeal->reservation_date,
                    'reservations_count' => $menuMeal->reservations_count,
                ];
            });

        return response()->json($stats);
    }
}
