<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Lupa Password - ANHA ELEKTRIK</title>
</head>
<body style="
    margin:0;
    padding:0;
    background:#f4f6f9;
    font-family:Arial, Helvetica, sans-serif;
">

<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="padding:40px 0; background:#f4f6f9;">

    <tr>
        <td align="center">

            <!-- Container -->
            <table width="500" cellpadding="0" cellspacing="0" border="0"
                   style="
                        background:#ffffff;
                        border-radius:16px;
                        overflow:hidden;
                        box-shadow:0 8px 24px rgba(0,0,0,0.08);
                   ">

                <!-- Header -->
                <tr>
                    <td align="center"
                        style="
                            background:linear-gradient(135deg,#2563eb,#4f46e5);
                            padding:35px;
                        ">

                        <h1 style="
                            color:#ffffff;
                            margin:0;
                            font-size:30px;
                            letter-spacing:1px;
                        ">
                            ANHA ELEKTRIK
                        </h1>

                        <p style="
                            color:#dbeafe;
                            margin-top:10px;
                            font-size:15px;
                        ">
                            Verifikasi Kode OTP Lupa Password
                        </p>

                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:40px;">

                        <p style="
                            color:#4b5563;
                            font-size:15px;
                            line-height:1.8;
                        ">
                            Kami menerima permintaan lupa password untuk akun Anda dengan email:
                        </p>

                        <!-- Email Box -->
                        <div style="
                            background:#f9fafb;
                            border:1px solid #e5e7eb;
                            padding:14px;
                            border-radius:10px;
                            margin-top:18px;
                            text-align:center;
                            color:#111827;
                            font-weight:bold;
                            font-size:15px;
                        ">
                            {{ $email }}
                        </div>

                        <p style="
                            margin-top:30px;
                            color:#4b5563;
                            font-size:15px;
                            line-height:1.8;
                            text-align:center;
                        ">
                            Gunakan kode OTP berikut untuk melanjutkan proses lupa password:
                        </p>

                        <!-- OTP -->
                        <div style="
                            margin-top:25px;
                            text-align:center;
                        ">

                            <div style="
                                display:inline-block;
                                background:linear-gradient(135deg,#4f46e5,#2563eb);
                                color:#ffffff;
                                padding:18px 40px;
                                border-radius:14px;
                                font-size:36px;
                                font-weight:bold;
                                letter-spacing:8px;
                                box-shadow:0 4px 14px rgba(79,70,229,0.3);
                            ">
                                {{ $token }}
                            </div>

                        </div>

                        <!-- Warning -->
                        <div style="
                            margin-top:35px;
                            background:#fef3c7;
                            border-left:5px solid #f59e0b;
                            padding:16px;
                            border-radius:10px;
                            color:#92400e;
                            font-size:14px;
                            line-height:1.7;
                        ">
                            Demi keamanan akun Anda, jangan bagikan kode OTP ini kepada siapa pun.
                        </div>

                        <!-- Footer Text -->
                        <p style="
                            margin-top:30px;
                            color:#6b7280;
                            font-size:14px;
                            line-height:1.8;
                            text-align:center;
                        ">
                            Jika Anda tidak merasa melakukan permintaan ini,
                            silakan abaikan email ini.
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center"
                        style="
                            background:#111827;
                            padding:22px;
                            color:#d1d5db;
                            font-size:13px;
                        ">
                        © <?= date('Y') ?> ANHA ELEKTRIK — All Rights Reserved
                    </td>
                </tr>

            </table>

        </td>
    </tr>

</table>

</body>
</html>