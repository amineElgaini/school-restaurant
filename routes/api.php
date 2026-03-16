<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\MealController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['role', 'permissions']);
    });

    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::apiResource('users', AdminController::class)->except(['show']);
        Route::post('users/{user}/permissions', [AdminController::class, 'updatePermissions']);
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('roles/permissions', [RoleController::class, 'rolesWithPermissions']);
    });

    Route::apiResource('meals', MealController::class);

    // Student Routes
    Route::prefix('student')->group(function () {
        Route::get('/available-meals', [StudentController::class, 'availableMeals']);
        Route::get('/reservations', [StudentController::class, 'reservations']);
        Route::post('/reservations', [StudentController::class, 'reserve']);
        Route::delete('/reservations/{reservation}', [StudentController::class, 'removeReservation']);
        Route::post('/reclamations', [StudentController::class, 'submitReclamation']);
    });

});
