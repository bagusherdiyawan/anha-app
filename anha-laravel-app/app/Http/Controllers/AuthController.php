<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('api_token') && data_get($request->session()->get('auth_user'), 'role') === 'admin') {
            return redirect()->route('dashboard');
        }

        return view('auth.login', [
            'redirect' => $request->query('redirect', session('redirect')),
            'forbidden' => $request->boolean('forbidden'),
        ]);
    }

    public function login(Request $request, ApiClient $api): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['nullable', 'string'],
            'redirect' => ['nullable', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
        ]);

        try {
            $response = $api->postMultipart('/login', [
                'email' => trim($validated['email']),
                'login_code' => trim($validated['email']),
                'password' => $validated['password'] ?? '',
            ]);
        } catch (ApiException $exception) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => $exception->getMessage(),
            ]);
        }

        $user = data_get($response, 'data.user');
        $token = data_get($response, 'data.token');
        $tokenType = data_get($response, 'data.token_type', 'Bearer');
        $role = strtolower((string) data_get($user, 'type', data_get($user, 'role', '')));

        if (! is_array($user) || ! $token) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Respons login dari server tidak valid.',
            ]);
        }

        if ($role !== 'admin') {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Akun ini tidak memiliki akses admin. Hubungi administrator jika Anda merasa ini salah.',
            ]);
        }

        $user['role'] = $role;
        $request->session()->regenerate();
        $request->session()->put('api_token', $token);
        $request->session()->put('token_type', $tokenType ?: 'Bearer');
        $request->session()->put('auth_user', $user);

        return redirect()->to($this->safeRedirect($validated['redirect'] ?? null));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['api_token', 'token_type', 'auth_user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function safeRedirect(?string $redirect): string
    {
        $redirect = trim((string) $redirect);
        $redirect = preg_replace('#^/master(?=/|$)#', '', $redirect) ?: $redirect;

        $prefixes = [
            '/dashboard',
            '/katalog',
            '/transaksi',
            '/utama',
            '/loyalitas',
            '/laporan',
            '/sistem',
            '/program',
        ];

        if (! empty($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//') && $redirect !== '/') {
            foreach ($prefixes as $prefix) {
                if ($redirect === $prefix || str_starts_with($redirect, $prefix.'/')) {
                    return $redirect;
                }
            }
        }

        return route('dashboard');
    }
}
