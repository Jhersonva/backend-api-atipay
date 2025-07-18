<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Products\ProductsController;
use App\Http\Controllers\Api\AuthUsers\AuthUserController;
use App\Http\Controllers\ProductCategories\ProductCategoriesController;


use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\NoUserExists;

// Rutas de la API AuthUser
Route::post('register', [AuthUserController::class, 'registerUser'])->middleware(NoUserExists::class);
Route::post('login', [AuthUserController::class, 'loginUser']);

//Auth
Route::middleware(IsUserAuth::class)->group(function () {

    // Rutas de la API AuthUser Authenticated
    Route::controller(AuthUserController::class)->group(function () {
        Route::post('refresh-token', 'refreshToken');
        Route::post('logout', 'logout');
        Route::get('user', 'getUser');
    });

    // Rutas de la API AuthUser -  Authenticated - Admin
    Route::middleware(IsAdmin::class)->group(function () {        
    });
});

// Product Categories - Públicas (CRUD completo sin autenticación)
Route::get('product-categories', [ProductCategoriesController::class, 'index']);
Route::post('product-categories', [ProductCategoriesController::class, 'store']);
Route::get('product-categories/{id}', [ProductCategoriesController::class, 'show']);
Route::put('product-categories/{id}', [ProductCategoriesController::class, 'update']);
Route::delete('product-categories/{id}', [ProductCategoriesController::class, 'destroy']);

// Route Api Products
Route::get('products', [ProductsController::class, 'index']);
Route::post('products', [ProductsController::class, 'store']);
Route::get('products/{id}', [ProductsController::class, 'show']);
Route::put('products/{id}', [ProductsController::class, 'update']);
Route::delete('products/{id}', [ProductsController::class, 'destroy']);