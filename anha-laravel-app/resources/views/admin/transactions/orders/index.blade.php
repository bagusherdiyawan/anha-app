@extends('layouts.dashboard')

@section('content')
    @php
        $formatCurrency = fn($value) => 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
        $formatDate = function ($value) {
            if (!$value) {
                return '-';
            }

            try {
                return \Illuminate\Support\Carbon::parse($value)->format('d M Y H:i');
            } catch (\Throwable) {
                return $value;
            }
        };
        $statusLabels = $statusOptions ?? [
            'pending' => 'Pending',
            'paid' => 'Dibayar',
            'confirmed' => 'Dikonfirmasi',
            'processed' => 'Diproses',
            'delivering' => 'Sedang diantar',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    @endphp

    <div class="space-y-5 p-4 md:p-6">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <i data-lucide="shopping-bag" class="h-6 w-6 text-primary"></i>
                Pesanan
            </h2>
            <p class="text-sm text-muted-foreground">Pantau dan kelola pesanan pelanggan.</p>
        </div>

        @include('partials.alerts')

        <form method="get" class="card flex flex-col gap-3 p-4 md:flex-row md:items-end">
            <div class="flex-1 space-y-1.5">
                <label for="q" class="text-sm font-medium">Cari</label>
                <div class="relative">
                    <i data-lucide="search"
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                    <input id="q" name="q" class="form-input pl-9" value="{{ request('q') }}"
                        placeholder="Cari invoice atau pelanggan...">
                </div>
            </div>
            <div class="space-y-1.5">
                <label for="status" class="text-sm font-medium">Status</label>
                <select id="status" name="status" class="form-input min-w-44">
                    <option value="all">Semua</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-outline" type="submit">
                <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
                Terapkan
            </button>
        </form>

        <section class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Invoice</th>
                            <th class="px-4 py-3 text-left font-medium">Pelanggan</th>
                            <th class="px-4 py-3 text-left font-medium">Total</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Tanggal</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-t border-border">
                                <td class="px-4 py-3 align-top">{{ data_get($row, 'invoice', '-') }}</td>
                                <td class="px-4 py-3 align-top">{{ data_get($row, 'user.name', '-') }}</td>
                                <td class="px-4 py-3 align-top">{{ $formatCurrency(data_get($row, 'total')) }}</td>
                                <td class="px-4 py-3 align-top">
                                    @php
                                        $status = data_get($row, 'status');

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
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabels[data_get($row, 'status')] ?? data_get($row, 'status', '-') }}</span>
                                </td>
                                <td class="px-4 py-3 align-top">{{ $formatDate(data_get($row, 'created_at')) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('orders.show', data_get($row, 'id')) }}"
                                        class="btn btn-outline px-2 py-1 text-xs">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5 text-primary"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="h-32 px-4 text-center text-muted-foreground">Belum ada pesanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @include('partials.pagination', ['meta' => $meta])
    </div>
@endsection
