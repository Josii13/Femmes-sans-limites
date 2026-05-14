<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $campaign->subject }}</title>
<style>
  body { margin:0; padding:0; background:#F4F3F6; font-family:'Helvetica Neue',Arial,sans-serif; }
  .wrapper { max-width:600px; margin:0 auto; background:#ffffff; }
  .header { background:#1A0A2E; padding:24px 32px; text-align:center; }
  .header img { height:48px; width:auto; }
  .body { padding:40px 32px; }
  .body-text { font-size:15px; line-height:1.75; color:#3D3450; white-space:pre-line; }
  .campaign-image { width:100%; max-width:100%; border-radius:12px; margin:24px 0; display:block; }
  .cta-wrap { text-align:center; margin:32px 0; }
  .cta-btn { display:inline-block; background:#D91E6E; color:#ffffff !important; text-decoration:none;
             padding:14px 36px; border-radius:50px; font-size:15px; font-weight:700; letter-spacing:0.02em; }
  .divider { border:none; border-top:1px solid #EEEBF0; margin:32px 0; }
  .footer { background:#F4F3F6; padding:24px 32px; text-align:center; }
  .footer p { font-size:12px; color:#9CA3AF; margin:4px 0; }
  .footer a { color:#D91E6E; text-decoration:none; }
</style>
</head>
<body>
<div class="wrapper">

  {{-- Header --}}
  <div class="header">
    <img src="{{ asset('logo_FSL.png') }}" alt="Femmes Sans Limites">
  </div>

  {{-- Corps --}}
  <div class="body">
    <p class="body-text">{{ $campaign->body }}</p>

    @if(in_array($campaign->type, ['text_image','text_image_cta']) && $campaign->image)
    <img src="{{ asset('storage/'.$campaign->image) }}" alt="" class="campaign-image">
    @endif

    @if(in_array($campaign->type, ['text_cta','text_image_cta']) && $campaign->cta_url)
    <div class="cta-wrap">
      <a href="{{ $campaign->cta_url }}" class="cta-btn">{{ $campaign->cta_label ?: 'En savoir plus' }}</a>
    </div>
    @endif
  </div>

  <hr class="divider">

  {{-- Footer --}}
  <div class="footer">
    <p>Tu reçois cet email car tu es membre de la communauté <strong>Femmes Sans Limites</strong>.</p>
    <p style="margin-top:8px;">© {{ date('Y') }} Femmes Sans Limites — Abidjan, Côte d'Ivoire</p>
  </div>

</div>

{{-- Pixel de tracking (1×1 transparent GIF) --}}
<img src="{{ url('/t/'.$recipient->token.'.gif') }}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;opacity:0;">

</body>
</html>
