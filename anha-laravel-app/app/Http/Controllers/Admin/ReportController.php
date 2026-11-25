<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ApiClient $api)
    {
    }

    public function show(Request $request, string $type): View|RedirectResponse
    {
        abort_unless(in_array($type, ['penjualan', 'pelanggan', 'poin', 'stok'], true), 404);

        $apiType = match ($type) {
            'penjualan' => 'sales',
            'pelanggan' => 'customers',
            'poin' => 'points',
            default => $type,
        };
        $query = $this->cleanQuery($request, ['page', 'limit', 'status', 'type', 'start_date', 'end_date']);
        if ($request->filled('q')) {
            $query['search'] = trim((string) $request->query('q'));
        }

        try {
            $payload = $this->api->get("/admin-reports/{$apiType}", $query);
            $list = $this->reportList($payload, $type);

            return view('admin.reports.index', [
                'title' => 'Laporan '.Str::title($type),
                'type' => $type,
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'summary' => data_get($payload, 'data.summary', data_get($payload, 'summary', [])),
                'columns' => $this->reportColumns($type, $list['rows']),
            ]);
        } catch (ApiException $exception) {
            return view('admin.reports.index', [
                'title' => 'Laporan '.Str::title($type),
                'type' => $type,
                'rows' => [],
                'meta' => [],
                'summary' => [],
                'columns' => [],
                'apiError' => $exception->getMessage(),
            ]);
        }
    }

    private function reportList(array $payload, string $type): array
    {
        $listKey = match ($type) {
            'penjualan' => 'orders',
            'pelanggan' => 'customers',
            'poin' => 'transactions',
            'stok' => 'products',
        };

        $container = data_get($payload, "data.{$listKey}", data_get($payload, $listKey, []));

        if (is_array($container) && isset($container['data']) && is_array($container['data'])) {
            return [
                'rows' => $container['data'],
                'meta' => [
                    'current_page' => (int) ($container['current_page'] ?? 1),
                    'last_page' => (int) ($container['last_page'] ?? 1),
                    'per_page' => (int) ($container['per_page'] ?? count($container['data'])),
                    'total' => (int) ($container['total'] ?? count($container['data'])),
                    'from' => $container['from'] ?? null,
                    'to' => $container['to'] ?? null,
                ],
            ];
        }

        if (is_array($container) && array_is_list($container)) {
            return [
                'rows' => $container,
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => count($container),
                    'total' => count($container),
                    'from' => count($container) ? 1 : null,
                    'to' => count($container) ?: null,
                ],
            ];
        }

        return $this->api->unwrapList($payload);
    }

    private function reportColumns(string $type, array $rows): array
    {
        return match ($type) {
            'penjualan' => [
                ['key' => 'invoice', 'label' => 'Invoice'],
                ['key' => 'customer_name', 'label' => 'Pelanggan'],
                ['key' => 'total', 'label' => 'Total', 'type' => 'currency'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ['key' => 'created_at', 'label' => 'Tanggal', 'type' => 'date'],
            ],
            'pelanggan' => [
                ['key' => 'name', 'label' => 'Nama'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'phone', 'label' => 'Telepon'],
                ['key' => 'role', 'label' => 'Tipe', 'type' => 'badge'],
                ['key' => 'total_orders', 'label' => 'Pesanan'],
            ],
            'poin' => [
                ['key' => 'user_name', 'label' => 'Pelanggan'],
                ['key' => 'type', 'label' => 'Tipe', 'type' => 'badge'],
                ['key' => 'amount', 'label' => 'Poin'],
                ['key' => 'balance_after', 'label' => 'Saldo Setelah'],
                ['key' => 'description', 'label' => 'Keterangan'],
                ['key' => 'created_at', 'label' => 'Tanggal', 'type' => 'date'],
            ],
            'stok' => [
                ['key' => 'kode_barang', 'label' => 'Kode'],
                ['key' => 'nama_barang', 'label' => 'Nama Produk'],
                ['key' => 'nama_kategori', 'label' => 'Kategori'],
                ['key' => 'stock', 'label' => 'Stok'],
                ['key' => 'satuan', 'label' => 'Satuan'],
            ],
            default => $this->dynamicColumns($rows),
        };
    }

    private function dynamicColumns(array $rows): array
    {
        $first = $rows[0] ?? [];
        if (! is_array($first)) {
            return [];
        }

        return collect(array_keys($first))
            ->reject(fn ($key) => is_array($first[$key] ?? null))
            ->take(6)
            ->map(fn ($key) => ['key' => $key, 'label' => Str::headline($key)])
            ->values()
            ->all();
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
