<?php

namespace App\Http\Controllers\Api\Referrals;

use App\Http\Controllers\Controller;
use App\Models\MonthlyUserPoint;
use App\Models\ReferralCommission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    /**
     * Ver puntos generados por afiliados del usuario autenticado por mes
     */
    public function myMonthlyPoints(): JsonResponse
    {
        $userId = Auth::id();

        $points = MonthlyUserPoint::where('user_id', $userId)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get(['month', 'year', 'points']);

        return response()->json($points);
    }

    /**
     * Ver comisiones ganadas por nivel
     */
    public function myCommissionsByLevel(): JsonResponse
    {
        $userId = Auth::id();

        $commissions = ReferralCommission::where('user_id', $userId)
            ->select('level', DB::raw('SUM(commission_amount) as total'))
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        return response()->json($commissions);
    }

    /**
     * Ver red de afiliados de cualquier usuario (por nivel) - ADMIN
     */
    public function referralNetwork($userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        $levels = [];
        $currentLevelUsers = [$user];

        for ($level = 1; $level <= 5; $level++) {
            $nextLevelUsers = [];
            foreach ($currentLevelUsers as $u) {
                $referrals = $u->referrals()->get(['id', 'username', 'email']);
                if ($referrals->isNotEmpty()) {
                    $levels[$level] = $levels[$level] ?? collect();
                    $levels[$level] = $levels[$level]->merge($referrals);
                    $nextLevelUsers = array_merge($nextLevelUsers, $referrals->all());
                }
            }
            $currentLevelUsers = $nextLevelUsers;
        }

        return response()->json($levels);
    }

    public function myReferralLevelsCount()
    {
        $user = auth('api')->user();
        $levels = [];

        $currentLevelUsers = [$user->id]; 

        for ($level = 1; $level <= 5; $level++) {
            $nextLevelUsers = User::whereIn('referred_by', $currentLevelUsers)->pluck('id')->toArray();
            $levels["nivel_$level"] = count($nextLevelUsers);
            $currentLevelUsers = $nextLevelUsers;
        }

        return response()->json([
            'success' => true,
            'data' => $levels,
        ]);
    }
}