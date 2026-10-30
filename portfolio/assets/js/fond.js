/* ==========================================================
   fond.js — « lampe torche » du fond du site
   Une lumière suit la souris (le doigt sur téléphone). Elle est posée
   sous le motif craquelé (assets/img/fond_craquele.svg) : on ne la voit
   qu'à travers les fissures. Voir le bloc « FOND » dans style.css.
   - ne consomme rien quand la souris est immobile
   - désactivée si le visiteur demande « moins de mouvement »
   - règle aussi la hauteur du calque du motif (répétition sur toute la page)
   ========================================================== */
(function () {
  // Fond : une "lampe torche" qui suit la souris (le doigt sur téléphone), posée sous le motif craquelé
  function initBackground() {
    // Le calque du motif prend la hauteur de la page : le motif se répète donc sur toute la page,
    // même si le navigateur calcule autrement la hauteur de son parent.
    var fx = document.querySelector('.bg-fx');
    function syncHeight() {
      if (!fx) return;
      fx.style.setProperty('--doc-h', Math.max(document.body.offsetHeight, window.innerHeight) + 'px');
    }
    syncHeight();
    window.addEventListener('resize', syncHeight);
    window.addEventListener('load', syncHeight);
    if (window.ResizeObserver) new ResizeObserver(syncHeight).observe(document.body);

    var light = document.getElementById('bgLight');
    if (!light) return;

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var half = light.offsetWidth / 2;                                   // rayon de la lumière (défini en CSS)
    var x = window.innerWidth * 0.72, y = window.innerHeight * 0.28;    // position actuelle
    var tx = x, ty = y;                                                 // position visée
    var raf = 0, pointerOn = false;

    function draw() {
      light.style.transform = 'translate3d(' + (x - half).toFixed(1) + 'px,' + (y - half).toFixed(1) + 'px,0)';
    }
    function frame() {
      raf = 0;
      x += (tx - x) * 0.16;
      y += (ty - y) * 0.16;
      draw();
      if (Math.abs(tx - x) > 0.4 || Math.abs(ty - y) > 0.4) raf = requestAnimationFrame(frame);
    }
    function go() { if (!raf) raf = requestAnimationFrame(frame); }

    // Sans souris ni doigt : la lumière reste à droite et descend doucement avec le défilement
    function rest() {
      var max = document.documentElement.scrollHeight - window.innerHeight;
      var p = max > 0 ? window.scrollY / max : 0;
      tx = window.innerWidth * 0.72;
      ty = window.innerHeight * (0.22 + 0.5 * p);
    }
    function on() { pointerOn = true; light.classList.add('is-active'); }
    function off() { pointerOn = false; light.classList.remove('is-active'); }

    rest(); x = tx; y = ty; draw();
    if (reduceMotion) return;                                           // mouvement réduit : la lumière reste fixe

    // Souris
    window.addEventListener('pointermove', function (e) {
      if (e.pointerType === 'touch') return;
      tx = e.clientX; ty = e.clientY; on(); go();
    }, { passive: true });
    document.documentElement.addEventListener('mouseleave', function () { off(); rest(); go(); });

    // Téléphone : la lumière suit le doigt, même pendant le défilement
    function touch(e) {
      var t = e.touches[0];
      if (!t) return;
      tx = t.clientX; ty = t.clientY; on(); go();
    }
    window.addEventListener('touchstart', touch, { passive: true });
    window.addEventListener('touchmove', touch, { passive: true });
    window.addEventListener('touchend', off, { passive: true });        // la lumière reste où le doigt s'est levé, atténuée
    window.addEventListener('touchcancel', off, { passive: true });

    window.addEventListener('scroll', function () { if (!pointerOn) { rest(); go(); } }, { passive: true });
    window.addEventListener('resize', function () {
      half = light.offsetWidth / 2;
      if (!pointerOn) { rest(); go(); }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBackground);
  } else {
    initBackground();
  }
})();
