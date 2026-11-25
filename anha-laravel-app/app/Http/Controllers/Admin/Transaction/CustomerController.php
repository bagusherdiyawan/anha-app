<?php

namespace App\Http\Controllers\Admin\Transaction;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private readonly ApiClient $api)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        return $this->resourceIndex($request, $this->resource(), 'customers');
    }

    private function resource(): array
    {
        return [
            'title' => 'Pelanggan',
            'endpoint' => '/users',
            'path' => '/transaksi/pelanggan',
            'indexView' => 'admin.transactions.customers.index',
            'columns' => [
                ['key' => 'name', 'label' => 'Nama', 'image' => 'photo_full_url'],
                ['key' => 'email', 'label' => 'Kode'],
                ['key' => 'phone', 'label' => 'Telepon'],
                ['key' => 'role', 'label' => 'Tipe', 'type' => 'badge'],
                ['key' => 'address', 'label' => 'Alamat'],
            ],
            'filters' => ['role' => 'Role'],
        ];
    }

    private function resourceIndex(Request $request, array $resource, string $key): View
    {
        $query = ['page' => 1, 'limit' => '10000'];
        $roleFilter = $this->cleanQuery($request, ['role']);
        if ($roleFilter !== []) {
            $query = array_merge($query, $roleFilter);
        }
        if ($request->filled('q')) {
            $query['q'] = trim((string) $request->query('q'));
        }

        try {
            $payload = $this->api->get($resource['endpoint'], $query);
            $list = $this->api->unwrapList($payload);
            $filtered = array_values(array_filter(
                $list['rows'],
                fn ($row) => strtolower((string) data_get($row, 'role')) !== 'admin'
            ));

            $page = max(1, (int) $request->query('page', 1));
            $rawLimit = (string) $request->query('limit', '20');
            $perPage = match ($rawLimit) {
                'all' => max(1, count($filtered)),
                '10', '50' => (int) $rawLimit,
                default => 20,
            };

            $total = count($filtered);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $page = min($page, $lastPage);
            $start = ($page - 1) * $perPage;
            $rows = array_slice($filtered, $start, $perPage);

            $meta = [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'from' => $total === 0 ? null : $start + 1,
                'to' => $total === 0 ? null : $start + count($rows),
            ];

            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => $key,
                'resource' => $resource,
                'rows' => $rows,
                'meta' => $meta,
                'filters' => $request->query(),
                'filterOptions' => [],
            ]);
        } catch (ApiException $exception) {
            return view($resource['indexView'], [
                'title' => $resource['title'],
                'resourceKey' => $key,
                'resource' => $resource,
                'rows' => [],
                'meta' => [],
                'filters' => $request->query(),
                'filterOptions' => [],
                'apiError' => $exception->getMessage(),
            ]);
        }
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
}
