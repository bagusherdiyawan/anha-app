<?php

namespace App\Http\Controllers\Admin\Transaction;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Dibayar',
        'confirmed' => 'Dikonfirmasi',
        'processed' => 'Diproses',
        'delivering' => 'Sedang diantar',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];

    public function __construct(
        private readonly ApiClient $api
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $query = $this->cleanQuery($request, ['page', 'limit', 'status']);
        if ($request->filled('q')) {
            $query['search'] = trim((string) $request->query('q'));
        }

        try {
            $payload = $this->api->get('/admin-orders', array_filter($query, fn($value) => $value !== null && $value !== ''));
            $list = $this->api->unwrapList($payload);

            return view('admin.transactions.orders.index', [
                'title' => 'Pesanan',
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'statusOptions' => self::STATUSES,
            ]);
        } catch (ApiException $exception) {
            return view('admin.transactions.orders.index', [
                'title' => 'Pesanan',
                'rows' => [],
                'meta' => [],
                'statusOptions' => self::STATUSES,
                'apiError' => $exception->getMessage(),
            ]);
        }
    }

    public function show(int|string $id): View|RedirectResponse
    {
        try {
            $payload = $this->api->get("/admin-orders/{$id}");

            return view('admin.transactions.orders.show', [
                'title' => 'Detail Pesanan',
                'order' => $this->api->unwrapItem($payload),
                'statusOptions' => self::STATUSES,
            ]);
        } catch (ApiException $exception) {
            return redirect()->route('orders.index')->with('error', $exception->getMessage());
        }
    }

    public function status(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:pending,paid,confirmed,processed,delivering,completed,cancelled']]);

        return $this->callAndBack(fn() => $this->api->patch("/admin-orders/{$id}/status", $validated), 'Status pesanan berhasil diubah.');
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
