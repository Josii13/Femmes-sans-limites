<!DOCTYPE html>
<html lang="fr" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Votre carte de membre — Femme Sans Limites</title>
</head>
<body style="margin:0;padding:0;background-color:#F4F3F6;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

{{-- Preheader --}}
<span style="display:none;font-size:1px;color:#F4F3F6;max-height:0;max-width:0;opacity:0;overflow:hidden;">
    Bienvenue {{ $member->name }} ! Votre carte de membre officielle FSL est prête.&nbsp;‌&nbsp;‌&nbsp;‌
</span>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       style="background-color:#F4F3F6;margin:0;padding:0;">
<tr>
<td align="center" style="padding:32px 16px;">

    <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
           style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

        {{-- Header --}}
        <tr>
            <td style="background-color:#1A0A10;padding:32px 40px;text-align:center;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('logo_FSL.png'))) }}"
                     alt="FSL" width="52" height="52"
                     style="display:inline-block;width:52px;height:52px;border-radius:12px;background-color:rgba(253,240,245,0.92);">
                <p style="margin:12px 0 4px;font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:700;color:#D91E6E;">
                    Femme Sans Limites
                </p>
                <p style="margin:0;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;color:rgba(255,255,255,0.55);">
                    Votre carte de membre officielle
                </p>
            </td>
        </tr>

        {{-- Corps --}}
        <tr>
            <td style="padding:40px 40px 32px;">

                <p style="margin:0 0 20px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;color:#1A0A10;line-height:1.5;">
                    Bonjour <strong>{{ $member->name }}</strong>,
                </p>

                <p style="margin:0 0 28px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;color:#6B7280;line-height:1.75;">
                    Bienvenue dans la communauté <strong style="color:#1A0A10;">Femme Sans Limites</strong> !<br>
                    Votre <strong style="color:#D91E6E;">carte de membre officielle</strong> est en pièce jointe.
                    Présentez-la lors de nos événements pour bénéficier de tous vos avantages.
                </p>

                {{-- Bloc numéro membre --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="background-color:#FDF0F5;border-radius:12px;margin-bottom:28px;">
                    <tr>
                        <td style="padding:20px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 4px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:11px;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.08em;">
                                            Numéro de membre
                                        </p>
                                        <p style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:20px;font-weight:700;color:#D91E6E;">
                                            {{ $member->member_number }}
                                        </p>
                                    </td>
                                    <td align="right" style="vertical-align:middle;">
                                        @if($member->type === 'premium')
                                        <span style="display:inline-block;background-color:#D91E6E;color:#ffffff;padding:5px 16px;border-radius:20px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.06em;">PREMIUM</span>
                                        @elseif($member->type === 'gold')
                                        <span style="display:inline-block;background-color:#C9A84C;color:#ffffff;padding:5px 16px;border-radius:20px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.06em;">GOLD</span>
                                        @else
                                        <span style="display:inline-block;background-color:#6B7280;color:#ffffff;padding:5px 16px;border-radius:20px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.06em;">STANDARD</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- CTA --}}
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 32px;">
                    <tr>
                        <td align="center" style="background-color:#D91E6E;border-radius:50px;padding:0;">
                            <a href="{{ url('/evenements') }}"
                               target="_blank"
                               style="display:inline-block;padding:14px 32px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:50px;">
                                Voir nos événements →
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="margin:0;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;color:#9CA3AF;line-height:1.6;">
                    Des questions ? Répondez simplement à cet email, nous serons ravis de vous aider.
                </p>
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td style="background-color:#F9F8FB;padding:24px 40px;border-top:1px solid #EEEBF0;text-align:center;">
                <p style="margin:0;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:12px;color:#C4BEC8;">
                    © {{ date('Y') }} Femme Sans Limites — Tous droits réservés
                </p>
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>
