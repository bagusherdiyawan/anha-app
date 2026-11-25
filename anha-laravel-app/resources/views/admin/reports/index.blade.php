@extends('layouts.dashboard')

@section('content')
@php
    $format = function ($row, $column) {
        $value = data_get($row, $column['key']);
        if (($column['type'] ?? null) === 'currency') return 'Rp '.number_format((float) ($value ?? 0), 0, ',', '.');
        if (($column['type'] ?? null) === 'date') {
            try { return $value ? \Illuminate\Support\Carbon::parse($value)->format('d M Y H:i') : '-'; } catch (\Throwable) { return $value ?: '-'; }
        }
        return is_bool($value) ? ($value ? 'Ya' : 'Tidak') : ($value ?? '-');
    };
    $reportIcon = [
        'penjualan' => 'trending-up',
        'pelanggan' => 'user-round',
        'poin' => 'star',
        'stok' => 'box',
    ][$type] ?? 'file-text';
@endphp
<div class="space-y-5 p-4 md:p-6">
    <div>
        <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
            <i data-lucide="{{ $reportIcon }}" class="h-6 w-6 text-primary"></i>
            {{ $title }}
        </h2>
        <p class="text-sm text-muted-foreground">Filter laporan berdasarkan kata kunci dan rentang tanggal.</p>
    </div>

    @include('partials.alerts')

    <form method="get" class="card grid gap-3 p-4 {{ $type === 'poin' ? 'md:grid-cols-5' : 'md:grid-cols-4' }} md:items-end">
        <div class="space-y-1.5">
            <label for="q" class="text-sm font-medium">Cari</label>
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <input id="q" name="q" class="form-input pl-9" value="{{ request('q') }}" placeholder="Cari...">
            </div>
        </div>
        <div class="space-y-1.5">
            <label for="start_date" class="text-sm font-medium">Tanggal mulai</label>
            <input id="start_date" name="start_date" type="date" class="form-input" value="{{ request('start_date') }}">
        </div>
        <div class="space-y-1.5">
            <label for="end_date" class="text-sm font-medium">Tanggal akhir</label>
            <input id="end_date" name="end_date" type="date" class="form-input" value="{{ request('end_date') }}">
        </div>
        @if ($type === 'poin')
            <div class="space-y-1.5">
                <label for="type" class="text-sm font-medium">Tipe</label>
                <select id="type" name="type" class="form-input">
                    <option value="all">Semua Tipe</option>
                    @foreach (['earn' => 'Didapat', 'redeem' => 'Digunakan', 'adjustment' => 'Penyesuaian', 'expired' => 'Kedaluwarsa'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button class="btn btn-outline" type="submit">
            <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
            Terapkan
        </button>
    </form>

    @if (! empty($summary))
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($summary as $key => $value)
                @continue(is_array($value))
                <section class="card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs uppercase tracking-wide text-muted-foreground">{{ \Illuminate\Support\Str::headline($key) }}</p>
                        <i data-lucide="activity" class="h-4 w-4 text-muted-foreground"></i>
                    </div>
                    <p class="mt-2 text-xl font-semibold">{{ is_numeric($value) ? number_format((float) $value, 0, ',', '.') : $value }}</p>
                </section>
            @endforeach
        </div>
    @endif

    <section class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        @foreach ($columns as $column)
                            <th class="px-4 py-3 text-left font-medium">{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-border">
                            @foreach ($columns as $column)
                                <td class="px-4 py-3">{{ $format($row, $column) }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ max(1, count($columns)) }}" class="h-32 px-4 text-center text-muted-foreground">Belum ada data laporan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('partials.pagination', ['meta' => $meta])
</div>
@endsection
