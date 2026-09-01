<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
  .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
  .header { background: linear-gradient(135deg, #1A0A10, #2d0d1b); padding: 36px; text-align: center; }
  .header h1 { color: #fff; font-size: 22px; margin: 0; font-weight: 700; letter-spacing: 0.5px; }
  .badge { display: inline-block; background: #D91E6E22; color: #D91E6E; border: 1px solid #D91E6E44; border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; }
  .content { padding: 36px; }
  .content p { color: #555; line-height: 1.7; font-size: 15px; margin: 0 0 16px; }
  .info-card { background: #faf9fb; border: 1px solid #ede8f0; border-radius: 10px; padding: 20px 24px; margin: 24px 0; }
  .info-row { padding: 8px 0; border-bottom: 1px solid #ede8f0; font-size: 14px; }
  .info-row:last-child { border-bottom: none; }
  .info-label { color: #999; font-weight: 500; display: block; margin-bottom: 2px; }
  .info-value { color: #333; font-weight: 600; }
  .message { background: #fff; border-left: 4px solid #D91E6E; padding: 16px 20px; margin: 8px 0 0; color: #333; font-size: 15px; line-height: 1.7; white-space: pre-line; }
  .footer { background: #1A0A10; padding: 20px; text-align: center; }
  .footer p { color: rgba(255,255,255,0.4); font-size: 12px; margin: 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>Nouveau message depuis le site</h1>
    <span class="badge">Formulaire de contact</span>
  </div>
  <div class="content">
    <p>Un visiteur vient de remplir le formulaire de contact du site <strong>Femme Sans Limites</strong>.</p>
    <div class="info-card">
      <div class="info-row">
        <span class="info-label">Nom</span>
        <span class="info-value">{{ $data['name'] }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Email</span>
        <span class="info-value">{{ $data['email'] }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Sujet</span>
        <span class="info-value">{{ $data['subject'] }}</span>
      </div>
    </div>
    <p style="margin-bottom:4px;"><strong>Message :</strong></p>
    <div class="message">{{ $data['message'] }}</div>
    <p style="margin-top:24px;font-size:13px;color:#999;">Il suffit de répondre à cet email pour écrire directement à {{ $data['name'] }}.</p>
  </div>
  <div class="footer">
    <p>Femme Sans Limites &mdash; Administration</p>
  </div>
</div>
</body>
</html>
