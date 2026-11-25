@extends('layouts.dashboard')

@section('content')
@php
    $action = $mode === 'create' ? route('banners.store') : route('banners.update', $id);
    $fieldValue = fn ($name, $default = '') => old($name, data_get($item, $name, $default));
    $truthy = function ($value) {
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'active', 'aktif', 'yes', 'ya'], true);
        }

        return in_array($value, [true, 1], true);
    };
    $dateLocal = function ($value) {
        if (! $value) {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return str_replace(' ', 'T', substr((string) $value, 0, 16));
        }
    };
@endphp

<div class="min-h-screen p-4 md:p-6">
    <div class="mx-auto max-w-4xl space-y-4">
        <a href="{{ route('banners.index') }}" class="btn btn-ghost">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke daftar banner
        </a>

        @include('partials.alerts')

        <section class="card">
            <div class="border-b border-border p-6">
                <h2 class="flex items-center gap-2 text-xl font-semibold">
                    <i data-lucide="{{ $mode === 'create' ? 'plus' : 'pencil' }}" class="h-5 w-5 text-primary"></i>
                    {{ $title }}
                </h2>
                <p class="text-sm text-muted-foreground">Atur konten banner, tautan, jadwal tampil, dan gambar banner.</p>
            </div>

            <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5 p-6" data-loading-label="Menyimpan...">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="title" class="text-sm font-medium">Judul</label>
                        <input id="title" name="title" type="text" class="form-input" value="{{ $fieldValue('title') }}">
                        @error('title')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    {{-- <div class="space-y-1.5">
                        <label for="deep_link" class="text-sm font-medium">Deep link</label>
                        <input id="deep_link" name="deep_link" type="text" class="form-input" value="{{ $fieldValue('deep_link') }}">
                        @error('deep_link')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div> --}}

                    <div class="md:col-span-2 space-y-1.5">
                        <label for="description" class="text-sm font-medium">Deskripsi</label>
                        <textarea id="description" name="description" class="form-textarea" rows="4">{{ $fieldValue('description') }}</textarea>
                        @error('description')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="sort_order" class="text-sm font-medium">Urutan</label>
                        <input id="sort_order" name="sort_order" type="number" class="form-input" value="{{ $fieldValue('sort_order', 0) }}">
                        @error('sort_order')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="flex items-center justify-between rounded-lg border border-border px-3 py-2">
                            <span class="text-sm font-medium">Aktif</span>
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-input text-primary" @checked($truthy($fieldValue('is_active', data_get($item, 'status_active', true))))>
                        </label>
                        @error('is_active')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="starts_at" class="text-sm font-medium">Tanggal mulai</label>
                        <input id="starts_at" name="starts_at" type="datetime-local" class="form-input" value="{{ old('starts_at', $dateLocal(data_get($item, 'starts_at'))) }}">
                        @error('starts_at')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="ends_at" class="text-sm font-medium">Tanggal berakhir</label>
                        <input id="ends_at" name="ends_at" type="datetime-local" class="form-input" value="{{ old('ends_at', $dateLocal(data_get($item, 'ends_at'))) }}">
                        @error('ends_at')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2 space-y-1.5">
                        <label for="image" class="flex items-center gap-2 text-sm font-medium">
                            <i data-lucide="upload" class="h-4 w-4 text-muted-foreground"></i>
                            Gambar banner
                        </label>
                        <input id="image" name="image" type="file" accept="image/*" class="form-input h-auto py-2" data-webp data-preview-target="#preview-image">
                        <div id="preview-image" class="mt-3 flex flex-wrap gap-3">
                            @foreach ($photos as $photo)
                                <img src="{{ $photo }}" alt="" class="h-20 w-20 rounded-lg border border-border object-cover">
                            @endforeach
                        </div>
                        @error('image')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                        <p class="text-xs text-muted-foreground">Format gambar jpeg,png,jpg,webp dan ukuran maximum 2mb</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border pt-5">
                    <a href="{{ route('banners.index') }}" class="btn btn-outline">
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
