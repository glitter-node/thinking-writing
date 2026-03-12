<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign in to Glitter Thought Write</title>
    </head>
    <body style="margin:0;padding:0;background-color:#09090b;color:#ffffff;font-family:Arial,sans-serif;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#09090b;padding:32px 16px;">
            <tr>
                <td align="center">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background-color:#18181b;border:1px solid #27272a;border-radius:16px;overflow:hidden;">
                        <tr>
                            <td style="padding:32px;">
                                <p style="margin:0 0 12px;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#fdba74;">Glitter Thought Write</p>
                                <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;color:#ffffff;">Your magic sign-in link is ready</h1>
                                <p style="margin:0 0 12px;font-size:16px;line-height:1.6;color:#e4e4e7;">
                                    Use the secure link below to sign in. This link expires in 10 minutes.
                                </p>
                                <p style="margin:0 0 24px;font-size:16px;font-weight:700;color:#ffffff;">{{ $email }}</p>
                                <p style="margin:0 0 32px;">
                                    <a href="{{ $magicUrl }}" style="display:inline-block;background-color:#fb923c;color:#09090b;text-decoration:none;font-weight:700;padding:14px 24px;border-radius:10px;">Sign in to Glitter Thought Write</a>
                                </p>
                                <p style="margin:0;font-size:14px;line-height:1.6;color:#a1a1aa;">
                                    If the button does not work, paste this link into your browser:
                                </p>
                                <p style="margin:12px 0 0;font-size:14px;line-height:1.6;word-break:break-all;">
                                    <a href="{{ $magicUrl }}" style="color:#fdba74;text-decoration:none;">{{ $magicUrl }}</a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:20px 32px;background-color:#09090b;border-top:1px solid #27272a;font-size:13px;color:#a1a1aa;">
                                © {{ date('Y') }} Glitter Thought Write
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
