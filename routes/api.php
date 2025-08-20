<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Promotions\PromotionController;
use App\Http\Controllers\Api\AuthUsers\AuthUserController;
use App\Http\Controllers\Api\AtipayTransfers\AtipayTransferController;
use App\Http\Controllers\Api\Withdrawals\WithdrawalController;
use App\Http\Controllers\Api\AtipayRecharges\AtipayRechargeController;
use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\Commissions\CommissionSettingController;
use App\Http\Controllers\Api\Referrals\ReferralController;
use App\Http\Controllers\Api\Commissions\CommissionSummaryController;
use App\Http\Controllers\Api\Investments\InvestmentController;
use App\Http\Controllers\Api\Investments\InvestmentWithdrawalController;


use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUserAuth;

// Rutas públicas
Route::post('register', [AuthUserController::class, 'registerUser']);
Route::post('login', [AuthUserController::class, 'loginUser']);

Route::middleware(IsUserAuth::class)->group(function () {

    // Authenticated user
    Route::controller(AuthUserController::class)->group(function () {
        Route::post('refresh-token', 'refreshToken');
        Route::post('logout', 'logout');
        Route::get('user', 'getUser');
    });

    // Atipay Transfers
    Route::get('atipay-transfers/sent', [AtipayTransferController::class, 'sent']);  //Transferencias enviadas
    Route::get('atipay-transfers/received', [AtipayTransferController::class, 'received']); //Transferencias recibidas
    Route::post('atipay-transfers', [AtipayTransferController::class, 'store']);
    Route::post('atipay-transfers/{id}/approve', [AtipayTransferController::class, 'approve']);
    Route::post('atipay-transfers/{id}/reject', [AtipayTransferController::class, 'reject']);
    Route::get('atipay-transfers/{id}', [AtipayTransferController::class, 'show']);

    // Promotions (socios)
    Route::get('promotions', [PromotionController::class, 'index']);
    Route::get('promotions/{id}', [PromotionController::class, 'show']);

    // Withdrawals (socios)
    Route::post('withdrawals', [WithdrawalController::class, 'store']);
    Route::get('withdrawals/my', [WithdrawalController::class, 'myWithdrawals']);

    // Atipay Recharges (socios)
    Route::post('atipay-recharges', [AtipayRechargeController::class, 'store']);
    Route::get('atipay-recharges/my', [AtipayRechargeController::class, 'myRecharges']);

    // Products (socios)
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/my-purchase-requests', [ProductController::class, 'myPurchaseRequests']);
    Route::post('products/purchase', [ProductController::class, 'purchase']);

    //Route::get('referrals/my-monthly-points', [ReferralController::class, 'myMonthlyPoints']);
    //Route::get('referrals/my-commissions-by-level', [ReferralController::class, 'myCommissionsByLevel']);

    // Ver Red de afiliados propios (socios)
    Route::get('referrals/my-network-count', [ReferralController::class, 'myReferralLevelsCount']);
    Route::get('referrals/my-network', [ReferralController::class, 'myReferralNetwork']);

    // Inversiones (socios)
    Route::get('investments', [InvestmentController::class, 'index']);
    Route::post('investments', [InvestmentController::class, 'store']);
    Route::get('investments/{id}/daily-gains', [InvestmentController::class, 'dailyGains']);
    Route::get('investments/{id}/monthly-gains', [InvestmentController::class, 'monthlyGains']);
    Route::get('investments/active', [InvestmentController::class, 'active']);
    Route::post('investments/{id}/withdraw', [InvestmentController::class, 'withdrawEarnings']);

    // Retiros de inversiones (socios)
    //Route::get('investment-withdrawals', [InvestmentWithdrawalController::class, 'index']);
    //Route::post('investment-withdrawals', [InvestmentWithdrawalController::class, 'store']);

    // Commissions Settings (socios and admin)
    Route::get('commissions/settings', [CommissionSettingController::class, 'index']);

    // Admin-only routes
    Route::middleware(IsAdmin::class)->group(function () {

        // Listar Users (Partner y Admin)
        Route::get('users', [AuthUserController::class, 'index']);

        // Promotions (admin)
        Route::post('promotions', [PromotionController::class, 'store']);
        Route::put('promotions/{id}', [PromotionController::class, 'update']);
        Route::delete('promotions/{id}', [PromotionController::class, 'destroy']);

        // Withdrawals (admin)
        Route::get('withdrawals', [WithdrawalController::class, 'index']);
        Route::get('withdrawals/{id}', [WithdrawalController::class, 'show']);
        Route::post('withdrawals/{id}/approve', [WithdrawalController::class, 'approve']);
        Route::post('withdrawals/{id}/reject', [WithdrawalController::class, 'reject']);


        // Atipay Recharges (admin)
        Route::get('atipay-recharges', [AtipayRechargeController::class, 'index']);
        Route::get('atipay-recharges/{id}', [AtipayRechargeController::class, 'show']);
        Route::post('atipay-recharges/{id}/approve', [AtipayRechargeController::class, 'approve']);
        Route::post('atipay-recharges/{id}/reject', [AtipayRechargeController::class, 'reject']);
    
        // Products (admin)
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);
        Route::get('products/purchase-requests', [ProductController::class, 'allPurchaseRequests']);
        Route::post('products/purchase-requests/{id}/approve', [ProductController::class, 'approvePurchase']);
        Route::post('products/purchase-requests/{id}/reject', [ProductController::class, 'rejectPurchase']);

        // Commissions Settings (admin)
        Route::post('commissions/settings', [CommissionSettingController::class, 'updateOrCreate']);
        Route::delete('commissions/settings/{level}', [CommissionSettingController::class, 'destroy']);

        //
        //Route::get('admin/referrals/network/{userId}', [ReferralController::class, 'viewNetworkByLevels']);
        //Route::get('admin/commissions/summary', [CommissionSummaryController::class, 'summaryByUser']);

        // Inversiones (admin)
        Route::get('investments/pending', [InvestmentController::class, 'pending']);
        //Route::get('investments/active', [InvestmentController::class, 'active']);
        Route::post('investments/{id}/approve', [InvestmentController::class, 'approve']);
        Route::post('investments/{id}/reject', [InvestmentController::class, 'reject']);

        // Retiros de inversiones (admin)
        Route::get('investment-withdrawals', [InvestmentWithdrawalController::class, 'all']);
        Route::post('investment-withdrawals/{id}/approve', [InvestmentWithdrawalController::class, 'approve']);
        Route::post('investment-withdrawals/{id}/reject', [InvestmentWithdrawalController::class, 'reject']);

    });
    
    Route::get('products/{id}', [ProductController::class, 'show']);
});
