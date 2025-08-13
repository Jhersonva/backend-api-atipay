<?php

namespace App\Http\Controllers\Api\Investments;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Investments\StoreInvestmentRequest;
use App\Services\InvestmentService;
use Illuminate\Http\JsonResponse;
use App\Models\Investment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    protected InvestmentService $investmentService;

    public function __construct(InvestmentService $investmentService)
    {
        $this->investmentService = $investmentService;
    }

    /**
     * Listar mis inversiones (todas: pendientes, activas, finalizadas)
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $investments = $this->investmentService->getUserInvestments($user);
        return response()->json($investments);
    }

    /**
     * Registrar nueva inversión
     */
    public function store(StoreInvestmentRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        $data = [
            'promotion_id' => $request->input('promotion_id'),
        ];

        $investment = $this->investmentService->store($user, $data);

        return response()->json([
            'message' => 'Inversión registrada correctamente. Pendiente de validación del administrador.',
            'investment' => $investment
        ], 201);
    }

    /**
     * Ver ganancias diarias de una inversión específica
     */
    public function dailyGains($id): JsonResponse
    {
        $user = auth('api')->user();
        $gains = $this->investmentService->getInvestmentGains($user, $id);
        return response()->json($gains);
    }

    /** 
    *Acciones Admin 
    */
    // Listar inversiones pendientes
    public function pending()
    {
        $pending = Investment::with('user', 'promotion')
                    ->where('status', 'pending')
                    ->latest()
                    ->get();
        return response()->json($pending);
    }

    // Listar inversiones activas
    public function active()
    {
        $active = Investment::with('user', 'promotion')
                    ->where('status', 'active')
                    ->latest()
                    ->get();
        return response()->json($active);
    }

    // Aprobar inversión
    public function approve(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        if ($investment->status !== 'pending') {
            return response()->json(['error' => 'Esta inversión ya fue validada.'], 400);
        }

        $this->investmentService->approve($investment, $request->admin_message);

        return response()->json(['message' => 'Inversión aprobada exitosamente.']);
    }

    // Rechazar inversión
    public function reject(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);
        
        if ($investment->status !== 'pending') {
            return response()->json(['error' => 'Esta inversión ya fue validada.'], 400);
        }

        $adminMessage = $request->input('admin_message');

        $this->investmentService->reject($investment, $adminMessage);

        return response()->json(['message' => 'Inversión rechazada correctamente y saldo reembolsado.']);
    }
}
