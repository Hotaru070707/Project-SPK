<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Password</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #334155;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f5; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #16a34a; padding: 28px 32px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.5px;">Mobeli</h1>
                            <p style="color: #dcfce7; margin: 4px 0 0; font-size: 13px;">Sistem Informasi & POS Toko Kelontong</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 32px 24px;">
                            <h2 style="font-size: 18px; color: #0f172a; margin: 0 0 12px; font-weight: 600;">Halo, {{ $userName }}!</h2>
                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 24px;">
                                Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda. Gunakan kode verifikasi (OTP) berikut untuk melanjutkan proses reset kata sandi:
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: #f0fdf4; border: 2px dashed #16a34a; border-radius: 12px; padding: 20px; text-align: center; margin: 0 0 24px;">
                                <span style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: #15803d; letter-spacing: 1px; display: block; margin-bottom: 8px;">KODE VERIFIKASI OTP</span>
                                <div style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 800; letter-spacing: 10px; color: #16a34a; padding-left: 10px;">
                                    {{ $otp }}
                                </div>
                                <span style="font-size: 12px; color: #64748b; display: block; margin-top: 8px;">Berlaku selama 15 menit</span>
                            </div>

                            <p style="font-size: 13px; line-height: 1.6; color: #64748b; margin: 0 0 16px;">
                                ⚠️ <strong>Keamanan:</strong> Jangan berikan kode OTP ini kepada siapa pun, termasuk pihak yang mengatasnamakan Mobeli.
                            </p>
                            <p style="font-size: 13px; line-height: 1.6; color: #94a3b8; margin: 0;">
                                Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini. Akun Anda tetap aman.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 32px; text-align: center; border-top: 1px solid #f1f5f9;">
                            <p style="font-size: 12px; color: #94a3b8; margin: 0;">
                                &copy; {{ date('Y') }} Mobeli. Seluruh hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
