<?php

namespace App\Services;

use App\Models\Commissions;
use Illuminate\Database\Eloquent\Collection;

class CommissionService
{
    public function getAll(): Collection
    {
        return Commissions::all();
    }

    public function getById(int $id): ?Commissions
    {
        return Commissions::find($id);
    }

    public function create(array $data): Commissions
    {
        return Commissions::create($data);
    }

    public function update(int $id, array $data): ?Commissions
    {
        $commissions = Commissions::find($id);
        if ($commissions) {
            $commissions->update($data);
        }
        return $commissions;
    }

    public function delete(int $id): bool
    {
        $commissions = Commissions::find($id);
        if ($commissions) {
            return $commissions->delete();
        }
        return false;
    }
}
