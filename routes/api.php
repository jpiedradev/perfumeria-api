<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|-----------------------------------------------------
| API Routes
|-----------------------------------------------------
*/

/// ===================================
// RUTAS PÚBLICAS (sin autenticación)
// ===================================

// Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Categorías
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);

// Productos
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/brands', [ProductController::class, 'brands']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// ===================================
// RUTAS PROTEGIDAS (requieren autenticación)
// ===================================
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Pedidos
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
});

// ===================================
// RUTAS DE ADMIN (requieren ser admin)
// ===================================
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);

    // Gestión de Pedidos
    Route::get('/orders', [AdminController::class, 'orders']);
    Route::get('/orders/{id}', [AdminController::class, 'orderShow']);
    Route::patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);

    // Gestión de Productos
    Route::get('/products', [AdminController::class, 'products']);
    Route::post('/products', [AdminController::class, 'storeProduct']);
    Route::post('/products/{id}', [AdminController::class, 'updateProduct']); // POST porque incluye imagen
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);
});
