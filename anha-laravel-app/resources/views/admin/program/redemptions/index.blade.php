@extends('layouts.dashboard')

@section('content')
@php
    $formatDate = function ($value) {
        if (! $value) {
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
        'claimed' => 'Terklaim',
        'cancelled' => 'Dibatalkan',
    ];
@endphp

<div class="space-y-5 p-4 md:p-6">
    <div>
        <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
            <i data-lucide="ticket" class="h-6 w-6 text-primary"></i>
            Klaim Hadiah
        </h2>
        <p class="text-sm text-muted-foreground">Verifikasi klaim reward dari pelanggan.</p>
    </div>

    @include('partials.alerts')

    <form method="get" class="card flex flex-col gap-3 p-4 md:flex-row md:items-end">
        <div class="flex-1 space-y-1.5">
            <label for="q" class="text-sm font-medium">Cari</label>
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <input id="q" name="q" class="form-input pl-9" value="{{ request('q') }}" placeholder="Cari kode klaim atau pelanggan...">
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
                        <th class="px-4 py-3 text-left font-medium">Kode Klaim</th>
                        <th class="px-4 py-3 text-left font-medium">Pelanggan</th>
                        <th class="px-4 py-3 text-left font-medium">Reward</th>
                        <th class="px-4 py-3 text-left font-medium">Poin</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Tanggal</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php $status = data_get($row, 'status'); @endphp
                        <tr class="border-t border-border">
                            <td class="px-4 py-3 align-top font-medium">{{ data_get($row, 'claim_code', '-') }}</td>
                            <td class="px-4 py-3 align-top">{{ data_get($row, 'user.name', '-') }}</td>
                            <td class="px-4 py-3 align-top">{{ data_get($row, 'reward.name', '-') }}</td>
                            <td class="px-4 py-3 align-top">{{ data_get($row, 'points_spent', '-') }}</td>
                            <td class="px-4 py-3 align-top">
                                <span class="badge badge-secondary">{{ $statusLabels[$status] ?? $status ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 align-top">{{ $formatDate(data_get($row, 'created_at')) }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($status === 'pending')
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <form method="post" action="{{ route('redemptions.status', data_get($row, 'id')) }}" data-confirm="Tandai hadiah ini sebagai terklaim?">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="claimed">
                                            <button class="btn btn-primary px-2 py-1 text-xs">
                                                <i data-lucide="check-circle" class="h-3.5 w-3.5"></i>
                                                Verifikasi
                                            </button>
                                        </form>
                                        <form method="post" action="{{ route('redemptions.status', data_get($row, 'id')) }}" data-confirm="Batalkan klaim dan kembalikan poin pengguna?">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="btn btn-danger px-2 py-1 text-xs">
                                                <i data-lucide="x-circle" class="h-3.5 w-3.5"></i>
                                                Batalkan
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs italic text-muted-foreground">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="h-32 px-4 text-center text-muted-foreground">Belum ada klaim hadiah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('partials.pagination', ['meta' => $meta])
</div>
@endsection
