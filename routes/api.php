<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Promotions\PromotionController;
use App\Http\Controllers\Api\AuthUsers\AuthUserController;
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

//Routes Publicas:

//Route Api Customers
Route::get('promotions', [PromotionController::class, 'index']);
Route::post('promotions', [PromotionController::class, 'store']);
Route::get('promotions/{id}', [PromotionController::class, 'show']);
Route::put('promotions/{id}', [PromotionController::class, 'update']);
Route::delete('promotions/{id}', [PromotionController::class, 'destroy']);

