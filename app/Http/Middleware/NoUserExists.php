<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class NoUserExists
{
    public function handle(Request $request, Closure $next): Response
    {
        // Contar usuarios con rol admin
        $adminCount = User::where('role', User::ROLE_ADMIN)->count();

        if ($adminCount >= 3) {
            return response()->json(['error' => 'No se pueden registrar más de 3 administradores.'], 403);
        }

        return $next($request);
    }
}
