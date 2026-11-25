<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRoleAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        return response()->json(['success' => false,'message' => 'Anda tidak memiliki izin untuk mengakses sumber daya [admin].'], 403);
    }
}