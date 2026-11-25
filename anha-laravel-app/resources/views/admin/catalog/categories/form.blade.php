@extends('layouts.dashboard')

@section('content')
    @php
        $action = $mode === 'create' ? route('categories.store') : route('categories.update', $id);
        $fieldValue = fn($name, $default = '') => old($name, data_get($item, $name, $default));
        $truthy = function ($value) {
            if (is_string($value)) {
                return in_array(strtolower($value), ['1', 'true', 'active', 'aktif', 'yes', 'ya'], true);
            }

            return in_array($value, [true, 1], true);
        };
    @endphp

    <div class="min-h-screen p-4 md:p-6">
        <div class="mx-auto max-w-4xl space-y-4">
            <a href="{{ route('categories.index') }}" class="btn btn-ghost">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Kembali ke daftar kategori
            </a>

            @include('partials.alerts')

            <section class="card">
                <div class="border-b border-border p-6">
                    <h2 class="flex items-center gap-2 text-xl font-semibold">
                        <i data-lucide="{{ $mode === 'create' ? 'plus' : 'pencil' }}" class="h-5 w-5 text-primary"></i>
                        {{ $title }}
                    </h2>
                    <p class="text-sm text-muted-foreground">Atur nama, kode, deskripsi, status, dan foto kategori.</p>
                </div>

                <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5 p-6"
                    data-loading-label="Menyimpan...">
                    @csrf
                    @if ($mode === 'edit')
                        @method('PUT')
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="kode" class="text-sm font-medium">Kode</label>
                            <input id="kode" name="kode" type="text" class="form-input"
                                value="{{ $fieldValue('kode') }}">
                            @error('kode')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="nama" class="text-sm font-medium">Nama</label>
                            <input id="nama" name="nama" type="text" class="form-input"
                                value="{{ $fieldValue('nama') }}">
                            @error('nama')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label for="deskripsi" class="text-sm font-medium">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-textarea" rows="4">{{ $fieldValue('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between rounded-lg border border-border px-3 py-2">
                                <span class="text-sm font-medium">Tampilkan di landing</span>
                                <input type="checkbox" name="landing" value="1"
                                    class="h-4 w-4 rounded border-input text-primary" @checked($truthy($fieldValue('landing', false)))>
                            </label>
                            @error('landing')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between rounded-lg border border-border px-3 py-2">
                                <span class="text-sm font-medium">Aktif</span>
                                <input type="checkbox" name="status_active" value="1"
                                    class="h-4 w-4 rounded border-input text-primary" @checked($truthy($fieldValue('status_active', true)))>
                            </label>
                            @error('status_active')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label for="foto" class="flex items-center gap-2 text-sm font-medium">
                                <i data-lucide="upload" class="h-4 w-4 text-muted-foreground"></i>
                                Foto kategori
                            </label>
                            <input id="foto" name="foto" type="file" accept="image/*"
                                class="form-input h-auto py-2" data-webp data-preview-target="#preview-foto">
                            <div id="preview-foto" class="mt-3 flex flex-wrap gap-3">
                                @foreach ($photos as $photo)
                                    <img src="{{ $photo }}" alt=""
                                        class="h-20 w-20 rounded-lg border border-border object-cover">
                                @endforeach
                            </div>
                            @error('foto')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted-foreground">Format gambar jpeg,png,jpg,webp dan ukuran maximum 2mb</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-border pt-5">
                        <a href="{{ route('categories.index') }}" class="btn btn-outline">
                            <i data-lucide="x" class="h-4 w-4"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
