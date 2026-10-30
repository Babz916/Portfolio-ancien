<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

$activeNav = 'categories';
$pageTitle = 'Catégories';
require_once __DIR__ . '/includes/admin_header.php';

$categories = $pdo->query('
    SELECT c.*, (SELECT COUNT(*) FROM projects p WHERE p.category_id = c.id) AS nb_projects
    FROM categories c
    ORDER BY c.position ASC, c.name ASC
')->fetchAll();
?>

<div class="page-head">
  <h1>Catégories</h1>
  <a href="<?= BASE_URL ?>/admin/category_form.php" class="btn btn-primary">+ Nouvelle catégorie</a>
</div>

<div class="card">
  <?php if (empty($categories)): ?>
    <p style="color:var(--ink-soft)">Aucune catégorie pour le moment. Créez-en une pour commencer à organiser vos projets.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Nom</th><th>Description</th><th>Projets</th><th>Ordre</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td data-label="Nom"><strong><?= h($cat['name']) ?></strong></td>
              <td data-label="Description"><?= h($cat['description']) ?></td>
              <td data-label="Projets"><?= (int)$cat['nb_projects'] ?></td>
              <td data-label="Ordre"><?= (int)$cat['position'] ?></td>
              <td data-label="">
                <div class="actions">
                  <a href="<?= BASE_URL ?>/admin/category_form.php?id=<?= (int)$cat['id'] ?>" class="btn btn-sm">Modifier</a>
                  <form method="post" action="<?= BASE_URL ?>/admin/category_delete.php" onsubmit="return confirm('Supprimer cette catégorie ? Les projets associés seront supprimés aussi.');">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
