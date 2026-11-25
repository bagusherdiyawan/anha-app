@extends('layouts.dashboard')

@section('content')
@php
    $groups = collect($configs)->groupBy(fn ($config) => data_get($config, 'group', 'general'));
    $labels = [
        'contact' => 'Kontak',
        'loyalty' => 'Poin Loyalitas',
        'shipping' => 'Pengiriman',
        'general' => 'Umum',
    ];
    $groupIcons = [
        'contact' => 'phone',
        'loyalty' => 'star',
        'shipping' => 'truck',
        'general' => 'settings-2',
    ];
    $fieldLabels = [
        'whatsapp_number' => 'Nomor WhatsApp',
        'phone_number' => 'Nomor Telepon',
        'email_contact' => 'Email Kontak',
        'whatsapp_template' => 'Template Pesan WhatsApp',
        'points_rate_per_point' => 'Belanja per 1 Poin (Rp)',
        'points_value_in_rupiah' => 'Nilai 1 Poin (Rp)',
        'points_expiry_days' => 'Masa Berlaku Poin (Hari)',
        'shipping_enabled' => 'Aktifkan Ongkir',
        'shipping_flat_rate' => 'Tarif Ongkir Flat (Rp)',
        'order_cancel_minutes' => 'Batas Pembatalan Pesanan (Menit)',
        'minimum_purchase_amount' => 'Minimal Belanja (Rp)',
    ];
@endphp
<div class="space-y-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <i data-lucide="settings" class="h-6 w-6 text-primary"></i>
                Pengaturan
            </h2>
            <p class="text-sm text-muted-foreground">Kelola konfigurasi sistem dan admin dashboard.</p>
        </div>
        <form method="post" action="{{ route('settings.clear-cache') }}" data-confirm="Bersihkan cache pengaturan?">
            @csrf
            <button class="btn btn-outline" type="submit">
                <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                Bersihkan Cache
            </button>
        </form>
    </div>

    @include('partials.alerts')

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse ($groups as $group => $items)
            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-border bg-muted/20 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <i data-lucide="{{ $groupIcons[$group] ?? 'settings-2' }}" class="h-4 w-4"></i>
                        </span>
                        <div>
                        <h3 class="font-semibold">{{ $labels[$group] ?? \Illuminate\Support\Str::headline($group) }}</h3>
                        <p class="text-xs text-muted-foreground">Pengaturan {{ strtolower($labels[$group] ?? $group) }}</p>
                        </div>
                    </div>
                </div>
                <form method="post" action="{{ route('settings.update') }}" class="space-y-4 p-5">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="group" value="{{ $group }}">
                    @foreach ($items as $config)
                        @php
                            $key = data_get($config, 'key');
                            $type = data_get($config, 'type', 'string');
                            $value = data_get($config, 'value', '');
                        @endphp
                        @continue($key === 'low_stock_threshold')
                        <div class="space-y-1.5">
                            <label for="{{ $key }}" class="text-sm font-medium">{{ $fieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</label>
                            @if ($type === 'boolean')
                                <label class="flex items-center justify-between rounded-lg border border-border px-3 py-2">
                                    <span class="text-xs text-muted-foreground">{{ data_get($config, 'description') }}</span>
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <input id="{{ $key }}" name="{{ $key }}" type="checkbox" value="1" class="h-4 w-4" @checked(in_array($value, [true, 1, '1', 'true'], true))>
                                </label>
                            @elseif ($key === 'whatsapp_template')
                                <textarea id="{{ $key }}" name="{{ $key }}" rows="3" class="form-textarea">{{ $value }}</textarea>
                            @else
                                <input id="{{ $key }}" name="{{ $key }}" type="{{ $type === 'integer' ? 'number' : 'text' }}" class="form-input" value="{{ $value }}">
                            @endif
                            @if (data_get($config, 'description') && $type !== 'boolean')
                                <p class="text-xs text-muted-foreground">{{ data_get($config, 'description') }}</p>
                            @endif
                        </div>
                    @endforeach
                    <button class="btn btn-primary" type="submit">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Simpan
                    </button>
                </form>
            </section>
        @empty
            <section class="card p-6 text-sm text-muted-foreground">Belum ada pengaturan dari API.</section>
        @endforelse
    </div>

    <section class="card overflow-hidden">
        <div class="border-b border-border p-5">
            <h3 class="flex items-center gap-2 font-semibold">
                <i data-lucide="shield-check" class="h-4 w-4 text-primary"></i>
                Admin
            </h3>
            <p class="text-sm text-muted-foreground">Tambah, aktifkan/nonaktifkan, atau hapus admin.</p>
        </div>
        <div class="grid gap-6 p-5 lg:grid-cols-[1fr_2fr]">
            <form method="post" action="{{ route('settings.admins.store') }}" class="space-y-3">
                @csrf
                <input name="name" class="form-input" placeholder="Nama admin">
                <input name="email" type="email" class="form-input" placeholder="Email">
                <input name="password" type="password" class="form-input" placeholder="Password">
                <button class="btn btn-primary w-full" type="submit">
                    <i data-lucide="user-plus" class="h-4 w-4"></i>
                    Tambah Admin
                </button>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($admins as $admin)
                            @php $active = in_array(data_get($admin, 'is_active', data_get($admin, 'status_active', data_get($admin, 'active', true))), [true, 1, '1', 'true'], true); @endphp
                            <tr class="border-t border-border">
                                <td class="px-4 py-3">{{ data_get($admin, 'name') }}</td>
                                <td class="px-4 py-3">{{ data_get($admin, 'email', '-') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $active ? 'bg-emerald-500/10 text-emerald-700' : 'bg-muted text-muted-foreground' }}">
                                        <i data-lucide="{{ $active ? 'check-circle-2' : 'x-circle' }}" class="h-3.5 w-3.5"></i>
                                        {{ $active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ((string) data_get($admin, 'id') !== (string) $currentUserId)
                                            <form method="post" action="{{ route('settings.admins.toggle', data_get($admin, 'id')) }}" data-confirm="Ubah status admin ini?">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-outline px-2 py-1 text-xs" type="submit">
                                                    <i data-lucide="power" class="h-3.5 w-3.5"></i>
                                                    Toggle
                                                </button>
                                            </form>
                                            <form method="post" action="{{ route('settings.admins.destroy', data_get($admin, 'id')) }}" data-confirm="Hapus admin ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger px-2 py-1 text-xs" type="submit">
                                                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs italic text-muted-foreground">Akun aktif</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="h-24 px-4 text-center text-muted-foreground">Belum ada admin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
