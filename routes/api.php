<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthUsers\AuthUserController;
use App\Http\Controllers\Api\ProductCategories\ProductCategoriesController;
use App\Http\Controllers\Api\Products\ProductsController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\NoUserExists;

// Rutas públicas
Route::post('register', [AuthUserController::class, 'registerUser'])->middleware(NoUserExists::class);
Route::post('login', [AuthUserController::class, 'loginUser']);

// Rutas protegidas
Route::middleware(IsUserAuth::class)->group(function () {

    // Usuario autenticado
    Route::controller(AuthUserController::class)->group(function () {
        Route::post('refresh-token', 'refreshToken');
        Route::post('logout', 'logout');
        Route::get('user', 'getUser');
    });

    // Categorías: lectura pública, edición solo admin
    Route::get('product-categories', [ProductCategoriesController::class, 'index']);
    Route::get('product-categories/{id}', [ProductCategoriesController::class, 'show']);

    Route::middleware(IsAdmin::class)->group(function () {
        Route::post('product-categories', [ProductCategoriesController::class, 'store']);
        Route::put('product-categories/{id}', [ProductCategoriesController::class, 'update']);
        Route::delete('product-categories/{id}', [ProductCategoriesController::class, 'destroy']);
    });

    // Productos: lectura pública
    Route::get('products', [ProductsController::class, 'index']);
    Route::get('products/{id}', [ProductsController::class, 'show']);

    // Admin puede gestionar productos
    Route::middleware(IsAdmin::class)->group(function () {
        Route::post('products', [ProductsController::class, 'store']);
        Route::put('products/{id}', [ProductsController::class, 'update']);
        Route::delete('products/{id}', [ProductsController::class, 'destroy']);
    });
});
