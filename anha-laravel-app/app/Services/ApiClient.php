<?php

namespace App\Services;

use App\Exceptions\ApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ApiClient
{
    public function get(string $endpoint, array $query = []): array
    {
        $url = $this->url($endpoint);

        if ($query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?').http_build_query($query);
        }

        return $this->decode($this->guard(fn () => $this->baseRequest()->retry(3, 400, $this->retryOnConnectionError(), throw: false)->get($url)));
    }

    public function post(string $endpoint, array $payload = []): array
    {
        return $this->afterWrite($endpoint, $this->decode($this->guard(fn () => $this->baseRequest()->post($this->url($endpoint), $payload))));
    }

    public function put(string $endpoint, array $payload = []): array
    {
        return $this->afterWrite($endpoint, $this->decode($this->guard(fn () => $this->baseRequest()->put($this->url($endpoint), $payload))));
    }

    public function patch(string $endpoint, array $payload = []): array
    {
        return $this->afterWrite($endpoint, $this->decode($this->guard(fn () => $this->baseRequest()->patch($this->url($endpoint), $payload))));
    }

    public function delete(string $endpoint): array
    {
        $response = $this->guard(fn () => $this->baseRequest()->delete($this->url($endpoint)));

        if ($response->status() === 204) {
            return $this->afterWrite($endpoint, ['success' => true, 'message' => 'Data berhasil dihapus.']);
        }

        return $this->afterWrite($endpoint, $this->decode($response));
    }

    public function postMultipart(string $endpoint, array $fields, array $files = []): array
    {
        return $this->afterWrite($endpoint, $this->decode($this->sendMultipart($endpoint, $fields, $files)));
    }

    public function updateMultipart(string $endpoint, array $fields, array $files = []): array
    {
        $withPut = array_merge($fields, ['_method' => 'PUT']);
        $response = $this->sendMultipart($endpoint, $withPut, $files);

        if (! $response->successful() && in_array($response->status(), [404, 405], true)) {
            $withPatch = array_merge($fields, ['_method' => 'PATCH']);
            $response = $this->sendMultipart($endpoint, $withPatch, $files);
        }

        return $this->afterWrite($endpoint, $this->decode($response));
    }

    public function assetUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['blob:', 'data:'])) {
            return $path;
        }

        $base = $this->assetBaseUrl();
        if ($base === '') {
            return $path;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $parsed = parse_url($path);
            $host = (string) ($parsed['host'] ?? '');

            // Fallback: migrate dead domain anha.inforkampus.com to api.anhagroup.com
            if ($host === 'anha.inforkampus.com') {
                $path = str_replace('//anha.inforkampus.com', '//api.anhagroup.com', $path);
                $parsed['host'] = 'api.anhagroup.com';
                $host = 'api.anhagroup.com';
            }

            $baseHost = parse_url($base, PHP_URL_HOST);
            $isLocalBase = in_array($baseHost, ['localhost', '127.0.0.1'], true);
            $isRemotePath = !in_array($host, ['localhost', '127.0.0.1'], true);

            // If the incoming path is already pointing to a live remote host and our base is localhost,
            // preserve the live remote URL so images don't break.
            if ($isRemotePath && $isLocalBase) {
                return $path;
            }

            $pathname = (string) ($parsed['path'] ?? '');

            if ($pathname !== '' && $this->isStorageLikePath($pathname)) {
                $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
                $fragment = isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

                return rtrim($base, '/').$pathname.$query.$fragment;
            }

            return $path;
        }

        if (Str::startsWith($path, '//')) {
            return parse_url($base, PHP_URL_SCHEME).':'.$path;
        }

        if (Str::startsWith($path, '/')) {
            return rtrim($base, '/').$path;
        }

        if (Str::startsWith($path, 'storage/')) {
            return rtrim($base, '/').'/'.$path;
        }

        return rtrim($base, '/').'/storage/'.ltrim($path, '/');
    }

    public function firstAssetUrl(mixed $value): ?string
    {
        foreach ($this->collectAssetCandidates($value) as $candidate) {
            $url = $this->assetUrl($candidate);
            if ($url) {
                return $url;
            }
        }

        return null;
    }

    public function baseUrl(): string
    {
        return rtrim((string) config('anha.api_base_url'), '/');
    }

    private function assetBaseUrl(): string
    {
        $explicit = rtrim((string) config('anha.asset_base_url'), '/');
        if ($explicit !== '') {
            return $explicit;
        }

        $apiBase = $this->baseUrl();
        if ($apiBase === '') {
            return '';
        }

        $scheme = parse_url($apiBase, PHP_URL_SCHEME);
        $host = parse_url($apiBase, PHP_URL_HOST);
        $port = parse_url($apiBase, PHP_URL_PORT);

        if (! $scheme || ! $host) {
            return $apiBase;
        }

        return $scheme.'://'.$host.($port ? ':'.$port : '');
    }

    public function unwrapList(array $payload): array
    {
        if (isset($payload['data']['data']) && is_array($payload['data']['data'])) {
            return [
                'rows' => $payload['data']['data'],
                'meta' => [
                    'current_page' => (int) ($payload['data']['current_page'] ?? 1),
                    'last_page' => (int) ($payload['data']['last_page'] ?? 1),
                    'per_page' => (int) ($payload['data']['per_page'] ?? count($payload['data']['data'])),
                    'total' => (int) ($payload['data']['total'] ?? count($payload['data']['data'])),
                    'from' => $payload['data']['from'] ?? null,
                    'to' => $payload['data']['to'] ?? null,
                ],
            ];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            $isAssoc = array_keys($payload['data']) !== range(0, count($payload['data']) - 1);

            if (! $isAssoc || isset($payload['meta'])) {
                $rows = $isAssoc && isset($payload['data'][0]) ? $payload['data'] : $payload['data'];

                return [
                    'rows' => is_array($rows) ? $rows : [],
                    'meta' => $payload['meta'] ?? [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => count($rows),
                        'total' => count($rows),
                        'from' => count($rows) ? 1 : null,
                        'to' => count($rows) ?: null,
                    ],
                ];
            }
        }

        return [
            'rows' => [],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 0, 'total' => 0, 'from' => null, 'to' => null],
        ];
    }

    public function unwrapItem(array $payload, ?string $nestedKey = null): array
    {
        $data = $payload['data'] ?? $payload;

        if ($nestedKey && isset($data[$nestedKey]) && is_array($data[$nestedKey])) {
            return $data[$nestedKey];
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<int, string>
     */
    public function collectAssetCandidates(mixed $value): array
    {
        $out = [];
        $this->collectAssetCandidatesInto($value, $out);

        return array_values(array_unique(array_filter($out)));
    }

    /**
     * Ambil daftar (rows) untuk data referensi yang jarang berubah (kategori, satuan)
     * dengan cache singkat. Hanya respons sukses yang di-cache; error tidak pernah disimpan.
     * Jika cache tidak tersedia, otomatis fallback ke panggilan API biasa.
     *
     * @return array<int, mixed>
     */
    public function cachedRows(string $endpoint, array $query = [], int $ttlSeconds = 300): array
    {
        $key = $this->rowsCacheKey($endpoint);

        try {
            $hit = Cache::store('file')->get($key);
            if (is_array($hit)) {
                return $hit;
            }
        } catch (\Throwable) {
            // cache bermasalah -> lanjut ke API
        }

        $rows = $this->unwrapList($this->get($endpoint, $query))['rows'];

        try {
            Cache::store('file')->put($key, $rows, $ttlSeconds);
        } catch (\Throwable) {
        }

        return $rows;
    }

    /**
     * Setiap penulisan yang berhasil membuang cache daftar dari endpoint yang sama,
     * sehingga perubahan kategori/satuan langsung terlihat tanpa menunggu TTL.
     */
    private function afterWrite(string $endpoint, array $result): array
    {
        try {
            Cache::store('file')->forget($this->rowsCacheKey($endpoint));
        } catch (\Throwable) {
        }

        return $result;
    }

    private function rowsCacheKey(string $endpoint): string
    {
        $base = explode('/', trim(parse_url($endpoint, PHP_URL_PATH) ?: $endpoint, '/'))[0];

        return 'anha.rows.'.md5($base);
    }

    /**
     * Retry hanya untuk error koneksi (mis. "Connection reset by peer"), bukan untuk respons HTTP 4xx/5xx.
     * Hanya dipakai pada request GET karena aman diulang (idempotent).
     */
    private function retryOnConnectionError(): \Closure
    {
        return fn (\Throwable $e) => $e instanceof ConnectionException;
    }

    /**
     * Ubah error jaringan (cURL 56 reset by peer, timeout, DNS) menjadi ApiException
     * supaya ditangani controller sebagai pesan error, bukan halaman 500 mentah.
     */
    private function guard(\Closure $callback): Response
    {
        try {
            return $callback();
        } catch (ConnectionException $e) {
            report($e);

            throw new ApiException('Koneksi ke server sedang terganggu. Silakan coba lagi beberapa saat.', 503);
        }
    }

    private function baseRequest(): PendingRequest
    {
        $request = Http::timeout((int) config('anha.request_timeout', 30))->acceptJson();

        $token = session('api_token');
        if ($token) {
            $request = $request->withToken($token, session('token_type', 'Bearer'));
        }

        return $request;
    }

    private function sendMultipart(string $endpoint, array $fields, array $files): Response
    {
        $multipart = [];

        foreach ($fields as $name => $value) {
            if ($value === null) {
                continue;
            }

            $multipart[] = [
                'name' => $name,
                'contents' => (string) $value,
            ];
        }

        foreach ($files as $name => $fileOrFiles) {
            foreach ($this->flattenFiles($fileOrFiles) as $file) {
                $multipart[] = [
                    'name' => $name,
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ];
            }
        }

        return $this->guard(fn () => $this->baseRequest()
            ->asMultipart()
            ->post($this->url($endpoint), $multipart));
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function flattenFiles(mixed $fileOrFiles): array
    {
        if ($fileOrFiles instanceof UploadedFile && $fileOrFiles->isValid()) {
            return [$fileOrFiles];
        }

        if (! is_array($fileOrFiles)) {
            return [];
        }

        $files = [];
        foreach ($fileOrFiles as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $files[] = $file;
            }
        }

        return $files;
    }

    private function collectAssetCandidatesInto(mixed $value, array &$out): void
    {
        if ($value === null) {
            return;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed !== '') {
                $out[] = $trimmed;
            }

            return;
        }

        if (is_array($value)) {
            $keys = ['foto_url', 'url', 'path', 'src', 'foto', 'image', 'image_url', 'image_path', 'photo_url', 'photo_full_url'];
            foreach ($keys as $key) {
                if (array_key_exists($key, $value) && is_string($value[$key]) && trim($value[$key]) !== '') {
                    $out[] = trim($value[$key]);

                    return;
                }
            }

            foreach ($value as $entry) {
                $this->collectAssetCandidatesInto($entry, $out);
            }
        }
    }

    private function isStorageLikePath(string $path): bool
    {
        return Str::startsWith($path, ['/storage', '/photos'])
            || str_contains($path, '/storage/')
            || str_contains($path, '/photos_')
            || str_contains($path, '/banners/');
    }

    private function url(string $endpoint): string
    {
        $base = $this->baseUrl();

        if ($base === '') {
            throw new ApiException('ANHA_API_BASE_URL belum diset.', 500);
        }

        return $base.'/'.ltrim($endpoint, '/');
    }

    private function decode(Response $response): array
    {
        $contentType = $response->header('content-type', '');
        $payload = str_contains($contentType, 'application/json')
            ? $response->json()
            : ['message' => $response->body()];

        if (! $response->successful()) {
            $message = is_array($payload)
                ? ($payload['message'] ?? 'Permintaan ke API gagal.')
                : 'Permintaan ke API gagal.';

            throw new ApiException($this->sanitizeError((string) $message), $response->status());
        }

        if ($response->status() === 204) {
            return [];
        }

        return is_array($payload) ? $payload : [];
    }

    private function sanitizeError(string $message): string
    {
        $trimmed = trim($message);

        if ($trimmed === '') {
            return 'Terjadi kesalahan saat memproses permintaan.';
        }

        if (str_contains($trimmed, '<html') || str_starts_with($trimmed, '<!DOCTYPE')) {
            return 'Gagal memuat data dari server. Silakan login ulang atau hubungi administrator.';
        }

        return Str::limit($trimmed, 500);
    }
}
