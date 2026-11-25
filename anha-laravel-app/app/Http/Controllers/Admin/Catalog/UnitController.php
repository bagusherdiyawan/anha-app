<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function __construct(private readonly ApiClient $api) {}

    private function resource(): array
    {
        return [
            'title' => 'Satuan',
            'endpoint' => '/admin-satuan-barang',
            'path' => '/katalog/satuan',
            'indexView' => 'admin.catalog.units.index',
            'formView' => 'admin.catalog.units.form',
            'createRoute' => 'units.create',
            'storeRoute' => 'units.store',
            'editRoute' => 'units.edit',
            'updateRoute' => 'units.update',
            'deleteRoute' => 'units.destroy',
            'columns' => [
                ['key' => 'nama', 'label' => 'Nama Satuan'],
            ],
            'filters' => [],
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
                'resourceKey' => 'units',
                'resource' => $resource,
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'filters' => $request->query(),
                'filterOptions' => [],
            ]);
        } catch (ApiException $exception) {
            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => 'units',
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
        [$fields] = $this->validatedPayload($request);

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
        [$fields] = $this->validatedPayload($request);

        try {
            $this->api->updateMultipart($resource['endpoint'] . '/' . $id, $fields);

            return redirect($resource['path'])->with('success', $resource['title'] . ' berhasil diubah.');
        } catch (ApiException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'min:2', 'max:200'],
        ]);

        $fields = [
            'nama' => $validated['nama'],
        ];

        return [$fields];
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
            } catch (ApiException $exception) {
                return redirect($resource['path'])->with('error', $exception->getMessage());
            }
        }

        return view($resource['formView'], [
            'title' => ($id === null ? 'Tambah ' : 'Ubah ') . $resource['title'],
            'resourceKey' => 'units',
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
            if ($request->filled($queryKey)) {
                $query[$queryKey] = $request->query($queryKey);
            }
        }

        return $query;
    }
}
