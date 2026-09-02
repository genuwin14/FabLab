{{--
    Shared shell for every FabLab email: navy masthead with the logo, gold
    accent bar, white body card, grey footer. Email clients ignore most of a
    <style> block, so everything that matters is inline and the layout is
    tables — flexbox and grid don't survive Gmail.

    The logo rides along as an inline attachment rather than a hosted URL, so
    it renders even while the app runs on a machine the mail client can't
    reach. $message only exists when a real mailer renders the view — tests
    and previews render without it, hence the guard.
--}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0;padding:0;background-color:#f2f4f7;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f2f4f7;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                    style="width:600px;max-width:100%;background-color:#ffffff;border:1px solid #e3e7ec;border-radius:12px;overflow:hidden;font-family:Arial,Helvetica,sans-serif;">

                    {{-- Masthead --}}
                    <tr>
                        <td style="background-color:#0e2e45;padding:20px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    @if (!empty($message))
                                        <td style="padding-right:14px;vertical-align:middle;">
                                            <img src="{{ $message->embed(public_path('img/FABLAB-LOGO.png')) }}"
                                                alt="CSPC FabLab" width="48" style="display:block;">
                                        </td>
                                    @endif
                                    <td style="vertical-align:middle;">
                                        <div style="color:#ffffff;font-size:20px;font-weight:bold;line-height:1.2;">
                                            CSPC FabLab</div>
                                        <div style="color:#ffc508;font-size:12px;letter-spacing:1px;">
                                            CAMARINES SUR POLYTECHNIC COLLEGES</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffc508;height:4px;line-height:4px;font-size:0;">&nbsp;</td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px;color:#333333;font-size:14px;line-height:1.6;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center"
                            style="background-color:#f7f9fb;border-top:1px solid #e3e7ec;padding:18px 32px;color:#6c757d;font-size:12px;">
                            All Rights Reserved &copy; {{ date('Y') }} CSPC FabLab
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
