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
    $typeLabels = [
        'cod' => ['label' => 'COD (Tunai)', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'transfer' => ['label' => 'Bank Transfer', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'wallet' => ['label' => 'E-Wallet / QRIS', 'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
    ];
@endphp

<div class="space-y-5 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <i data-lucide="credit-card" class="h-6 w-6 text-primary"></i>
                Rekening & Metode Pembayaran
            </h2>
            <p class="text-sm text-muted-foreground">Kelola rekening resmi toko ANHA Electrical (Bank Transfer, QRIS, dan COD).</p>
        </div>
        <a href="{{ route('rekenings.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Rekening
        </a>
    </div>

    @include('partials.alerts')

    <form method="get" class="card flex flex-wrap items-end gap-3 p-4">
        <div class="w-full space-y-1.5 sm:w-64">
            <label for="q" class="text-sm font-medium">Cari</label>
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <input id="q" name="q" class="form-input pl-9" value="{{ request('q') }}" placeholder="Cari nama bank, nomor...">
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
                        <th class="px-4 py-3 text-left font-medium">Metode / Bank</th>
                        <th class="px-4 py-3 text-left font-medium">Nomor Rekening</th>
                        <th class="px-4 py-3 text-left font-medium">Tipe</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $id = data_get($row, 'id');
                            $nama = data_get($row, 'nama', '-');
                            $nomor = data_get($row, 'nomor', '-');
                            $type = strtolower((string) data_get($row, 'type', 'transfer'));
                            $typeMeta = $typeLabels[$type] ?? ['label' => strtoupper($type), 'class' => 'bg-gray-100 text-gray-700 border-gray-200'];
                            $isActive = $truthy(data_get($row, 'status_active', true));
                            $foto = $api->firstAssetUrl(data_get($row, 'foto'));
                        @endphp
                        <tr class="border-t border-border hover:bg-muted/30">
                            <td class="px-4 py-3 align-middle">
                                <div class="flex items-center gap-3">
                                    @if ($foto)
                                        <img src="{{ $foto }}" alt="{{ $nama }}" class="h-9 w-9 rounded-md border border-border object-contain bg-white p-0.5">
                                    @else
                                        <div class="flex h-9 w-9 items-center justify-center rounded-md border border-border bg-muted text-xs font-bold text-muted-foreground">
                                            @if ($type === 'cod')
                                                💵
                                            @elseif ($type === 'wallet')
                                                📱
                                            @else
                                                🏦
                                            @endif
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-foreground">{{ $nama }}</p>
                                        @if (data_get($row, 'keterangan'))
                                            <p class="text-xs text-muted-foreground">{{ \Illuminate\Support\Str::limit(data_get($row, 'keterangan'), 40) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle font-mono font-medium">
                                {{ $nomor ?: '-' }}
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $typeMeta['class'] }}">
                                    {{ $typeMeta['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <form method="post" action="{{ route('rekenings.toggle', $id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors {{ $isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}" title="Klik untuk mengubah status">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right align-middle">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('rekenings.edit', $id) }}" class="btn btn-outline px-2.5 py-1 text-xs">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        Edit
                                    </a>
                                    <form method="post" action="{{ route('rekenings.destroy', $id) }}" data-confirm="Hapus rekening {{ $nama }} ini? Pelanggan tidak akan bisa lagi melihat rekening ini.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger px-2.5 py-1 text-xs" type="submit">
                                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="h-32 px-4 text-center text-muted-foreground">Belum ada daftar rekening toko.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('partials.pagination', ['meta' => $meta])
</div>
@endsection

