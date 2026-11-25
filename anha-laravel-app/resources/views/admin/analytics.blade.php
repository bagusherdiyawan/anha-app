@extends('layouts.dashboard')

@section('content')
    @php
        $formatRupiah = fn($value) => 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
        $maxRevenue = max(1, collect($chartData)->max(fn($row) => (float) data_get($row, 'revenue', 0)) ?: 1);
    @endphp
    <div class="space-y-6 p-4 md:p-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Analitik</h2>
            <p class="text-sm text-muted-foreground">Ringkasan performa dan aktivitas toko Anda.</p>
        </div>

        @include('partials.alerts')

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <section class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted-foreground">Total Pendapatan</p>
                    <i data-lucide="dollar-sign" class="h-4 w-4 text-muted-foreground"></i>
                </div>
                <p class="mt-2 text-2xl font-bold">{{ $formatRupiah(data_get($kpis, 'total_revenue', 0)) }}</p>
                <p class="mt-1 text-xs text-muted-foreground">Total transaksi berhasil</p>
            </section>
            <section class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted-foreground">Total Pesanan</p>
                    <i data-lucide="activity" class="h-4 w-4 text-muted-foreground"></i>
                </div>
                <p class="mt-2 text-2xl font-bold">
                    +{{ number_format((float) data_get($kpis, 'total_orders', 0), 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-muted-foreground">Semua pesanan masuk</p>
            </section>
            <section class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted-foreground">Pelanggan Aktif</p>
                    <i data-lucide="users" class="h-4 w-4 text-muted-foreground"></i>
                </div>
                <p class="mt-2 text-2xl font-bold">
                    {{ number_format((float) data_get($kpis, 'total_users', 0), 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-muted-foreground">Grosir & Ritel</p>
            </section>
            <section class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted-foreground">Total Produk</p>
                    <i data-lucide="package" class="h-4 w-4 text-muted-foreground"></i>
                </div>
                <p class="mt-2 text-2xl font-bold">
                    {{ number_format((float) data_get($kpis, 'total_products', 0), 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-muted-foreground">Produk di katalog</p>
            </section>
        </div>

        <div class="grid gap-4 xl:grid-cols-7">
            <section class="card p-5 xl:col-span-4">
                <h3 class="flex items-center gap-2 font-semibold">
                    <i data-lucide="bar-chart-3" class="h-4 w-4 text-primary"></i>
                    Pendapatan 7 Hari Terakhir
                </h3>
                <div class="mt-6 flex h-72 items-end gap-3 border-b border-border pb-3">
                    @forelse ($chartData as $row)
                        @php
                            $revenue = (float) data_get($row, 'revenue', 0);
                            $height = max(4, ($revenue / $maxRevenue) * 100);
                        @endphp
                        <div class="flex h-full flex-1 flex-col justify-end gap-2">
                            <div class="rounded-t-md bg-primary" style="height: {{ $height }}%"
                                title="{{ $formatRupiah($revenue) }}"></div>
                            <p class="truncate text-center text-xs text-muted-foreground">{{ data_get($row, 'name', '-') }}
                            </p>
                        </div>
                    @empty
                        <div class="flex h-full w-full items-center justify-center text-sm text-muted-foreground">Belum ada
                            data grafik.</div>
                    @endforelse
                </div>
            </section>

            <section class="card overflow-hidden xl:col-span-3">
                <div class="border-b border-border p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="flex items-center gap-2 font-semibold">
                                <i data-lucide="shopping-bag" class="h-4 w-4 text-primary"></i>
                                Pesanan Terbaru
                            </h3>
                            <p class="text-sm text-muted-foreground">Transaksi terakhir yang masuk ke sistem.</p>
                        </div>
                        <a href="{{ route('orders.index') }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                            Lihat semua
                            <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="table-header">
                            <tr>
                                <th class="px-4 py-3 text-left">Pelanggan</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusLabels = $statusOptions ?? [
                                    'pending' => 'Pending',
                                    'confirmed' => 'Dikonfirmasi',
                                    'processed' => 'Diproses',
                                    'delivering' => 'Sedang diantar',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                ];
                            @endphp
                            @forelse ($recentOrders as $order)
                                <tr class="border-t border-border">
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ data_get($order, 'nama_user', 'Tanpa Nama') }}</p>
                                        <p class="text-xs text-muted-foreground">{{ data_get($order, 'tanggal', '-') }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{-- <span class="badge badge-secondary">
                                            {{ data_get($order, 'status', 'Unknown') }}
                                        </span> --}}
                                        @php
                                            $status = data_get($order, 'status');

                                            $badgeClass = match ($status) {
                                                'pending' => 'bg-yellow-300 text-gray-900',
                                                'confirmed' => 'bg-orange-400 text-gray-900',
                                                'processed' => 'bg-green-400 text-white',
                                                'delivering' => 'bg-green-800 text-white',
                                                'completed' => 'bg-blue-400 text-white',
                                                'cancelled' => 'bg-red-400 text-red-200',
                                                default => 'bg-gray-100 text-gray-900',
                                            };
                                        @endphp
                                        <span
                                            class="badge {{ $badgeClass }}">{{ $statusLabels[data_get($order, 'status')] ?? data_get($order, 'status', '-') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">
                                        {{ $formatRupiah(data_get($order, 'total')) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="h-24 px-4 text-center text-muted-foreground">Belum ada pesanan
                                        terbaru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
