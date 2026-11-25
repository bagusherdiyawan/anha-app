@extends('layouts.dashboard')

@section('content')
@php
    $api = app(\App\Services\ApiClient::class);
    $roleLabels = ['admin' => 'Admin', 'ritel' => 'Ritel', 'grosir' => 'Grosir'];
@endphp

<div class="space-y-5 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <i data-lucide="users-2" class="h-6 w-6 text-primary"></i>
                Pengguna
            </h2>
            <p class="text-sm text-muted-foreground">Kelola akun admin dan staf ANHA Elektrik.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah User
        </a>
    </div>

    @include('partials.alerts')

    <form method="get" class="card flex flex-wrap items-end gap-3 p-4">
        <div class="w-full space-y-1.5 sm:w-64">
            <label for="q" class="text-sm font-medium">Cari</label>
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <input id="q" name="q" class="form-input pl-9" value="{{ request('q') }}" placeholder="Cari nama atau email...">
            </div>
        </div>
        <div class="w-full space-y-1.5 sm:w-44">
            <label for="role" class="text-sm font-medium">Role</label>
            <select id="role" name="role" class="form-input">
                <option value="all">Semua</option>
                <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                <option value="ritel" @selected(request('role') === 'ritel')>Ritel</option>
                <option value="grosir" @selected(request('role') === 'grosir')>Grosir</option>
            </select>
        </div>
        <div class="w-full space-y-1.5 sm:w-32">
            <label for="limit" class="text-sm font-medium">Limit</label>
            <select id="limit" name="limit" class="form-input">
                @foreach (['20' => '20', '10' => '10', '50' => '50', 'all' => 'Semua'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('limit', '20') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-outline w-full sm:w-auto" type="submit">
            <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
            Terapkan
        </button>
    </form>

    <section class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Nama</th>
                        <th class="px-4 py-3 text-left font-medium">Kode Pelanggan</th>
                        <th class="px-4 py-3 text-left font-medium">Telepon</th>
                        <th class="px-4 py-3 text-left font-medium">Alamat</th>
                        <th class="px-4 py-3 text-left font-medium">Role</th>
                        {{-- <th class="px-4 py-3 text-left font-medium">Verifikasi</th> --}}
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $image = $api->firstAssetUrl(data_get($row, 'photo_full_url'));
                            $role = data_get($row, 'role');
                            $verified = filled(data_get($row, 'email_verified_at'));
                        @endphp
                        <tr class="border-t border-border">
                            <td class="px-4 py-3 align-middle">
                                <div class="flex items-center gap-3">
                                    @if ($image)
                                        <img src="{{ $image }}" alt="" class="h-11 w-11 rounded-md border border-border object-cover">
                                    @else
                                        <span class="flex h-11 w-11 items-center justify-center rounded-md border border-border bg-muted text-xs text-muted-foreground">No Img</span>
                                    @endif
                                    <span>{{ data_get($row, 'name', '-') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'email', '-') }}</td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'phone', '-') }}</td>
                            <td class="px-4 py-3 align-middle">{{ data_get($row, 'address', '-') }}</td>
                            <td class="px-4 py-3 align-middle">
                                <span class="badge badge-secondary">{{ $roleLabels[$role] ?? $role ?? '-' }}</span>
                            </td>
                            {{--
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $verified ? 'bg-emerald-500/10 text-emerald-700' : 'bg-muted text-muted-foreground' }}">
                                    <i data-lucide="{{ $verified ? 'check-circle-2' : 'x-circle' }}" class="h-3.5 w-3.5"></i>
                                    {{ $verified ? 'Terverifikasi' : 'Belum' }}
                                </span>
                            </td>
                            --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button" class="btn btn-outline px-2 py-1 text-xs"
                                        data-user-detail-trigger data-user-id="{{ data_get($row, 'id') }}">
                                        <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                                        Detail
                                    </button>
                                    <a href="{{ route('users.edit', data_get($row, 'id')) }}" class="btn btn-outline px-2 py-1 text-xs">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        Edit
                                    </a>
                                    <form method="post" action="{{ route('users.destroy', data_get($row, 'id')) }}" data-confirm="Hapus user ini?">
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
                            <td colspan="6" class="h-32 px-4 text-center text-muted-foreground">Belum ada pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('partials.pagination', ['meta' => $meta])
</div>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4" id="user-detail-modal" aria-hidden="true">
    <section class="w-full max-w-2xl rounded-xl border border-border bg-card p-5 text-card-foreground shadow-xl" role="dialog" aria-modal="true" aria-labelledby="user-detail-title">
        <div class="flex items-start justify-between gap-3">
            <h2 class="text-lg font-semibold" id="user-detail-title">Detail Pengguna</h2>
            <button type="button" class="btn btn-ghost h-8 w-8 px-0" id="user-detail-close">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>
        <div class="mt-4 max-h-[70vh] space-y-4 overflow-y-auto" id="user-detail-body">
            <div class="text-sm text-muted-foreground" id="user-detail-loading">Memuat...</div>
            <div class="hidden space-y-4" id="user-detail-content"></div>
        </div>
    </section>
</div>
@endsection
