<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\MenuMeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReservationController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_reservations'),
        ];
    }

    public function index(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'name' => 'nullable|string'
        ]);

        $date = $request->input('date');
        $name = $request->input('name');

        $usersQuery = \App\Models\User::whereHas('reservations', function ($q) use ($date) {
            $q->whereHas('menuMeal', function ($q2) use ($date) {
                $q2->whereDate('reservation_date', $date);
            });
        });

        if ($name) {
            $usersQuery->where('name', 'like', "%{$name}%");
        }

        $users = $usersQuery->get();

        return response()->json($users);
    }


    public function show(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);

        $query = Reservation::with(['menuMeal.meal'])
            ->where('user_id', $request->user_id);

        $query->whereHas('menuMeal', function ($q) use ($request) {
            $q->where('reservation_date', $request->query('date'));
        });

        return $query->get();
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
