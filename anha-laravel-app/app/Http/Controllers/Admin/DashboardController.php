<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ApiClient $api)
    {
    }

    public function dashboard(): View
    {
        $payload = $this->safeGet('/admin-dashboard');

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'stats' => data_get($payload, 'data.stats', data_get($payload, 'data', [])),
            'orders' => data_get($payload, 'data.latest_orders', []),
            'apiError' => data_get($payload, '_error'),
        ]);
    }

    public function analytics(): View
    {
        $payload = $this->safeGet('/admin-dashboard');

        return view('admin.analytics', [
            'title' => 'Analitik',
            'kpis' => data_get($payload, 'data.kpis', []),
            'chartData' => data_get($payload, 'data.chart_data', []),
            'recentOrders' => data_get($payload, 'data.recent_orders', []),
            'apiError' => data_get($payload, '_error'),
        ]);
    }

    private function safeGet(string $endpoint): array
    {
        try {
            return $this->api->get($endpoint);
        } catch (ApiException $exception) {
            return ['_error' => $exception->getMessage()];
        }
    }
}
