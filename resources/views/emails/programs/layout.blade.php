<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background-color:#EFF6FF;font-family:'Segoe UI',Arial,sans-serif;color:#14183E;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#EFF6FF;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:#14183E;padding:20px 32px;">
                            <span style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:0.5px;">SkillUp</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;border-top:1px solid #E2E8F0;color:#64748B;font-size:12px;line-height:18px;">
                            You are receiving this because you registered for a SkillUp programme.
                            <br>© {{ now()->year }} SkillUp Edtech. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
