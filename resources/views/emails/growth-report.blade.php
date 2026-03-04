<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
</head>
<body style="margin:0; padding:0; background-color:#121212; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#121212;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px;">
                    {{-- Intro copy: confident, on-brand --}}
                    <tr>
                        <td style="padding-bottom: 28px;">
                            <h1 style="margin:0 0 16px 0; font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.25; color: #ffffff;">
                                {{ $intro_heading }}
                            </h1>
                            <p style="margin:0; font-size: 16px; line-height: 1.6; color: rgba(255,255,255,0.88);">
                                {{ $intro_body }}
                            </p>
                        </td>
                    </tr>
                    {{-- Accent line (teal glow) --}}
                    <tr>
                        <td style="padding-bottom: 24px;">
                            <div style="height: 2px; background: linear-gradient(90deg, #25F4EE 0%, #FE2C55 100%); border-radius: 1px;"></div>
                        </td>
                    </tr>
                    {{-- Report content --}}
                    <tr>
                        <td style="color: rgba(255,255,255,0.92);">
                            {!! $reportHtml !!}
                        </td>
                    </tr>
                    {{-- Footer --}}
                    <tr>
                        <td style="padding-top: 32px; border-top: 1px solid rgba(255,255,255,0.12); font-size: 13px; color: rgba(255,255,255,0.5);">
                            GoViral — Engineered for Viral Growth.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
