<?php

namespace App\Http\Controllers\Api\AuthUsers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthUsers\RegisterAuthRequest;
use App\Http\Requests\AuthUsers\LoginAuthRequest;
use App\Services\AuthUserService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\JsonResponse;
use Exception;

class AuthUserController extends Controller
{
    protected $authService;

    public function __construct(AuthUserService $authService)
    {
        $this->authService = $authService;
    }

    public function registerUser(RegisterAuthRequest $request)
    {
        try {
            // Llamamos al servicio para registrar un nuevo admin
            $this->authService->registerAdmin($request->all());

            return response()->json(['message' => 'Usuario creado con éxito'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function loginUser(LoginAuthRequest $request)
    {
        try {
            // Llamamos al servicio para realizar el login
            $token = $this->authService->login($request->only(['username', 'password']));

            return response()->json([
                'token' => $token,
                'expires_in' => JWTAuth::factory()->getTTL()
            ], 200);
        } catch (\Exception $e) { 
            return response()->json(['error' => $e->getMessage()], 401); 
        }
    }


    public function refreshToken(): JsonResponse
    {
        try {
            // Llamamos al servicio para refrescar el token
            $newToken = $this->authService->refreshToken();
            return new JsonResponse([
                'token' => $newToken,
                'expires_in' => JWTAuth::factory()->getTTL()
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUser()
    {
        try {
            // Llamamos al servicio para obtener el usuario
            $user = $this->authService->getUser();
            return response()->json($user, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function logout()
    {
        try {
            // Llamamos al servicio para hacer logout
            $message = $this->authService->logout();
            return response()->json(['message' => $message], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
