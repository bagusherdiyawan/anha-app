<?php

namespace App\Http\Controllers\Admin\Program;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly ApiClient $api
    ) {}

    private function resource(): array
    {
        return [
            'title' => 'Notifikasi',
            'endpoint' => '/push-notifications',
            'path' => '/program/notifikasi',
            'indexView' => 'admin.program.notifications.index',
            'formView' => 'admin.program.notifications.form',
            'createRoute' => 'notifications.create',
            'storeRoute' => 'notifications.store',
            'editRoute' => 'notifications.edit',
            'updateRoute' => 'notifications.update',
            'deleteRoute' => 'notifications.destroy',
            'columns' => [
                ['key' => 'title', 'label' => 'Judul', 'image' => 'image_url'],
                ['key' => 'category', 'label' => 'Kategori'],
                ['key' => 'body', 'label' => 'Pesan'],
                ['key' => 'action_url', 'label' => 'Tautan'],
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
                'resourceKey' => 'notifications',
                'resource' => $resource,
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'filters' => $request->query(),
                'filterOptions' => [],
            ]);
        } catch (ApiException $exception) {
            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'notifications',
                'resource' => $resource,
                'rows' => [],
                'meta' => [],
                'filters' => $request->query(),
                'filterOptions' => [],
                'apiError' => $exception->getMessage()
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

    private function cleanQuery(Request $request, array $keys): array
    {
        $query = [];
        foreach ($keys as $queryKey) {
            if ($request->filled($queryKey)) {
                $query[$queryKey] = $request->query($queryKey);
            }
        }

        return $query;
    }

    private function validatedPayload(Request $request, bool $create): array
    {
        $validated = $request->validate([
            'user_id' => ['required', 'string'],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:200'],
            'action_url' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $category = trim((string) ($validated['category'] ?? ''));
        if ($category === '') {
            $category = 'promo';
        }

        $fields = [
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'category' => $category,
            'action_url' => $validated['action_url'] ?? null,
        ];

        $files = $request->hasFile('image') ? ['image' => $request->file('image')] : [];

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
            'resourceKey' => 'notifications',
            'resource' => $resource,
            'item' => $item,
            'photos' => $photos,
            'options' => [],
            'mode' => $id === null ? 'create' : 'edit',
            'id' => $id,
        ]);
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
}
