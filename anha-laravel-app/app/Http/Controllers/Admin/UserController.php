<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly ApiClient $api)
    {
    }

    private function resource(): array
    {
        return [
            'title' => 'Pengguna',
            'endpoint' => '/users',
            'path' => '/sistem/pengguna',
            'indexView' => 'admin.system.users.index',
            'formView' => 'admin.system.users.form',
            'createRoute' => 'users.create',
            'storeRoute' => 'users.store',
            'editRoute' => 'users.edit',
            'updateRoute' => 'users.update',
            'deleteRoute' => 'users.destroy',
            'columns' => [
                ['key' => 'name', 'label' => 'Nama', 'image' => 'photo_full_url'],
                ['key' => 'email', 'label' => 'Kode'],
                ['key' => 'phone', 'label' => 'Telepon'],
                ['key' => 'role', 'label' => 'Role', 'type' => 'badge'],
                ['key' => 'email_verified_at', 'label' => 'Verifikasi', 'type' => 'badge'],
            ],
            'filters' => ['role' => 'Role'],
        ];
    }

    public function getCustomers(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $query = ['limit' => 50];

        if ($search !== '') {
            $query['q'] = $search;
        }

        try {
            $payload = $this->api->get($this->resource()['endpoint'], $query);
            $list = $this->api->unwrapList($payload);

            $users = collect($list['rows'])
                ->whereIn('role', ['grosir', 'ritel'])
                ->map(function ($user) {
                    $code = data_get($user, 'email', '');
                    $role = ucfirst((string) data_get($user, 'role', ''));
                    $label = data_get($user, 'name', 'User #' . $user['id']);
                    if ($code !== '') {
                        $label .= " ({$code} - {$role})";
                    } elseif ($role !== '') {
                        $label .= " ({$role})";
                    }

                    return [
                        'id' => $user['id'],
                        'name' => $label,
                    ];
                });

            if ($search === '' || str_contains(strtolower('semua'), strtolower($search)) || str_contains(strtolower('all'), strtolower($search))) {
                $users = $users->prepend([
                    'id' => 0,
                    'name' => 'Semua Pelanggan (Broadcast)',
                ]);
            }

            return $users->values()->all();
        } catch (ApiException $exception) {
            return [];
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        $resource = $this->resource();
        $query = $this->cleanQuery($request, ['page', 'limit', 'role']);
        if ($request->filled('q')) {
            $query['q'] = trim((string) $request->query('q'));
        }

        try {
            $payload = $this->api->get($resource['endpoint'], $query);
            $list = $this->api->unwrapList($payload);

            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'users',
                'resource' => $resource,
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'filters' => $request->query(),
                'filterOptions' => [],
            ]);
        } catch (ApiException $exception) {
            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'users',
                'resource' => $resource,
                'rows' => [],
                'meta' => [],
                'filters' => $request->query(),
                'filterOptions' => [],
                'apiError' => $exception->getMessage(),
            ]);
        }
    }

    public function create(): View|RedirectResponse
    {
        return $this->formView();
    }

    public function detail(int|string $id)
    {
        try {
            $payload = $this->api->get($this->resource()['endpoint'] . '/' . $id);
            $item = $this->api->unwrapItem($payload);

            return response()->json(['success' => true, 'data' => $item]);
        } catch (ApiException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $exception->status ?? 500);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $resource = $this->resource();
        $fields = $this->validatedPayload($request, true);

        try {
            $this->api->postMultipart($resource['endpoint'], $fields);

            return redirect($resource['path'])->with('success', $resource['title'] . ' berhasil ditambahkan.');
        } catch (ApiException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function edit(int|string $id): View|RedirectResponse
    {
        return $this->formView($id);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $resource = $this->resource();
        $fields = $this->validatedPayload($request, false);

        try {
            $this->api->updateMultipart($resource['endpoint'] . '/' . $id, $fields);

            return redirect($resource['path'])->with('success', $resource['title'] . ' berhasil diubah.');
        } catch (ApiException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        if ((string) data_get(session('auth_user'), 'id') === (string) $id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $resource = $this->resource();

        try {
            $this->api->delete($resource['endpoint'] . '/' . $id);

            return redirect($resource['path'])->with('success', $resource['title'] . ' berhasil dihapus.');
        } catch (ApiException $exception) {
            return $this->backForApiException($exception);
        }
    }

    private function validatedPayload(Request $request, bool $create): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            //'email' => ['nullable', 'string'],
            'phone' => ['required', 'string', 'min:6'],
            'address' => ['required', 'string', 'min:3'],
            'role' => ['required', $create ? 'in:ritel,grosir' : 'in:admin,ritel,grosir'],
            //'password' => [$create ? 'required' : 'nullable', 'string', 'min:6'],
        ]);

        $fields = [
            'name' => $validated['name'],
            //'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'role' => $validated['role'],
        ];

        if (($validated['password'] ?? '') !== '') {
            $fields['password'] = $validated['password'];
        }

        return $fields;
    }

    private function formView(int|string|null $id = null): View|RedirectResponse
    {
        $resource = $this->resource();
        $item = [];

        if ($id !== null) {
            try {
                $payload = $this->api->get($resource['endpoint'] . '/' . $id);
                $item = $this->api->unwrapItem($payload);
            } catch (ApiException $exception) {
                return redirect($resource['path'])->with('error', $exception->getMessage());
            }
        }

        return view($resource['formView'], [
            'title' => ($id === null ? 'Tambah ' : 'Ubah ') . $resource['title'],
            'resourceKey' => 'users',
            'resource' => $resource,
            'item' => $item,
            'photos' => [],
            'options' => ['roles' => ['admin' => 'Admin', 'ritel' => 'Ritel', 'grosir' => 'Grosir']],
            'mode' => $id === null ? 'create' : 'edit',
            'id' => $id,
        ]);
    }

    private function cleanQuery(Request $request, array $keys): array
    {
        $query = [];
        foreach ($keys as $queryKey) {
            if ($request->filled($queryKey)) {
                if ($queryKey === 'role' && $request->query($queryKey) === 'all') {
                    continue;
                }
                $query[$queryKey] = $request->query($queryKey);
            }
        }

        return $query;
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
