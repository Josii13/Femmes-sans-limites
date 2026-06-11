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
        <p style="color:rgba(255,255,255,0.6);margin:0;font-size:13px;">Reçu d'inscription{{ $payment ? ' & QR d\'accès' : ' & QR d\'accès' }}</p>
      </td></tr>
      <tr><td style="padding:40px;text-align:center;">
        <p style="font-size:16px;color:#1A0A10;margin:0 0 8px;">Bonjour <strong>{{ $registration->first_name }}</strong> 🎉</p>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 8px;">
          Ton inscription à <strong>{{ $registration->event->title }}</strong> est confirmée et payée. Merci !
        </p>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 28px;">
          Ton <strong>QR code d'accès</strong> est en pièce jointe — présente-le à l'entrée le jour de l'événement.
        </p>

        <table cellpadding="0" cellspacing="0" style="background:#FDF0F5;border-radius:16px;margin:0 auto 24px;width:320px;">
          <tr><td style="padding:24px;">
            <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:#9CA3AF;">Événement</p>
            <p style="margin:0 0 14px;font-size:16px;font-weight:700;color:#1A0A10;">{{ $registration->event->title }}</p>
            <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:#9CA3AF;">Date</p>
            <p style="margin:0 0 14px;font-size:14px;color:#1A0A10;">{{ $registration->event->event_date->translatedFormat('d F Y à H\hi') }}</p>
            <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:#9CA3AF;">Lieu</p>
            <p style="margin:0;font-size:14px;color:#1A0A10;">{{ $registration->event->location }}{{ $registration->event->city ? ', '.$registration->event->city : '' }}</p>
          </td></tr>
        </table>

        @if($payment)
        {{-- Bloc reçu de paiement --}}
        <table cellpadding="0" cellspacing="0" style="border:1px solid #EEEBF0;border-radius:16px;margin:0 auto 24px;width:320px;">
          <tr><td style="padding:20px 24px;">
            <p style="margin:0 0 12px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6B7280;">Reçu de paiement</p>
            <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;color:#1A0A10;">
              <tr><td style="padding:3px 0;color:#6B7280;text-align:left;">Montant payé</td><td style="padding:3px 0;text-align:right;font-weight:700;color:#D91E6E;">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td></tr>
              <tr><td style="padding:3px 0;color:#6B7280;text-align:left;">Référence</td><td style="padding:3px 0;text-align:right;font-family:monospace;">{{ $payment->provider_reference ?? $payment->reference }}</td></tr>
              <tr><td style="padding:3px 0;color:#6B7280;text-align:left;">Date</td><td style="padding:3px 0;text-align:right;">{{ optional($payment->paid_at)->translatedFormat('d/m/Y H\hi') }}</td></tr>
              <tr><td style="padding:3px 0;color:#6B7280;text-align:left;">Statut</td><td style="padding:3px 0;text-align:right;color:#059669;font-weight:700;">Payé ✓</td></tr>
            </table>
          </td></tr>
        </table>
        @endif

        <p style="color:#aaa;font-size:12px;">QR code également joint en pièce jointe.</p>
      </td></tr>
      <tr><td style="background:#f9f9f9;padding:20px 40px;border-top:1px solid #eee;text-align:center;">
        <p style="color:#aaa;font-size:12px;margin:0;">© {{ date('Y') }} Femme Sans Limites — À bientôt !</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
