<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
@php
    $recipientFirst = \App\Mail\BirthdayAnnouncementMail::firstName($recipient);
    $single = $celebrants->count() === 1;
@endphp
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
      <tr><td style="background:#1A0A10;padding:32px;text-align:center;">
        <img src="{{ asset('logo-email.png') }}" alt="FSL" width="48" height="48" style="display:inline-block;border-radius:10px;background:rgba(253,240,245,0.92);">
        <h1 style="color:#D91E6E;font-family:Georgia,serif;font-size:24px;margin:12px 0 4px;">Femme Sans Limites</h1>
        <p style="color:rgba(255,255,255,0.6);margin:0;font-size:13px;">Jour de fête dans la communauté</p>
      </td></tr>

      <tr><td style="padding:40px 40px 16px;text-align:center;">
        <p style="font-size:18px;color:#1A0A10;margin:0 0 16px;font-family:Georgia,serif;">
          Bonjour {{ $recipientFirst }},
        </p>
        <p style="color:#6B7280;line-height:1.7;margin:0;">
          @if($single)
            Aujourd'hui, c'est l'anniversaire de <strong style="color:#1A0A10;">{{ $celebrants->first()->name }}</strong> 🎂
            Un petit mot de ta part lui fera plaisir : son mur de vœux est ouvert toute la semaine.
          @else
            Aujourd'hui, plusieurs membres fêtent leur anniversaire 🎂
            Un petit mot de ta part leur fera plaisir : leurs murs de vœux sont ouverts toute la semaine.
          @endif
        </p>
      </td></tr>

      @foreach($celebrants as $celebrant)
      <tr><td style="padding:12px 40px;text-align:center;">
        @unless($single)
        <p style="color:#1A0A10;font-weight:700;margin:0 0 10px;">{{ $celebrant->name }}</p>
        @endunless
        <a href="{{ route('birthdays.page', $celebrant->birthdayToken()) }}"
           style="display:inline-block;background:#D91E6E;color:#fff;text-decoration:none;padding:14px 32px;border-radius:999px;font-weight:700;font-size:15px;">
          Souhaiter un joyeux anniversaire à {{ \App\Mail\BirthdayAnnouncementMail::firstName($celebrant) }}
        </a>
      </td></tr>
      @endforeach

      <tr><td style="padding:16px 40px 40px;text-align:center;">
        <p style="color:#9CA3AF;font-size:12px;line-height:1.7;margin:0;">
          Pas besoin de compte : écris ton prénom et ton message, c'est tout.
          {{ $single ? 'Elle recevra' : 'Elles recevront' }} tous les mots en un seul message le lendemain.
        </p>
      </td></tr>

      <tr><td style="background:#f9f9f9;padding:20px 40px;border-top:1px solid #eee;text-align:center;">
        <p style="color:#aaa;font-size:12px;margin:0;">© {{ date('Y') }} Femme Sans Limites</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
