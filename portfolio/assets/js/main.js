document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('navToggle');
  var links = document.getElementById('navLinks');
  if (toggle && links) {
    toggle.addEventListener('click', function () {
      var open = links.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    links.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        links.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  initAccessibilityToolbar();
  trackVisitedProject();
  initProgressBar();
  initRandomProject();
});

var VISITED_KEY = 'visitedProjects';

function getVisitedProjects() {
  try {
    var list = JSON.parse(localStorage.getItem(VISITED_KEY) || '[]');
    return Array.isArray(list) ? list : [];
  } catch (e) {
    return [];
  }
}

// Sur la fiche d'un projet : on enregistre que ce projet a été ouvert
function trackVisitedProject() {
  var meta = document.getElementById('projectMeta');
  if (!meta) return;
  var slug = meta.getAttribute('data-slug');
  if (!slug) return;
  var visited = getVisitedProjects();
  if (visited.indexOf(slug) === -1) {
    visited.push(slug);
    try { localStorage.setItem(VISITED_KEY, JSON.stringify(visited)); } catch (e) {}
  }
}

// Barre de progression : projets consultés / total (visible sur toutes les pages publiques)
function initProgressBar() {
  var wrap = document.getElementById('progressWrap');
  var track = document.getElementById('scrollProgress');
  var bar = document.getElementById('scrollProgressBar');
  var tooltip = document.getElementById('progressTooltip');
  if (!wrap || !track || !bar || !tooltip) return;

  var slugs = [];
  try { slugs = JSON.parse(wrap.getAttribute('data-slugs') || '[]'); } catch (e) {}
  var total = slugs.length;
  if (!total) { wrap.hidden = true; return; }

  var cards = document.querySelectorAll('.project-card[data-slug]');

  function update() {
    var visited = getVisitedProjects();
    var seen = 0;
    slugs.forEach(function (slug) {
      if (visited.indexOf(slug) !== -1) seen++;
    });
    var remaining = total - seen;
    var pct = Math.round((seen / total) * 100);

    bar.style.width = pct + '%';
    track.setAttribute('aria-valuenow', pct);

    if (remaining <= 0) {
      tooltip.innerHTML = 'Vous avez consulté les <strong>' + total + '</strong> projet' + (total > 1 ? 's' : '');
    } else {
      tooltip.innerHTML = '<strong>' + remaining + '</strong> projet' + (remaining > 1 ? 's' : '') + ' restant' + (remaining > 1 ? 's' : '') + ' à consulter sur ' + total;
    }
    track.setAttribute('aria-valuetext', tooltip.textContent);

    // Repère "Consulté" sur les cartes de l'accueil
    cards.forEach(function (card) {
      card.classList.toggle('is-visited', visited.indexOf(card.getAttribute('data-slug')) !== -1);
    });
  }

  update();
  // Mise à jour au retour depuis une fiche (bouton précédent) et entre onglets
  window.addEventListener('pageshow', update);
  window.addEventListener('storage', update);
}

// Bouton "Projet aléatoire" dans la nav : petite animation façon machine à sous puis redirection
function initRandomProject() {
  var btn = document.getElementById('randomProjectBtn');
  var label = document.getElementById('randomProjectLabel');
  if (!btn || !label) return;

  var projects = [];
  try { projects = JSON.parse(btn.getAttribute('data-projects') || '[]'); } catch (e) {}
  if (!projects.length) return;

  var base = btn.getAttribute('data-base') || '';
  var defaultLabel = label.textContent;
  var currentMeta = document.getElementById('projectMeta');
  var currentSlug = currentMeta ? currentMeta.getAttribute('data-slug') : null;

  btn.addEventListener('click', function () {
    if (btn.classList.contains('is-rolling')) return;

    var candidates = projects.filter(function (p) { return p.slug !== currentSlug; });
    if (!candidates.length) candidates = projects;
    var target = candidates[Math.floor(Math.random() * candidates.length)];

    btn.classList.add('is-rolling');
    btn.disabled = true;

    var ticks = 0;
    var maxTicks = 9;
    var interval = setInterval(function () {
      var r = projects[Math.floor(Math.random() * projects.length)];
      label.textContent = r.title;
      ticks++;
      if (ticks >= maxTicks) {
        clearInterval(interval);
        label.textContent = target.title;
        setTimeout(function () {
          window.location.href = base + '/project.php?slug=' + encodeURIComponent(target.slug);
        }, 280);
      }
    }, 80);
  });

  window.addEventListener('pageshow', function () {
    btn.classList.remove('is-rolling');
    btn.disabled = false;
    label.textContent = defaultLabel;
  });
}

function initAccessibilityToolbar() {
  var a11yToggle = document.getElementById('a11yToggle');
  var a11yPanel = document.getElementById('a11yPanel');
  if (!a11yToggle || !a11yPanel) return;

  // ---- Ouverture / fermeture du panneau ----
  a11yToggle.addEventListener('click', function () {
    var open = a11yPanel.hasAttribute('hidden');
    if (open) {
      a11yPanel.removeAttribute('hidden');
    } else {
      a11yPanel.setAttribute('hidden', '');
    }
    a11yToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  document.addEventListener('click', function (e) {
    var toolbar = document.getElementById('a11yToolbar');
    if (!toolbar.contains(e.target) && !a11yPanel.hasAttribute('hidden')) {
      a11yPanel.setAttribute('hidden', '');
      a11yToggle.setAttribute('aria-expanded', 'false');
    }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !a11yPanel.hasAttribute('hidden')) {
      a11yPanel.setAttribute('hidden', '');
      a11yToggle.setAttribute('aria-expanded', 'false');
      a11yToggle.focus();
    }
  });

  // ---- Taille du texte ----
  var MIN_SCALE = 0.8;
  var MAX_SCALE = 1.5;
  var STEP = 0.1;

  function currentScale() {
    var stored = parseFloat(localStorage.getItem('a11yFontScale'));
    return isNaN(stored) ? 1 : stored;
  }
  function applyScale(scale) {
    scale = Math.round(Math.min(MAX_SCALE, Math.max(MIN_SCALE, scale)) * 100) / 100;
    document.documentElement.style.setProperty('--a11y-scale', scale);
    try { localStorage.setItem('a11yFontScale', scale); } catch (e) {}
  }

  var textPlus = document.getElementById('a11yTextPlus');
  var textMinus = document.getElementById('a11yTextMinus');
  var textReset = document.getElementById('a11yTextReset');

  if (textPlus) textPlus.addEventListener('click', function () { applyScale(currentScale() + STEP); });
  if (textMinus) textMinus.addEventListener('click', function () { applyScale(currentScale() - STEP); });
  if (textReset) textReset.addEventListener('click', function () {
    document.documentElement.style.removeProperty('--a11y-scale');
    try { localStorage.removeItem('a11yFontScale'); } catch (e) {}
  });

  // ---- Mode dyslexie ----
  var dyslexiaToggle = document.getElementById('a11yDyslexiaToggle');
  if (dyslexiaToggle) {
    dyslexiaToggle.checked = document.documentElement.classList.contains('a11y-dyslexia');
    dyslexiaToggle.addEventListener('change', function () {
      document.documentElement.classList.toggle('a11y-dyslexia', dyslexiaToggle.checked);
      try { localStorage.setItem('a11yDyslexia', dyslexiaToggle.checked ? '1' : '0'); } catch (e) {}
    });
  }

  // ---- Traduction en anglais (widget Google Translate) ----
  var translateBtn = document.getElementById('a11yTranslateBtn');
  if (translateBtn) {
    function getCookie(name) {
      var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
      return match ? decodeURIComponent(match[2]) : '';
    }
    function setTranslateLabel() {
      var isEnglish = getCookie('googtrans').indexOf('/en') !== -1;
      translateBtn.textContent = isEnglish ? 'Revenir au français' : 'Translate to English';
    }
    setTranslateLabel();

    translateBtn.addEventListener('click', function () {
      var isEnglish = getCookie('googtrans').indexOf('/en') !== -1;
      var host = window.location.hostname;
      if (isEnglish) {
        document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;domain=' + host;
        document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/';
      } else {
        document.cookie = 'googtrans=/fr/en;path=/;domain=' + host;
        document.cookie = 'googtrans=/fr/en;path=/';
      }
      window.location.reload();
    });
  }
}