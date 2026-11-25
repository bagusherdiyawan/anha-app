@extends('layouts.dashboard')

@section('content')
    @php
        $action = $mode === 'create' ? route('notifications.store') : route('notifications.update', $id);
        $fieldValue = fn($name, $default = '') => old($name, data_get($item, $name, $default));
    @endphp

    <div class="min-h-screen p-4 md:p-6">
        <div class="mx-auto max-w-4xl space-y-4">
            <a href="{{ route('notifications.index') }}" class="btn btn-ghost">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Kembali ke daftar notifikasi
            </a>

            @include('partials.alerts')

            <section class="card">
                <div class="border-b border-border p-6">
                    <h2 class="flex items-center gap-2 text-xl font-semibold">
                        <i data-lucide="{{ $mode === 'create' ? 'plus' : 'pencil' }}" class="h-5 w-5 text-primary"></i>
                        {{ $title }}
                    </h2>
                    <p class="text-sm text-muted-foreground">Atur konten notifikasi, tautan, dan gambar.</p>
                </div>

                <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5 p-6"
                    data-loading-label="Menyimpan...">
                    @csrf
                    @if ($mode === 'edit')
                        @method('PUT')
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        
                        <div class="space-y-1.5 relative" data-search-select data-url="{{ route('users.search') }}">
                            <label for="user" class="text-sm font-medium">Penerima</label>
                            <input id="user" type="text" class="form-input" autocomplete="off"
                                placeholder="Ketik nama / kode pelanggan atau pilih Semua..."
                                data-search-input value="{{ $fieldValue('user_id') === 0 || $fieldValue('user_id') === '0' ? 'Semua' : $fieldValue('user.name') }}" />
                            <input type="hidden" name="user_id" data-search-value value="{{ $fieldValue('user_id', '0') }}" />
                            <p class="text-xs text-muted-foreground">Tulis nama / kode pelanggan dan pilih dari list, atau pilih "Semua"</p>
                            <ul data-search-result class="absolute left-0 right-0 z-30 mt-1 hidden max-h-60 overflow-y-auto rounded-md border border-border bg-card shadow-lg"></ul>
                            @error('user_id')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="title" class="text-sm font-medium">Judul</label>
                            <input id="title" name="title" type="text" class="form-input"
                                value="{{ $fieldValue('title') }}">
                            @error('title')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="category" class="text-sm font-medium">Category</label>
                            <input id="category" name="category" type="text" class="form-input"
                                value="{{ $fieldValue('category') }}">
                            @error('category')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="action_url" class="text-sm font-medium">Tautan</label>
                            <input id="action_url" name="action_url" type="text" class="form-input"
                                value="{{ $fieldValue('action_url') }}">
                            @error('action_url')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label for="body" class="text-sm font-medium">Pesan</label>
                            <textarea id="body" name="body" class="form-textarea" rows="4">{{ $fieldValue('body') }}</textarea>
                            @error('body')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label for="image" class="flex items-center gap-2 text-sm font-medium">
                                <i data-lucide="upload" class="h-4 w-4 text-muted-foreground"></i>
                                Gambar <span class="text-xs font-normal text-muted-foreground">(Opsional)</span>
                            </label>
                            <input id="image" name="image" type="file" accept="image/*"
                                class="form-input h-auto py-2" data-webp data-preview-target="#preview-image">
                            <div id="preview-image" class="mt-3 flex flex-wrap gap-3">
                                @foreach ($photos as $photo)
                                    <img src="{{ $photo }}" alt=""
                                        class="h-20 w-20 rounded-lg border border-border object-cover">
                                @endforeach
                            </div>
                            @error('image')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted-foreground">Opsional. Format gambar jpeg, png, jpg, webp dan ukuran maksimum 2MB.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-border pt-5">
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline">
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
