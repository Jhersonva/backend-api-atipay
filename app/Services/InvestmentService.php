<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvestmentService
{
    /**
    * Inversiones de un usuario
    */
    public function getUserInvestments(User $user)
    {
        return $user->investments()->with('promotion', 'withdrawals')->latest()->get();
    }

    /**
    * Crear un nuev solicitud de inversión
    */
    public function store(User $user, array $data): Investment
    {
        return DB::transaction(function () use ($user, $data) {

            // Obtener la promoción
            $promotion = Promotion::findOrFail($data['promotion_id']);

            // Usar el precio de la promoción
            $price = $promotion->atipay_price_promotion;

            // Validar saldo usando atipay_money
            if ($user->atipay_money < $price) {
                throw new \Exception('Saldo insuficiente para realizar esta inversión.');
            }

            // Descontar del saldo atipay_money
            $user->atipay_money -= $price;
            $user->save();

            // Crear inversión
            $investment = Investment::create([
                'user_id'       => $user->id,
                'promotion_id'  => $promotion->id,
                'status'        => 'pending',
                'daily_earning' => $this->calculateDailyEarning($price, $promotion),
            ]);

            return $investment;
        });
    }

    /**
    * Aprobar solicitud de inversion
    */
    public function approve(Investment $investment, string $adminMessage = null): void
    {
        // Fecha de inicio: a las 00:00 del día actual
        $startDate = now()->addDay()->startOfDay();

        // Fecha de fin: sumar los meses que dura la promoción y fijar a las 00:00
        $durationMonths = $investment->promotion->duration_months;
        $endDate = $startDate->copy()->addMonths($durationMonths)->startOfDay();

        $investment->update([
            'status'        => 'active',
            'approved_at'   => now(),
            'rejected_at'   => null,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'admin_message' => $adminMessage,
        ]);
    }

    /**
    * Rechazar solicitud de inversion
    */
    public function reject(Investment $investment, string $adminMessage = null): void
    {
        DB::transaction(function () use ($investment, $adminMessage) {
            if ($investment->status === 'rejected') {
                return; 
            }

            $investment->update([
                'status' => 'rejected',
                'rejected_at'   => now(),
                'approved_at'   => null,
                'admin_message' => $adminMessage,
            ]);

            // Obtener el precio de la promoción para reembolsar
            $promotionPrice = $investment->promotion->atipay_price_promotion;

            // Devolver el monto al saldo atipay_money
            $user = $investment->user;
            $user->atipay_money += $promotionPrice;
            $user->save();
        });
    }

    /**
    * Calcular el monto de la inversión
    */
    private function calculateDailyEarning(float $price, Promotion $promotion): float
    {
        // Ganancia total en función del precio de la promoción
        $totalReturn = $price * ($promotion->percentaje / 100);

        // Duración total en días
        $days = $promotion->duration_months * 30;

        return round($totalReturn / $days, 2);
    }

    /**
    * Obtener el monto de la inversión
    */
    public function getInvestmentGains(User $user, int $investmentId): array
    {
        $investment = $user->investments()->where('id', $investmentId)->firstOrFail();

        if ($investment->status !== 'active') {
            throw new \Exception('La inversión no está activa.');
        }

        $startDate = $investment->approved_at ?? $investment->created_at;
        $today = now();
        $daysActive = $startDate->diffInDays($today);

        $totalGain = round($daysActive * $investment->daily_earning, 2);

        return [
            'investment_id' => $investment->id,
            'days_active' => $daysActive,
            'daily_earning' => $investment->daily_earning,
            'total_gain' => $totalGain,
            'approved_at' => $investment->approved_at,
        ];
    }
}
