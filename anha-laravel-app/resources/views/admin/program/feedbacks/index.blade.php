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
        'submitted' => 'Baru',
        'read' => 'Dibaca',
        'replied' => 'Dibalas',
    ];
    $categoryLabels = $categoryOptions ?? [
        'product' => 'Produk',
        'service' => 'Layanan',
        'delivery' => 'Pengiriman',
        'app' => 'Aplikasi',
        'other' => 'Lainnya',
    ];
@endphp

<div class="space-y-5 p-4 md:p-6">
    <div>
        <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
            <i data-lucide="message-square-more" class="h-6 w-6 text-primary"></i>
            Kritik & Saran
        </h2>
        <p class="text-sm text-muted-foreground">Pantau dan tanggapi masukan dari pelanggan.</p>
    </div>

    @include('partials.alerts')

    <form method="get" class="card flex flex-col gap-3 p-4 md:flex-row md:items-end">
        <div class="flex-1 space-y-1.5">
            <label for="q" class="text-sm font-medium">Cari</label>
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <input id="q" name="q" class="form-input pl-9" value="{{ request('q') }}" placeholder="Cari pelanggan atau pesan...">
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
        <div class="space-y-1.5">
            <label for="category" class="text-sm font-medium">Kategori</label>
            <select id="category" name="category" class="form-input min-w-44">
                <option value="all">Semua</option>
                @foreach ($categoryLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
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
                        <th class="px-4 py-3 text-left font-medium">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium">Pelanggan</th>
                        <th class="px-4 py-3 text-left font-medium">Kategori</th>
                        <th class="px-4 py-3 text-left font-medium">Pesan</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $category = data_get($row, 'category');
                            $status = data_get($row, 'status');
                        @endphp
                        <tr class="border-t border-border">
                            <td class="px-4 py-3 align-top">{{ $formatDate(data_get($row, 'created_at')) }}</td>
                            <td class="px-4 py-3 align-top">{{ data_get($row, 'user.name', '-') }}</td>
                            <td class="px-4 py-3 align-top">
                                <span class="badge badge-secondary">{{ $categoryLabels[$category] ?? $category ?? '-' }}</span>
                            </td>
                            <td class="max-w-xs px-4 py-3 align-top">
                                <span class="line-clamp-3">{{ data_get($row, 'message', '-') }}</span>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <span class="badge badge-secondary">{{ $statusLabels[$status] ?? $status ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('feedbacks.show', data_get($row, 'id')) }}" class="btn btn-outline px-2 py-1 text-xs">
                                        <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                                        Detail
                                    </a>
                                    <form method="post" action="{{ route('feedbacks.reply', data_get($row, 'id')) }}" class="flex gap-2">
                                        @csrf
                                        <input name="content" class="form-input h-8 w-44" placeholder="Balas..." value="">
                                        <button class="btn btn-primary px-2 py-1 text-xs" type="submit">
                                            <i data-lucide="send" class="h-3.5 w-3.5"></i>
                                            Kirim
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('feedbacks.destroy', data_get($row, 'id')) }}" data-confirm="Hapus kritik dan saran ini?">
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
                            <td colspan="6" class="h-32 px-4 text-center text-muted-foreground">Belum ada kritik dan saran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('partials.pagination', ['meta' => $meta])
</div>
@endsection
