<?php

namespace App\Http\Controllers\Api\Investments;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Investments\StoreInvestmentRequest;
use App\Services\InvestmentService;
use Illuminate\Http\JsonResponse;
use App\Models\Investment;
use Illuminate\Support\Facades\DB;
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

        $investments->transform(function ($investment) {
            $investment = $this->investmentService->autoUpdateEarnings($investment);

            // Redondear valores a 2 decimales antes de devolver
            $investment->daily_earning   = round($investment->daily_earning, 2);
            $investment->total_earning   = round($investment->total_earning, 2);
            $investment->already_earned  = round($investment->already_earned, 2);

            return $investment;
        });

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

        try {
            $investment = $this->investmentService->store($user, $data);

            // Redondear valores
            $investment->daily_earning   = round($investment->daily_earning, 2);
            $investment->total_earning   = round($investment->total_earning, 2);
            $investment->already_earned  = round($investment->already_earned, 2);

            return response()->json([
                'message' => 'Inversión registrada correctamente. Pendiente de validación del administrador.',
                'investment' => $investment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ver ganancias diarias de una inversión específica
     */
    public function dailyGains($id): JsonResponse
    {
        $user = auth('api')->user();
        $investment = Investment::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $this->investmentService->autoUpdateEarnings($investment);

        $gains = $this->investmentService->getInvestmentDailyGains($user, $id);

        // Redondear ganancias diarias
        foreach ($gains['gains_by_day'] as &$day) {
            $day['gain'] = round($day['gain'], 2);
        }

        return response()->json($gains);
    }

    /**
     * Ver ganancias mensuales de una inversión específica
     */
    public function monthlyGains($id): JsonResponse
    {
        $user = auth('api')->user();
        $gains = $this->investmentService->getInvestmentGains($user, $id);

        // Redondear ganancias mensuales
        foreach ($gains['gains_by_month'] as &$month) {
            $month['gain'] = round($month['gain'], 2);
        }

        return response()->json($gains);
    }

    public function withdrawEarnings($id): JsonResponse
    {
        /**
         * @var \App\Models\User $user
         */
        $user = auth('api')->user();
        $investment = Investment::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $monto = $this->investmentService->withdrawEarnings($user, $investment);

            return response()->json([
                'message'      => 'Ganancias retiradas exitosamente.',
                'monto'        => $monto,
                'nuevo_saldo'  => round($user->atipay_money, 2)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /** 
    *Acciones Admin 
    */

    /** 
    * Solicitudes de inversiones pendientes
    */
    public function pending()
    {
        $pending = Investment::with('user', 'promotion')
                    ->where('status', 'pending')
                    ->latest()
                    ->get();
        return response()->json($pending);
    }

    /** 
    * Solicitudes de inversiones activas
    */
    public function active()
    {
        $active = Investment::with('user', 'promotion')
                    ->where('status', 'active')
                    ->latest()
                    ->get();
        return response()->json($active);
    }

    /** 
    * Solicitudes de inversiones aprobadas
    */
    public function approve(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        if ($investment->status !== 'pending') {
            return response()->json(['error' => 'Esta inversión ya fue validada.'], 400);
        }

        $this->investmentService->approve($investment, $request->admin_message);

        return response()->json(['message' => 'Inversión aprobada exitosamente.']);
    }

    /** 
    * Solicitudes de inversiones rechazadas
    */
    public function reject(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);
        
        if ($investment->status !== 'pending') {
            return response()->json(['error' => 'Esta inversión ya fue validada.'], 400);
        }

        $this->investmentService->reject($investment, $request->input('admin_message'));

        return response()->json(['message' => 'Inversión rechazada correctamente y saldo reembolsado.']);
    }
}
