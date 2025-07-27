<?php

namespace App\Http\Controllers\Api\AtipayTransfers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AtipayTransfers\StoreAtipayTransferRequest;
use App\Http\Requests\AtipayTransfers\ConfirmTransferRequest;
use App\Services\AtipayTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AtipayTransferController extends Controller
{
    protected AtipayTransferService $transferService;

    public function __construct(AtipayTransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Listar transferencias enviadas por el usuario autenticado
     */
    public function sent(): JsonResponse
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        return response()->json($this->transferService->getSentTransfers($user->id));
    }

    /**
     * Listar transferencias recibidas por el usuario autenticado
     */
    public function received(): JsonResponse
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        return response()->json($this->transferService->getReceivedTransfers($user->id));
    }

    /**
     * Crear una nueva transferencia
     */
    public function store(StoreAtipayTransferRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['sender_id'] = auth('api')->id();

        $transfer = $this->transferService->create($validated);

        return response()->json(['message' => 'Transferencia creada correctamente', 'data' => $transfer], 201);
    }

    /**
     * Confirmar una transferencia recibida
     */
    public function confirm($id): JsonResponse
    {
        try {
            $transfer = $this->transferService->confirm($id);
            return response()->json(['message' => 'Transferencia confirmada', 'data' => $transfer]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Ver detalles de una transferencia
     */
    public function show($id): JsonResponse
    {
        $transfer = $this->transferService->getById($id);
        if (!$transfer) {
            return response()->json(['message' => 'Transferencia no encontrada'], 404);
        }

        return response()->json($transfer);
    }
}
