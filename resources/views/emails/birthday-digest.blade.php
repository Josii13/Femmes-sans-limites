<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
@php
    $first = explode(' ', trim($member->name))[0] ?: $member->name;
@endphp
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
      <tr><td style="background:#1A0A10;padding:32px;text-align:center;">
        <img src="{{ asset('logo-email.png') }}" alt="FSL" width="48" height="48" style="display:inline-block;border-radius:10px;background:rgba(253,240,245,0.92);">
        <h1 style="color:#D91E6E;font-family:Georgia,serif;font-size:24px;margin:12px 0 4px;">Femme Sans Limites</h1>
        <p style="color:rgba(255,255,255,0.6);margin:0;font-size:13px;">Tes messages d'anniversaire</p>
      </td></tr>

      <tr><td style="padding:40px 40px 16px;">
        <p style="font-size:16px;color:#1A0A10;margin:0 0 8px;">{{ $first }},</p>
        <p style="color:#6B7280;line-height:1.7;margin:0 0 24px;">
          Voici {{ $wishes->count() > 1 ? 'les '.$wishes->count().' messages' : 'le message' }}
          que la communauté t'a {{ $wishes->count() > 1 ? 'laissés' : 'laissé' }}.
        </p>

        @foreach($wishes as $wish)
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#FDF0F5;border-radius:16px;margin:0 0 12px;">
          <tr><td style="padding:18px 22px;">
            <p style="margin:0 0 8px;font-size:14px;color:#1A0A10;line-height:1.6;">{{ $wish->message }}</p>
            <p style="margin:0;font-size:12px;color:#9CA3AF;">— {{ $wish->author_name }}</p>
          </td></tr>
        </table>
        @endforeach
      </td></tr>

      <tr><td style="padding:8px 40px 40px;text-align:center;">
        <a href="{{ route('birthdays.page', $member->birthdayToken()) }}"
           style="display:inline-block;background:#D91E6E;color:#fff;text-decoration:none;padding:14px 32px;border-radius:999px;font-weight:700;font-size:15px;">
          Revoir ma page
        </a>
      </td></tr>

      <tr><td style="background:#f9f9f9;padding:20px 40px;border-top:1px solid #eee;text-align:center;">
        <p style="color:#aaa;font-size:12px;margin:0;">© {{ date('Y') }} Femme Sans Limites</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
