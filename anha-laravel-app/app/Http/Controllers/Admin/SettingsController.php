<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly ApiClient $api)
    {
    }

    public function index(): View
    {
        $configs = [];
        $admins = [];
        $apiError = null;

        try {
            $configs = $this->api->unwrapList($this->api->get('/admin-system-configs', ['per_page' => 100]))['rows'];
            $admins = $this->api->unwrapList($this->api->get('/admin-settings/admins'))['rows'];
        } catch (ApiException $exception) {
            $apiError = $exception->getMessage();
        }

        return view('admin.settings.index', [
            'title' => 'Pengaturan',
            'configs' => $configs,
            'admins' => $admins,
            'apiError' => $apiError,
            'currentUserId' => data_get(session('auth_user'), 'id'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $payload = Arr::except($request->all(), ['_token', '_method', 'group']);

        return $this->callAndBack(fn () => $this->api->patch('/admin-system-configs-bulk-update', $payload), 'Pengaturan berhasil disimpan.');
    }

    public function clearCache(): RedirectResponse
    {
        return $this->callAndBack(fn () => $this->api->post('/admin-system-configs/clear-cache'), 'Cache berhasil dibersihkan.');
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        return $this->callAndBack(fn () => $this->api->postMultipart('/admin-settings/admins', $validated), 'Admin berhasil ditambahkan.');
    }

    public function toggleAdmin(int|string $id): RedirectResponse
    {
        if ((string) data_get(session('auth_user'), 'id') === (string) $id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.');
        }

        return $this->callAndBack(fn () => $this->api->patch("/admin-settings/admins/{$id}/toggle"), 'Status admin berhasil diubah.');
    }

    public function destroyAdmin(int|string $id): RedirectResponse
    {
        if ((string) data_get(session('auth_user'), 'id') === (string) $id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        return $this->callAndBack(fn () => $this->api->delete("/admin-settings/admins/{$id}"), 'Admin berhasil dihapus.');
    }

    private function callAndBack(callable $callback, string $message): RedirectResponse
    {
        try {
            $callback();

            return back()->with('success', $message);
        } catch (ApiException $exception) {
            return $this->backForApiException($exception);
        }
    }

    private function backForApiException(ApiException $exception): RedirectResponse
    {
        if ($exception->status === 401) {
            session()->forget(['api_token', 'token_type', 'auth_user']);

            return redirect()->route('login')->with('error', 'Sesi API berakhir. Silakan login ulang.');
        }

        return back()->with('error', $exception->getMessage());
    }
}
