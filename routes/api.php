<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthUsers\AuthUserController;
use App\Http\Controllers\Api\ProductCategories\ProductCategoriesController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\NoUserExists;

// Rutas públicas
Route::post('register', [AuthUserController::class, 'registerUser'])->middleware(NoUserExists::class);
Route::post('login', [AuthUserController::class, 'loginUser']);

// Rutas protegidas (usuarios autenticados)
Route::middleware(IsUserAuth::class)->group(function () {
    
    Route::controller(AuthUserController::class)->group(function () {
        Route::post('refresh-token', 'refreshToken');
        Route::post('logout', 'logout');
        Route::get('user', 'getUser');
    });

    // Rutas de categorías (lectura para todos los autenticados)
    Route::get('product-categories', [ProductCategoriesController::class, 'index']);
    Route::get('product-categories/{id}', [ProductCategoriesController::class, 'show']);

    // Admin: crear, actualizar y eliminar categorías
    Route::middleware(IsAdmin::class)->group(function () {
        Route::post('product-categories', [ProductCategoriesController::class, 'store']);
        Route::put('product-categories/{id}', [ProductCategoriesController::class, 'update']);
        Route::delete('product-categories/{id}', [ProductCategoriesController::class, 'destroy']);
    });
});
