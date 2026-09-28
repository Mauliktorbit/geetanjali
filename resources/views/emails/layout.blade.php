<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('brand.name'))</title>
</head>
<body style="margin:0;padding:0;background:#FAF8F2;font-family:Georgia,'Times New Roman',serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F2;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #eadfca;">
                    <tr>
                        <td style="background:#064E3B;padding:22px 28px;text-align:center;">
                            <p style="margin:0;color:#C9A24D;letter-spacing:0.18em;font-size:12px;text-transform:uppercase;">{{ config('brand.name') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 28px 12px;color:#16352d;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px 28px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#888;line-height:1.55;">
                            Need help? Email
                            <a href="mailto:{{ config('brand.contact.email') }}" style="color:#064E3B;">{{ config('brand.contact.email') }}</a>
                            or call {{ config('brand.contact.phone') }}.<br>
                            &copy; {{ date('Y') }} {{ config('brand.name') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
