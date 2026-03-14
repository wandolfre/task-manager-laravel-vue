<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public routes: register and login (no token required).
| Protected routes: logout and all task CRUD (require auth:sanctum token).
|
*/

// Authentication (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes — require a valid Sanctum API token
Route::middleware('auth:sanctum')->group(function () {
    // Logout (revoke current token)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Authenticated user profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Task CRUD — uses apiResource for standard REST endpoints
    Route::apiResource('tasks', TaskController::class);
});
