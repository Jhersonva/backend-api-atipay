<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Promotions\PromotionController;
use App\Http\Controllers\Api\PointsHistory\PointsHistoryController;
use App\Http\Controllers\Api\Commissions\CommissionController;
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


//Route Points History
Route::get('points-history', [PointsHistoryController::class, 'index']);
Route::post('points-history', [PointsHistoryController::class, 'create']);
Route::get('points-history/{id}', [PointsHistoryController::class, 'show']);
Route::put('points-history/{id}', [PointsHistoryController::class, 'update']);
Route::delete('points-history/{id}', [PointsHistoryController::class, 'destroy']);


//Route Points History
Route::get('commissions', [CommissionController::class, 'index']);
Route::post('commissions', [CommissionController::class, 'create']);
Route::get('commissions/{id}', [CommissionController::class, 'show']);
Route::put('commissions/{id}', [CommissionController::class, 'update']);
Route::delete('commissions/{id}', [CommissionController::class, 'destroy']);