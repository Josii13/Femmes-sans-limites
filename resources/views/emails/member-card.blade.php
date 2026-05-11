<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:'DM Sans',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
      <!-- Header -->
      <tr><td style="background:#1A0A10;padding:32px;text-align:center;">
        <h1 style="color:#D91E6E;font-family:Georgia,serif;font-size:28px;margin:0;">Femmes Sans Limites</h1>
        <p style="color:rgba(255,255,255,0.6);margin:8px 0 0;font-size:13px;">Votre carte de membre officielle</p>
      </td></tr>
      <!-- Body -->
      <tr><td style="padding:40px 40px 32px;">
        <p style="font-size:16px;color:#1A0A10;margin:0 0 16px;">Bonjour <strong>{{ $member->name }}</strong>,</p>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 24px;">
          Bienvenue dans la communauté <strong>Femmes Sans Limites</strong> !<br>
          Vous trouverez en pièce jointe votre <strong>carte de membre officielle</strong>.
        </p>
        <table cellpadding="0" cellspacing="0" style="background:#FDF0F5;border-radius:12px;padding:20px;width:100%;margin-bottom:24px;">
          <tr><td>
            <p style="margin:0 0 6px;font-size:13px;color:#6B7280;">Numéro de membre</p>
            <p style="margin:0;font-size:18px;font-weight:700;color:#D91E6E;">{{ $member->member_number }}</p>
          </td><td align="right">
            @if($member->type === 'premium')
            <span style="background:#D91E6E;color:white;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;">PREMIUM</span>
            @elseif($member->type === 'gold')
            <span style="background:#C9A84C;color:white;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;">GOLD</span>
            @else
            <span style="background:#6B7280;color:white;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;">STANDARD</span>
            @endif
          </td></tr>
        </table>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 32px;">
          Présentez cette carte lors de nos événements pour bénéficier de vos avantages membres.
        </p>
        <table cellpadding="0" cellspacing="0" style="margin:0 0 32px;">
          <tr><td style="background:#D91E6E;border-radius:30px;padding:14px 32px;">
            <a href="{{ url('/evenements') }}" style="color:white;text-decoration:none;font-weight:600;font-size:15px;">Voir nos événements →</a>
          </td></tr>
        </table>
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#f9f9f9;padding:24px 40px;border-top:1px solid #eee;text-align:center;">
        <p style="color:#aaa;font-size:12px;margin:0;">© {{ date('Y') }} Femmes Sans Limites — Tous droits réservés</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
