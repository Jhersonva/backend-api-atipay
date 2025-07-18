<?php

namespace App\Services;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthUserService
{ 
    public function registerAdmin(array $data)
    {
        // Verifica si ya hay 3 admins / no va
        $referredBy = null;

        if (!empty($data['reference_code'])) {
            $referrer = User::where('reference_code', $data['reference_code'])->first();
            if ($referrer) {
                $referredBy = $referrer->id;
            }
        }

        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'reference_code' => Str::random(8),
            'referred_by' => $referredBy,
        ]);
    }

     public function login(array $credentials)
    {
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new \Exception('Credenciales inválidas.'); 
            }
            return $token;
        } catch (JWTException $e) {
            throw new \Exception('No se pudo crear el token: ' . $e->getMessage()); 
        }
    }

    public function refreshToken()
    {
        return JWTAuth::parseToken()->refresh();
    }

    public function getUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                throw new \Exception('Usuario no autenticado.');
            }
            return $user;
        } catch (JWTException $e) {
            throw new \Exception('Error de token: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());  
            return 'Cierre de Sesión Exitosa';  
        } catch (JWTException $e) {
            throw new JWTException('No se pudo invalidar el token: ' . $e->getMessage());
        }
    }
}