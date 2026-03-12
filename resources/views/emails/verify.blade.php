<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Glitter Thought Write 이메일 인증</title>
    </head>
    <body style="margin:0;padding:0;background-color:#09090b;color:#ffffff;font-family:Arial,sans-serif;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#09090b;padding:32px 16px;">
            <tr>
                <td align="center">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background-color:#18181b;border:1px solid #27272a;border-radius:16px;overflow:hidden;">
                        <tr>
                            <td style="padding:32px;">
                                <p style="margin:0 0 12px;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#fdba74;">Glitter Thought Write</p>
                                <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;color:#ffffff;">이메일 주소를 인증해 주세요</h1>
                                <p style="margin:0 0 12px;font-size:16px;line-height:1.6;color:#e4e4e7;">
                                    아래 이메일 주소에 대한 인증을 완료하면 Glitter Thought Write를 계속 사용할 수 있습니다.
                                </p>
                                <p style="margin:0 0 24px;font-size:16px;font-weight:700;color:#ffffff;">{{ $email }}</p>
                                <p style="margin:0 0 32px;">
                                    <a href="{{ $verifyUrl }}" style="display:inline-block;background-color:#fb923c;color:#09090b;text-decoration:none;font-weight:700;padding:14px 24px;border-radius:10px;">이메일 인증하기</a>
                                </p>
                                <p style="margin:0;font-size:14px;line-height:1.6;color:#a1a1aa;">
                                    버튼이 동작하지 않으면 아래 링크를 브라우저에 붙여 넣으세요.
                                </p>
                                <p style="margin:12px 0 0;font-size:14px;line-height:1.6;word-break:break-all;">
                                    <a href="{{ $verifyUrl }}" style="color:#fdba74;text-decoration:none;">{{ $verifyUrl }}</a>
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
