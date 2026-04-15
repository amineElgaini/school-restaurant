<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
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

    /**
     * Show users who have reservations for a specific date.
     * Optional filter by name.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'name' => ['nullable', 'string'],
        ]);

        $date = $validated['date'];
        $name = $validated['name'] ?? null;

        $usersQuery = User::query()
            ->whereHas('reservations', function ($q) use ($date) {
                $q->whereHas('menuMeal', function ($q2) use ($date) {
                    $q2->whereDate('served_at', $date);
                });
            });

        if ($name) {
            $usersQuery->where('name', 'like', "%{$name}%");
        }

        $users = $usersQuery
            ->select('id', 'name', 'email', 'image')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    /**
     * Show reservation details for one user on a specific date.
     */
    public function show(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
        ]);

        $user = User::select('id', 'name', 'email', 'image')
            ->findOrFail($validated['user_id']);

        $reservations = Reservation::with([
                'menuMeal.meal.mealType'
            ])
            ->where('user_id', $validated['user_id'])
            ->whereHas('menuMeal', function ($q) use ($validated) {
                $q->whereDate('served_at', $validated['date']);
            })
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'user' => $user,
            'reservations' => $reservations,
        ]);
    }
}