{{-- Intro 3D « Particules → nom FSL + défilé des valeurs » (Three.js, rejouée à chaque rechargement) --}}
<div id="fsl-intro" role="presentation" aria-hidden="true">
    <canvas id="fsl-intro-canvas"></canvas>
    <div id="fsl-intro-fallback" class="fsl-intro__center">
        <p class="fsl-intro__name">FEMME SANS LIMITES</p>
        <p class="fsl-intro__tag">Brise tes limites, révèle ta puissance</p>
    </div>
    <button type="button" id="fsl-intro-skip" class="fsl-intro__skip">Passer l'intro &rsaquo;</button>
</div>

<style>
#fsl-intro{position:fixed;inset:0;z-index:99999;background:#120A0E;overflow:hidden;opacity:1;transition:opacity .7s ease;}
#fsl-intro.fsl-intro--hide{opacity:0;}
#fsl-intro.fsl-intro--done{display:none!important;}
#fsl-intro::before,#fsl-intro::after{content:"";position:absolute;border-radius:9999px;filter:blur(12px);pointer-events:none;}
#fsl-intro::before{width:70vw;height:70vw;top:-22vw;right:-16vw;background:radial-gradient(circle,rgba(217,30,110,.16),transparent 60%);}
#fsl-intro::after{width:60vw;height:60vw;bottom:-22vw;left:-16vw;background:radial-gradient(circle,rgba(201,168,76,.12),transparent 60%);}
#fsl-intro-canvas{position:absolute;inset:0;width:100%;height:100%;display:block;}
#fsl-intro .fsl-intro__center{position:absolute;inset:0;display:none;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:0 24px;pointer-events:none;}
#fsl-intro .fsl-intro__center.show{display:flex;}
#fsl-intro .fsl-intro__name{margin:0;font-family:'Playfair Display',Georgia,serif;font-weight:700;font-size:clamp(20px,5vw,32px);letter-spacing:.2em;background:linear-gradient(90deg,#D91E6E,#F2C4D8 30%,#C9A84C 55%,#D91E6E 80%);background-size:200% auto;-webkit-background-clip:text;background-clip:text;color:transparent;animation:fsl-shimmer 3s linear infinite;}
#fsl-intro .fsl-intro__tag{margin:12px 0 0;font-size:12px;letter-spacing:.06em;color:rgba(255,255,255,.5);}
#fsl-intro .fsl-intro__skip{position:absolute;bottom:24px;right:24px;background:transparent;border:0;color:rgba(255,255,255,.45);font-size:12px;cursor:pointer;letter-spacing:.04em;transition:color .2s;opacity:0;animation:fsl-fade .6s ease 1.2s forwards;}
#fsl-intro .fsl-intro__skip:hover{color:#fff;}
@keyframes fsl-fade{to{opacity:1;}}
@keyframes fsl-shimmer{0%{background-position:-150% center;}100%{background-position:150% center;}}
@media (prefers-reduced-motion:reduce){#fsl-intro{display:none!important;}}
</style>

<script>
(function(){
    var el = document.getElementById('fsl-intro');
    if(!el) return;

    // Le nom puis les valeurs FSL (modifiables ici) :
    var BRAND = (window.innerWidth < 720) ? 'FSL' : 'FEMME SANS LIMITES';
    var VALUES = ['Ambition', 'Sororité', 'Audace', 'Liberté', 'Puissance'];

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function webglOK(){ try{ var c=document.createElement('canvas'); return !!(window.WebGLRenderingContext && (c.getContext('webgl')||c.getContext('experimental-webgl'))); }catch(e){ return false; } }
    if(reduce){ el.classList.add('fsl-intro--done'); return; }

    var html = document.documentElement, prevOverflow = html.style.overflow;
    html.style.overflow = 'hidden';
    var raf=null, renderer=null, finished=false, cam=null;

    function finish(){
        if(finished) return; finished=true;
        if(raf) cancelAnimationFrame(raf);
        window.removeEventListener('resize', onResize);
        el.classList.add('fsl-intro--hide');
        setTimeout(function(){
            html.style.overflow = prevOverflow || '';
            try{ if(renderer){ renderer.dispose(); renderer.forceContextLoss && renderer.forceContextLoss(); } }catch(e){}
            el.parentNode && el.parentNode.removeChild(el);
        }, 720);
    }
    document.getElementById('fsl-intro-skip').addEventListener('click', finish);
    var safety = setTimeout(finish, 12000);

    // Fallback sans WebGL : écran de marque CSS.
    if(!webglOK()){ document.getElementById('fsl-intro-fallback').classList.add('show'); setTimeout(finish, 2600); return; }

    function onResize(){ if(!renderer||!cam) return; renderer.setSize(innerWidth,innerHeight); cam.aspect=innerWidth/innerHeight; cam.updateProjectionMatrix(); }

    // Échantillonne un texte → positions (x,y,z) pour COUNT particules.
    function sampleText(text, count){
        var cw=1200, ch=300, cnv=document.createElement('canvas'); cnv.width=cw; cnv.height=ch;
        var ctx=cnv.getContext('2d');
        ctx.fillStyle='#fff'; ctx.textAlign='center'; ctx.textBaseline='middle';
        var fs=220;
        do { ctx.font='700 '+fs+'px "Playfair Display", Georgia, serif'; fs-=6; }
        while(fs>20 && ctx.measureText(text).width > cw*0.9);
        ctx.fillText(text, cw/2, ch/2);
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

            var COUNT = (innerWidth<720) ? 3200 : 6500;

            // Cibles : nom puis chaque valeur.
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

            // Séquence temporelle (ms) : index de phase + dispersion finale.
            var HOLD_BRAND=1700, HOLD_VAL=1150;
            var schedule=[], t=300; // petit délai d'entrée
            schedule.push({at:t, target:phases[0]}); t+=HOLD_BRAND;
            for(var k=1;k<phases.length;k++){ schedule.push({at:t, target:phases[k]}); t+=HOLD_VAL; }
            var DISPERSE_AT=t, END_AT=t+900;

            // Dispersion finale (explosion vers la caméra).
            var disperse=new Float32Array(COUNT*3);
            for(i=0;i<COUNT;i++){ disperse[i*3]=(Math.random()-0.5)*18; disperse[i*3+1]=(Math.random()-0.5)*14; disperse[i*3+2]=2+Math.random()*8; }

            window.addEventListener('resize', onResize);
            var start=performance.now(), targets=phases[0], lerp=0.10;
            var posAttr=geo.getAttribute('position');

            function loop(now){
                var ms=now-start;
                mat.opacity=Math.min(1.0, ms/500);
                // choisir la cible courante
                for(var s=schedule.length-1;s>=0;s--){ if(ms>=schedule[s].at){ targets=schedule[s].target; break; } }
                if(ms>=DISPERSE_AT){ targets=disperse; lerp=0.06; mat.opacity=Math.max(0, 1.0*(1-(ms-DISPERSE_AT)/900)); }
                var a=posAttr.array;
                for(var j=0;j<a.length;j++){ a[j]+= (targets[j]-a[j])*lerp; }
                posAttr.needsUpdate=true;
                pts.rotation.y=Math.sin(ms/2200)*0.022; // balancement très léger (texte reste lisible)
                renderer.render(scene,cam);
                if(ms<END_AT){ raf=requestAnimationFrame(loop); } else { finish(); }
            }
            raf=requestAnimationFrame(loop);
        }catch(e){ finish(); }
    }

    var s=document.createElement('script');
    s.src='https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
    s.async=true;
    s.onload=function(){ if(window.THREE) init(window.THREE); else finish(); };
    s.onerror=finish;
    document.head.appendChild(s);
})();
</script>
