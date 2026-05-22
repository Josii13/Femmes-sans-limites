<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
  .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
  .header { background: linear-gradient(135deg, #1A0A10, #2d0d1b); padding: 36px; text-align: center; }
  .header img { height: 48px; margin-bottom: 12px; }
  .header h1 { color: #fff; font-size: 22px; margin: 0; font-weight: 700; letter-spacing: 0.5px; }
  .badge { display: inline-block; background: #D91E6E22; color: #D91E6E; border: 1px solid #D91E6E44; border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; }
  .content { padding: 36px; }
  .content p { color: #555; line-height: 1.7; font-size: 15px; margin: 0 0 16px; }
  .info-card { background: #faf9fb; border: 1px solid #ede8f0; border-radius: 10px; padding: 20px 24px; margin: 24px 0; }
  .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ede8f0; font-size: 14px; }
  .info-row:last-child { border-bottom: none; }
  .info-label { color: #999; font-weight: 500; }
  .info-value { color: #333; font-weight: 600; }
  .cta { text-align: center; margin: 28px 0 8px; }
  .btn { display: inline-block; background: #D91E6E; color: #fff !important; text-decoration: none; padding: 13px 32px; border-radius: 50px; font-weight: 700; font-size: 14px; letter-spacing: 0.5px; }
  .footer { background: #1A0A10; padding: 20px; text-align: center; }
  .footer p { color: rgba(255,255,255,0.4); font-size: 12px; margin: 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>Nouvelle candidature membre</h1>
    <span class="badge">Action requise</span>
  </div>
  <div class="content">
    <p>Une nouvelle personne souhaite rejoindre la communauté <strong>Femme Sans Limites</strong>. Voici ses informations :</p>
    <div class="info-card">
      <div class="info-row">
        <span class="info-label">Nom</span>
        <span class="info-value">{{ $member->name }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Email</span>
        <span class="info-value">{{ $member->email }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Profession</span>
        <span class="info-value">{{ $member->profession }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Ville, Pays</span>
        <span class="info-value">{{ $member->city }}, {{ $member->country }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Type souhaité</span>
        <span class="info-value">{{ ucfirst($member->type) }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">N° Membre</span>
        <span class="info-value">{{ $member->member_number }}</span>
      </div>
    </div>
    <p>La carte membre a été générée automatiquement. Rendez-vous dans le back office pour valider et activer ce compte.</p>
    <div class="cta">
      <a href="{{ url('/admin/members') }}" class="btn">Voir dans le back office</a>
    </div>
  </div>
  <div class="footer">
    <p>Femme Sans Limites &mdash; Administration</p>
  </div>
</div>
</body>
</html>
