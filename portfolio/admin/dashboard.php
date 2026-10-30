<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

$activeNav = 'dashboard';
$pageTitle = 'Tableau de bord';
require_once __DIR__ . '/includes/admin_header.php';

$nbCategories = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$nbProjects   = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$nbFeatured   = (int)$pdo->query('SELECT COUNT(*) FROM projects WHERE featured = 1')->fetchColumn();

$recentProjects = $pdo->query('
    SELECT p.*, c.name AS category_name
    FROM projects p JOIN categories c ON c.id = p.category_id
    ORDER BY p.created_at DESC LIMIT 5
')->fetchAll();
?>

<div class="page-head">
  <h1>Tableau de bord</h1>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="num"><?= $nbCategories ?></div>
    <div class="label">Catégories</div>
  </div>
  <div class="stat-card">
    <div class="num"><?= $nbProjects ?></div>
    <div class="label">Projets</div>
  </div>
  <div class="stat-card">
    <div class="num"><?= $nbFeatured ?></div>
    <div class="label">Projets mis en avant</div>
  </div>
</div>

<div class="card">
  <h2 style="font-size:1.1rem;">Actions rapides</h2>
  <div class="form-actions">
    <a href="<?= BASE_URL ?>/admin/category_form.php" class="btn btn-primary">+ Nouvelle catégorie</a>
    <a href="<?= BASE_URL ?>/admin/project_form.php" class="btn btn-primary">+ Nouveau projet</a>
    <a href="<?= BASE_URL ?>/admin/profile.php" class="btn">Modifier mon profil</a>
  </div>
</div>

<div class="card">
  <h2 style="font-size:1.1rem;">Derniers projets ajoutés</h2>
  <?php if (empty($recentProjects)): ?>
    <p style="color:var(--ink-soft)">Aucun projet pour l'instant.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Projet</th><th>Catégorie</th><th>Ajouté le</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($recentProjects as $p): ?>
            <tr>
              <td data-label="Projet"><?= h($p['title']) ?></td>
              <td data-label="Catégorie"><?= h($p['category_name']) ?></td>
              <td data-label="Ajouté le"><?= h(date('d/m/Y', strtotime($p['created_at']))) ?></td>
              <td data-label=""><a href="<?= BASE_URL ?>/admin/project_form.php?id=<?= (int)$p['id'] ?>">Modifier</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
