<?php

namespace App\Services;

use App\Models\AtipayTransfer;
use App\Models\User;
use Illuminate\Support\Facades\Auth as LaravelAuth;
use Illuminate\Support\Facades\DB;

class AtipayTransferService
{
    /**
     * Crea una nueva transferencia entre usuarios
     */
    public function create(array $data): AtipayTransfer
    {
        return DB::transaction(function () use ($data) {
            // Validar que el tipo sea igual entre sender y receiver
            $sender = User::findOrFail($data['sender_id']);
            $receiver = User::findOrFail($data['receiver_id']);

            // Asegurarse que no se transfiera a sí mismo
            if ($data['sender_id'] === $data['receiver_id']) {
                throw new \Exception("No puedes transferirte a ti mismo.");
            }

            // Verificar que el usuario tenga suficiente saldo de ese tipo / logica

            return AtipayTransfer::create([
                'sender_id' => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'amount' => $data['amount'],
                'type' => $data['type'], // investment o store
                'confirmed' => false,
            ]);
        });
    }

    /**
     * Confirmar una transferencia por parte del receptor
     */
    public function confirm($id)
    {
        $transfer = AtipayTransfer::findOrFail($id);

        if ($transfer->confirmed) {
            throw new \Exception('La transferencia ya fue confirmada.');
        }

        DB::transaction(function () use ($transfer) {
            $sender = $transfer->sender;
            $receiver = $transfer->receiver;
            $amount = $transfer->amount;
            $type = $transfer->type;

            // Verificar saldo del sender
            if ($type === 'investment' && $sender->atipay_investment_balance < $amount) {
                throw new \Exception('Saldo de inversión insuficiente.');
            }

            if ($type === 'store' && $sender->atipay_store_balance < $amount) {
                throw new \Exception('Saldo de tienda insuficiente.');
            }

            // Restar saldo al sender
            if ($type === 'investment') {
                $sender->atipay_investment_balance -= $amount;
                $receiver->atipay_investment_balance += $amount;
            } else {
                $sender->atipay_store_balance -= $amount;
                $receiver->atipay_store_balance += $amount;
            }

            $sender->save();
            $receiver->save();

            // Marcar la transferencia como confirmada
            $transfer->confirmed = true;
            $transfer->save();
        });

        return $transfer->fresh();
    }

    /**
     * Obtener transferencias enviadas por un usuario
     */
    public function getSentTransfers(int $userId)
    {
        return AtipayTransfer::where('sender_id', $userId)->latest()->get();
    }

    /**
     * Obtener transferencias recibidas por un usuario
     */
    public function getReceivedTransfers(int $userId)
    {
        return AtipayTransfer::where('receiver_id', $userId)->latest()->get();
    }

    /**
     * Obtener detalles de una transferencia específica
     */
    public function getById(int $id): ?AtipayTransfer
    {
        return AtipayTransfer::find($id);
    }
}
