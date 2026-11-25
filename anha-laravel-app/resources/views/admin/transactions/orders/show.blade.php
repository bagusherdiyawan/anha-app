@extends('layouts.dashboard')

@section('content')
@php
    $formatRupiah = fn ($value) => 'Rp '.number_format((float) ($value ?? 0), 0, ',', '.');
    $items = data_get($order, 'order_items', data_get($order, 'items', data_get($order, 'details', [])));
    $lat = data_get($order, 'alamat_pengiriman.latitude');
    $lng = data_get($order, 'alamat_pengiriman.longitude');
    $mapEmbedUrl = ($lat !== null && $lng !== null)
        ? sprintf(
            'https://www.openstreetmap.org/export/embed.html?bbox=%F,%F,%F,%F&marker=%F,%F',
            $lng - 0.003, $lat - 0.003, $lng + 0.003, $lat + 0.003, $lat, $lng
        )
        : null;
@endphp
<div class="space-y-5 p-4 md:p-6">
    <a href="{{ route('orders.index') }}" class="btn btn-ghost">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Kembali ke pesanan
    </a>
    @include('partials.alerts')

    <section class="card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="flex items-center gap-2 text-xl font-semibold">
                    <i data-lucide="shopping-bag" class="h-5 w-5 text-primary"></i>
                    Pesanan #{{ data_get($order, 'invoice', data_get($order, 'order_number', data_get($order, 'id'))) }}
                </h2>
                <p class="text-sm text-muted-foreground">{{ data_get($order, 'created_at', '-') }}</p>
                <p class="mt-2 text-sm">{{ data_get($order, 'user.name', data_get($order, 'customer_name', '-')) }}</p>
            </div>
            <form
                method="post"
                action="{{ route('orders.status', data_get($order, 'id')) }}"
                class="flex gap-2"
                data-confirm="Simpan perubahan status pesanan ini?"
                data-confirm-default="Simpan perubahan status pesanan ini?"
                data-confirm-cancelled="Pesanan akan dibatalkan. Jika dana dari pembeli sudah masuk/ditransfer, segera hubungi pembeli untuk proses pengembalian dana (refund) sebelum melanjutkan. Lanjutkan pembatalan?"
                data-confirm-title-default="Konfirmasi tindakan"
                data-confirm-title-cancelled="Pembatalan pesanan"
                onsubmit="return true"
            >
                @csrf
                @method('PATCH')
                <select
                    name="status"
                    class="form-input"
                    onchange="
                        const form = this.form;
                        if (this.value === 'cancelled') {
                            form.setAttribute('data-confirm', form.getAttribute('data-confirm-cancelled'));
                            form.setAttribute('data-confirm-title', form.getAttribute('data-confirm-title-cancelled'));
                        } else {
                            form.setAttribute('data-confirm', form.getAttribute('data-confirm-default'));
                            form.setAttribute('data-confirm-title', form.getAttribute('data-confirm-title-default'));
                        }
                    "
                >
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(data_get($order, 'status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="submit">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Simpan
                </button>
            </form>
        </div>
    </section>

    <section class="card p-6">
        <div>
            <h3 class="flex items-center gap-2 font-semibold">
                <i data-lucide="map" class="h-4 w-4 text-primary"></i>
                Pengiriman
            </h3>
        </div>
        <div>
            <p class="mt-2 text-sm">
                Alamat: ({{ data_get($order, 'alamat_pengiriman.label') }}) 
                {{ data_get($order, 'alamat_pengiriman.address') }},
                {{ data_get($order, 'alamat_pengiriman.district') }},
                {{ data_get($order, 'alamat_pengiriman.city') }},
                {{ data_get($order, 'alamat_pengiriman.province') }}
                {{ data_get($order, 'alamat_pengiriman.postal_code') }}
            </p>
            <p class="text-sm">
                Pembayaran: {{ data_get($order, 'metode_pembayaran.nama') }} {{ data_get($order, 'metode_pembayaran.nomor') ?? '' }}
            </p>
            <p class="text-sm text-muted-foreground">
                Catatan: {{ data_get($order, 'alamat_pengiriman.catetan') ?? '-' }}
            </p>
            @if ($mapEmbedUrl)
                <div class="mt-3 overflow-hidden rounded-lg border border-border">
                    <iframe
                        src="{{ $mapEmbedUrl }}"
                        width="100%"
                        height="280"
                        style="border:0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
                <a
                    href="https://www.openstreetmap.org/?mlat={{ $lat }}&mlon={{ $lng }}#map=17/{{ $lat }}/{{ $lng }}"
                    target="_blank" rel="noopener"
                    class="mt-2 inline-flex items-center gap-1 text-sm text-primary hover:underline"
                >
                    <i data-lucide="external-link" class="h-3.5 w-3.5"></i>
                    Buka di peta penuh
                </a>
            @else
                <p class="mt-3 text-sm text-muted-foreground italic">Titik lokasi tidak tersedia untuk alamat ini.</p>
            @endif
        </div>
    </section>

    <section class="card overflow-hidden">
        <div class="border-b border-border p-4">
            <h3 class="flex items-center gap-2 font-semibold">
                <i data-lucide="package" class="h-4 w-4 text-primary"></i>
                Item Pesanan
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left">Qty</th>
                        <th class="px-4 py-3 text-left">Harga</th>
                        <th class="px-4 py-3 text-left">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr class="border-t border-border">
                            <td class="px-4 py-3">{{ data_get($item, 'barang.nama_barang', data_get($item, 'product.nama_barang', data_get($item, 'nama_barang', '-'))) }}</td>
                            <td class="px-4 py-3">{{ data_get($item, 'qty', data_get($item, 'quantity', 0)) }}</td>
                            <td class="px-4 py-3">{{ $formatRupiah(data_get($item, 'price', data_get($item, 'harga'))) }}</td>
                            <td class="px-4 py-3">{{ $formatRupiah(data_get($item, 'subtotal')) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="h-24 px-4 text-center text-muted-foreground">Tidak ada item.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-border p-4 text-right">
            @if (data_get($order, 'voucher.code'))
                <p class="text-sm text-muted-foreground">
                    Voucher: <span class="font-medium text-foreground">{{ data_get($order, 'voucher.code') }}</span>
                    &middot; Diskon: -{{ $formatRupiah(data_get($order, 'discount_amount')) }}
                </p>
            @endif
            @if ((int) data_get($order, 'shipping_cost', 0) > 0)
                <p class="text-sm text-muted-foreground">
                    Ongkos Kirim: {{ $formatRupiah(data_get($order, 'shipping_cost')) }}
                </p>
            @endif
            <p class="text-sm text-muted-foreground">Total</p>
            <p class="text-2xl font-bold">{{ $formatRupiah(data_get($order, 'total')) }}</p>
        </div>
    </section>
</div>
@endsection
