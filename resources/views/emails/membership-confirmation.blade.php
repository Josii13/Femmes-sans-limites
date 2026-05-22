<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
  .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
  .header { background: linear-gradient(135deg, #1A0A10, #2d0d1b); padding: 40px 36px; text-align: center; }
  .header h1 { color: #D91E6E; font-size: 28px; margin: 0 0 6px; font-weight: 800; letter-spacing: 1px; }
  .header p { color: rgba(255,255,255,0.6); font-size: 14px; margin: 0; }
  .content { padding: 36px; }
  .content h2 { color: #1A0A10; font-size: 20px; margin: 0 0 12px; }
  .content p { color: #555; line-height: 1.7; font-size: 15px; margin: 0 0 16px; }
  .highlight { background: linear-gradient(135deg, #1A0A10, #2d0d1b); border-radius: 12px; padding: 24px; text-align: center; margin: 24px 0; }
  .highlight p { color: rgba(255,255,255,0.7); font-size: 13px; margin: 0 0 4px; }
  .highlight .name { color: #fff; font-size: 22px; font-weight: 700; margin: 0; }
  .highlight .num { color: #C9A84C; font-size: 13px; font-family: monospace; margin: 8px 0 0; }
  .steps { margin: 24px 0; }
  .step { display: flex; gap: 14px; margin-bottom: 16px; align-items: flex-start; }
  .step-num { width: 28px; height: 28px; min-width: 28px; background: #D91E6E; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; }
  .step-text { color: #555; font-size: 14px; line-height: 1.5; padding-top: 4px; }
  .footer { background: #1A0A10; padding: 20px 36px; text-align: center; }
  .footer p { color: rgba(255,255,255,0.35); font-size: 12px; margin: 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>FEMMES SANS LIMITES</h1>
    <p>Ta candidature a bien été reçue ✨</p>
  </div>
  <div class="content">
    <h2>Bonjour {{ $member->name }},</h2>
    <p>Merci d'avoir souhaité rejoindre la communauté <strong>Femme Sans Limites</strong>. Ta candidature est en cours d'examen par notre équipe.</p>
    <div class="highlight">
      <p>Candidature déposée pour</p>
      <p class="name">{{ $member->name }}</p>
      <p class="num">{{ $member->member_number }} &nbsp;·&nbsp; {{ ucfirst($member->type) }}</p>
    </div>
    <p>Voici les prochaines étapes :</p>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-text">Notre équipe examine ta candidature sous 48h ouvrées.</div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-text">Si ta candidature est validée, tu recevras un email de confirmation avec ton accès membre et ta carte personnalisée.</div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-text">Tu pourras alors accéder aux événements, formations et à toute la communauté FSL.</div>
      </div>
    </div>
    <p>En attendant, n'hésite pas à nous contacter si tu as des questions.</p>
  </div>
  <div class="footer">
    <p>Femme Sans Limites &mdash; femmessanslimites.com</p>
  </div>
</div>
</body>
</html>
