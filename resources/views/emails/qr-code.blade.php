<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
      <tr><td style="background:#1A0A10;padding:32px;text-align:center;">
        <h1 style="color:#D91E6E;font-family:Georgia,serif;font-size:28px;margin:0;">Femmes Sans Limites</h1>
        <p style="color:rgba(255,255,255,0.6);margin:8px 0 0;font-size:13px;">Votre QR code d'accès</p>
      </td></tr>
      <tr><td style="padding:40px;text-align:center;">
        <p style="font-size:16px;color:#1A0A10;margin:0 0 8px;">Bonjour <strong>{{ $registration->first_name }}</strong> 🎉</p>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 8px;">
          Votre paiement a été confirmé pour <strong>{{ $registration->event->title }}</strong>.
        </p>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 32px;">
          Votre QR code d'accès est joint à cet email. <strong>Présentez-le à l'entrée</strong> le jour de l'événement.
        </p>

        <table cellpadding="0" cellspacing="0" style="background:#FDF0F5;border-radius:16px;padding:24px;margin:0 auto 32px;width:280px;">
          <tr><td align="center">
            <p style="margin:0 0 4px;font-size:12px;color:#6B7280;">Événement</p>
            <p style="margin:0 0 12px;font-size:16px;font-weight:700;color:#1A0A10;">{{ $registration->event->title }}</p>
            <p style="margin:0 0 4px;font-size:12px;color:#6B7280;">Date</p>
            <p style="margin:0 0 12px;font-size:14px;color:#1A0A10;">{{ $registration->event->event_date->format('d M Y à H\hi') }}</p>
            <p style="margin:0 0 4px;font-size:12px;color:#6B7280;">Lieu</p>
            <p style="margin:0;font-size:14px;color:#1A0A10;">{{ $registration->event->location }}</p>
          </td></tr>
        </table>

        <p style="color:#aaa;font-size:12px;">Le QR code est également joint en pièce jointe de cet email.</p>
      </td></tr>
      <tr><td style="background:#f9f9f9;padding:20px 40px;border-top:1px solid #eee;text-align:center;">
        <p style="color:#aaa;font-size:12px;margin:0;">© {{ date('Y') }} Femmes Sans Limites — À bientôt !</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
