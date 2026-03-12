<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });

    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::apiResource('users', AdminController::class);
        Route::apiResource('roles', RoleController::class)->except(['update', 'show']);
    });
});
