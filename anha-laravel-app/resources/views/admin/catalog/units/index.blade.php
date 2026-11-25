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
@endphp

<div class="space-y-5 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <i data-lucide="layout-grid" class="h-6 w-6 text-primary"></i>
                Satuan
            </h2>
            <p class="text-sm text-muted-foreground">Kelola satuan produk ANHA Elektrik.</p>
        </div>
        <a href="{{ route('units.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Satuan
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
                        <th class="px-4 py-3 text-left font-medium">Nama Satuan</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-border">
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'nama', '-') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('units.edit', data_get($row, 'id')) }}" class="btn btn-outline px-2 py-1 text-xs">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        Edit
                                    </a>
                                    <form method="post" action="{{ route('units.destroy', data_get($row, 'id')) }}" data-confirm="Hapus satuan ini?">
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="h-32 px-4 text-center text-muted-foreground">Belum ada satuan produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('partials.pagination', ['meta' => $meta])
</div>
@endsection
