<?php

namespace App\Http\Controllers\Api\Reward;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rewards\StoreRewardRequest;
use App\Http\Requests\Rewards\UpdateRewardRequest;
use App\Services\RewardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class RewardController extends Controller
{
    protected RewardService $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    /**
     * Listar todas las recompensas
     */
    public function index(): JsonResponse
    {
        $rewards = $this->rewardService->getAll();
        return response()->json($rewards, 200);
    }

    /**
     * Crear una nueva recompensa
     */
    public function store(StoreRewardRequest $request): JsonResponse
    {
        $reward = $this->rewardService->store($request->validated());

        return response()->json([
            'message' => 'Recompensa creada exitosamente',
            'data' => [
                'id' => $reward->id,
                'name' => $reward->name,
                'description' => $reward->description,
                'redeem_points' => $reward->redeem_points,
                'image_url' => $reward->image_url,
            ]
        ], 201);
    }

    /**
     * Canjear una recompensa
     */
    public function redeem(int $id): JsonResponse
    {
        $user = auth('api')->user(); 

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $result = $this->rewardService->redeemReward($id, $user->id);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json([
            'message' => $result['message'],
            'reward' => [
                'id' => $result['data']['reward']->id,
                'name' => $result['data']['reward']->name,
                'description' => $result['data']['reward']->description,
                'redeem_points' => $result['data']['reward']->redeem_points,
                'image_url' => $result['data']['reward']->image_url,
            ],
            'remaining_points' => $result['data']['remaining_points']
        ], 200);
    }

    /**
     * Mostrar una recompensa específica
     */
    public function show(int $id): JsonResponse
    {
        $reward = $this->rewardService->getById($id);

        if (!$reward) {
            return response()->json(['message' => 'Recompensa no encontrada'], 404);
        }

        return response()->json($reward, 200);
    }

    /**
     * Actualizar una recompensa
     */
    public function update(UpdateRewardRequest $request, int $id): JsonResponse
    {
        $reward = $this->rewardService->update($id, $request->validated());

        if (!$reward) {
            return response()->json(['message' => 'Recompensa no encontrada'], 404);
        }

        return response()->json([
            'message' => 'Recompensa actualizada exitosamente',
            'data' => [
                'id' => $reward->id,
                'name' => $reward->name,
                'description' => $reward->description,
                'redeem_points' => $reward->redeem_points,
                'image_url' => $reward->image_url,
            ]
        ], 200);
    }

    /**
     * Eliminar una recompensa
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->rewardService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Recompensa no encontrada'], 404);
        }

        return response()->json(['message' => 'Recompensa eliminada exitosamente'], 200);
    }
}