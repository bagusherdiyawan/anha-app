<?php

namespace App\Http\Controllers\Admin\Program;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function __construct(private readonly ApiClient $api)
    {
    }

    private function resource(): array
    {
        return [
            'title' => 'Program Reward',
            'singular' => 'Reward',
            'endpoint' => '/admin-rewards',
            'path' => '/program/program-reward',
            'indexView' => 'admin.program.rewards.index',
            'formView' => 'admin.program.rewards.form',
            'createRoute' => 'rewards.create',
            'storeRoute' => 'rewards.store',
            'editRoute' => 'rewards.edit',
            'updateRoute' => 'rewards.update',
            'deleteRoute' => 'rewards.destroy',
            'toggleRoute' => 'rewards.toggle',
            'columns' => [
                ['key' => 'name', 'label' => 'Reward', 'image' => 'image_url'],
                ['key' => 'type', 'label' => 'Tipe', 'type' => 'badge'],
                ['key' => 'points_required', 'label' => 'Poin'],
                ['key' => 'stock', 'label' => 'Stok'],
                ['key' => 'is_active', 'label' => 'Status', 'type' => 'badge'],
            ],
            'filters' => ['type' => 'Tipe'],
        ];
    }



    public function index(Request $request): View|RedirectResponse
    {
        $resource = $this->resource();
        $query = $this->cleanQuery($request, ['page', 'limit', 'type']);
        if ($request->filled('q')) {
            $query['q'] = trim((string) $request->query('q'));
        }

        try {
            $payload = $this->api->get($resource['endpoint'], $query);
            $list = $this->api->unwrapList($payload);

            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'rewards',
                'resource' => $resource,
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'filters' => $request->query(),
                'filterOptions' => [],
            ]);
        } catch (ApiException $exception) {
            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'rewards',
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

    public function store(Request $request): RedirectResponse
    {
        $resource = $this->resource();
        [$fields, $files] = $this->validatedPayload($request);

        try {
            $this->api->postMultipart($resource['endpoint'], $fields, $files);

            return redirect($resource['path'])->with('success', $resource['singular'].' berhasil ditambahkan.');
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
        [$fields, $files] = $this->validatedPayload($request);

        try {
            $this->api->updateMultipart($resource['endpoint'].'/'.$id, $fields, $files);

            return redirect($resource['path'])->with('success', $resource['singular'].' berhasil diubah.');
        } catch (ApiException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $resource = $this->resource();

        try {
            $this->api->delete($resource['endpoint'].'/'.$id);

            return redirect($resource['path'])->with('success', $resource['singular'].' berhasil dihapus.');
        } catch (ApiException $exception) {
            return $this->backForApiException($exception);
        }
    }

    public function toggle(int|string $id): RedirectResponse
    {
        return $this->callAndBack(fn () => $this->api->patch("/admin-rewards/{$id}/toggle"), 'Status reward berhasil diubah.');
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'barang_id' => ['nullable', 'integer'],
            'type' => ['required', 'string'],
            'points_required' => ['required', 'integer', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:-1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $fields = [
            'barang_id' => $validated['barang_id'] ?? null,
            'type' => $validated['type'],
            'points_required' => $validated['points_required'],
            'stock' => $validated['stock'] ?? null,
            'is_active' => $request->boolean('is_active') ? '1' : '0',
        ];

        return [$fields, []];
    }

    private function formView(int|string|null $id = null): View|RedirectResponse
    {
        $resource = $this->resource();
        $item = [];

        if ($id !== null) {
            try {
                $payload = $this->api->get($resource['endpoint'].'/'.$id);
                $item = $this->api->unwrapItem($payload);
            } catch (ApiException $exception) {
                return redirect($resource['path'])->with('error', $exception->getMessage());
            }
        }

        return view($resource['formView'], [
            'title' => ($id === null ? 'Tambah ' : 'Ubah ').$resource['singular'],
            'resourceKey' => 'rewards',
            'resource' => $resource,
            'item' => $item,
            'options' => [
                'reward_types' => ['physical_item' => 'Barang Fisik'],
            ],
            'mode' => $id === null ? 'create' : 'edit',
            'id' => $id,
        ]);
    }

    private function cleanQuery(Request $request, array $keys): array
    {
        $query = [];
        foreach ($keys as $queryKey) {
            if ($request->filled($queryKey) && ($queryKey === 'limit' || $request->query($queryKey) !== 'all')) {
                $query[$queryKey] = $request->query($queryKey);
            }
        }

        return $query;
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
