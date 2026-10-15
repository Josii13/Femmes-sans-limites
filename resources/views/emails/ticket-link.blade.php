<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
      <tr><td style="background:#1A0A10;padding:32px;text-align:center;">
        <img src="{{ asset('logo-email.png') }}" alt="FSL" width="48" height="48" style="display:inline-block;border-radius:10px;background:rgba(253,240,245,0.92);">
        <h1 style="color:#D91E6E;font-family:Georgia,serif;font-size:24px;margin:12px 0 4px;">Femme Sans Limites</h1>
        <p style="color:rgba(255,255,255,0.6);margin:0;font-size:13px;">Vos billets d'accès</p>
      </td></tr>
      <tr><td style="padding:40px;">
        <p style="font-size:16px;color:#1A0A10;margin:0 0 8px;">Bonjour <strong>{{ $registrations->first()->first_name }}</strong>,</p>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 28px;">
          Voici {{ $registrations->count() > 1 ? 'les liens de vos billets' : 'le lien de votre billet' }}.
          Ouvrez-{{ $registrations->count() > 1 ? 'les' : 'le' }} sur votre téléphone le jour J : le QR s'affiche
          directement à l'écran, rien à imprimer.
        </p>

        @foreach($registrations as $registration)
        <table cellpadding="0" cellspacing="0" width="100%" style="background:#FDF0F5;border-radius:16px;margin:0 0 16px;">
          <tr><td style="padding:24px;">
            <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:#9CA3AF;">Événement</p>
            <p style="margin:0 0 14px;font-size:16px;font-weight:700;color:#1A0A10;">{{ $registration->event->title }}</p>
            <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:#9CA3AF;">Date</p>
            <p style="margin:0 0 18px;font-size:14px;color:#1A0A10;">{{ $registration->event->event_date->translatedFormat('d F Y à H\hi') }}</p>
            <a href="{{ route('tickets.show', $registration->qr_token) }}"
               style="display:inline-block;background:#D91E6E;color:#fff;text-decoration:none;padding:12px 26px;border-radius:999px;font-weight:700;font-size:14px;">
              Voir mon billet
            </a>
          </td></tr>
        </table>
        @endforeach

        <p style="color:#9CA3AF;font-size:12px;line-height:1.7;margin:24px 0 0;">
          Ce lien vaut billet d'entrée : gardez-le pour vous.
        </p>
      </td></tr>
      <tr><td style="background:#f9f9f9;padding:20px 40px;border-top:1px solid #eee;text-align:center;">
        <p style="color:#aaa;font-size:12px;margin:0;">© {{ date('Y') }} Femme Sans Limites — À bientôt !</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
