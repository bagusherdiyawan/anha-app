@extends('layouts.dashboard')

@section('content')
    @php
        $action = $mode === 'create' ? route('products.store') : route('products.update', $id);
        $valueFor = function ($name, $default = '') use ($item) {
            $aliases = [
                'status_active_ritel' => ['status_active_ritel', 'status_ritel'],
                'status_active_grosir' => ['status_active_grosir', 'status_grosir'],
            ];

            foreach ($aliases[$name] ?? [$name] as $key) {
                $value = data_get($item, $key);
                if ($value !== null) {
                    return $value;
                }
            }

            return $default;
        };
        $truthy = function ($value) {
            if (is_string($value)) {
                return in_array(strtolower($value), ['1', 'true', 'active', 'aktif', 'yes', 'ya'], true);
            }

            return in_array($value, [true, 1], true);
        };
        $inputValue = fn($name, $default = '') => old($name, $valueFor($name, $default));
        $selectedRating = max(0, min(5, (int) round((float) $inputValue('rating', 0))));
    @endphp

    <div class="min-h-screen p-4 md:p-6">
        <div class="mx-auto max-w-4xl space-y-4">
            <a href="{{ route('products.index') }}" class="btn btn-ghost">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Kembali ke daftar produk
            </a>

            @include('partials.alerts')

            <section class="card">
                <div class="border-b border-border p-6">
                    <h2 class="flex items-center gap-2 text-xl font-semibold">
                        <i data-lucide="{{ $mode === 'create' ? 'plus' : 'pencil' }}" class="h-5 w-5 text-primary"></i>
                        {{ $title }}
                    </h2>
                    <p class="text-sm text-muted-foreground">Lengkapi informasi produk, harga, status penjualan, dan foto
                        produk.</p>
                </div>

                <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5 p-6"
                    data-loading-label="Menyimpan...">
                    @csrf
                    @if ($mode === 'edit')
                        @method('PUT')
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="nama_barang" class="text-sm font-medium">Nama barang</label>
                            <input id="nama_barang" name="nama_barang" type="text" class="form-input"
                                value="{{ $inputValue('nama_barang') }}">
                            @error('nama_barang')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="kode_barang" class="text-sm font-medium">Kode barang</label>
                            <input id="kode_barang" name="kode_barang" type="text" class="form-input"
                                value="{{ $inputValue('kode_barang') }}">
                            @error('kode_barang')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="barcode" class="text-sm font-medium">Barcode</label>
                            <input id="barcode" name="barcode" type="text" class="form-input"
                                value="{{ $inputValue('barcode') }}">
                            @error('barcode')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="kode_kategori" class="text-sm font-medium">Kategori</label>
                            <select id="kode_kategori" name="kode_kategori" class="form-input">
                                <option value="">Pilih kategori</option>
                                @foreach ($options['categories'] ?? [] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected((string) $inputValue('kode_kategori') === (string) $optionValue)>{{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kode_kategori')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="harga_ritel" class="text-sm font-medium">Harga ritel</label>
                            <input id="harga_ritel" name="harga_ritel" type="number" class="form-input"
                                value="{{ $inputValue('harga_ritel') }}">
                            @error('harga_ritel')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="harga_grosir" class="text-sm font-medium">Harga grosir</label>
                            <input id="harga_grosir" name="harga_grosir" type="number" class="form-input"
                                value="{{ $inputValue('harga_grosir') }}">
                            @error('harga_grosir')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="rating" class="text-sm font-medium">Rating</label>
                            <div class="flex w-fit items-center gap-1 rounded-md border border-input bg-background px-3 py-2 shadow-xs"
                                role="radiogroup" aria-label="Rating" data-rating-picker>
                                <input id="rating" type="hidden" name="rating" value="{{ $selectedRating }}"
                                    data-rating-input>
                                @for ($star = 1; $star <= 5; $star++)
                                    <button type="button"
                                        class="text-amber-500 transition hover:scale-105 focus-visible:outline-2 focus-visible:outline-ring"
                                        role="radio" aria-label="Rating {{ $star }}"
                                        aria-checked="{{ $selectedRating === $star ? 'true' : 'false' }}" data-rating-star
                                        data-rating-value="{{ $star }}">
                                        <i data-lucide="star"
                                            class="h-5 w-5 {{ $selectedRating >= $star ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground' }}"></i>
                                    </button>
                                @endfor
                            </div>
                            @error('rating')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="stock" class="text-sm font-medium">Stok</label>
                            <input id="stock" name="stock" type="number" min="0" step="1" class="form-input"
                                value="{{ $inputValue('stock', 0) }}">
                            @error('stock')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- <div class="space-y-1.5">
                            <label for="satuan" class="text-sm font-medium">Satuan</label>
                            <input id="satuan" name="satuan" type="text" class="form-input"
                                value="{{ $inputValue('satuan') }}">
                            @error('satuan')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div> --}}

                        <div class="space-y-1.5">
                            <label for="satuan" class="text-sm font-medium">Satuan</label>
                            <select id="satuan" name="satuan" class="form-input">
                                <option value="">Pilih satuan</option>
                                @foreach ($options['units'] ?? [] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected((string) $inputValue('satuan') === (string) $optionValue)>{{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('satuan')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label for="deskripsi" class="text-sm font-medium">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-textarea" rows="4">{{ $inputValue('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between rounded-lg border border-border px-3 py-2">
                                <span class="text-sm font-medium">Aktif ritel</span>
                                <input type="checkbox" name="status_active_ritel" value="1"
                                    class="h-4 w-4 rounded border-input text-primary" @checked($truthy($inputValue('status_active_ritel', true)))>
                            </label>
                            @error('status_active_ritel')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between rounded-lg border border-border px-3 py-2">
                                <span class="text-sm font-medium">Aktif grosir</span>
                                <input type="checkbox" name="status_active_grosir" value="1"
                                    class="h-4 w-4 rounded border-input text-primary" @checked($truthy($inputValue('status_active_grosir', true)))>
                            </label>
                            @error('status_active_grosir')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between rounded-lg border border-border px-3 py-2">
                                <span class="text-sm font-medium">Tersedia (tidak habis)</span>
                                <input type="hidden" name="is_available" value="0">
                                <input type="checkbox" name="is_available" value="1"
                                    class="h-4 w-4 rounded border-input text-primary" @checked($truthy($inputValue('is_available', true)))>
                            </label>
                            @error('is_available')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label for="foto" class="flex items-center gap-2 text-sm font-medium">
                                <i data-lucide="upload" class="h-4 w-4 text-muted-foreground"></i>
                                Foto produk
                            </label>
                            <input id="foto" name="foto[]" type="file" accept="image/*"
                                class="form-input h-auto py-2" data-webp data-preview-target="#preview-foto"
                                data-photo-picker data-append-files multiple>
                            @if ($mode === 'edit')
                                <input type="hidden" name="had_existing_foto"
                                    value="{{ count($photos) > 0 ? '1' : '0' }}">
                            @endif
                            <div id="preview-foto" class="mt-3 flex flex-wrap gap-3">
                                @foreach ($photos as $photo)
                                    @if ($mode === 'edit')
                                        <div class="relative" data-existing-photo>
                                            <img src="{{ $photo }}" alt=""
                                                class="h-20 w-20 rounded-lg border border-border object-cover">
                                            <input type="hidden" name="existing_foto[]" value="{{ $photo }}">
                                            <button type="button"
                                                class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-destructive text-white shadow"
                                                title="Hapus foto" aria-label="Hapus foto produk"
                                                data-remove-existing-photo>
                                                <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                            </button>
                                        </div>
                                    @else
                                        <img src="{{ $photo }}" alt=""
                                            class="h-20 w-20 rounded-lg border border-border object-cover">
                                    @endif
                                @endforeach
                            </div>
                            <p class="text-xs text-muted-foreground">Format gambar jpeg,png,jpg,webp dan ukuran maximum
                                2mb.
                            </p>
                            <p class="text-xs text-muted-foreground">Tambahkan foto baru tanpa menghapus foto yang sudah
                                ada. Klik tombol X pada foto untuk menghapusnya saat disimpan.</p>
                            @error('foto')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                            @error('foto.*')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-border pt-5">
                        <a href="{{ route('products.index') }}" class="btn btn-outline">
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
