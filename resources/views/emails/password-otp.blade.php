<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password reset OTP</title>
</head>
<body style="margin:0;padding:0;background:#FAF8F2;font-family:Georgia,'Times New Roman',serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F2;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #eadfca;">
                    <tr>
                        <td style="background:#064E3B;padding:22px 28px;text-align:center;">
                            <p style="margin:0;color:#C9A24D;letter-spacing:0.18em;font-size:12px;text-transform:uppercase;">Geetanjali Jewellers</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 28px 12px;color:#16352d;">
                            <h1 style="margin:0 0 12px;font-size:24px;font-weight:600;">Password reset OTP</h1>
                            <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#444;font-family:Arial,Helvetica,sans-serif;">
                                Hello {{ $customerName }}, use this one-time code to reset your account password. It expires in {{ \App\Services\PasswordOtpService::TTL_MINUTES }} minutes.
                            </p>
                            <p style="margin:0 0 8px;text-align:center;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#C9A24D;font-family:Arial,Helvetica,sans-serif;">Your OTP</p>
                            <p style="margin:0 0 22px;text-align:center;font-size:36px;letter-spacing:0.28em;font-weight:700;color:#064E3B;">{{ $otp }}</p>
                            <p style="margin:0;font-size:13px;line-height:1.55;color:#666;font-family:Arial,Helvetica,sans-serif;">
                                If you did not request a password reset, you can ignore this email. Do not share this code with anyone.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px 28px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#888;">
                            &copy; {{ date('Y') }} Geetanjali Jewellers
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
