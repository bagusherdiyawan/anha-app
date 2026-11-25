<?php

namespace App\Http\Controllers\Admin\Program;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function __construct(
        private readonly ApiClient $api
    ) {}

    private function resource(): array
    {
        return [
            'title' => 'Banner',
            'endpoint' => '/banners',
            'path' => '/program/banner',
            'indexView' => 'admin.program.banners.index',
            'formView' => 'admin.program.banners.form',
            'createRoute' => 'banners.create',
            'storeRoute' => 'banners.store',
            'editRoute' => 'banners.edit',
            'updateRoute' => 'banners.update',
            'deleteRoute' => 'banners.destroy',
            'columns' => [
                ['key' => 'title', 'label' => 'Judul', 'image' => 'image_url'],
                // ['key' => 'deep_link', 'label' => 'Deep Link'],
                ['key' => 'sort_order', 'label' => 'Urutan'],
                ['key' => 'is_active', 'label' => 'Status', 'type' => 'badge'],
                ['key' => 'starts_at', 'label' => 'Mulai', 'type' => 'date'],
            ],
        ];
    }

    public function index(Request $request): View|RedirectResponse
    {
        $resource = $this->resource();
        $query = $this->cleanQuery($request, ['page', 'limit']);
        if ($request->filled('q')) {
            $query['q'] = trim((string) $request->query('q'));
        }

        try {
            $payload = $this->api->get($resource['endpoint'], $query);
            $list = $this->api->unwrapList($payload);

            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'banners',
                'resource' => $resource,
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'filters' => $request->query(),
                'filterOptions' => [],
            ]);
        } catch (ApiException $exception) {
            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'banners',
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
        [$fields, $files] = $this->validatedPayload($request, false);

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

    private function validatedPayload(Request $request, bool $create): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            // 'deep_link' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'image' => [$create ? 'required' : 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $fields = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            // 'deep_link' => $validated['deep_link'] ?? null,
            'sort_order' => $validated['sort_order'],
            'starts_at' => $this->formatDateTime($validated['starts_at'] ?? ''),
            'ends_at' => $this->formatDateTime($validated['ends_at'] ?? ''),
            'is_active' => $request->boolean('is_active') ? '1' : '0',
        ];

        $files = $request->hasFile('image') ? ['image' => $request->file('image')] : [];

        return [$fields, $files];
    }

    private function formatDateTime(mixed $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = str_replace('T', ' ', $value);

        return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value) ? $value . ':00' : $value;
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
            'resourceKey' => 'banners',
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

        foreach (['image', 'image_url', 'image_path', 'foto', 'foto_url'] as $photoKey) {
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
