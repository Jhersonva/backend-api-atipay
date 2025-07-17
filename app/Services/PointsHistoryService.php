<?php

namespace App\Services;

use App\Models\PointsHistory;
use Illuminate\Database\Eloquent\Collection;

class PointsHistoryService
{
    public function getAll(): Collection
    {
        return PointsHistory::all();
    }

    public function getById(int $id): ?PointsHistory
    {
        return PointsHistory::find($id);
    }

    public function create(array $data): PointsHistory
    {
        return PointsHistory::create($data);
    }

    public function update(int $id, array $data): ?PointsHistory
    {
        $points_history = PointsHistory::find($id);
        if ($points_history) {
            $points_history->update($data);
        }
        return $points_history;
    }

    public function delete(int $id): bool
    {
        $points_history = PointsHistory::find($id);
        if ($points_history) {
            return $points_history->delete();
        }
        return false;
    }
}
