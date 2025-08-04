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
     * Registrar nueva inversión (con comprobante)
     */
    public function store(StoreInvestmentRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        // Guardar archivo de comprobante
        //$path = $request->file('receipt')->store('investment_receipts', 'public');

        $data = [
            'promotion_id' => $request->input('promotion_id'),
            'amount' => $request->input('amount'),
            'receipt' => $request->file('receipt'),
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
    public function approve($id)
    {
        $investment = Investment::findOrFail($id);
        if ($investment->status !== 'pending') {
            return response()->json(['error' => 'Esta inversión ya fue validada.'], 400);
        }

        $now = Carbon::now();

        $investment->status = 'active';
        $investment->start_date = $now;
        $investment->approved_at = $now;
        $investment->end_date = $now->copy()->addMonths($investment->promotion->duration_months);

        $investment->save();

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
