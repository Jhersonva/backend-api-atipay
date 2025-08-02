<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentWithdrawal;
use App\Models\User;

class InvestmentWithdrawalService
{
    public function requestWithdrawal(Investment $investment, float $amount): InvestmentWithdrawal
    {
        if ($investment->status !== 'active') {
            throw new \Exception('La inversión no está activa.');
        }

        return InvestmentWithdrawal::create([
            'investment_id' => $investment->id,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    public function approve(InvestmentWithdrawal $withdrawal): void
    {
        $withdrawal->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);
    }

    public function getUserWithdrawals(User $user)
    {
        return InvestmentWithdrawal::whereHas('investment', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->latest()->get();
    }

    public function reject(InvestmentWithdrawal $withdrawal, string $adminMessage = null): void
    {
        $withdrawal->update([
            'status' => 'rejected',
            'admin_message' => $adminMessage,
        ]);
    }
}
