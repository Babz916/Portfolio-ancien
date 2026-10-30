<?php
/**
 * En-tête commun aux pages du back-office.
 * Attend $activeNav ('dashboard'|'categories'|'projects'|'profile') et $pageTitle.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$flash = getFlash();
$activeNav = $activeNav ?? '';
$pageTitle = $pageTitle ?? 'Back-office';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?> — Back-office</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>

<a href="#admin-main-content" class="skip-link">Aller au contenu principal</a>

<div class="mobile-topbar">
  <strong><?= h($pageTitle) ?></strong>
  <button id="sidebarToggle" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="adminSidebar">&#9776;</button>
</div>

<div class="admin-wrapper">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-brand">Back-office</div>
    <nav aria-label="Navigation du back-office">
      <ul class="admin-nav">
        <li><a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>" <?= $activeNav === 'dashboard' ? 'aria-current="page"' : '' ?>>Tableau de bord</a></li>
        <li><a href="<?= BASE_URL ?>/admin/categories.php" class="<?= $activeNav === 'categories' ? 'active' : '' ?>" <?= $activeNav === 'categories' ? 'aria-current="page"' : '' ?>>Catégories</a></li>
        <li><a href="<?= BASE_URL ?>/admin/projects.php" class="<?= $activeNav === 'projects' ? 'active' : '' ?>" <?= $activeNav === 'projects' ? 'aria-current="page"' : '' ?>>Projets</a></li>
        <li><a href="<?= BASE_URL ?>/admin/profile.php" class="<?= $activeNav === 'profile' ? 'active' : '' ?>" <?= $activeNav === 'profile' ? 'aria-current="page"' : '' ?>>Mon profil</a></li>
        <li class="divider"></li>
        <li><a href="<?= BASE_URL ?>/index.php" target="_blank" rel="noopener">Voir le site &#8599;<span class="sr-only"> (nouvel onglet)</span></a></li>
        <li><a href="<?= BASE_URL ?>/admin/logout.php">Se déconnecter</a></li>
      </ul>
    </nav>
  </aside>

  <main class="admin-main" id="admin-main-content">
    <?php if ($flash): ?>
      <div class="flash flash-<?= h($flash['type']) ?>" role="alert"><?= h($flash['message']) ?></div>
    <?php endif; ?>
