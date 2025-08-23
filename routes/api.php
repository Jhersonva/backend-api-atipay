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
use App\Http\Controllers\Api\Purchases\PurchaseRequestController;
use App\Http\Controllers\Api\Reward\RewardController;


use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUserAuth;

// Rutas públicas
Route::post('register', [AuthUserController::class, 'registerUser']);
Route::post('login', [AuthUserController::class, 'loginUser']);

Route::middleware(IsUserAuth::class)->group(function () {

    // Authenticated User
    Route::controller(AuthUserController::class)->group(function () {
        Route::post('refresh-token', 'refreshToken');
        Route::post('logout', 'logout');
        Route::get('user', 'getUser');
    });

    // Edit Partner Profile (Auth)
    Route::put('partner/profile', [AuthUserController::class, 'updateOwnProfile']);
    
    // Buscar socio con su id
    Route::get('partners/{id}/username', [AuthUserController::class, 'getPartnerUsername']);

    // Ver si califique para ser un socio activo
    Route::get('purchase-requests', [PurchaseRequestController::class, 'index']);

    // Atipay Transfers (Auth)
    Route::get('atipay-transfers/sent', [AtipayTransferController::class, 'sent']);  
    Route::get('atipay-transfers/received', [AtipayTransferController::class, 'received']); 
    Route::post('atipay-transfers', [AtipayTransferController::class, 'store']);
    Route::post('atipay-transfers/{id}/approve', [AtipayTransferController::class, 'approve']);
    Route::post('atipay-transfers/{id}/reject', [AtipayTransferController::class, 'reject']);
    Route::get('atipay-transfers/{id}', [AtipayTransferController::class, 'show']);

    // Promotions (Auth)
    Route::get('promotions', [PromotionController::class, 'index']);
    Route::get('promotions/{id}', [PromotionController::class, 'show']);

    // Withdrawals (Auth)
    Route::post('withdrawals', [WithdrawalController::class, 'store']);
    Route::get('withdrawals/my', [WithdrawalController::class, 'myWithdrawals']);

    // Atipay Recharges (Auth)
    Route::post('atipay-recharges', [AtipayRechargeController::class, 'store']);
    Route::get('atipay-recharges/my', [AtipayRechargeController::class, 'myRecharges']);

    // Products (Auth)
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/my-purchase-requests', [ProductController::class, 'myPurchaseRequests']);
    Route::post('products/purchase', [ProductController::class, 'purchase']);

    // Reward (Auth)
    Route::get('/rewards', [RewardController::class, 'index']);
    Route::get('/rewards/{id}', [RewardController::class, 'show']);

    // Canjear Recomenzas (Auth)
    Route::post('/rewards/{id}/redeem', [RewardController::class, 'redeem']);

    // Ver Red de afiliados propios (Auth)
    Route::get('referrals/my-network-count', [ReferralController::class, 'myReferralLevelsCount']);
    Route::get('referrals/my-network', [ReferralController::class, 'myReferralNetwork']);

    // Inversiones (Auth)
    Route::get('investments', [InvestmentController::class, 'index']);
    Route::post('investments', [InvestmentController::class, 'store']);
    Route::get('investments/{id}/daily-gains', [InvestmentController::class, 'dailyGains']);
    Route::get('investments/{id}/monthly-gains', [InvestmentController::class, 'monthlyGains']);
    Route::get('investments/active', [InvestmentController::class, 'active']);
    Route::post('investments/{id}/withdraw', [InvestmentController::class, 'withdrawEarnings']);

    // Commissions Settings (Auth)
    Route::get('commissions/settings', [CommissionSettingController::class, 'index']);

    // Admin-only routes
    Route::middleware(IsAdmin::class)->group(function () {

        // Listar Users (Admin)
        Route::get('users', [AuthUserController::class, 'index']);

        // Edit Admin Profile (Admin)
        Route::put('admin/profile', [AuthUserController::class, 'updateOwnAdminProfile']);
        
        // Edit Partner Profile por (Admin)
        Route::put('admin/profile/partners/{id}', [AuthUserController::class, 'updatePartner']);

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

        // Reward (admin)
        Route::post('/rewards', [RewardController::class, 'store']); 
        Route::put('/rewards/{id}', [RewardController::class, 'update']);
        Route::delete('/rewards/{id}', [RewardController::class, 'destroy']);

        // Commissions Settings (admin)
        Route::post('commissions/settings', [CommissionSettingController::class, 'updateOrCreate']);
        Route::delete('commissions/settings/{level}', [CommissionSettingController::class, 'destroy']);

        // Inversiones (admin)
        Route::get('investments/pending', [InvestmentController::class, 'pending']);
        Route::post('investments/{id}/approve', [InvestmentController::class, 'approve']);
        Route::post('investments/{id}/reject', [InvestmentController::class, 'reject']);

    });
    
    Route::get('products/{id}', [ProductController::class, 'show']);
});
