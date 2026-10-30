<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

$activeNav = 'projects';
$pageTitle = 'Projets';
require_once __DIR__ . '/includes/admin_header.php';

$categoryFilter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$sql = '
    SELECT p.*, c.name AS category_name
    FROM projects p JOIN categories c ON c.id = p.category_id
';
$params = [];
if ($categoryFilter) {
    $sql .= ' WHERE p.category_id = :cid';
    $params[':cid'] = $categoryFilter;
}
$sql .= ' ORDER BY c.name ASC, p.position ASC, p.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
?>

<div class="page-head">
  <h1>Projets</h1>
  <a href="<?= BASE_URL ?>/admin/project_form.php" class="btn btn-primary">+ Nouveau projet</a>
</div>

<?php if (!empty($categories)): ?>
<div class="card" style="padding:14px 24px;">
  <form method="get" action="<?= BASE_URL ?>/admin/projects.php" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
    <label for="category_id" style="font-size:.88rem; font-weight:600;">Filtrer par catégorie :</label>
    <select id="category_id" name="category_id" onchange="this.form.submit()" style="width:auto;">
      <option value="0">Toutes les catégories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $categoryFilter === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <?php if (empty($categories)): ?>
    <p style="color:var(--ink-soft)">Vous devez d'abord créer une catégorie avant d'ajouter un projet. <a href="<?= BASE_URL ?>/admin/category_form.php">Créer une catégorie &rarr;</a></p>
  <?php elseif (empty($projects)): ?>
    <p style="color:var(--ink-soft)">Aucun projet pour le moment.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th></th><th>Titre</th><th>Catégorie</th><th>Mis en avant</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($projects as $p): ?>
            <tr>
              <td data-label="">
                <?php if (!empty($p['image'])): ?>
                  <img src="<?= BASE_URL ?>/<?= h($p['image']) ?>" alt="" class="thumb-sm">
                <?php endif; ?>
              </td>
              <td data-label="Titre"><strong><?= h($p['title']) ?></strong></td>
              <td data-label="Catégorie"><?= h($p['category_name']) ?></td>
              <td data-label="Mis en avant"><?= $p['featured'] ? 'Oui' : 'Non' ?></td>
              <td data-label="">
                <div class="actions">
                  <a href="<?= BASE_URL ?>/admin/project_form.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm">Modifier</a>
                  <form method="post" action="<?= BASE_URL ?>/admin/project_delete.php" onsubmit="return confirm('Supprimer ce projet ?');">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
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
