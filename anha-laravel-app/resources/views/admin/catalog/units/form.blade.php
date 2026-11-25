@extends('layouts.dashboard')

@section('content')
    @php
        $action = $mode === 'create' ? route('units.store') : route('units.update', $id);
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
            <a href="{{ route('units.index') }}" class="btn btn-ghost">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Kembali ke daftar satuan
            </a>

            @include('partials.alerts')

            <section class="card">
                <div class="border-b border-border p-6">
                    <h2 class="flex items-center gap-2 text-xl font-semibold">
                        <i data-lucide="{{ $mode === 'create' ? 'plus' : 'pencil' }}" class="h-5 w-5 text-primary"></i>
                        {{ $title }}
                    </h2>
                    <p class="text-sm text-muted-foreground">Atur nama satuan.</p>
                </div>

                <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5 p-6"
                    data-loading-label="Menyimpan...">
                    @csrf
                    @if ($mode === 'edit')
                        @method('PUT')
                    @endif


                    <div class="space-y-1.5">
                        <label for="nama" class="text-sm font-medium">Nama</label>
                        <input id="nama" name="nama" type="text" class="form-input"
                            value="{{ $fieldValue('nama') }}">
                        @error('nama')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2 border-t border-border pt-5">
                        <a href="{{ route('units.index') }}" class="btn btn-outline">
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
