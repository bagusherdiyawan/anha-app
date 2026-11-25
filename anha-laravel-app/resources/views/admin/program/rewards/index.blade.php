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
    $rewardTypeLabels = [
        'voucher_discount' => 'Voucher Diskon',
        'direct_discount' => 'Diskon Langsung',
        'physical_item' => 'Barang Fisik',
    ];
@endphp

<div class="space-y-5 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <i data-lucide="circle-dollar-sign" class="h-6 w-6 text-primary"></i>
                Program Reward
            </h2>
            <p class="text-sm text-muted-foreground">Kelola program reward loyalitas pelanggan.</p>
        </div>
        <a href="{{ route('rewards.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Reward
        </a>
    </div>

    @include('partials.alerts')

    <form method="get" class="card flex flex-wrap items-end gap-3 p-4">
        <div class="w-full space-y-1.5 sm:w-64">
            <label for="q" class="text-sm font-medium">Cari</label>
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <input id="q" name="q" class="form-input pl-9" value="{{ request('q') }}" placeholder="Cari nama reward...">
            </div>
        </div>
        <div class="w-full space-y-1.5 sm:w-44">
            <label for="type" class="text-sm font-medium">Tipe</label>
            <select id="type" name="type" class="form-input">
                <option value="all">Semua</option>
                <option value="voucher_discount" @selected(request('type') === 'voucher_discount')>Voucher Diskon</option>
                <option value="direct_discount" @selected(request('type') === 'direct_discount')>Diskon Langsung</option>
                <option value="physical_item" @selected(request('type') === 'physical_item')>Barang Fisik</option>
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
                        <th class="px-4 py-3 text-left font-medium">Reward</th>
                        <th class="px-4 py-3 text-left font-medium">Tipe</th>
                        <th class="px-4 py-3 text-left font-medium">Poin</th>
                        <th class="px-4 py-3 text-left font-medium">Stok</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $image = $api->firstAssetUrl(data_get($row, 'image_url'));
                            $isActive = $truthy(data_get($row, 'is_active'));
                            $type = data_get($row, 'type');
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
                            <td class="px-4 py-3 align-middle">
                                <span class="badge badge-secondary">{{ $rewardTypeLabels[$type] ?? $type ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'points_required', '-') }}</td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'stock', '-') }}</td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $isActive ? 'bg-emerald-500/10 text-emerald-700' : 'bg-muted text-muted-foreground' }}">
                                    <i data-lucide="{{ $isActive ? 'check-circle-2' : 'x-circle' }}" class="h-3.5 w-3.5"></i>
                                    {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <form method="post" action="{{ route('rewards.toggle', data_get($row, 'id')) }}" data-confirm="Ubah status reward ini?">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-outline px-2 py-1 text-xs" type="submit">
                                            <i data-lucide="power" class="h-3.5 w-3.5"></i>
                                            Toggle
                                        </button>
                                    </form>
                                    <a href="{{ route('rewards.edit', data_get($row, 'id')) }}" class="btn btn-outline px-2 py-1 text-xs">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        Edit
                                    </a>
                                    <form method="post" action="{{ route('rewards.destroy', data_get($row, 'id')) }}" data-confirm="Hapus reward ini?">
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
                            <td colspan="6" class="h-32 px-4 text-center text-muted-foreground">Belum ada reward.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('partials.pagination', ['meta' => $meta])
</div>
@endsection
