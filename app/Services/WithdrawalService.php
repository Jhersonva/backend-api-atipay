<?php

namespace App\Services;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class WithdrawalService
{
    /**
     * Crear una nueva solicitud de retiro
     */
    public function create(array $data): Withdrawal
    {
        return DB::transaction(function () use ($data) {
            return Withdrawal::create([
                'user_id' => auth('api')->id(),
                'amount' => $data['amount'],
                'method' => $data['method'],
                'status' => 'earring',
            ]);
        });
    }

    /**
     * Cambiar el estado de una solicitud (por el admin)
     */
    public function updateStatus(int $id, string $status): ?Withdrawal
    {
        return DB::transaction(function () use ($id, $status) {
            $withdrawal = Withdrawal::with('user')->find($id);

            if (!$withdrawal) {
                throw new \Exception('Solicitud de retiro no encontrada.');
            }

            // Si ya está aprobado o rechazado, no se vuelve a procesar
            if (in_array($withdrawal->status, ['approved', 'rejected'])) {
                throw new \Exception('Este retiro ya ha sido procesado.');
            }

            $user = $withdrawal->user;

            if ($status === 'approved') {
                if ($user->withdrawable_balance < $withdrawal->amount) {
                    throw new \Exception('Saldo insuficiente para aprobar este retiro.');
                }

                // Restar automáticamente el balance
                $user->withdrawable_balance -= $withdrawal->amount;
                $user->save();
            }

            // Actualizar estado del retiro
            $withdrawal->status = $status;
            $withdrawal->save();

            return $withdrawal;
        });
    }

    /**
     * Obtener todos los retiros hechos por un usuario
     */
    public function getByUser(int $userId)
    {
        return Withdrawal::where('user_id', $userId)->latest()->get();
    }

    /**
     * Obtener todos los retiros del sistema (admin)
     */
    public function getAll()
    {
        return Withdrawal::with('user')->latest()->get();
    }

    /**
     * Obtener una solicitud específica
     */
    public function getById(int $id): ?Withdrawal
    {
        return Withdrawal::with('user')->find($id);
    }
}
