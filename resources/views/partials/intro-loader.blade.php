{{-- Intro 3D « Particules → nom FSL + défilé des valeurs » (Three.js).

     Jouée UNE SEULE FOIS par session de navigation : la séquence masque la
     page et bloque le défilement, ce qui devenait pénible à chaque retour sur
     l'accueil.

     Elle durait 8,2 s. C'est très long quand on vient chercher une date
     d'atelier : ramenée à ~4,3 s, avec une barre de progression pour qu'on
     voie qu'elle finit, et une sortie possible d'un clic n'importe où. --}}
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
{{-- Conteneur neutre : l'ancien aria-hidden="true" masquait le bouton
     « Passer » aux lecteurs d'écran tout en le laissant focusable. --}}
<div id="fsl-intro">
    <canvas id="fsl-intro-canvas" aria-hidden="true"></canvas>
    <div id="fsl-intro-fallback" class="fsl-intro__center" aria-hidden="true">
        <p class="fsl-intro__name">FEMME SANS LIMITES</p>
        <p class="fsl-intro__tag">Brise tes limites, révèle ta puissance</p>
    </div>
    <button type="button" id="fsl-intro-skip" class="fsl-intro__skip">Passer l'intro</button>
    <div class="fsl-intro__progress" aria-hidden="true"><span id="fsl-intro-bar"></span></div>
</div>

<style>
#fsl-intro{position:fixed;inset:0;z-index:99999;background:#120A0E;overflow:hidden;opacity:1;transition:opacity .5s ease;cursor:pointer;}
#fsl-intro.fsl-intro--hide{opacity:0;}
#fsl-intro.fsl-intro--done{display:none!important;}
#fsl-intro::before,#fsl-intro::after{content:"";position:absolute;border-radius:9999px;filter:blur(12px);pointer-events:none;}
#fsl-intro::before{width:70vw;height:70vw;top:-22vw;right:-16vw;background:radial-gradient(circle,rgba(217,30,110,.16),transparent 60%);}
#fsl-intro::after{width:60vw;height:60vw;bottom:-22vw;left:-16vw;background:radial-gradient(circle,rgba(201,168,76,.12),transparent 60%);}
#fsl-intro-canvas{position:absolute;inset:0;width:100%;height:100%;display:block;}
#fsl-intro .fsl-intro__center{position:absolute;inset:0;display:none;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:0 24px;pointer-events:none;}
#fsl-intro .fsl-intro__center.show{display:flex;}
#fsl-intro .fsl-intro__name{margin:0;font-family:'Playfair Display',Georgia,serif;font-weight:700;font-size:clamp(22px,6vw,34px);letter-spacing:.18em;background:linear-gradient(90deg,#D91E6E,#F2C4D8 30%,#C9A84C 55%,#D91E6E 80%);background-size:200% auto;-webkit-background-clip:text;background-clip:text;color:transparent;animation:fsl-shimmer 3s linear infinite;}
#fsl-intro .fsl-intro__tag{margin:12px 0 0;font-size:13px;letter-spacing:.06em;color:rgba(255,255,255,.55);}

/* Le bouton faisait 12px en gris à 45 % d'opacité, sans marge interne : illisible
   et quasi impossible à viser au pouce. Zone de frappe de 44px, contraste tenu. */
#fsl-intro .fsl-intro__skip{position:absolute;bottom:22px;right:22px;z-index:2;
    background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.22);border-radius:999px;
    color:rgba(255,255,255,.88);font-size:13px;font-weight:600;letter-spacing:.03em;
    padding:11px 20px;min-height:44px;cursor:pointer;
    -webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);
    opacity:0;animation:fsl-fade .5s ease .4s forwards;transition:background .2s,border-color .2s;}
#fsl-intro .fsl-intro__skip:hover{background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.4);}
#fsl-intro .fsl-intro__skip:focus-visible{outline:2px solid #F2C4D8;outline-offset:3px;}

/* Savoir que ça finit change l'attente en patience. */
#fsl-intro .fsl-intro__progress{position:absolute;left:0;right:0;bottom:0;height:2px;background:rgba(255,255,255,.08);}
#fsl-intro .fsl-intro__progress span{display:block;height:100%;width:0;background:linear-gradient(90deg,#C9A84C,#D91E6E);transition:width .18s linear;}

@media (max-width:520px){
    #fsl-intro .fsl-intro__skip{bottom:18px;right:50%;transform:translateX(50%);}
}
@keyframes fsl-fade{to{opacity:1;}}
@keyframes fsl-shimmer{0%{background-position:-150% center;}100%{background-position:150% center;}}
@media (prefers-reduced-motion:reduce){#fsl-intro{display:none!important;}}
</style>

<script>
(function(){
    var el = document.getElementById('fsl-intro');
    if(!el) return;

    // Le nom puis les valeurs FSL (modifiables ici).
    // Sur mobile, le nom tient sur deux lignes plutôt que d'être réduit au
    // sigle « FSL » : l'écran où l'on découvre la marque est justement celui
    // où elle doit s'écrire en entier.
    var NARROW = window.innerWidth < 720;
    var BRAND = NARROW ? ['FEMME', 'SANS LIMITES'] : ['FEMME SANS LIMITES'];
    var VALUES = ['Excellence', 'Résilience', 'Foi', 'Ambition'];

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function webglOK(){ try{ var c=document.createElement('canvas'); return !!(window.WebGLRenderingContext && (c.getContext('webgl')||c.getContext('experimental-webgl'))); }catch(e){ return false; } }

    // Une seule fois par session de navigation. sessionStorage plutôt que
    // localStorage : l'intro reste une entrée en matière pour une nouvelle visite,
    // mais ne se rejoue pas à chaque retour sur l'accueil depuis une autre page.
    // Le stockage peut lever une exception (navigation privée) : dans ce cas on
    // laisse simplement l'intro se jouer.
    var SEEN_KEY = 'fsl.intro.seen';
    function seen(){ try{ return sessionStorage.getItem(SEEN_KEY) === '1'; }catch(e){ return false; } }
    function markSeen(){ try{ sessionStorage.setItem(SEEN_KEY, '1'); }catch(e){} }

    function dismiss(){ el.parentNode && el.parentNode.removeChild(el); }

    if(reduce || seen()){ dismiss(); return; }

    markSeen(); // Quitter la page en pleine intro ne doit pas la rejouer.

    var html = document.documentElement, prevOverflow = html.style.overflow;
    html.style.overflow = 'hidden';
    var raf=null, renderer=null, finished=false, cam=null, bar=document.getElementById('fsl-intro-bar');

    function finish(){
        if(finished) return; finished=true;
        if(raf) cancelAnimationFrame(raf);
        window.removeEventListener('resize', onResize);
        document.removeEventListener('keydown', onKey);
        el.classList.add('fsl-intro--hide');
        setTimeout(function(){
            html.style.overflow = prevOverflow || '';
            try{ if(renderer){ renderer.dispose(); renderer.forceContextLoss && renderer.forceContextLoss(); } }catch(e){}
            el.parentNode && el.parentNode.removeChild(el);
        }, 520);
    }

    function onKey(e){ if(e.key === 'Escape' || e.key === 'Enter' || e.key === ' ') finish(); }

    // Trois sorties plutôt qu'une : le bouton, un clic n'importe où, Échap.
    document.getElementById('fsl-intro-skip').addEventListener('click', finish);
    el.addEventListener('click', finish);
    document.addEventListener('keydown', onKey);

    // Si le CDN traîne, on n'impose pas un écran noir : 2,5 s et on passe.
    // L'ancien filet de sécurité était à 12 s.
    var safety = setTimeout(finish, 2500);

    // Fallback sans WebGL : écran de marque CSS.
    if(!webglOK()){ document.getElementById('fsl-intro-fallback').classList.add('show'); clearTimeout(safety); setTimeout(finish, 1800); return; }

    var applyFitRef = function(){};
    function onResize(){ if(!renderer||!cam) return; renderer.setSize(innerWidth,innerHeight); cam.aspect=innerWidth/innerHeight; cam.updateProjectionMatrix(); applyFitRef(); }

    /**
     * Échantillonne un texte (une ou plusieurs lignes) → positions (x,y,z).
     */
    function sampleText(lines, count){
        lines = [].concat(lines);
        var cw=1200, ch = lines.length > 1 ? 560 : 300;
        var cnv=document.createElement('canvas'); cnv.width=cw; cnv.height=ch;
        var ctx=cnv.getContext('2d');
        ctx.fillStyle='#fff'; ctx.textAlign='center'; ctx.textBaseline='middle';

        // Taille ajustée sur la ligne la plus large.
        var fs=220;
        function widest(){ return Math.max.apply(null, lines.map(function(t){ return ctx.measureText(t).width; })); }
        do { ctx.font='700 '+fs+'px "Playfair Display", Georgia, serif'; fs-=6; }
        while(fs>20 && widest() > cw*0.9);

        var lineH = fs*1.12, top = ch/2 - (lines.length-1)*lineH/2;
        lines.forEach(function(t,i){ ctx.fillText(t, cw/2, top + i*lineH); });

        var d=ctx.getImageData(0,0,cw,ch).data, src=[];
        // Échantillonnage dense (pas de 2px) pour des lettres pleines.
        for(var y=0;y<ch;y+=2) for(var x=0;x<cw;x+=2) if(d[(y*cw+x)*4+3]>130) src.push([x,y]);
        // Mélange puis assignation séquentielle → couverture UNIFORME (pas de trous).
        for(var m=src.length-1;m>0;m--){ var n=(Math.random()*(m+1))|0, tmp=src[m]; src[m]=src[n]; src[n]=tmp; }

        var S=165, out=new Float32Array(count*3);
        for(var i=0;i<count;i++){
            var p = src.length ? src[i % src.length] : [cw/2,ch/2];
            out[i*3]   = (p[0]-cw/2)/S + (Math.random()-0.5)*0.015;
            out[i*3+1] = -(p[1]-ch/2)/S + (Math.random()-0.5)*0.015;
            out[i*3+2] = (Math.random()-0.5)*0.08; // profondeur réduite → texte net
        }
        return out;
    }

    function init(THREE){
        clearTimeout(safety);
        try{
            var canvas=document.getElementById('fsl-intro-canvas');
            renderer=new THREE.WebGLRenderer({canvas:canvas, antialias:true, alpha:true});
            renderer.setPixelRatio(Math.min(devicePixelRatio||1,2));
            renderer.setSize(innerWidth, innerHeight);
            var scene=new THREE.Scene();
            cam=new THREE.PerspectiveCamera(50, innerWidth/innerHeight, 0.1, 100); cam.position.set(0,0,7);

            var COUNT = NARROW ? 3200 : 6500;

            var phases=[BRAND].concat(VALUES).map(function(t){ return sampleText(t, COUNT); });

            // Positions de départ : nuage sphérique dispersé.
            var cur=new Float32Array(COUNT*3), col=new Float32Array(COUNT*3);
            var rose=[0.95,0.24,0.55], gold=[0.97,0.82,0.45];
            for(var i=0;i<COUNT;i++){
                var r=5+Math.random()*7, th=Math.random()*Math.PI*2, ph=Math.acos(2*Math.random()-1);
                cur[i*3]=r*Math.sin(ph)*Math.cos(th); cur[i*3+1]=r*Math.sin(ph)*Math.sin(th); cur[i*3+2]=r*Math.cos(ph)-3;
                var c=(i%2===0?rose:gold); col[i*3]=c[0]; col[i*3+1]=c[1]; col[i*3+2]=c[2];
            }
            var geo=new THREE.BufferGeometry();
            geo.setAttribute('position', new THREE.BufferAttribute(cur,3));
            geo.setAttribute('color', new THREE.BufferAttribute(col,3));
            var mat=new THREE.PointsMaterial({size:0.04, vertexColors:true, transparent:true, opacity:0, blending:THREE.AdditiveBlending, depthWrite:false});
            var pts=new THREE.Points(geo, mat); scene.add(pts);

            // ── Mise à l'échelle sur les DEUX axes ──────────────────────
            // L'ajustement ne regardait que la largeur. En portrait, un nom sur
            // deux lignes débordait alors en haut et en bas.
            var maxX=0.0001, maxY=0.0001;
            phases.forEach(function(ph){
                for(var q=0;q<ph.length;q+=3){
                    var ax=Math.abs(ph[q]); if(ax>maxX) maxX=ax;
                    var ay=Math.abs(ph[q+1]); if(ay>maxY) maxY=ay;
                }
            });
            function applyFit(){
                var visHalfH = Math.tan((50*Math.PI/180)/2) * cam.position.z;
                var visHalfW = visHalfH * cam.aspect;
                var f = Math.min(1, (visHalfW*0.86)/maxX, (visHalfH*0.78)/maxY);
                pts.scale.setScalar(f);
                // Particules un peu plus grosses quand on réduit beaucoup (mobile).
                mat.size = 0.04 / Math.max(0.5, f);
            }
            applyFitRef = applyFit; applyFit();

            // ── Séquence (ms) ───────────────────────────────────────────
            // 1 300 + 4×550 au lieu de 1 700 + 4×1 150 : la lecture reste
            // confortable, l'attente passe de 8,2 s à environ 4,3 s.
            var HOLD_BRAND=1300, HOLD_VAL=550;
            var schedule=[], t=250;
            schedule.push({at:t, target:phases[0]}); t+=HOLD_BRAND;
            for(var k=1;k<phases.length;k++){ schedule.push({at:t, target:phases[k]}); t+=HOLD_VAL; }
            var DISPERSE_AT=t, DISPERSE_MS=650, END_AT=t+DISPERSE_MS;

            var disperse=new Float32Array(COUNT*3);
            for(i=0;i<COUNT;i++){ disperse[i*3]=(Math.random()-0.5)*18; disperse[i*3+1]=(Math.random()-0.5)*14; disperse[i*3+2]=2+Math.random()*8; }

            window.addEventListener('resize', onResize);
            var start=performance.now(), targets=phases[0], lerp=0.14;
            var posAttr=geo.getAttribute('position');

            function loop(now){
                var ms=now-start;
                mat.opacity=Math.min(1.0, ms/420);
                for(var s=schedule.length-1;s>=0;s--){ if(ms>=schedule[s].at){ targets=schedule[s].target; break; } }
                if(ms>=DISPERSE_AT){ targets=disperse; lerp=0.08; mat.opacity=Math.max(0, 1.0*(1-(ms-DISPERSE_AT)/DISPERSE_MS)); }
                var a=posAttr.array;
                for(var j=0;j<a.length;j++){ a[j]+= (targets[j]-a[j])*lerp; }
                posAttr.needsUpdate=true;
                pts.rotation.y=Math.sin(ms/2200)*0.022; // balancement très léger
                if(bar) bar.style.width = Math.min(100, (ms/END_AT)*100) + '%';
                renderer.render(scene,cam);
                if(ms<END_AT){ raf=requestAnimationFrame(loop); } else { finish(); }
            }
            raf=requestAnimationFrame(loop);
        }catch(e){ finish(); }
    }

    var s=document.createElement('script');
    s.src='https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
    s.async=true;
    s.onload=function(){ if(finished) return; if(window.THREE) init(window.THREE); else finish(); };
    s.onerror=finish;
    document.head.appendChild(s);
})();
</script>
