<?php

namespace App\Http\Controllers\Admin\Program;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function __construct(
        private readonly ApiClient $api
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $query = $this->cleanQuery($request, ['page', 'limit', 'status', 'category']) + ['search' => trim((string) $request->query('q'))];

        try {
            $payload = $this->api->get('/admin-feedbacks', array_filter($query, fn($value) => $value !== null && $value !== ''));
            $list = $this->api->unwrapList($payload);

            return view('admin.program.feedbacks.index', [
                'title' => 'Kritik & Saran',
                'rows' => $list['rows'],
                'meta' => $list['meta'],
                'statusOptions' => [
                    'submitted' => 'Baru',
                    'read' => 'Dibaca',
                    'replied' => 'Dibalas',
                ],
                'categoryOptions' => ['product' => 'Produk', 'service' => 'Layanan', 'delivery' => 'Pengiriman', 'app' => 'Aplikasi', 'other' => 'Lainnya'],
            ]);
        } catch (ApiException $exception) {
            return view('admin.program.feedbacks.index', [
                'title' => 'Kritik & Saran',
                'rows' => [],
                'meta' => [],
                'statusOptions' => ['submitted' => 'Baru', 'read' => 'Dibaca', 'replied' => 'Dibalas'],
                'categoryOptions' => ['product' => 'Produk', 'service' => 'Layanan', 'delivery' => 'Pengiriman', 'app' => 'Aplikasi', 'other' => 'Lainnya'],
                'apiError' => $exception->getMessage(),
            ]);
        }
    }

    public function show(int|string $id): View|RedirectResponse
    {
        try {
            $payload = $this->api->get("/admin-feedbacks/{$id}");

            return view('admin.program.feedbacks.show', [
                'title' => 'Detail Kritik & Saran',
                'feedback' => $this->api->unwrapItem($payload),
            ]);
        } catch (ApiException $exception) {
            return redirect()->route('feedbacks.index')->with('error', $exception->getMessage());
        }
    }

    public function reply(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate(['content' => ['required', 'string', 'max:5000']]);

        return $this->callAndBack(fn() => $this->api->post("/admin-feedbacks/{$id}/reply", $validated), 'Balasan berhasil dikirim.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        return $this->callAndBack(fn() => $this->api->delete("/admin-feedbacks/{$id}"), 'Kritik dan saran berhasil dihapus.');
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
