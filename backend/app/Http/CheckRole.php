<?php

namespace App\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $roles = [
            'admin' => 1,
            'client' => 2,
            'freelancer' => 3,
        ];

        if (!isset($roles[$role]) || $request->user()->role_id !== $roles[$role]) {
            return response()->json(['message' => "Accès réservé aux {$role}s."], 403);
        }

        return $next($request);
    }
}
