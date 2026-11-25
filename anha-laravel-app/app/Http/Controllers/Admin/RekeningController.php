<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekeningController extends Controller
{
    public function __construct(
        private readonly ApiClient $api
    ) {}

    private function resource(): array
    {
        return [
            'title' => 'Rekening & Metode Pembayaran',
            'endpoint' => '/admin-rekening',
            'path' => '/sistem/rekening',
            'indexView' => 'admin.system.rekenings.index',
            'formView' => 'admin.system.rekenings.form',
            'createRoute' => 'rekenings.create',
            'storeRoute' => 'rekenings.store',
            'editRoute' => 'rekenings.edit',
            'updateRoute' => 'rekenings.update',
            'deleteRoute' => 'rekenings.destroy',
            'toggleRoute' => 'rekenings.toggle',
            'columns' => [
                ['key' => 'nama', 'label' => 'Metode / Bank', 'image' => 'foto'],
                ['key' => 'nomor', 'label' => 'Nomor Rekening'],
                ['key' => 'type', 'label' => 'Tipe Pembayaran'],
                ['key' => 'status_active', 'label' => 'Status', 'type' => 'badge'],
            ],
        ];
    }

    public function index(Request $request): View|RedirectResponse
    {
        $resource = $this->resource();
        $query = $this->cleanQuery($request, ['page', 'limit']);
        if ($request->filled('q')) {
            $query['search'] = trim((string) $request->query('q'));
        }

        try {
            $payload = $this->api->get($resource['endpoint'], $query);
            $list = $this->api->unwrapList($payload);

            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'rekenings',
                'resource' => $resource,
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'filters' => $request->query(),
                'filterOptions' => [],
            ]);
        } catch (ApiException $exception) {
            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'rekenings',
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
        [$fields, $files] = $this->validatedPayload($request, true);

        try {
            $this->api->postMultipart($resource['endpoint'], $fields, $files);

            return redirect($resource['path'])->with('success', 'Rekening berhasil ditambahkan.');
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
        [$fields, $files] = $this->validatedPayload($request, false);

        try {
            $this->api->updateMultipart($resource['endpoint'] . '/' . $id, $fields, $files);

            return redirect($resource['path'])->with('success', 'Rekening berhasil diperbarui.');
        } catch (ApiException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function toggle(int|string $id): RedirectResponse
    {
        $resource = $this->resource();

        try {
            $this->api->patch($resource['endpoint'] . '/' . $id . '/toggle');

            return redirect($resource['path'])->with('success', 'Status rekening berhasil diubah.');
        } catch (ApiException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $resource = $this->resource();

        try {
            $this->api->delete($resource['endpoint'] . '/' . $id);

            return redirect($resource['path'])->with('success', 'Rekening berhasil dihapus.');
        } catch (ApiException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    private function validatedPayload(Request $request, bool $create): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nomor' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:cod,transfer,wallet'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'status_active' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'clear_foto' => ['nullable', 'string'],
        ]);

        $fields = [
            'nama' => $validated['nama'],
            'nomor' => $validated['nomor'] ?? '-',
            'type' => $validated['type'],
            'keterangan' => $validated['keterangan'] ?? null,
            'status_active' => $request->boolean('status_active') ? '1' : '0',
        ];

        if ($request->input('clear_foto') === 'ya') {
            $fields['clear_foto'] = 'ya';
        }

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
            'resourceKey' => 'rekenings',
            'resource' => $resource,
            'item' => $item,
            'photos' => $photos,
            'typeOptions' => [
                'cod' => 'COD (Tunai saat barang tiba)',
                'transfer' => 'Bank Transfer',
                'wallet' => 'E-Wallet / QRIS',
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

    private function extractPhotos(array $payload): array
    {
        $data = $payload['data'] ?? [];
        $photos = [];

        foreach (['foto', 'foto_url', 'image', 'image_url'] as $photoKey) {
            foreach ($this->api->collectAssetCandidates(data_get($data, $photoKey)) as $candidate) {
                $photos[] = $this->api->assetUrl($candidate);
            }
        }

        return array_values(array_unique(array_filter($photos)));
    }
}

