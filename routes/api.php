<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Promotions\PromotionController;
use App\Http\Controllers\Api\AuthUsers\AuthUserController;
use App\Http\Controllers\Api\AtipayTransfers\AtipayTransferController;
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

    ////Route Api AtipayTransfers
    Route::get('atipay-transfers/sent', [AtipayTransferController::class, 'sent']);
    Route::get('atipay-transfers/received', [AtipayTransferController::class, 'received']);
    Route::post('atipay-transfers', [AtipayTransferController::class, 'store']);
    Route::post('atipay-transfers/confirm/{id}', [AtipayTransferController::class, 'confirm']);
    Route::get('atipay-transfers/{id}', [AtipayTransferController::class, 'show']);

    //Route Api Promotions
    Route::get('promotions', [PromotionController::class, 'index']);
    Route::get('promotions/{id}', [PromotionController::class, 'show']);

    // Rutas de la API AuthUser -  Authenticated - Admin
    Route::middleware(IsAdmin::class)->group(function () {

        //Route Api Promotions - Acciones
        Route::post('promotions', [PromotionController::class, 'store']);
        Route::put('promotions/{id}', [PromotionController::class, 'update']);
        Route::delete('promotions/{id}', [PromotionController::class, 'destroy']);
    });
});

//Routes Publicas:

