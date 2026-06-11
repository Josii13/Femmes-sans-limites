<!DOCTYPE html>
<html lang="fr" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>{{ $campaign->subject }}</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#F4F3F6;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

{{-- Preheader (caché, affiché dans l'aperçu Gmail) --}}
<span style="display:none;font-size:1px;color:#F4F3F6;max-height:0;max-width:0;opacity:0;overflow:hidden;">
    {{ mb_substr(strip_tags($resolvedBody), 0, 140) }}&nbsp;‌&nbsp;‌&nbsp;‌
</span>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       style="background-color:#F4F3F6;margin:0;padding:0;">
<tr>
<td align="center" style="padding:32px 16px;">

    {{-- Carte email --}}
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
           style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.07);">

        {{-- ── HEADER ── --}}
        <tr>
            <td style="background-color:#1A0A10;padding:28px 40px;text-align:center;">
                <img src="{{ asset('logo-email.png') }}"
                     alt="Femme Sans Limites"
                     width="52" height="52"
                     style="display:inline-block;width:52px;height:52px;border-radius:12px;background-color:rgba(253,240,245,0.92);">
                <p style="margin:12px 0 0;font-family:Georgia,'Times New Roman',serif;font-size:20px;font-weight:700;color:#D91E6E;letter-spacing:0.02em;">
                    Femme Sans Limites
                </p>
            </td>
        </tr>

        {{-- ── CORPS ── --}}
        <tr>
            <td style="padding:40px 40px 32px;">

                {{-- Texte principal --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.8;color:#3D3450;white-space:pre-line;">{{ $resolvedBody }}</td>
                    </tr>
                </table>

                {{-- Image optionnelle --}}
                @if(in_array($campaign->type, ['text_image','text_image_cta']) && $campaign->image)
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0 0;">
                    <tr>
                        <td>
                            <img src="{{ url('storage/'.$campaign->image) }}"
                                 alt=""
                                 width="520"
                                 style="display:block;width:100%;max-width:520px;height:auto;border-radius:10px;">
                        </td>
                    </tr>
                </table>
                @endif

                {{-- Bouton CTA optionnel --}}
                @if(in_array($campaign->type, ['text_cta','text_image_cta']) && $campaign->cta_url)
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:32px auto 0;">
                    <tr>
                        <td align="center" style="background-color:#D91E6E;border-radius:50px;padding:0;">
                            <a href="{{ $campaign->cta_url }}"
                               target="_blank"
                               style="display:inline-block;padding:14px 38px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;letter-spacing:0.03em;border-radius:50px;">
                                {{ $campaign->cta_label ?: 'En savoir plus' }}
                            </a>
                        </td>
                    </tr>
                </table>
                @endif

            </td>
        </tr>

        {{-- ── SÉPARATEUR ── --}}
        <tr>
            <td style="padding:0 40px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="border-top:1px solid #EEEBF0;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ── FOOTER ── --}}
        <tr>
            <td style="background-color:#F9F8FB;padding:28px 40px;text-align:center;">
                <p style="margin:0 0 6px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:12px;color:#9CA3AF;line-height:1.6;">
                    Tu reçois cet email car tu fais partie de la communauté <strong style="color:#6B7280;">Femme Sans Limites</strong>.
                </p>
                @if(! empty($unsubscribeUrl))
                <p style="margin:0 0 6px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:12px;color:#9CA3AF;line-height:1.6;">
                    <a href="{{ $unsubscribeUrl }}" style="color:#9CA3AF;text-decoration:underline;">Se désinscrire de ces communications</a>
                </p>
                @endif
                <p style="margin:0;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:12px;color:#C4BEC8;">
                    © {{ date('Y') }} Femme Sans Limites — Abidjan, Côte d'Ivoire
                </p>
            </td>
        </tr>

    </table>
    {{-- /carte --}}

</td>
</tr>
</table>

{{-- Pixel de tracking 1×1 --}}
<img src="{{ url('/t/'.$recipient->token.'.gif') }}"
     width="1" height="1" alt=""
     style="display:block;width:1px;height:1px;border:0;opacity:0;max-height:1px;overflow:hidden;">

</body>
</html>
