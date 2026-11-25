<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class CheckUserRoleUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && in_array($request->user()->role, ['ritel', 'grosir', 'admin'])) {
            return $next($request);
        }

        return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk mengakses sumber daya [user]'], 403);
    }
}
