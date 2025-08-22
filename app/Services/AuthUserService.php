<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
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
                $referrer->atipay_money += 0.50;
                $referrer->save();

                $referredBy = $referrer->id;
            }
        }

        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $partnerRoleId,
            'status' => 'inactive',
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

        $users = User::whereIn('role_id', [$partnerRoleId, $adminRoleId])
            ->with(['role', 'referrer.role'])
            ->orderBy('username')
            ->get();

        // Mapeo para la respuesta
        return $users->map(function ($user) {
            return [
                'id'                => $user->id,
                'username'          => $user->username,
                'email'             => $user->email,
                'role_id'           => $user->role_id,
                'status'            => $user->status,
                'atipay_money'      => $user->atipay_money,
                'accumulated_points'=> $user->accumulated_points,
                'reference_code'    => $user->reference_code,
                'referred_by'       => $user->referred_by,
                'registration_date' => $user->registration_date,
                'registration_time' => $user->registration_time,
                'referral_url'      => $user->referral_url,
                'role' => [
                    'id'   => $user->role->id,
                    'name' => $user->role->name,
                ],
  
                'referrer' => $user->referrer ? [
                    'username' => $user->referrer->username,
                ] : null,
            ];
        });
    }

    public function updatePartnerByAdmin(int $partnerId, array $data)
    {
        $partnerRoleId = Role::where('name', User::ROLE_PARTNER)->value('id');

        $user = User::where('id', $partnerId)
            ->where('role_id', $partnerRoleId)
            ->firstOrFail();

        $user->update($data);

        return $user;
    }

    public function updateOwnProfile(array $data)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            throw new \Exception('Usuario no autenticado.');
        }

        // Solo partners y admins pueden editar su perfil
        if (!$user->hasRole(User::ROLE_PARTNER) && !$user->hasRole(User::ROLE_ADMIN)) {
            throw new \Exception('No tienes permiso para editar tu perfil.');
        }

        $user->update($data);

        return $user;
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
