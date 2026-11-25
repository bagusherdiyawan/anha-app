@extends('layouts.dashboard')

@section('content')
@php
    $action = $mode === 'create' ? route('rekenings.store') : route('rekenings.update', $id);
    $fieldValue = fn ($name, $default = '') => old($name, data_get($item, $name, $default));
    $truthy = function ($value) {
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'active', 'aktif', 'yes', 'ya'], true);
        }

        return in_array($value, [true, 1], true);
    };
    $currentPhoto = $photos[0] ?? null;
@endphp

<div class="min-h-screen p-4 md:p-6">
    <div class="mx-auto max-w-3xl space-y-4">
        <a href="{{ route('rekenings.index') }}" class="btn btn-ghost">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke daftar rekening
        </a>

        @include('partials.alerts')

        <section class="card">
            <div class="border-b border-border p-6">
                <h2 class="flex items-center gap-2 text-xl font-semibold">
                    <i data-lucide="{{ $mode === 'create' ? 'plus' : 'pencil' }}" class="h-5 w-5 text-primary"></i>
                    {{ $title }}
                </h2>
                <p class="text-sm text-muted-foreground">Atur nomor rekening resmi toko, nama bank/e-wallet, tipe pembayaran, dan barcode QRIS/logo.</p>
            </div>

            <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5 p-6" data-loading-label="Menyimpan...">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-1.5 md:col-span-2">
                        <label for="nama" class="text-sm font-medium">Nama Bank / Metode Pembayaran <span class="text-destructive">*</span></label>
                        <input id="nama" name="nama" type="text" class="form-input" value="{{ $fieldValue('nama') }}" placeholder="Contoh: Bank Mandiri, QRIS Toko, BCA" required>
                        @error('nama')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="nomor" class="text-sm font-medium">Nomor Rekening / Pembayaran</label>
                        <input id="nomor" name="nomor" type="text" class="form-input" value="{{ $fieldValue('nomor') }}" placeholder="Contoh: 1234567890 (atau '-' untuk COD)">
                        <p class="text-xs text-muted-foreground">Isi tanda hubung (-) jika metode tunai / COD.</p>
                        @error('nomor')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="type" class="text-sm font-medium">Tipe Pembayaran <span class="text-destructive">*</span></label>
                        <select id="type" name="type" class="form-input" required>
                            @foreach ($typeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($fieldValue('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5 md:col-span-2">
                        <label for="keterangan" class="text-sm font-medium">Keterangan / Panduan Transfer</label>
                        <textarea id="keterangan" name="keterangan" class="form-textarea" rows="3" placeholder="Contoh: a/n PT ANHA Electrical Indonesia. Berita transfer cantumkan nomor invoice.">{{ $fieldValue('keterangan') }}</textarea>
                        @error('keterangan')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5 md:col-span-2">
                        <label class="flex items-center justify-between rounded-lg border border-border px-4 py-3">
                            <div>
                                <span class="text-sm font-medium">Status Aktif</span>
                                <p class="text-xs text-muted-foreground">Jika aktif, rekening akan tampil pada aplikasi pelanggan di halaman Rekening & Cara Bayar.</p>
                            </div>
                            <input type="checkbox" name="status_active" value="1" class="h-4 w-4 rounded border-input text-primary" @checked($truthy($fieldValue('status_active', data_get($item, 'status_active', true))))>
                        </label>
                        @error('status_active')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5 md:col-span-2">
                        <label for="foto" class="flex items-center gap-2 text-sm font-medium">
                            <i data-lucide="upload" class="h-4 w-4 text-muted-foreground"></i>
                            Logo Bank / Gambar Barcode QRIS
                        </label>
                        <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="form-input">
                        <p class="text-xs text-muted-foreground">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                        @error('foto')<p class="text-xs text-destructive">{{ $message }}</p>@enderror

                        @if ($currentPhoto)
                            <div class="mt-3 flex items-center gap-4 rounded-lg border border-border p-3 bg-muted/20">
                                <img src="{{ $currentPhoto }}" alt="Foto Rekening" class="h-16 w-16 rounded border border-border object-contain bg-white p-1">
                                <div class="space-y-1">
                                    <p class="text-xs font-medium text-foreground">Gambar saat ini terpasang</p>
                                    <label class="inline-flex items-center gap-1.5 text-xs text-destructive hover:underline cursor-pointer">
                                        <input type="checkbox" name="clear_foto" value="ya" class="h-3.5 w-3.5 rounded text-destructive">
                                        Hapus gambar saat menyimpan
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-border pt-5">
                    <a href="{{ route('rekenings.index') }}" class="btn btn-outline">Batal</a>
                    <button class="btn btn-primary" type="submit">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Simpan Rekening
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection

