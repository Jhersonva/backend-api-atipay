<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if ($user && $user->role === User::ROLE_ADMIN) {
            return $next($request);
        } else{
            return response()->json(['message' => 'You are not ADMIN'], 403);
        }
    }
}
