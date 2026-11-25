@extends('layouts.dashboard')

@section('content')
@php
    $api = app(\App\Services\ApiClient::class);
    $roleLabels = ['ritel' => 'Ritel', 'grosir' => 'Grosir'];
@endphp

<div class="space-y-5 p-4 md:p-6">
    <div>
        <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
            <i data-lucide="users" class="h-6 w-6 text-primary"></i>
            Pelanggan
        </h2>
        <p class="text-sm text-muted-foreground">Daftar pelanggan ritel dan grosir ANHA Elektrik.</p>
    </div>

    @include('partials.alerts')

    <form method="get" class="card flex flex-wrap items-end gap-3 p-4">
        <div class="w-full space-y-1.5 sm:w-64">
            <label for="q" class="text-sm font-medium">Cari</label>
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <input id="q" name="q" class="form-input pl-9" value="{{ request('q') }}" placeholder="Cari nama atau telepon...">
            </div>
        </div>
        <div class="w-full space-y-1.5 sm:w-44">
            <label for="role" class="text-sm font-medium">Tipe</label>
            <select id="role" name="role" class="form-input">
                <option value="all">Semua</option>
                <option value="ritel" @selected(request('role') === 'ritel')>Ritel</option>
                <option value="grosir" @selected(request('role') === 'grosir')>Grosir</option>
            </select>
        </div>
        <div class="w-full space-y-1.5 sm:w-32">
            <label for="limit" class="text-sm font-medium">Limit</label>
            <select id="limit" name="limit" class="form-input">
                @foreach (['20' => '20', '10' => '10', '50' => '50', 'all' => 'Semua'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('limit', '20') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-outline w-full sm:w-auto" type="submit">
            <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
            Terapkan
        </button>
    </form>

    <section class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Nama</th>
                        <th class="px-4 py-3 text-left font-medium">Kode Pelanggan</th>
                        <th class="px-4 py-3 text-left font-medium">Telepon</th>
                        <th class="px-4 py-3 text-left font-medium">Tipe</th>
                        <th class="px-4 py-3 text-left font-medium">Alamat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $image = $api->firstAssetUrl(data_get($row, 'photo_full_url'));
                            $role = data_get($row, 'role');
                        @endphp
                        <tr class="border-t border-border">
                            <td class="px-4 py-3 align-middle">
                                <div class="flex items-center gap-3">
                                    @if ($image)
                                        <img src="{{ $image }}" alt="" class="h-11 w-11 rounded-md border border-border object-cover">
                                    @else
                                        <span class="flex h-11 w-11 items-center justify-center rounded-md border border-border bg-muted text-xs text-muted-foreground">No Img</span>
                                    @endif
                                    <span>{{ data_get($row, 'name', '-') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'email', '-') }}</td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'phone', '-') }}</td>
                            <td class="px-4 py-3 align-middle">
                                <span class="badge badge-secondary">{{ $roleLabels[$role] ?? $role ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'address', '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="h-32 px-4 text-center text-muted-foreground">Belum ada pelanggan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('partials.pagination', ['meta' => $meta])
</div>
@endsection
