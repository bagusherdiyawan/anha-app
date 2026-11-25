@extends('layouts.dashboard')

@section('content')
@php
    $formatRupiah = fn ($value) => 'Rp '.number_format((float) ($value ?? 0), 0, ',', '.');
    $cards = [
        ['label' => 'Pendapatan hari ini', 'icon' => 'dollar-sign', 'tone' => 'bg-emerald-500/15 text-emerald-600', 'value' => $formatRupiah(data_get($stats, 'pendapatanHariIni', data_get($stats, 'today_revenue', 0))), 'sub' => 'Bulan ini: '.$formatRupiah(data_get($stats, 'pendapatanBulanIni', data_get($stats, 'month_revenue', 0)))],
        ['label' => 'Pesanan hari ini', 'icon' => 'clipboard-list', 'tone' => 'bg-sky-500/15 text-sky-600', 'value' => data_get($stats, 'pesananHariIni', data_get($stats, 'today_orders', 0)), 'sub' => data_get($stats, 'pesananSubtext', 'Semua diproses')],
        // ['label' => 'Pelanggan aktif', 'icon' => 'users', 'tone' => 'bg-violet-500/15 text-violet-600', 'value' => data_get($stats, 'pelangganAktif', data_get($stats, 'active_customers', 0)), 'sub' => 'B2C/B2B pelanggan terdaftar'],
    ];
@endphp
<div class="space-y-6 p-4 md:p-6">
    @include('partials.alerts')

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-2">
        @foreach ($cards as $card)
            <section class="card p-5">
                <div class="flex items-start justify-between gap-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ $card['label'] }}</p>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $card['tone'] }}">
                        <i data-lucide="{{ $card['icon'] }}" class="h-4 w-4"></i>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-bold tabular-nums">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-muted-foreground">{{ $card['sub'] }}</p>
            </section>
        @endforeach
    </div>

    <section class="card">
        <div class="flex items-center justify-between border-b border-border p-4">
            <h2 class="flex items-center gap-2 font-semibold">
                <i data-lucide="shopping-bag" class="h-4 w-4 text-primary"></i>
                {{ ($analytics ?? false) ? 'Ringkasan analitik' : 'Pesanan terbaru' }}
            </h2>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                Lihat semua
                <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Order</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Total</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="border-t border-border">
                            <td class="px-4 py-3">{{ data_get($order, 'order_number', data_get($order, 'id')) }}</td>
                            <td class="px-4 py-3">{{ data_get($order, 'user.name', data_get($order, 'customer_name', '-')) }}</td>
                            <td class="px-4 py-3">{{ $formatRupiah(data_get($order, 'total')) }}</td>
                            <td class="px-4 py-3"><span class="badge badge-secondary">{{ data_get($order, 'status', '-') }}</span></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ data_get($order, 'created_at', '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="h-32 px-4 text-center text-muted-foreground">Belum ada pesanan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
