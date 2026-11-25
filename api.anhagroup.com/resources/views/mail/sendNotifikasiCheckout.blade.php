<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Checkout - ANHA ELEKTRIK</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f6f9; padding:30px 0;">
    <tr>
        <td align="center">

            <!-- Container -->
            <table width="650" cellpadding="0" cellspacing="0" border="0"
                   style="background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 5px 20px rgba(0,0,0,0.08);">

                <!-- Header -->
                <tr>
                    <td align="center"
                        style="background:linear-gradient(135deg,#4f46e5,#7c3aed); padding:35px;">
                        <h1 style="margin:0; color:#ffffff; font-size:30px;">
                            Notifikasi Checkout
                        </h1>

                        <p style="margin-top:10px; color:#e0e7ff; font-size:15px;">
                            Pesanan baru berhasil dibuat
                        </p>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:35px;">

                        <h2 style="margin-top:0; color:#111827;">
                            Detail Pelanggan
                        </h2>

                        <!-- Customer Info -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">

                            <tr style="background:#f9fafb;">
                                <td style="padding:14px; font-weight:bold; width:35%;">
                                    Nama
                                </td>
                                <td style="padding:14px;">
                                    {{ $user['name'] }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:14px; font-weight:bold;">
                                    Email
                                </td>
                                <td style="padding:14px;">
                                    {{ $user['email'] }}
                                </td>
                            </tr>

                            <tr style="background:#f9fafb;">
                                <td style="padding:14px; font-weight:bold;">
                                    Invoice
                                </td>
                                <td style="padding:14px;">
                                    {{ $order['invoice'] }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:14px; font-weight:bold;">
                                    Total
                                </td>
                                <td style="padding:14px; color:#16a34a; font-weight:bold;">
                                    Rp {{ number_format($order['total'], 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr style="background:#f9fafb;">
                                <td style="padding:14px; font-weight:bold;">
                                    Status
                                </td>
                                <td style="padding:14px;">
                                    <span style="
                                        background:#facc15;
                                        color:#78350f;
                                        padding:6px 14px;
                                        border-radius:20px;
                                        font-size:12px;
                                        font-weight:bold;
                                        text-transform:uppercase;
                                    ">
                                        {{ $order['status'] }}
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:14px; font-weight:bold;">
                                    Alamat Pengiriman
                                </td>
                                <td style="padding:14px; line-height:1.7;">
                                    {{ $order['alamat_pengiriman'] }}
                                </td>
                            </tr>

                        </table>

                        <!-- Product Section -->
                        <h2 style="margin-top:40px; color:#111827;">
                            Detail Barang
                        </h2>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="border-collapse:collapse;">

                            <thead>
                            <tr style="background:#4f46e5; color:#ffffff;">
                                <th align="left" style="padding:14px;">Barang</th>
                                <th align="center" style="padding:14px;">Qty</th>
                                <th align="right" style="padding:14px;">Subtotal</th>
                            </tr>
                            </thead>

                            <tbody>

                            @foreach ($keranjang as $item)

                                <tr style="border-bottom:1px solid #e5e7eb;">
                                    <td style="padding:18px 14px;">

                                        <div style="font-weight:bold; color:#111827;">
                                            {{ $item->barang->nama_barang }}
                                        </div>

                                        <div style="font-size:13px; color:#6b7280; margin-top:6px;">
                                            {{ $item->barang->deskripsi }}
                                        </div>

                                        <div style="margin-top:8px;">
                                            <span style="
                                                display:inline-block;
                                                background:#eef2ff;
                                                color:#4338ca;
                                                padding:4px 10px;
                                                border-radius:6px;
                                                font-size:12px;
                                            ">
                                                Satuan: {{ $item->barang->satuan }}
                                            </span>

                                            <span style="
                                                display:inline-block;
                                                background:#fef3c7;
                                                color:#92400e;
                                                padding:4px 10px;
                                                border-radius:6px;
                                                font-size:12px;
                                                margin-left:6px;
                                            ">
                                                Rating: ⭐ {{ $item->barang->rating }}
                                            </span>
                                        </div>

                                    </td>

                                    <td align="center" style="padding:18px 14px;">
                                        {{ $item->qty }}
                                    </td>

                                    <td align="right"
                                        style="padding:18px 14px; font-weight:bold; color:#16a34a;">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>

                            @endforeach

                            </tbody>
                        </table>

                        <!-- Footer Note -->
                        <div style="
                            margin-top:35px;
                            background:#f9fafb;
                            padding:18px;
                            border-radius:10px;
                            color:#6b7280;
                            font-size:14px;
                            line-height:1.8;
                        ">
                            Terima kasih telah melakukan checkout.
                            Pesanan Anda akan segera diproses oleh tim kami.
                        </div>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center"
                        style="background:#111827; padding:22px; color:#d1d5db; font-size:13px;">
                        © <?= date('Y') ?> ANHA ELEKTRIK — All Rights Reserved
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>