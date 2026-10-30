<?php
/**
 * En-tête du site public : chargement du profil + navigation + bannière hero
 * Attend éventuellement $pageTitle défini avant l'include.
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$pdo = getPDO();
$profile = $pdo->query('SELECT * FROM profile WHERE id = 1')->fetch() ?: [];
$categories = $pdo->query('SELECT * FROM categories ORDER BY position ASC, name ASC')->fetchAll();
$allProjectsNav = $pdo->query('SELECT slug, title FROM projects')->fetchAll();
$allProjectSlugs = array_column($allProjectsNav, 'slug');

$pageTitle = $pageTitle ?? ('Portfolio de ' . ($profile['full_name'] ?? SITE_NAME));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?></title>
<meta name="description" content="<?= h($profile['tagline'] ?? '') ?>">
<?php if (!empty($profile['logo'])): ?>
  <link rel="icon" type="image/webp" href="<?= BASE_URL ?>/<?= h($profile['logo']) ?>">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Public+Sans:wght@400;500;600&family=Atkinson+Hyperlegible:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="bg-fx" aria-hidden="true">
  <div class="bg-light" id="bgLight"></div>
  <div class="bg-texture"></div>
</div>
<script 
  src="<?= BASE_URL ?>/assets/js/fond.js" defer>
  // Réapplique immédiatement les préférences d'accessibilité enregistrées,
  // avant l'affichage de la page, pour éviter tout effet de clignotement.
  (function () {
    try {
      var scale = localStorage.getItem('a11yFontScale');
      if (scale) document.documentElement.style.setProperty('--a11y-scale', scale);
      if (localStorage.getItem('a11yDyslexia') === '1') {
        document.documentElement.classList.add('a11y-dyslexia');
      }
    } catch (e) {}
  })();
</script>

<div class="a11y-toolbar" id="a11yToolbar">
  <button type="button" class="a11y-toggle" id="a11yToggle" aria-expanded="false" aria-controls="a11yPanel" aria-label="Ouvrir les options d'accessibilité">
    <span aria-hidden="true">&#9881;</span>
  </button>
  <div class="a11y-panel" id="a11yPanel" hidden>
    <h2 class="a11y-panel-title">Accessibilité</h2>

    <div class="a11y-group">
      <span class="a11y-group-label" id="a11yTextLabel">Taille du texte</span>
      <div class="a11y-buttons" role="group" aria-labelledby="a11yTextLabel">
        <button type="button" id="a11yTextMinus" aria-label="Réduire la taille du texte">A-</button>
        <button type="button" id="a11yTextReset" aria-label="Taille du texte par défaut">A</button>
        <button type="button" id="a11yTextPlus" aria-label="Agrandir la taille du texte">A+</button>
      </div>
    </div>

    <div class="a11y-group">
      <label class="a11y-switch" for="a11yDyslexiaToggle">
        <input type="checkbox" id="a11yDyslexiaToggle">
        <span>Mode dyslexie (police et espacement adaptés)</span>
      </label>
    </div>

    <div class="a11y-group">
      <span class="a11y-group-label" id="a11yLangLabel">Langue</span>
      <button type="button" class="a11y-translate-btn" id="a11yTranslateBtn" aria-describedby="a11yLangLabel">Translate to English</button>
    </div>
  </div>
</div>

<div id="google_translate_element" style="display:none;"></div>

<header class="site-header">
  <div class="container nav-bar">
        <a href="<?= BASE_URL ?>/index.php" class="brand">
      <?php if (!empty($profile['logo'])): ?>
        <img src="<?= BASE_URL ?>/<?= h($profile['logo']) ?>" alt="" class="brand-avatar brand-logo">
      <?php elseif (!empty($profile['avatar'])): ?>
        <img src="<?= BASE_URL ?>/<?= h($profile['avatar']) ?>" alt="" class="brand-avatar">
      <?php else: ?>
        <span class="brand-avatar" aria-hidden="true"></span>
      <?php endif; ?>
      <span><?= h($profile['full_name'] ?? SITE_NAME) ?></span>
    </a>

    <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="navLinks" aria-label="Ouvrir le menu">
      <span></span><span></span><span></span>
    </button>

    <ul class="nav-links" id="navLinks">
      <li><a href="<?= BASE_URL ?>/index.php#profil">Profil</a></li>
      <?php foreach ($categories as $cat): ?>
        <li><a href="<?= BASE_URL ?>/index.php#cat-<?= (int)$cat['id'] ?>"><?= h($cat['name']) ?></a></li>
      <?php endforeach; ?>
      <?php if (!empty($profile['email'])): ?>
        <li><a href="mailto:<?= h($profile['email']) ?>">Contact</a></li>
      <?php endif; ?>
            <?php if (!empty($profile['cv'])): ?>
        <li><a href="<?= BASE_URL ?>/<?= h($profile['cv']) ?>" class="nav-cv-btn" target="_blank" rel="noopener" download>Télécharger mon CV</a></li>
      <?php endif; ?>
      <?php if (!empty($allProjectsNav)): ?>
        <li>
          <span class="nav-random-wrap">
            <span class="nav-random-ring" aria-hidden="true"></span>
            <button type="button" class="nav-random-btn" id="randomProjectBtn"
                    data-projects="<?= h(json_encode($allProjectsNav, JSON_UNESCAPED_UNICODE)) ?>"
                    data-base="<?= BASE_URL ?>">
              <span class="nav-random-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                  <polyline points="16 3 21 3 21 8"></polyline>
                  <line x1="4" y1="20" x2="21" y2="3"></line>
                  <polyline points="21 16 21 21 16 21"></polyline>
                  <line x1="15" y1="15" x2="21" y2="21"></line>
                  <line x1="4" y1="4" x2="9" y2="9"></line>
                </svg>
              </span>
              <span id="randomProjectLabel">Projet aléatoire</span>
            </button>
          </span>
        </li>
      <?php endif; ?>
    </ul>
  </div>

  <div class="progress-wrap" id="progressWrap" data-slugs="<?= h(json_encode($allProjectSlugs)) ?>">
    <div class="scroll-progress" id="scrollProgress" role="progressbar" tabindex="0" aria-label="Projets consultés" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
      <div class="scroll-progress-bar" id="scrollProgressBar"></div>
    </div>
    <div class="progress-tooltip" id="progressTooltip" aria-hidden="true"></div>
  </div>
</header>