<?php

namespace App\Http\Controllers\Api\Commissions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commissions\CommissionRequest;
use App\Http\Requests\Commissions\UpadateCommissionRequest;
use App\Services\CommissionService;
use Illuminate\Http\JsonResponse;

class PromotionController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(): JsonResponse
    {
        $commission = $this->promotionService->getAll();
        return response()->json($commission);
    }

    public function store(StorePromotionRequest $request): JsonResponse
    {
        $commission = $this->commissionService->create($request->validated());
        return response()->json($commission, 201);
    }

    public function show($id): JsonResponse
    {
        $commission = $this->commissionService->getById($id);
        if (!$commission) {
            return response()->json(['message' => 'Comision no encontrada'], 404);
        }
        return response()->json($commission);
    }

    public function update(UpdatePromotionRequest $request, $id): JsonResponse
    {
        $commission = $this->commissionService->update($id, $request->validated());
        if (!$commission) {
            return response()->json(['message' => 'Comision no encontrada'], 404);
        }
        return response()->json($commission);
    }

    public function destroy($id): JsonResponse
    {
        $deleted = $this->commissionService->delete($id);
        return $deleted
            ? response()->json(['message' => 'Comision eliminada con exito'])
            : response()->json(['message' => 'Comision no encontrada'], 404);
    }
}
