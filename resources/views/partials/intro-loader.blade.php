{{-- Intro 3D « Anneau d'or + poussière d'étoiles » (Three.js, rejoué à chaque rechargement) --}}
<div id="fsl-intro" role="presentation" aria-hidden="true">
    <canvas id="fsl-intro-canvas"></canvas>
    <div class="fsl-intro__center">
        <p class="fsl-intro__name">FEMME SANS LIMITES</p>
        <p class="fsl-intro__tag">Brise tes limites, révèle ta puissance</p>
    </div>
    <button type="button" id="fsl-intro-skip" class="fsl-intro__skip">Passer l'intro &rsaquo;</button>
</div>

<style>
#fsl-intro{position:fixed;inset:0;z-index:99999;background:#140A0E;overflow:hidden;opacity:1;transition:opacity .7s ease;}
#fsl-intro.fsl-intro--hide{opacity:0;}
#fsl-intro.fsl-intro--done{display:none!important;}
#fsl-intro::before,#fsl-intro::after{content:"";position:absolute;border-radius:9999px;filter:blur(10px);pointer-events:none;}
#fsl-intro::before{width:70vw;height:70vw;top:-20vw;right:-15vw;background:radial-gradient(circle,rgba(217,30,110,.18),transparent 60%);}
#fsl-intro::after{width:60vw;height:60vw;bottom:-20vw;left:-15vw;background:radial-gradient(circle,rgba(201,168,76,.13),transparent 60%);}
#fsl-intro-canvas{position:absolute;inset:0;width:100%;height:100%;display:block;}
#fsl-intro .fsl-intro__center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:0 24px;pointer-events:none;}
#fsl-intro .fsl-intro__name{
    margin:0;font-family:'Playfair Display',Georgia,serif;font-weight:700;
    font-size:clamp(20px,5vw,34px);letter-spacing:.2em;
    background:linear-gradient(90deg,#D91E6E 0%,#F2C4D8 30%,#C9A84C 55%,#D91E6E 80%);
    background-size:200% auto;-webkit-background-clip:text;background-clip:text;color:transparent;
    opacity:0;animation:fsl-fade .9s ease .5s forwards, fsl-shimmer 3s linear .5s infinite;
    text-shadow:0 2px 30px rgba(217,30,110,.25);
}
#fsl-intro .fsl-intro__tag{margin:12px 0 0;font-size:12px;letter-spacing:.06em;color:rgba(255,255,255,.5);opacity:0;animation:fsl-fade .9s ease .9s forwards;}
#fsl-intro .fsl-intro__skip{position:absolute;bottom:24px;right:24px;background:transparent;border:0;color:rgba(255,255,255,.45);font-size:12px;cursor:pointer;letter-spacing:.04em;transition:color .2s;opacity:0;animation:fsl-fade .6s ease 1.4s forwards;}
#fsl-intro .fsl-intro__skip:hover{color:#fff;}
@keyframes fsl-fade{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
@keyframes fsl-shimmer{0%{background-position:-150% center;}100%{background-position:150% center;}}
@media (prefers-reduced-motion:reduce){#fsl-intro{display:none!important;}}
</style>

<script>
(function(){
    var el = document.getElementById('fsl-intro');
    if(!el) return;

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function webglOK(){ try{ var c=document.createElement('canvas'); return !!(window.WebGLRenderingContext && (c.getContext('webgl')||c.getContext('experimental-webgl'))); }catch(e){ return false; } }

    if(reduce){ el.classList.add('fsl-intro--done'); return; }

    var html = document.documentElement;
    var prevOverflow = html.style.overflow;
    html.style.overflow = 'hidden';
    var raf = null, renderer = null, finished = false;

    function finish(){
        if(finished) return; finished = true;
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
    // Filet de sécurité (si le CDN ou le WebGL échoue) :
    var safety = setTimeout(finish, 5000);

    // Pas de WebGL → on garde juste l'écran de marque animé (CSS) ~2.4s puis on révèle.
    if(!webglOK()){ setTimeout(finish, 2400); return; }

    function onResize(){
        if(!renderer) return;
        var w = window.innerWidth, h = window.innerHeight;
        renderer.setSize(w, h);
        cam.aspect = w/h; cam.updateProjectionMatrix();
    }
    var cam;

    function init(THREE){
        clearTimeout(safety);
        try{
            var canvas = document.getElementById('fsl-intro-canvas');
            renderer = new THREE.WebGLRenderer({canvas:canvas, antialias:true, alpha:true});
            renderer.setPixelRatio(Math.min(window.devicePixelRatio||1, 2));
            renderer.setSize(window.innerWidth, window.innerHeight);

            var scene = new THREE.Scene();
            cam = new THREE.PerspectiveCamera(50, window.innerWidth/window.innerHeight, 0.1, 100);
            cam.position.set(0, 0, 6.2);

            // ── Anneau métallique rose-or ──
            var ring = new THREE.Mesh(
                new THREE.TorusGeometry(2.05, 0.5, 48, 180),
                new THREE.MeshStandardMaterial({ color:0xC9A84C, metalness:1.0, roughness:0.26, emissive:0xD91E6E, emissiveIntensity:0.07 })
            );
            ring.rotation.x = 0.5;
            ring.scale.setScalar(0.01);
            scene.add(ring);

            // ── Lumières (reflets mobiles) ──
            scene.add(new THREE.AmbientLight(0x442233, 0.7));
            var lRose = new THREE.PointLight(0xD91E6E, 1.5, 50); lRose.position.set(5,3,5); scene.add(lRose);
            var lGold = new THREE.PointLight(0xF0D890, 1.4, 50); lGold.position.set(-5,-2,4); scene.add(lGold);
            var lWhite = new THREE.PointLight(0xffffff, 0.8, 60); lWhite.position.set(0,4,6); scene.add(lWhite);

            // ── Poussière d'étoiles ──
            var COUNT = (window.innerWidth < 768) ? 1100 : 2200;
            var pos = new Float32Array(COUNT*3), col = new Float32Array(COUNT*3);
            var rose=[0.85,0.12,0.43], gold=[0.82,0.70,0.34];
            for(var i=0;i<COUNT;i++){
                var r = 3 + Math.random()*9, th = Math.random()*Math.PI*2, ph = Math.acos(2*Math.random()-1);
                pos[i*3]   = r*Math.sin(ph)*Math.cos(th);
                pos[i*3+1] = r*Math.sin(ph)*Math.sin(th);
                pos[i*3+2] = r*Math.cos(ph) - 2;
                var t = Math.random(), c = (t<0.5?rose:gold);
                col[i*3]=c[0]; col[i*3+1]=c[1]; col[i*3+2]=c[2];
            }
            var pg = new THREE.BufferGeometry();
            pg.setAttribute('position', new THREE.BufferAttribute(pos,3));
            pg.setAttribute('color', new THREE.BufferAttribute(col,3));
            var dust = new THREE.Points(pg, new THREE.PointsMaterial({ size:0.055, vertexColors:true, transparent:true, opacity:0, blending:THREE.AdditiveBlending, depthWrite:false }));
            scene.add(dust);

            window.addEventListener('resize', onResize);

            var start = performance.now();
            function loop(now){
                var t = (now - start)/1000;
                // entrée de l'anneau (scale élastique)
                var s = Math.min(1, t/0.8);
                ring.scale.setScalar(0.2 + 0.8*(1-Math.pow(1-s,3)));
                ring.rotation.y += 0.010;
                ring.rotation.x += 0.004;
                // reflets : lumières qui orbitent
                lRose.position.set(Math.cos(t*0.9)*6, Math.sin(t*0.7)*4, 5);
                lGold.position.set(Math.cos(t*0.9+2.1)*6, Math.sin(t*0.7+2.1)*4, 4);
                // poussière : fondu + lente rotation + scintillement
                dust.material.opacity = Math.min(0.9, t*0.6);
                dust.rotation.y += 0.0006; dust.rotation.x += 0.0002;
                dust.material.size = 0.05 + Math.sin(t*2.2)*0.012;
                renderer.render(scene, cam);
                raf = requestAnimationFrame(loop);
            }
            raf = requestAnimationFrame(loop);

            // Sortie : on révèle le site (léger dolly + fondu géré par finish()).
            setTimeout(finish, 3200);
        }catch(e){ finish(); }
    }

    // Chargement non bloquant de Three.js (CDN), init à la fin.
    var s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
    s.async = true;
    s.onload = function(){ if(window.THREE) init(window.THREE); else finish(); };
    s.onerror = finish;
    document.head.appendChild(s);
})();
</script>
