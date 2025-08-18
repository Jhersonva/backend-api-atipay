<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\CommissionSetting;
use App\Models\MonthlyUserPoint;
use Illuminate\Support\Facades\DB;

class ReferralCommissionService
{
    const MAX_LEVEL = 5;
    const REQUIRED_MONTHLY_POINTS = 100;

    /**
     * Procesar comisiones cuando un usuario genera puntos (ej. por compra).
     */
    public function process(User $referrerUser, int $pointsGenerated, string $sourceType = 'purchase'): void
    {
        $referralChain = $this->getReferralChain($referrerUser);

        DB::transaction(function () use ($referralChain, $referrerUser, $pointsGenerated, $sourceType) {
            $month = now()->month;
            $year = now()->year;

            foreach ($referralChain as $level => $uplineUser) {
                if (!$this->qualifiesForCommission($uplineUser, $month, $year)) {
                    continue;
                }

                $percentage = CommissionSetting::getPercentageForLevel($level);
                if ($percentage <= 0) continue;

                $commissionAmount = ($pointsGenerated * $percentage) / 100;

                // Crear la comisión
                ReferralCommission::create([
                    'user_id' => $uplineUser->id,
                    'referred_user_id' => $referrerUser->id,
                    'level' => $level,
                    'commission_amount' => $commissionAmount,
                    'points_generated' => $pointsGenerated,
                    'source_type' => $sourceType,
                ]);

                // Sumar puntos y saldo al usuario de nivel superior
                $uplineUser->accumulated_points += $commissionAmount;
                $uplineUser->atipay_money += $commissionAmount;
                $uplineUser->save();
            }

            // Guardar puntos del usuario que generó la acción (nivel 0)
            $this->addPersonalPoints($referrerUser, $pointsGenerated, $month, $year);
        });
    }

    /**
     * Obtener la cadena de referidos hacia arriba hasta 5 niveles.
     */
    private function getReferralChain(User $user): array
    {
        $chain = [];
        $current = $user;
        $level = 1;

        while ($current->referrer && $level <= self::MAX_LEVEL) {
            $chain[$level] = $current->referrer;
            $current = $current->referrer;
            $level++;
        }

        return $chain;
    }

    /**
     * Verifica si el usuario tiene al menos 100 puntos personales este mes.
     */
    private function qualifiesForCommission(User $user, int $month, int $year): bool
    {
        $points = MonthlyUserPoint::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->value('points');

        return $points >= self::REQUIRED_MONTHLY_POINTS;
    }

    /**
     * Sumar puntos personales del usuario.
     */
    private function addPersonalPoints(User $user, int $points, int $month, int $year): void
    {
        $monthly = MonthlyUserPoint::firstOrNew([
            'user_id' => $user->id,
            'month' => $month,
            'year' => $year,
        ]);

        $monthly->points = ($monthly->points ?? 0) + $points;
        $monthly->save();
    }
}
