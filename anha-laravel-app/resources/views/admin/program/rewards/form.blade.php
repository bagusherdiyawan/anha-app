@extends('layouts.dashboard')

@section('content')
@php
    $action = $mode === 'create' ? route('rewards.store') : route('rewards.update', $id);
    $fieldValue = fn ($name, $default = '') => old($name, data_get($item, $name, $default));
    $truthy = function ($value) {
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'active', 'aktif', 'yes', 'ya'], true);
        }

        return in_array($value, [true, 1], true);
    };
@endphp

<div class="min-h-screen p-4 md:p-6">
    <div class="mx-auto max-w-4xl space-y-4">
        <a href="{{ route('rewards.index') }}" class="btn btn-ghost">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke daftar reward
        </a>

        @include('partials.alerts')

        <section class="card">
            <div class="border-b border-border p-6">
                <h2 class="flex items-center gap-2 text-xl font-semibold">
                    <i data-lucide="{{ $mode === 'create' ? 'plus' : 'pencil' }}" class="h-5 w-5 text-primary"></i>
                    {{ $title }}
                </h2>
                <p class="text-sm text-muted-foreground">Pilih barang, atur kebutuhan poin, dan stok reward.</p>
            </div>

            <form method="post" action="{{ $action }}" class="space-y-5 p-6" data-loading-label="Menyimpan...">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <input type="hidden" name="type" value="physical_item">

                    <div class="space-y-1.5 relative md:col-span-2" id="field-barang" data-search-select data-url="{{ route('products.search') }}">
                        <label for="barang" class="text-sm font-medium">Pilih barang</label>
                        <input id="barang" type="text" class="form-input" data-search-input
                            value="{{ $fieldValue('barang.nama_barang') }}" placeholder="Cari nama produk...">
                        <input type="hidden" name="barang_id" data-search-value value="{{ $fieldValue('barang_id') }}">
                        <p class="text-xs text-muted-foreground">Nama, deskripsi, dan gambar reward mengikuti produk yang dipilih.</p>
                        <ul data-search-result class="absolute z-10 hidden w-full rounded-md border border-primary bg-card"></ul>
                        @error('barang_id')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="points_required" class="text-sm font-medium">Poin dibutuhkan</label>
                        <input id="points_required" name="points_required" type="number" class="form-input" value="{{ $fieldValue('points_required') }}">
                        @error('points_required')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="stock" class="text-sm font-medium">Stok</label>
                        <input id="stock" name="stock" type="number" class="form-input" value="{{ $fieldValue('stock') }}">
                        @error('stock')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="mt-6 flex items-center justify-between rounded-lg border border-border px-3 py-2">
                            <span class="text-sm font-medium">Aktif</span>
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-input text-primary" @checked($truthy($fieldValue('is_active', data_get($item, 'status_active', true))))>
                        </label>
                        @error('is_active')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                </div>

                <div class="flex justify-end gap-2 border-t border-border pt-5">
                    <a href="{{ route('rewards.index') }}" class="btn btn-outline">
                        <i data-lucide="x" class="h-4 w-4"></i>
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
