<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly ApiClient $api
    ) {}

    private function resource(): array
    {
        return [
            'title' => 'Kategori',
            'endpoint' => '/kategoribarangs',
            'path' => '/katalog/kategori',
            'indexView' => 'admin.catalog.categories.index',
            'formView' => 'admin.catalog.categories.form',
            'createRoute' => 'categories.create',
            'storeRoute' => 'categories.store',
            'editRoute' => 'categories.edit',
            'updateRoute' => 'categories.update',
            'deleteRoute' => 'categories.destroy',
            'columns' => [
                ['key' => 'nama', 'label' => 'Nama', 'image' => 'foto'],
                ['key' => 'kode', 'label' => 'Kode'],
                ['key' => 'barangs_count', 'label' => 'Jumlah Produk'],
                ['key' => 'landing', 'label' => 'Landing', 'type' => 'badge'],
                ['key' => 'status_active', 'label' => 'Status', 'type' => 'badge'],
            ],
            'filters' => ['status' => 'Status'],
        ];
    }

    public function index(Request $request): View|RedirectResponse
    {
        $resource = $this->resource();
        $query = $this->cleanQuery($request, ['page', 'limit']);
        
        if ($request->filled('q')) {
            $query['q'] = trim((string) $request->query('q'));
        }

        if ($request->filled('status') && $request->query('status') !== 'all') {
            $query['status_active'] = $request->query('status') === 'active' ? '1' : '0';
        }

        try {
            $payload = $this->api->get($resource['endpoint'], $query);
            $list = $this->api->unwrapList($payload);

            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'categories',
                'resource' => $resource,
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'filters' => $request->query(),
                'filterOptions' => [],
            ]);
        } catch (ApiException $exception) {
            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'categories',
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
        [$fields, $files] = $this->validatedPayload($request);

        try {
            $this->api->updateMultipart($resource['endpoint'] . '/' . $id, $fields, $files);

            return redirect($resource['path'])->with('success', $resource['title'] . ' berhasil diubah.');
        } catch (ApiException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $resource = $this->resource();

        try {
            $this->api->delete($resource['endpoint'] . '/' . $id);

            return redirect($resource['path'])->with('success', $resource['title'] . ' berhasil dihapus.');
        } catch (ApiException $exception) {
            return $this->backForApiException($exception);
        }
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'min:2', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'landing' => ['nullable', 'boolean'],
            'status_active' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $fields = [
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'landing' => $request->boolean('landing') ? '1' : '0',
            'status_active' => $request->boolean('status_active') ? '1' : '0',
        ];

        $files = $request->hasFile('foto') ? ['foto' => $request->file('foto')] : [];

        return [$fields, $files];
    }

    private function formView(int|string|null $id = null): View|RedirectResponse
    {
        $resource = $this->resource();
        $item = [];
        $photos = [];

        if ($id !== null) {
            try {
                $payload = $this->api->get($resource['endpoint'] . '/' . $id);
                $item = $this->api->unwrapItem($payload);
                $photos = $this->extractPhotos($payload);
            } catch (ApiException $exception) {
                return redirect($resource['path'])->with('error', $exception->getMessage());
            }
        }

        return view($resource['formView'], [
            'title' => ($id === null ? 'Tambah ' : 'Ubah ') . $resource['title'],
            'resourceKey' => 'categories',
            'resource' => $resource,
            'item' => $item,
            'photos' => $photos,
            'options' => [],
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

    private function extractPhotos(array $payload): array
    {
        $data = $payload['data'] ?? [];
        $photos = [];

        foreach (['foto', 'foto_url', 'image', 'image_url', 'image_path'] as $photoKey) {
            foreach ($this->api->collectAssetCandidates(data_get($data, $photoKey)) as $candidate) {
                $photos[] = $this->api->assetUrl($candidate);
            }
        }

        return array_values(array_unique(array_filter($photos)));
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
