<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthUserService
{ 
    public function registerAdmin(array $data)
    {
        $referredBy = null;
        $partnerRoleId = Role::where('name', User::ROLE_PARTNER)->value('id');

        if (!empty($data['reference_code'])) {
            $referrer = User::where('reference_code', $data['reference_code'])->first();
            if ($referrer) {
                $referrer->accumulated_points += 10;
                $referrer->withdrawable_balance += 0.50;
                $referrer->save();

                $referredBy = $referrer->id;
            }
        }

        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $partnerRoleId,
            'reference_code' => Str::random(8),
            'referred_by' => $referrer->id,
            'registration_date' => now('America/Lima')->toDateString(),
            'registration_time' => now('America/Lima')->format('h:i:s A'),
        ]);
    }

    public function login(array $credentials)
    {
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new \Exception('Credenciales inválidas.');
            }

            $user = JWTAuth::user()->load('role');

            return [
                'token' => $token,
                'user' => $user
            ];

        } catch (JWTException $e) {
            throw new \Exception('No se pudo crear el token: ' . $e->getMessage());
        }
    }

    public function getUsersByRolePartnerOrAdmin()
    {
        $partnerRoleId = Role::where('name', User::ROLE_PARTNER)->value('id');
        $adminRoleId   = Role::where('name', User::ROLE_ADMIN)->value('id');

        return User::whereIn('role_id', [$partnerRoleId, $adminRoleId])
            ->with('role')
            ->orderBy('username')
            ->get();
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
