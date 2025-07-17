<?php

namespace App\Http\Controllers\Api\PointsHistory;

use App\Http\Controllers\Controller;
use App\Http\Requests\PointsHistory\PointsHistoryRequest;
use App\Http\Requests\PointsHistory\UpdatePointsHistoryRequest;
use App\Services\PointsHistoryService;
use Illuminate\Http\JsonResponse;

class PointsHistoryController extends Controller
{
    protected $point_historyService;

    public function __construct(PointsHistoryService $point_historyService)
    {
        $this->PointsHistoryService = $point_historyService;
    }

    public function index(): JsonResponse
    {
        $points_history = $this->PointsHistoryService->getAll();
        return response()->json($points_history);
    }

    public function create(PointsHistoryRequest $request): JsonResponse
    {
        $points_history = $this->PointsHistoryService->create($request->validated());
        return response()->json($points_history, 201);
    }

    public function show($id): JsonResponse
    {
        $points_history = $this->PointsHistoryService->getById($id);
        if (!$points_history) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
        return response()->json($points_history);
    }

    public function update(UpdatePointsHistoryRequest $request, $id): JsonResponse
    {
        $points_history = $this->PointsHistoryService->update($id, $request->validated());
        if (!$points_history) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
        return response()->json($points_history);
    }

    public function destroy($id): JsonResponse
    {
        $deleted = $this->PointsHistoryService->delete($id);
        return $deleted
            ? response()->json(['message' => 'Registro eliminado con exito'])
            : response()->json(['message' => 'Registro no encontrado'], 404);
    }

}
