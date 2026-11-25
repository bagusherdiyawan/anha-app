<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('api_token')) {
            return redirect()
                ->route('login')
                ->with('redirect', $request->getRequestUri());
        }

        $role = strtolower((string) data_get($request->session()->get('auth_user'), 'role', ''));

        if ($role !== 'admin') {
            $request->session()->forget(['api_token', 'token_type', 'auth_user']);

            return redirect()
                ->route('login', ['forbidden' => 1])
                ->withErrors(['email' => 'Akses ditolak. Hanya akun admin yang dapat membuka halaman tersebut.']);
        }

        return $next($request);
    }
}
