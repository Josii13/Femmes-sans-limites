{{-- Loader d'intro « Révélation du logo » — affiché une fois par session, accueil uniquement --}}
<div id="fsl-intro" role="presentation" aria-hidden="true">
    <div class="fsl-intro__glow fsl-intro__glow--rose"></div>
    <div class="fsl-intro__glow fsl-intro__glow--gold"></div>

    <div class="fsl-intro__center">
        <div class="fsl-intro__logo">
            <img src="{{ asset('logo-email.png') }}" alt="Femme Sans Limites" width="92" height="92">
        </div>
        <div class="fsl-intro__line"></div>
        <p class="fsl-intro__name">FEMME SANS LIMITES</p>
        <p class="fsl-intro__tag">Brise tes limites, révèle ta puissance</p>
    </div>
</div>

<style>
#fsl-intro{
    position:fixed;inset:0;z-index:99999;
    background:#1A0A10;
    display:flex;align-items:center;justify-content:center;
    overflow:hidden;
    animation:fsl-curtain .9s cubic-bezier(.76,0,.24,1) 2.05s forwards;
    will-change:transform,opacity;
}
#fsl-intro .fsl-intro__glow{position:absolute;border-radius:9999px;filter:blur(8px);pointer-events:none;}
#fsl-intro .fsl-intro__glow--rose{width:60vw;height:60vw;top:-15vw;right:-10vw;background:radial-gradient(circle,rgba(217,30,110,.20),transparent 60%);}
#fsl-intro .fsl-intro__glow--gold{width:50vw;height:50vw;bottom:-15vw;left:-12vw;background:radial-gradient(circle,rgba(201,168,76,.14),transparent 60%);}
#fsl-intro .fsl-intro__center{position:relative;text-align:center;padding:0 24px;}
#fsl-intro .fsl-intro__logo{
    width:108px;height:108px;margin:0 auto;border-radius:26px;
    background:rgba(253,240,245,.95);
    display:flex;align-items:center;justify-content:center;
    opacity:0;transform:scale(.82);
    animation:fsl-pop .7s cubic-bezier(.2,.8,.2,1) .05s forwards, fsl-halo 2.2s ease-in-out .7s infinite;
}
#fsl-intro .fsl-intro__logo img{width:92px;height:92px;display:block;}
#fsl-intro .fsl-intro__line{
    width:0;height:2px;margin:22px auto 16px;border-radius:2px;opacity:0;
    background:linear-gradient(90deg,transparent,#C9A84C,transparent);
    animation:fsl-line .8s ease .55s forwards;
}
#fsl-intro .fsl-intro__name{
    margin:0;font-family:'Playfair Display',Georgia,serif;font-weight:700;
    font-size:clamp(20px,5vw,30px);letter-spacing:.18em;
    background:linear-gradient(90deg,#D91E6E 0%,#F2C4D8 30%,#C9A84C 55%,#D91E6E 80%);
    background-size:200% auto;-webkit-background-clip:text;background-clip:text;color:transparent;
    opacity:0;animation:fsl-fade .7s ease .7s forwards, fsl-shimmer 3s linear .7s infinite;
}
#fsl-intro .fsl-intro__tag{
    margin:10px 0 0;font-size:12px;letter-spacing:.05em;color:rgba(255,255,255,.45);
    opacity:0;animation:fsl-fade .7s ease 1s forwards;
}
@keyframes fsl-pop{to{opacity:1;transform:scale(1);}}
@keyframes fsl-line{to{width:130px;opacity:1;}}
@keyframes fsl-fade{from{transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
@keyframes fsl-shimmer{0%{background-position:-150% center;}100%{background-position:150% center;}}
@keyframes fsl-halo{
    0%,100%{box-shadow:0 0 36px 6px rgba(217,30,110,.28);}
    50%{box-shadow:0 0 64px 16px rgba(217,30,110,.48);}
}
@keyframes fsl-curtain{to{transform:translateY(-100%);opacity:0;visibility:hidden;}}
#fsl-intro.fsl-intro--done{display:none!important;}

/* Accessibilité : pas d'animation si l'utilisateur la refuse */
@media (prefers-reduced-motion:reduce){
    #fsl-intro{animation:none;display:none!important;}
}
</style>

<script>
(function(){
    var el = document.getElementById('fsl-intro');
    if(!el) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var seen = false;
    try{ seen = sessionStorage.getItem('fsl_intro_seen') === '1'; }catch(e){}

    // Déjà vu cette session ou animations réduites → on retire immédiatement (pas de flash).
    if(seen || reduce){
        el.classList.add('fsl-intro--done');
        return;
    }
    try{ sessionStorage.setItem('fsl_intro_seen','1'); }catch(e){}

    // Bloque le scroll pendant l'intro.
    var html = document.documentElement;
    var prev = html.style.overflow;
    html.style.overflow = 'hidden';

    function finish(){
        if(el.classList.contains('fsl-intro--done')) return;
        el.classList.add('fsl-intro--done');
        html.style.overflow = prev || '';
        el.parentNode && el.parentNode.removeChild(el);
    }
    el.addEventListener('animationend', function(ev){ if(ev.animationName === 'fsl-curtain') finish(); });
    // Filet de sécurité.
    setTimeout(finish, 3400);
})();
</script>
