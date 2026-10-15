<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
@php
    $first = explode(' ', trim($member->name))[0] ?: $member->name;
    $page = route('birthdays.page', $member->birthdayToken());
@endphp
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
      <tr><td style="background:#1A0A10;padding:32px;text-align:center;">
        <img src="{{ asset('logo-email.png') }}" alt="FSL" width="48" height="48" style="display:inline-block;border-radius:10px;background:rgba(253,240,245,0.92);">
        <h1 style="color:#D91E6E;font-family:Georgia,serif;font-size:24px;margin:12px 0 4px;">Femme Sans Limites</h1>
        <p style="color:rgba(255,255,255,0.6);margin:0;font-size:13px;">C'est ton jour</p>
      </td></tr>

      <tr><td style="padding:40px 40px 24px;text-align:center;">
        <p style="font-size:20px;color:#1A0A10;margin:0 0 16px;font-family:Georgia,serif;">
          Joyeux anniversaire <strong>{{ $first }}</strong> 🎂
        </p>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 8px;">
          Toute la communauté Femme Sans Limites pense à toi aujourd'hui.
          On t'a préparé une carte à ton nom — elle est en pièce jointe, et tu peux
          la partager où tu veux.
        </p>
      </td></tr>

      @if($member->birthday_card_path)
      <tr><td style="padding:0 40px 24px;text-align:center;">
        <img src="{{ asset('storage/'.$member->birthday_card_path) }}" alt="Joyeux anniversaire {{ $first }}"
             width="400" style="width:400px;max-width:100%;border-radius:16px;display:block;margin:0 auto;">
      </td></tr>
      @endif

      <tr><td style="padding:0 40px 40px;text-align:center;">
        <a href="{{ $page }}"
           style="display:inline-block;background:#D91E6E;color:#fff;text-decoration:none;padding:14px 32px;border-radius:999px;font-weight:700;font-size:15px;">
          Voir ma page et mes messages
        </a>
        <p style="color:#9CA3AF;font-size:12px;line-height:1.7;margin:18px 0 0;">
          Les autres membres peuvent t'y laisser un mot pendant une semaine.
          Partage le lien si tu veux qu'elles passent.
        </p>
      </td></tr>

      <tr><td style="background:#f9f9f9;padding:20px 40px;border-top:1px solid #eee;text-align:center;">
        <p style="color:#aaa;font-size:12px;margin:0;">© {{ date('Y') }} Femme Sans Limites — Belle année à toi !</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
