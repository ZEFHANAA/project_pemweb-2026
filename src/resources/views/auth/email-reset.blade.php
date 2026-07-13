<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f4f4f5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f5; padding: 48px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; border: 1px solid #e4e4e7;">

                    <!-- Header -->
                    <tr>
                        <td style="padding: 32px 36px 0;">
                            <p style="margin: 0; font-size: 18px; font-weight: 700; color: #18181b;">Peta Wisata Indonesia</p>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 16px 36px 0;">
                            <div style="height: 1px; background-color: #e4e4e7;"></div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 24px 36px 0;">
                            <p style="margin: 0; font-size: 14px; color: #3f3f46; line-height: 1.6;">Halo {{ $user->name }},</p>
                            <p style="margin: 16px 0 0; font-size: 14px; color: #3f3f46; line-height: 1.6;">
                                Seseorang meminta reset password untuk akun yang terhubung dengan alamat e-mail ini. Jika ini memang Anda, klik tombol di bawah:
                            </p>
                        </td>
                    </tr>

                    <!-- Button -->
                    <tr>
                        <td style="padding: 24px 36px 0;">
                            <a href="{{ $action_link }}" target="_blank" style="display: inline-block; padding: 12px 28px; background-color: #0c2d3a; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 6px;">Reset Password</a>
                        </td>
                    </tr>

                    <!-- Expiry -->
                    <tr>
                        <td style="padding: 20px 36px 0;">
                            <p style="margin: 0; font-size: 13px; color: #71717a; line-height: 1.6;">
                                Link ini berlaku selama 60 menit. Setelah itu, Anda perlu meminta link baru.
                            </p>
                        </td>
                    </tr>

                    <!-- Safety note -->
                    <tr>
                        <td style="padding: 16px 36px 0;">
                            <p style="margin: 0; font-size: 13px; color: #71717a; line-height: 1.6;">
                                Jika Anda tidak meminta reset password, abaikan e-mail ini.
                            </p>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 28px 36px 0;">
                            <div style="height: 1px; background-color: #e4e4e7;"></div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 16px 36px 24px;">
                            <p style="margin: 0; font-size: 12px; color: #a1a1aa;">&copy; {{ date('Y') }} Peta Wisata Indonesia</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
