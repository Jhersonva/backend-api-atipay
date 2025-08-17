<?php

namespace App\Services;

use App\Models\AtipayTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AtipayTransferService
{
    /**
     * Crea una nueva transferencia entre usuarios
     */
    public function create(array $data): AtipayTransfer
    {
        return DB::transaction(function () use ($data) {
            $sender   = User::findOrFail($data['sender_id']);
            $receiver = User::findOrFail($data['receiver_id']);

            if ($data['sender_id'] === $data['receiver_id']) {
                throw new \Exception("No puedes transferirte a ti mismo.");
            }

            if ($sender->atipay_money < $data['amount']) {
                throw new \Exception("Saldo insuficiente.");
            }

            // Descontar al sender al crear la transferencia
            $sender->atipay_money -= $data['amount'];
            $sender->save();

            return AtipayTransfer::create([
                'sender_id'   => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'amount'      => $data['amount'],
                'status'      => 'pending',
            ]);
        });
    }

    /**
     * Confirmar (aprobar) una transferencia por parte del receptor
     */
    public function approve($id): AtipayTransfer
    {
        $transfer = AtipayTransfer::findOrFail($id);

        if ($transfer->status !== 'pending') {
            throw new \Exception('La transferencia ya fue procesada.');
        }

        DB::transaction(function () use ($transfer) {
            $receiver = $transfer->receiver;
            $amount   = $transfer->amount;

            $receiver->atipay_money += $amount;
            $receiver->save();

            $transfer->status = 'approved';
            $transfer->save();
        });

        return $transfer->fresh();
    }

    /**
     * Rechazar una transferencia
     */
    public function reject($id): AtipayTransfer
    {
        $transfer = AtipayTransfer::findOrFail($id);

        if ($transfer->status !== 'pending') {
            throw new \Exception('La transferencia ya fue procesada.');
        }

        DB::transaction(function () use ($transfer) {
            $sender = $transfer->sender;
            $sender->atipay_money += $transfer->amount;
            $sender->save();

            $transfer->status = 'rejected';
            $transfer->save();
        });

        return $transfer->fresh();
    }

    /**
     * Expiracion de transferencia
     */
    public function expirePendingTransfers()
    {
        $transfers = AtipayTransfer::where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(30))
            ->get();

        foreach ($transfers as $transfer) {
            DB::transaction(function () use ($transfer) {
                $sender = $transfer->sender;
                $sender->atipay_money += $transfer->amount;
                $sender->save();

                $transfer->status = 'not_evaluated';
                $transfer->save();
            });
        }

        return $transfers;
    }

    /**
     * Expiracion de transferencia
     */
    private function expire(AtipayTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $sender = $transfer->sender;
            $sender->atipay_money += $transfer->amount;
            $sender->save();

            $transfer->status = 'not_evaluated';
            $transfer->save();
        });
    }

    /**
     * Obtener transferencias enviadas por un usuario
     */
    public function getSentTransfers(int $userId)
    {
        $transfers = AtipayTransfer::where('sender_id', $userId)->latest()->get();

        foreach ($transfers as $transfer) {
            if ($transfer->status === 'pending' && $transfer->created_at <= now()->subMinutes(1)) {
                $this->expire($transfer);
                $transfer->refresh();
            }
        }

        return $transfers;
    }

    /**
     * Obtener transferencias recibidas por un usuario
     */
    public function getReceivedTransfers(int $userId)
    {
        $transfers = AtipayTransfer::where('receiver_id', $userId)->latest()->get();

        foreach ($transfers as $transfer) {
            if ($transfer->status === 'pending' && $transfer->created_at <= now()->subMinutes(1)) {
                $this->expire($transfer);
                $transfer->refresh();
            }
        }

        return $transfers;
    }

    /**
     * Obtener detalles de una transferencia específica
     */
    public function getById(int $id): ?AtipayTransfer
    {
        $transfer = AtipayTransfer::find($id);

        if ($transfer && $transfer->status === 'pending' && $transfer->created_at <= now()->subMinutes(1)) {
            $this->expire($transfer);
            $transfer->refresh();
        }

        return $transfer;
    }
}
