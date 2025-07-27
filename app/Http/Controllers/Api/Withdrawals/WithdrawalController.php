<?php

namespace App\Http\Controllers\Api\Withdrawals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Withdrawals\StoreWithdrawalRequest;
use App\Http\Requests\Withdrawals\UpdateWithdrawalStatusRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    protected WithdrawalService $withdrawalService;

    public function __construct(WithdrawalService $withdrawalService)
    {
        $this->withdrawalService = $withdrawalService;
    }

    /**
     * Usuario: Crear nueva solicitud de retiro
     */
    public function store(StoreWithdrawalRequest $request): JsonResponse
    {
        $withdrawal = $this->withdrawalService->create($request->validated());
        return response()->json(['message' => 'Solicitud enviada correctamente', 'data' => $withdrawal], 201);
    }

    /**
     * Usuario: Ver sus solicitudes de retiro
     */
    public function myWithdrawals(): JsonResponse
    {
        $userId = auth('api')->id();
        $withdrawals = $this->withdrawalService->getByUser($userId);
        return response()->json($withdrawals);
    }

    /**
     * Admin: Ver todas las solicitudes
     */
    public function index(): JsonResponse
    {
        $all = $this->withdrawalService->getAll();
        return response()->json($all);
    }

    /**
     * Admin: Ver detalle de una solicitud
     */
    public function show($id): JsonResponse
    {
        $withdrawal = $this->withdrawalService->getById($id);
        if (!$withdrawal) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }

        return response()->json($withdrawal);
    }

    /**
     * Admin: Aprobar o rechazar una solicitud
     */
    public function updateStatus(UpdateWithdrawalStatusRequest $request, $id): JsonResponse
    {
        $withdrawal = $this->withdrawalService->updateStatus($id, $request->validated()['status']);
        if (!$withdrawal) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }

        return response()->json(['message' => 'Estado actualizado', 'data' => $withdrawal]);
    }
}
