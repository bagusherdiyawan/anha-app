<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ApiClient $api)
    {
    }

    private function resource(): array
    {
        return [
            'title' => 'Produk',
            'endpoint' => '/barangs',
            'path' => '/katalog/produk',
            'indexView' => 'admin.catalog.products.index',
            'formView' => 'admin.catalog.products.form',
            'createRoute' => 'products.create',
            'storeRoute' => 'products.store',
            'editRoute' => 'products.edit',
            'updateRoute' => 'products.update',
            'deleteRoute' => 'products.destroy',
            'nested' => 'barang',
            'columns' => [
                ['key' => 'nama_barang', 'label' => 'Produk', 'image' => 'foto'],
                ['key' => 'kode_barang', 'label' => 'Kode'],
                ['key' => 'nama_kategori', 'label' => 'Kategori'],
                ['key' => 'rating', 'label' => 'Rating', 'type' => 'rating'],
                ['key' => 'harga_ritel', 'label' => 'Harga Ritel', 'type' => 'currency'],
                ['key' => 'status_ritel', 'label' => 'Ritel', 'type' => 'badge'],
                ['key' => 'status_grosir', 'label' => 'Grosir', 'type' => 'badge'],
                ['key' => 'is_available', 'label' => 'Ketersediaan', 'type' => 'badge'],
            ],
            'filters' => ['status_active_ritel' => 'Status Ritel', 'status_active_grosir' => 'Status Grosir', 'kategori' => 'Kategori'],
        ];
    }

    public function search(Request $request)
    {
        try {
            $queryParam = trim((string) $request->query('q'));
            $payload = $this->api->get($this->resource()['endpoint'], ['q' => $queryParam, 'limit' => 8]);
            $list = $this->api->unwrapList($payload);

            return collect($list['rows'])
                ->map(fn ($row) => [
                    'id' => $row['id'],
                    'name' => $row['nama_barang'],
                ])
                ->values()
                ->all();
        } catch (ApiException $exception) {
            return [];
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        $resource = $this->resource();
        $query = $this->resourceQuery($request);

        try {
            $payload = $this->api->get($resource['endpoint'], $query);
            $list = $this->api->unwrapList($payload);

            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'products',
                'resource' => $resource,
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'filters' => $request->query(),
                'filterOptions' => ['categories' => $this->categoryOptions()],
            ]);
        } catch (ApiException $exception) {
            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'products',
                'resource' => $resource,
                'rows' => [],
                'meta' => [],
                'filters' => $request->query(),
                'filterOptions' => ['categories' => $this->categoryOptions()],
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

            return redirect($resource['path'])->with('success', $resource['title'].' berhasil ditambahkan.');
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
        $tempFiles = [];

        try {
            $tempFiles = $this->prepareProductPhotoUpdate($request, $fields, $files);
            $this->api->updateMultipart($resource['endpoint'].'/'.$id, $fields, $files);

            return redirect($resource['path'])->with('success', $resource['title'].' berhasil diubah.');
        } catch (ApiException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } finally {
            $this->deleteTempFiles($tempFiles);
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $resource = $this->resource();

        try {
            $this->api->delete($resource['endpoint'].'/'.$id);

            return redirect($resource['path'])->with('success', $resource['titile'].' berhasil dihapus.');
        } catch (ApiException $exception) {
            return $this->backForApiException($exception);
        }
    }

    public function status(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'status_channel' => ['required', 'in:ritel,grosir'],
            'status_active_ritel' => ['required', 'in:0,1'],
            'status_active_grosir' => ['required', 'in:0,1'],
        ]);

        try {
            $this->api->postMultipart("/barang-update-status/{$id}", [
                '_method' => 'PATCH',
                'status_active_ritel' => $validated['status_active_ritel'],
                'status_active_grosir' => $validated['status_active_grosir'],
            ]);

            $label = $validated['status_channel'] === 'ritel' ? 'ritel' : 'grosir';

            return back()->with('success', "Status produk {$label} berhasil diubah.");
        } catch (ApiException $exception) {
            return $this->backForApiException($exception);
        }
    }

    public function availability(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'is_available' => ['required', 'in:0,1'],
        ]);

        try {
            $this->api->postMultipart("/barang-update-availability/{$id}", [
                '_method' => 'PATCH',
                'is_available' => $validated['is_available'],
            ]);

            $label = $validated['is_available'] === '1' ? 'Tersedia' : 'Habis';

            return back()->with('success', "Ketersediaan produk diubah menjadi {$label}.");
        } catch (ApiException $exception) {
            return $this->backForApiException($exception);
        }
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'nama_barang' => ['required', 'string', 'min:3', 'max:200'],
            'kode_barang' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'kode_kategori' => ['required', 'string', 'max:20'],
            'harga_ritel' => ['required', 'numeric', 'min:0'],
            'harga_grosir' => ['required', 'numeric', 'min:0'],
            'rating' => ['required', 'numeric', 'between:0,5'],
            'satuan' => ['required', 'string', 'max:20'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'foto.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $fields = [
            'nama_barang' => $validated['nama_barang'],
            'kode_barang' => $validated['kode_barang'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'kode_kategori' => $validated['kode_kategori'],
            'harga_ritel' => $validated['harga_ritel'],
            'harga_grosir' => $validated['harga_grosir'],
            'rating' => $validated['rating'],
            'satuan' => $validated['satuan'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'stock' => $validated['stock'] ?? 0,
            'status_active_ritel' => $request->boolean('status_active_ritel') ? '1' : '0',
            'status_active_grosir' => $request->boolean('status_active_grosir') ? '1' : '0',
            'is_available' => $request->boolean('is_available', true) ? '1' : '0',
        ];

        $files = $request->hasFile('foto') ? ['foto[]' => $request->file('foto')] : [];

        return [$fields, $files];
    } 

    private function formView(int|string|null $id = null): View|RedirectResponse
    {
        $resource = $this->resource();
        $item = [];
        $photos = [];

        if ($id !== null) {
            try {
                $payload = $this->api->get($resource['endpoint'].'/'.$id);
                $item = $this->api->unwrapItem($payload, $resource['nested'] ?? null);
                $root = $payload['data'] ?? $payload;
                $item = $this->normalizeProductItemForForm($item, is_array($root) ? $root : []);
                $photos = $this->extractPhotos($payload, $resource);
            } catch (ApiException $exception) {
                return redirect($resource['path'])->with('error', $exception->getMessage());
            }
        }

        return view($resource['formView'], [
            'title' => ($id === null ? 'Tambah ' : 'Ubah ').$resource['title'],
            'resourceKey' => 'products',
            'resource' => $resource,
            'item' => $item,
            'photos' => $photos,
            'options' => [
                'categories' => $this->categoryOptions(data_get($item, 'kode_kategori'), data_get($item, 'nama_kategori')),
                'units' => $this->unitOptions(data_get($item, 'satuan'), data_get($item, 'nama')),
            ],
            'mode' => $id === null ? 'create' : 'edit',
            'id' => $id,
        ]);
    }

    private function resourceQuery(Request $request): array
    {
        $query = $this->cleanQuery($request, ['page', 'limit', 'kategori']);

        if ($request->filled('q')) {
            $query['q'] = trim((string) $request->query('q'));
        }

        foreach (['status_active_ritel', 'status_active_grosir'] as $statusKey) {
            if ($request->filled($statusKey) && $request->query($statusKey) !== 'all') {
                $query[$statusKey] = $this->boolQueryValue((string) $request->query($statusKey));
            }
        }

        return $query;
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

    private function boolQueryValue(string $value): string
    {
        return in_array(strtolower($value), ['1', 'true', 'active', 'aktif'], true) ? 'true' : 'false';
    }

    private function categoryOptions(?string $currentCode = null, ?string $currentName = null): array
    {
        try {
            $rows = $this->api->cachedRows('/kategoribarangs', ['page' => 1, 'limit' => 10000]);
        } catch (ApiException) {
            $rows = [];
        }

        $options = collect($rows)
            ->mapWithKeys(function ($row) {
                $code = trim((string) data_get($row, 'kode'));
                if ($code === '') {
                    return [];
                }

                $name = trim((string) data_get($row, 'nama', $code));

                return [$code => $name !== '' ? "{$name} ({$code})" : $code];
            })
            ->all();

        $currentCode = trim((string) $currentCode);
        if ($currentCode !== '' && ! isset($options[$currentCode])) {
            $name = trim((string) $currentName);
            $options = [$currentCode => ($name !== '' ? "{$name} ({$currentCode})" : "{$currentCode} (di luar daftar)")] + $options;
        }

        return $options;
    }

    private function unitOptions(?string $currentCode = null, ?string $currentName = null): array
    {
        try {
            $rows = $this->api->cachedRows('/admin-satuan-barang', ['page' => 1, 'limit' => 10000]);
        } catch (ApiException) {
            $rows = [];
        }

        $options = collect($rows)
            ->mapWithKeys(function ($row) {
                $code = trim((string) data_get($row, 'id'));
                if ($code === '') {
                    return [];
                }

                $name = trim((string) data_get($row, 'nama', $code));

                return [$code => $name !== '' ? "{$name}" : $code];
            })
            ->all();

        $currentCode = trim((string) $currentCode);
        
        if ($currentCode !== '' && ! isset($options[$currentCode])) {
            $name = trim((string) $currentName);
            $options = [$currentCode => ($name !== '' ? "{$name}" : "{$currentCode} (di luar daftar)")] + $options;
        }

        return $options;
    }

    private function normalizeProductItemForForm(array $item, array $root = []): array
    {
        $code = trim((string) (
            data_get($item, 'kode_kategori')
            ?? data_get($item, 'kategori.kode')
            ?? data_get($item, 'category.kode')
            ?? data_get($root, 'kode_kategori')
            ?? data_get($root, 'kategori.kode')
            ?? data_get($root, 'category.kode')
            ?? ''
        ));
        $name = trim((string) (
            data_get($item, 'nama_kategori')
            ?? data_get($item, 'kategori.nama')
            ?? data_get($item, 'category.nama')
            ?? data_get($root, 'nama_kategori')
            ?? data_get($root, 'kategori.nama')
            ?? data_get($root, 'category.nama')
            ?? ''
        ));

        if ($code === '' && $name !== '') {
            $matchedCode = collect($this->categoryOptions())
                ->search(fn ($label) => Str::beforeLast((string) $label, ' (') === $name);

            if (is_string($matchedCode)) {
                $code = $matchedCode;
            }
        }

        if ($code !== '') {
            $item['kode_kategori'] = $code;
        }

        if ($name !== '') {
            $item['nama_kategori'] = $name;
        }

        return $item;
    }

    private function extractPhotos(array $payload, array $resource): array
    {
        $data = $payload['data'] ?? [];
        $photos = [];
        $nested = data_get($data, $resource['nested'] ?? '', []);

        foreach ([$data, $nested] as $source) {
            if (! is_array($source)) {
                continue;
            }

            foreach (['foto', 'fotos', 'foto_url', 'photo', 'photos', 'image', 'images', 'gambar', 'foto_barangs', 'barang_fotos', 'image_url', 'image_path', 'photo_full_url', 'photo_url'] as $photoKey) {
                foreach ($this->api->collectAssetCandidates(data_get($source, $photoKey)) as $candidate) {
                    $photos[] = $this->api->assetUrl($candidate);
                }
            }
        }

        return array_values(array_unique(array_filter($photos)));
    }

    /**
     * @return array<int, string>
     */
    private function prepareProductPhotoUpdate(Request $request, array &$fields, array &$files): array
    {
        $hadExisting = $request->boolean('had_existing_foto');
        $keptUrls = collect(Arr::wrap($request->input('existing_foto', [])))
            ->map(fn ($url) => trim((string) $url))
            ->filter()
            ->unique()
            ->values();

        $newFiles = Arr::wrap($files['foto[]'] ?? []);
        $hasNewFiles = collect($newFiles)
            ->contains(fn ($file) => $file instanceof UploadedFile && $file->isValid());

        if ($hadExisting && $keptUrls->isEmpty() && ! $hasNewFiles) {
            $fields['clear_foto'] = 'ya';

            return [];
        }

        if ($keptUrls->isEmpty()) {
            return [];
        }

        $tempFiles = [];
        $keptFiles = [];

        foreach ($keptUrls as $index => $url) {
            $keptFiles[] = $this->downloadPhotoAsUpload($url, $index, $tempFiles);
        }

        $files['foto[]'] = array_values(array_filter([...$keptFiles, ...$newFiles]));

        return $tempFiles;
    }

    private function downloadPhotoAsUpload(string $url, int $index, array &$tempFiles): UploadedFile
    {
        $normalized = $this->api->assetUrl($url);

        if (! $normalized || ! $this->isTrustedPhotoUrl($normalized)) {
            throw new ApiException('URL foto produk tidak valid atau tidak diizinkan.', 422);
        }

        $response = Http::timeout(20)->get($normalized);
        if (! $response->successful()) {
            throw new ApiException('Gagal memuat ulang foto produk yang dipertahankan.', 422);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'anha-photo-');
        if ($tempPath === false || file_put_contents($tempPath, $response->body()) === false) {
            throw new ApiException('Gagal menyiapkan foto produk untuk dikirim ulang.', 500);
        }

        $tempFiles[] = $tempPath;

        return new UploadedFile(
            $tempPath,
            $this->filenameFromUrl($normalized, $index),
            $response->header('content-type') ?: 'image/webp',
            null,
            true
        );
    }

    private function filenameFromUrl(string $url, int $index): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $filename = $path ? basename($path) : '';

        return preg_match('/\.[a-z0-9]+$/i', $filename) ? $filename : 'existing-'.($index + 1).'.webp';
    }

    private function isTrustedPhotoUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $path = (string) parse_url($url, PHP_URL_PATH);

        if (! in_array($scheme, ['http', 'https'], true) || ! $this->isStorageLikePath($path)) {
            return false;
        }

        $origin = $this->originFromUrl($url);
        $allowed = array_values(array_filter(array_unique([
            $this->originFromUrl((string) config('anha.asset_base_url')),
            $this->originFromUrl($this->api->baseUrl()),
        ])));

        return $origin !== null && (empty($allowed) || in_array($origin, $allowed, true));
    }

    private function isStorageLikePath(string $path): bool
    {
        return Str::startsWith($path, ['/storage', '/photos'])
            || str_contains($path, '/storage/')
            || str_contains($path, '/photos_')
            || str_contains($path, '/banners/');
    }

    private function originFromUrl(string $url): ?string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);

        if (! $scheme || ! $host) {
            return null;
        }

        return $scheme.'://'.$host.($port ? ':'.$port : '');
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteTempFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
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
