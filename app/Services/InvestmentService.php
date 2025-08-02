<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvestmentService
{
    public function getUserInvestments(User $user)
    {
        return $user->investments()->with('promotion', 'withdrawals')->latest()->get();
    }

    public function store(User $user, array $data): Investment
    {
        return DB::transaction(function () use ($user, $data) {
            Log::info('Iniciando store de inversión', [
                'user_id' => $user->id,
                'balance' => $user->atipay_investment_balance,
                'amount' => $data['amount'],
            ]);

            $promotion = Promotion::findOrFail($data['promotion_id']);
            $amount = $data['amount'];

            // Validar saldo
            if ($user->atipay_investment_balance < $amount) {
                Log::error('Saldo insuficiente al intentar invertir.', [
                    'user_id' => $user->id,
                    'balance' => $user->atipay_investment_balance,
                    'amount' => $amount,
                ]);
                throw new \Exception('Saldo insuficiente para realizar esta inversión.');
            }

            // Descontar saldo
            $user->atipay_investment_balance -= $amount;
            $user->save();

            // Subir comprobante
            $receiptPath = $this->uploadReceipt($data['receipt']);

            // Crear inversión
            $investment = Investment::create([
                'user_id' => $user->id,
                'promotion_id' => $promotion->id,
                'amount' => $amount,
                'receipt_path' => $receiptPath,
                'status' => 'pending',
                'daily_earning' => $this->calculateDailyEarning($amount, $promotion),
            ]);

            Log::info('Inversión creada correctamente', ['investment_id' => $investment->id]);

            return $investment;
        });
    }


    public function approve(Investment $investment): void
    {
        $investment->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);
    }

    public function reject(Investment $investment, string $adminMessage = null): void
    {
        DB::transaction(function () use ($investment, $adminMessage) {
            if ($investment->status === 'rejected') {
                return; 
            }

            $investment->update([
                'status' => 'rejected',
                'admin_message' => $adminMessage,
            ]);

            $user = $investment->user;
            $user->atipay_investment_balance += $investment->amount;
            $user->save();
        });
    }

    private function calculateDailyEarning(float $amount, Promotion $promotion): float
    {
        $totalReturn = $amount * ($promotion->percentaje / 100);
        $days = $promotion->duration_months * 30;
        return round($totalReturn / $days, 2);
    }

    private function uploadReceipt($file): string
    {
        return $file->store('receipts', 'public');
    }

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
