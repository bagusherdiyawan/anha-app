@extends('layouts.dashboard')

@section('content')
@php
    $api = app(\App\Services\ApiClient::class);
    $truthy = function ($value) {
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'active', 'aktif', 'yes', 'ya'], true);
        }

        return in_array($value, [true, 1], true);
    };
    $formatCurrency = fn ($value) => 'Rp '.number_format((float) ($value ?? 0), 0, ',', '.');
    $formatRating = fn ($value) => number_format((float) ($value ?? 0), 1, ',', '.');
@endphp

<div class="space-y-5 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <i data-lucide="package" class="h-6 w-6 text-primary"></i>
                Produk
            </h2>
            <p class="text-sm text-muted-foreground">Kelola katalog produk ANHA Elektrik.</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Produk
        </a>
    </div>

    @include('partials.alerts')

    <form method="get" class="card flex flex-wrap items-end gap-3 p-4">
        <div class="w-full space-y-1.5 sm:w-64">
            <label for="q" class="text-sm font-medium">Cari</label>
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <input id="q" name="q" class="form-input pl-9" value="{{ request('q') }}" placeholder="Cari nama atau kode...">
            </div>
        </div>
        <div class="w-full space-y-1.5 sm:w-44">
            <label for="status_active_ritel" class="text-sm font-medium">Status Ritel</label>
            <select id="status_active_ritel" name="status_active_ritel" class="form-input">
                <option value="all">Semua</option>
                <option value="active" @selected(request('status_active_ritel') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status_active_ritel') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="w-full space-y-1.5 sm:w-44">
            <label for="status_active_grosir" class="text-sm font-medium">Status Grosir</label>
            <select id="status_active_grosir" name="status_active_grosir" class="form-input">
                <option value="all">Semua</option>
                <option value="active" @selected(request('status_active_grosir') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status_active_grosir') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="w-full space-y-1.5 sm:w-72">
            <label for="kategori" class="text-sm font-medium">Kategori</label>
            <select id="kategori" name="kategori" class="form-input">
                <option value="all">Semua kategori</option>
                @foreach (($filterOptions['categories'] ?? []) as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) request('kategori') === (string) $optionValue)>{{ $optionLabel }}</option>
                @endforeach
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
                        <th class="px-4 py-3 text-left font-medium">Produk</th>
                        <th class="px-4 py-3 text-left font-medium">Kode</th>
                        <th class="px-4 py-3 text-left font-medium">Kategori</th>
                        <th class="px-4 py-3 text-left font-medium">Rating</th>
                        <th class="px-4 py-3 text-left font-medium">Harga Ritel</th>
                        <th class="px-4 py-3 text-left font-medium">Stok</th>
                        <th class="px-4 py-3 text-left font-medium">Ritel</th>
                        <th class="px-4 py-3 text-left font-medium">Grosir</th>
                        <th class="px-4 py-3 text-left font-medium">Ketersediaan</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $image = $api->firstAssetUrl(data_get($row, 'foto'));
                            $ritelActive = $truthy(data_get($row, 'status_ritel'));
                            $grosirActive = $truthy(data_get($row, 'status_grosir'));
                            $isAvailable = $truthy(data_get($row, 'is_available', true));
                        @endphp
                        <tr class="border-t border-border">
                            <td class="px-4 py-3 align-middle">
                                <div class="flex items-center gap-3">
                                    @if ($image)
                                        <img src="{{ $image }}" alt="" class="h-11 w-11 rounded-md border border-border object-cover">
                                    @else
                                        <span class="flex h-11 w-11 items-center justify-center rounded-md border border-border bg-muted text-xs text-muted-foreground">No Img</span>
                                    @endif
                                    <span>{{ data_get($row, 'nama_barang', '-') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'kode_barang', '-') }}</td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'nama_kategori', '-') }}</td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-flex items-center gap-1 font-medium text-amber-600">
                                    <i data-lucide="star" class="h-3.5 w-3.5 fill-amber-400 text-amber-500"></i>
                                    {{ $formatRating(data_get($row, 'rating')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="font-semibold">
                                    {{ $formatCurrency(data_get($row, 'harga_ritel')) }}
                                </span>
                                <span>
                                     / {{ data_get($row, 'satuan_nama') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="font-semibold {{ (int) data_get($row, 'stock', 0) <= 0 ? 'text-rose-600' : '' }}">
                                    {{ (int) data_get($row, 'stock', 0) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $ritelActive ? 'bg-emerald-500/10 text-emerald-700' : 'bg-muted text-muted-foreground' }}">
                                    <i data-lucide="{{ $ritelActive ? 'check-circle-2' : 'x-circle' }}" class="h-3.5 w-3.5"></i>
                                    {{ $ritelActive ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $grosirActive ? 'bg-emerald-500/10 text-emerald-700' : 'bg-muted text-muted-foreground' }}">
                                    <i data-lucide="{{ $grosirActive ? 'check-circle-2' : 'x-circle' }}" class="h-3.5 w-3.5"></i>
                                    {{ $grosirActive ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $isAvailable ? 'bg-emerald-500/10 text-emerald-700' : 'bg-rose-500/10 text-rose-700' }}">
                                    <i data-lucide="{{ $isAvailable ? 'check-circle-2' : 'x-circle' }}" class="h-3.5 w-3.5"></i>
                                    {{ $isAvailable ? 'Tersedia' : 'Habis' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <form method="post" action="{{ route('products.availability', data_get($row, 'id')) }}" data-confirm="Tandai produk ini sebagai {{ $isAvailable ? 'Habis' : 'Tersedia' }}?">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_available" value="{{ $isAvailable ? '0' : '1' }}">
                                        <button class="btn btn-outline px-2 py-1 text-xs" type="submit">
                                            <i data-lucide="{{ $isAvailable ? 'toggle-right' : 'toggle-left' }}" class="h-3.5 w-3.5"></i>
                                            Tandai {{ $isAvailable ? 'Habis' : 'Tersedia' }}
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('products.status', data_get($row, 'id')) }}" data-confirm="{{ $ritelActive ? 'Matikan' : 'Aktifkan' }} status ritel produk ini?">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_channel" value="ritel">
                                        <input type="hidden" name="status_active_ritel" value="{{ $ritelActive ? '0' : '1' }}">
                                        <input type="hidden" name="status_active_grosir" value="{{ $grosirActive ? '1' : '0' }}">
                                        <button class="btn btn-outline px-2 py-1 text-xs" type="submit">
                                            <i data-lucide="{{ $ritelActive ? 'toggle-right' : 'toggle-left' }}" class="h-3.5 w-3.5"></i>
                                            {{ $ritelActive ? 'Matikan' : 'Aktifkan' }} Ritel
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('products.status', data_get($row, 'id')) }}" data-confirm="{{ $grosirActive ? 'Matikan' : 'Aktifkan' }} status grosir produk ini?">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_channel" value="grosir">
                                        <input type="hidden" name="status_active_ritel" value="{{ $ritelActive ? '1' : '0' }}">
                                        <input type="hidden" name="status_active_grosir" value="{{ $grosirActive ? '0' : '1' }}">
                                        <button class="btn btn-outline px-2 py-1 text-xs" type="submit">
                                            <i data-lucide="{{ $grosirActive ? 'toggle-right' : 'toggle-left' }}" class="h-3.5 w-3.5"></i>
                                            {{ $grosirActive ? 'Matikan' : 'Aktifkan' }} Grosir
                                        </button>
                                    </form>
                                    <a href="{{ route('products.edit', data_get($row, 'id')) }}" class="btn btn-outline px-2 py-1 text-xs">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        Edit
                                    </a>
                                    <form method="post" action="{{ route('products.destroy', data_get($row, 'id')) }}" data-confirm="Hapus produk ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger px-2 py-1 text-xs" type="submit">
                                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="h-32 px-4 text-center text-muted-foreground">Belum ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('partials.pagination', ['meta' => $meta])
</div>
@endsection
