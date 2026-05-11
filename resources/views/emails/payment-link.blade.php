<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
      <tr><td style="background:#1A0A10;padding:32px;text-align:center;">
        <h1 style="color:#D91E6E;font-family:Georgia,serif;font-size:28px;margin:0;">Femmes Sans Limites</h1>
        <p style="color:rgba(255,255,255,0.6);margin:8px 0 0;font-size:13px;">Lien de paiement — {{ $event->title }}</p>
      </td></tr>
      <tr><td style="padding:40px;">
        <p style="font-size:16px;color:#1A0A10;margin:0 0 16px;">Bonjour <strong>{{ $registration->first_name }}</strong>,</p>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 24px;">
          Merci pour votre inscription à <strong>{{ $event->title }}</strong>.<br>
          Pour finaliser votre place, veuillez procéder au paiement via le lien ci-dessous.
        </p>
        <table cellpadding="0" cellspacing="0" style="background:#FDF0F5;border-radius:12px;padding:20px;width:100%;margin-bottom:24px;">
          <tr>
            <td>
              <p style="margin:0 0 4px;font-size:12px;color:#6B7280;">Montant à payer</p>
              <p style="margin:0;font-size:24px;font-weight:700;color:#D91E6E;">{{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency }}</p>
            </td>
            <td>
              <p style="margin:0 0 4px;font-size:12px;color:#6B7280;">Date de l'événement</p>
              <p style="margin:0;font-size:14px;font-weight:600;color:#1A0A10;">{{ $event->event_date->format('d M Y') }}</p>
            </td>
          </tr>
        </table>
        <table cellpadding="0" cellspacing="0" style="margin:0 auto 32px;">
          <tr><td style="background:#D91E6E;border-radius:30px;padding:14px 40px;text-align:center;">
            <a href="{{ $event->payment_link }}" style="color:white;text-decoration:none;font-weight:700;font-size:16px;">Payer maintenant →</a>
          </td></tr>
        </table>
        <p style="color:#aaa;font-size:12px;line-height:1.7;margin:0;">
          Une fois le paiement effectué, votre QR code d'accès vous sera envoyé par email dans les 24h.<br>
          En cas de problème : contact@femmessanslimites.com
        </p>
      </td></tr>
      <tr><td style="background:#f9f9f9;padding:20px 40px;border-top:1px solid #eee;text-align:center;">
        <p style="color:#aaa;font-size:12px;margin:0;">© {{ date('Y') }} Femmes Sans Limites</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
