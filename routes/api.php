<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\MenuMealController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::apiResource('users', AdminController::class)->except(['show']);
        Route::get('roles/permissions', [RoleController::class, 'rolesWithPermissions']);
    });

    Route::apiResource('meals', MealController::class);
    Route::apiResource('menu-meals', MenuMealController::class)->except(['show', 'update']);
    Route::get('reservations/stats', [ReservationController::class, 'stats']);
    Route::apiResource('reservations', ReservationController::class)->only(['index', 'show']);

    // Student Routes
    Route::prefix('student')->group(function () {
        Route::get('/reservations', [StudentController::class, 'reservations']);
        Route::post('/reservations', [StudentController::class, 'reserve']);
        Route::delete('/reservations/{reservation}', [StudentController::class, 'removeReservation']);
        Route::post('/complaints', [StudentController::class, 'submitComplaint']);
    });
});
