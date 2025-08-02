<?php

namespace App\Http\Controllers\Api\Commissions;

use App\Http\Controllers\Controller;
use App\Models\ReferralCommission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CommissionSummaryController extends Controller
{
    /**
     * Generar resumen de comisiones por usuario - ADMIN
     */
    public function summaryByUser(): JsonResponse
    {
        $summary = ReferralCommission::select('user_id', DB::raw('SUM(commission_amount) as total'))
            ->groupBy('user_id')
            ->with('user:id,username,email')
            ->orderByDesc('total')
            ->get();

        return response()->json($summary);
    }
}