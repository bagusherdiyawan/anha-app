<?php

namespace App\Http\Controllers\Admin\Program;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardRedemptionController extends Controller
{
    public function __construct(private readonly ApiClient $api)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $query = $this->cleanQuery($request, ['page', 'limit', 'status']) + ['search' => trim((string) $request->query('q'))];

        try {
            $payload = $this->api->get('/admin-reward-redemptions', array_filter($query, fn ($value) => $value !== null && $value !== ''));
            $list = $this->api->unwrapList($payload);

            return view('admin.program.redemptions.index', [
                'title' => 'Klaim Hadiah',
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'statusOptions' => ['pending' => 'Pending', 'claimed' => 'Terklaim', 'cancelled' => 'Dibatalkan'],
            ]);
        } catch (ApiException $exception) {
            return view('admin.program.redemptions.index', [
                'title' => 'Klaim Hadiah',
                'rows' => [],
                'meta' => [],
                'statusOptions' => ['pending' => 'Pending', 'claimed' => 'Terklaim', 'cancelled' => 'Dibatalkan'],
                'apiError' => $exception->getMessage(),
            ]);
        }
    }

    public function status(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:claimed,cancelled']]);

        return $this->callAndBack(fn () => $this->api->patch("/admin-reward-redemptions/{$id}/status", $validated), 'Status klaim berhasil diubah.');
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
