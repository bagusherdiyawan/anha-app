@extends('layouts.dashboard')

@section('content')
@php
    $action = $mode === 'create' ? route('users.store') : route('users.update', $id);
    $fieldValue = fn ($name, $default = '') => old($name, data_get($item, $name, $default));
    $currentRole = (string) $fieldValue('role');
@endphp

<div class="min-h-screen p-4 md:p-6">
    <div class="mx-auto max-w-4xl space-y-4">
        <a href="{{ route('users.index') }}" class="btn btn-ghost">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke daftar pengguna
        </a>

        @include('partials.alerts')

        <section class="card">
            <div class="border-b border-border p-6">
                <h2 class="flex items-center gap-2 text-xl font-semibold">
                    <i data-lucide="{{ $mode === 'create' ? 'user-plus' : 'pencil' }}" class="h-5 w-5 text-primary"></i>
                    {{ $title }}
                </h2>
                <p class="text-sm text-muted-foreground">Kelola identitas, kontak, alamat, role, dan password pengguna.</p>
            </div>

            <form method="post" action="{{ $action }}" class="space-y-5 p-6" data-loading-label="Menyimpan...">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="name" class="text-sm font-medium">Nama</label>
                        <input id="name" name="name" type="text" class="form-input" value="{{ $fieldValue('name') }}">
                        @error('name')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    {{-- <div class="space-y-1.5">
                        <label for="email" class="text-sm font-medium">Kode</label>
                        <input id="email" name="email" type="text" class="form-input" value="{{ $fieldValue('email') }}">
                        @error('email')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div> --}}

                    <div class="space-y-1.5">
                        <label for="phone" class="text-sm font-medium">Telepon</label>
                        <input id="phone" name="phone" type="text" class="form-input" value="{{ $fieldValue('phone') }}">
                        @error('phone')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="role" class="text-sm font-medium">Role</label>
                        <select id="role" name="role" class="form-input">
                            <option value="">Pilih role</option>
                            @foreach (($options['roles'] ?? []) as $optionValue => $optionLabel)
                                @continue($optionValue === 'admin' && ! ($mode === 'edit' && $currentRole === 'admin'))
                                <option value="{{ $optionValue }}" @selected((string) $fieldValue('role') === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                        @error('role')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2 space-y-1.5">
                        <label for="address" class="text-sm font-medium">Alamat</label>
                        <textarea id="address" name="address" class="form-textarea" rows="4">{{ $fieldValue('address') }}</textarea>
                        @error('address')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2 space-y-1.5">
                        <label for="password" class="text-sm font-medium">Password</label>
                        <input id="password" name="password" type="password" class="form-input" autocomplete="new-password">
                        @if ($mode === 'edit')
                            <p class="text-xs text-muted-foreground">Kosongkan jika tidak ingin mengubah password.</p>
                        @endif
                        @error('password')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border pt-5">
                    <a href="{{ route('users.index') }}" class="btn btn-outline">
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
