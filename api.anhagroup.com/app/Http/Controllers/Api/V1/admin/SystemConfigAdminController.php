<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SystemConfig\SystemConfigResource;
use App\Models\SystemConfigAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SystemConfigAdminController extends Controller
{
    /**
     * List system config
     */
    public function index(Request $request)
    {
        $query = SystemConfigAdmin::query();

        // filter group
        if ($request->group) {
            $query->where(
                'group',
                $request->group
            );
        }

        // search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where(
                    'key',
                    'like',
                    '%' . $request->search . '%'
                )->orWhere(
                    'description',
                    'like',
                    '%' . $request->search . '%'
                );
            });
        }

        $configs = $query
            ->orderBy('group')
            ->orderBy('key')
            ->paginate($request->per_page ?? 10)
            ->appends($request->query());

        return SystemConfigResource::collection($configs);
    }

    /**
     * Store config
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' =>
                'required|string|max:255|unique:system_configs,key',
            'value' =>
                'nullable',
            'type' =>
                'required|in:string,integer,boolean,json',
            'group' =>
                'required|string|max:255',
            'description' =>
                'nullable|string|max:255',
        ]);

        // handle json
        if (
            $validated['type'] === 'json' &&
            is_array($validated['value'])
        ) {
            $validated['value'] = json_encode(
                $validated['value']
            );
        }

        $config = SystemConfigAdmin::create($validated);

        Cache::forget(
            "config:{$config->key}"
        );

        return response()->json([
            'success' => true,
            'message' => 'System config berhasil dibuat',
            'data' => new SystemConfigResource($config)
        ], 201);
    }

    /**
     * Detail config
     */
    public function show(
        string $id
    ): JsonResponse {
        $config = SystemConfigAdmin::find($id);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'System config tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail system config',
            'data' => new SystemConfigResource($config)
        ]);
    }

    /**
     * Update config
     */
    public function update(
        Request $request,
        string $id
    ): JsonResponse {
        $config = SystemConfigAdmin::find($id);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'System config tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'key' =>
                'sometimes|required|string|max:255|unique:system_configs,key,' . $config->id,
            'value' =>
                'nullable',
            'type' =>
                'sometimes|required|in:string,integer,boolean,json',
            'group' =>
                'sometimes|required|string|max:255',
            'description' =>
                'nullable|string|max:255',
        ]);

        // handle json
        if (
            isset($validated['type']) &&
            $validated['type'] === 'json' &&
            isset($validated['value']) &&
            is_array($validated['value'])
        ) {
            $validated['value'] = json_encode(
                $validated['value']
            );
        }

        $oldKey = $config->key;

        $config->update($validated);

        Cache::forget(
            "config:{$oldKey}"
        );

        Cache::forget(
            "config:{$config->key}"
        );

        return response()->json([
            'success' => true,
            'message' => 'System config berhasil diupdate',
            'data' => new SystemConfigResource(
                $config->fresh()
            )
        ]);
    }

    /**
     * Delete config
     */
    public function destroy(
        string $id
    ): JsonResponse {
        $config = SystemConfigAdmin::find($id);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'System config tidak ditemukan'
            ], 404);
        }

        Cache::forget(
            "config:{$config->key}"
        );

        $config->delete();

        return response()->json([
            'success' => true,
            'message' => 'System config berhasil dihapus'
        ]);
    }

    /**
     * Bulk update config
     */
    public function bulkUpdate(
        Request $request
    ): JsonResponse {
        $configs = $request->all();

        foreach ($configs as $key => $value) {
            SystemConfigAdmin::set(
                $key,
                $value
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'System config berhasil diperbarui'
        ]);
    }

    /**
     * Clear cache
     */
    public function clearCache(): JsonResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return response()->json([
            'success' => true,
            'message' => 'Cache berhasil dibersihkan'
        ]);
    }
}
