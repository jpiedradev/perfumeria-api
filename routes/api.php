<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (sin autenticación)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Ruta de prueba solo para admin
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/test', function () {
        return response()->json([
            'success' => true,
            'message' => 'Acceso autorizado como admin',
            'user' => auth()->user()->name,
        ]);
    });
});
