<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentWithdrawal;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

            $promotion = Promotion::findOrFail($data['promotion_id']);
            $price = $promotion->atipay_price_promotion;

            if ($user->atipay_money < $price) {
                throw new \Exception('Saldo insuficiente para realizar esta inversión.');
            }

            $user->atipay_money -= $price;
            $user->save();

            $totalEarning = $price * ($promotion->percentaje / 100);

            // Estimación de días usando promedio 30.44 días por mes
            $estimatedTotalDays = round($promotion->duration_months * 30.44);
            $dailyEarning = $estimatedTotalDays > 0 ? round($totalEarning / $estimatedTotalDays, 2) : 0;

            return Investment::create([
                'user_id'        => $user->id,
                'promotion_id'   => $promotion->id,
                'status'         => 'pending',
                'daily_earning'  => $dailyEarning,    
                'total_earning'  => round($totalEarning, 2),
                'already_earned' => 0,
            ]);
        });
    }

    /**
     * Aprobacion de inversiones 
    */
    public function approve(Investment $investment, string $adminMessage = null): void
    {
        if ($investment->status !== 'pending') return;

        $startDate = now();
        $durationMonths = $investment->promotion->duration_months;
        $endDate = $startDate->copy()->addMonths($durationMonths)->subSecond(); 

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $dailyEarning = $totalDays > 0 ? round($investment->total_earning / $totalDays, 2) : 0;

        $investment->update([
            'status'         => 'active',
            'approved_at'    => $startDate,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'daily_earning'  => $dailyEarning,
            'already_earned' => 0,
            'last_earned_at' => $startDate, 
            'admin_message'  => $adminMessage,
        ]);
    }

    /**
     * Rechazo de inversiones
    */
    public function reject(Investment $investment, string $adminMessage = null): void
    {
        DB::transaction(function () use ($investment, $adminMessage) {
            if ($investment->status === 'rejected') return;

            $investment->update([
                'status' => 'rejected',
                'rejected_at'   => now(),
                'approved_at'   => null,
                'admin_message' => $adminMessage,
            ]);

            $user = $investment->user;
            $user->atipay_money += $investment->promotion->atipay_price_promotion;
            $user->save();
        });
    }

    /**
     * Actualizacion automatica de ganancias 
    */
    public function autoUpdateEarnings(Investment $investment)
    {
        if ($investment->status !== 'active' || !$investment->start_date) {
            return $investment;
        }

        $now = now();
        $start = Carbon::parse($investment->start_date);

        if ($now->lt($start)) {
            $investment->already_earned = 0;
            $investment->save();
            return $investment;
        }

        // Última fecha en la que se sumaron ganancias
        $lastEarned = $investment->last_earned_at 
            ? Carbon::parse($investment->last_earned_at) 
            : $start->copy()->subDay(); // <-- forzar que se sume el primer día

        // Calculando días completos desde la última actualización
        $daysElapsed = $lastEarned->diffInDays($now);

        if ($daysElapsed < 1) {
            return $investment;
        }

        // Solo sumamos los días completos transcurridos
        $earned = round($daysElapsed * $investment->daily_earning, 2);

        $investment->already_earned = min($investment->already_earned + $earned, $investment->total_earning);

        // Guardar la fecha de la última actualización
        $investment->last_earned_at = $lastEarned->copy()->addDays($daysElapsed);
        $investment->save();

        return $investment;
    }

    /**
     * Obtener ganancias de inversiones
    */
    public function getInvestmentGains(User $user, int $investmentId): array
    {
        $investment = $user->investments()->with('promotion')->where('id', $investmentId)->firstOrFail();

        if ($investment->status !== 'active') throw new \Exception('La inversión no está activa.');

        $promotion = $investment->promotion;
        $durationMonths = $promotion->duration_months;
        $startDate = Carbon::parse($investment->start_date);
        $now = now();

        $gains = [];
        for ($i = 1; $i <= $durationMonths; $i++) {
            $monthStart = $startDate->copy()->addMonthsNoOverflow($i - 1);
            $monthEnd   = $monthStart->copy()->addMonthNoOverflow()->subSecond(); 
            $daysInMonth = $monthStart->diffInDays($monthEnd) + 1;
            $monthlyGain = round($investment->daily_earning * $daysInMonth, 2);

            if ($now->greaterThanOrEqualTo($monthEnd)) {
                $status = 'completado';
                $gain = $monthlyGain;
            } elseif ($now->between($monthStart, $monthEnd)) {
                $secondsElapsed = $monthStart->diffInSeconds($now);
                $daysElapsed = floor($secondsElapsed / (24 * 60 * 60)) + 1;
                $gain = round($investment->daily_earning * min($daysElapsed, $daysInMonth), 2);
                $status = 'en curso';
            } else {
                $gain = 0;
                $status = 'pendiente';
            }

            $gains[] = [
                'month'   => $i,
                'period'  => $monthStart->format('Y-m-d H:i:s') . ' a ' . $monthEnd->format('Y-m-d H:i:s'),
                'gain'    => $gain,
                'status'  => $status
            ];
        }

        return [
            'investment_id'  => $investment->id,
            'price'          => $promotion->atipay_price_promotion,
            'percentaje'     => $promotion->percentaje,
            'duration_months'=> $durationMonths,
            'daily_earning'  => $investment->daily_earning,
            'gains_by_month' => $gains,
            'total_projected'=> $investment->total_earning,
        ];
    }

    /**
     * Obtener ganancias de diarias de inversiones
    */
    public function getInvestmentDailyGains(User $user, int $investmentId): array
    {
        $investment = $user->investments()->with('promotion')->where('id', $investmentId)->firstOrFail();

        if ($investment->status !== 'active') throw new \Exception('La inversión no está activa.');

        $startDate = Carbon::parse($investment->start_date);
        $endDate   = Carbon::parse($investment->end_date);
        $now       = now();

        $gains = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Calcular si el pago de hoy ya corresponde según la hora exacta
            if ($now->greaterThanOrEqualTo($currentDate)) {
                $status = $now->between($currentDate, $currentDate->copy()->addDay()->subSecond()) 
                    ? 'hoy' : 'completado';
                $gain = $investment->daily_earning;
            } else {
                $status = 'pendiente';
                $gain = 0;
            }

            $gains[] = [
                'date'  => $currentDate->format('Y-m-d H:i:s'),
                'gain'  => $gain,
                'status'=> $status,
            ];

            $currentDate->addDay();
        }

        return [
            'investment_id'  => $investment->id,
            'daily_earning'  => $investment->daily_earning,
            'gains_by_day'   => $gains,
            'total_projected'=> $investment->total_earning,
        ];
    }

    /**
     * Retirar ganancias
    */
    public function withdrawEarnings(User $user, Investment $investment): float
    {
        if ($investment->status !== 'active') {
            throw new \Exception('La inversión no está activa.');
        }

        $this->autoUpdateEarnings($investment);

        $available = round($investment->already_earned, 2);

        if ($available <= 0) {
            throw new \Exception('No tienes ganancias disponibles para retirar.');
        }

        return DB::transaction(function () use ($user, $investment, $available) {
            // Aumentar saldo en la wallet
            $user->atipay_money = round($user->atipay_money + $available, 2);
            $user->save();

            // Registrar en investment_withdrawals
            InvestmentWithdrawal::create([
                'investment_id' => $investment->id,
                'amount'        => $available,
                'transferred_at'=> now(),
            ]);

            // Resetear ganancias ya retiradas
            $investment->already_earned = 0;
            $investment->save();

            return $available;
        });
    }
}