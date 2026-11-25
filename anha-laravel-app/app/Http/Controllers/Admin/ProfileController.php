<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly ApiClient $api)
    {
    }

    public function index(): View
    {
        $id = data_get(session('auth_user'), 'id');
        $profile = session('auth_user', []);
        $apiError = null;

        if ($id) {
            try {
                $profile = $this->api->unwrapItem($this->api->get("/users/{$id}"));
            } catch (ApiException $exception) {
                $apiError = $exception->getMessage();
            }
        }

        return view('admin.profile.index', ['title' => 'Profil', 'profile' => $profile, 'apiError' => $apiError]);
    }

    public function update(Request $request): RedirectResponse
    {
        $id = data_get(session('auth_user'), 'id');
        abort_if(! $id, 401);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'min:6'],
            'address' => ['nullable', 'string', 'min:3'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }

        try {
            $response = $this->api->put("/users/{$id}", $validated);
            $sessionUser = array_merge(session('auth_user', []), $this->api->unwrapItem($response));
            $sessionUser['role'] = data_get($sessionUser, 'role', data_get($sessionUser, 'type', 'admin'));
            session(['auth_user' => $sessionUser]);

            return back()->with('success', 'Profil berhasil disimpan.');
        } catch (ApiException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }
}
