<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pdo = getPDO();

$projectId = (int)($_GET['project_id'] ?? $_POST['project_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id');
$stmt->execute([':id' => $projectId]);
$project = $stmt->fetch();

if (!$project) {
    setFlash('error', 'Projet introuvable.');
    header('Location: ' . BASE_URL . '/admin/projects.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $caption = trim($_POST['caption'] ?? '');

    try {
        $imagePath = handleImageUpload($_FILES['image'] ?? [], 'gallery');
    } catch (RuntimeException $e) {
        $imagePath = null;
        $errors[] = $e->getMessage();
    }

    if (empty($errors) && !$imagePath) {
        $errors[] = 'Merci de choisir une image à ajouter.';
    }

    if (empty($errors)) {
        $maxPos = (int)$pdo->query('SELECT COALESCE(MAX(position), -1) FROM project_images WHERE project_id = ' . (int)$projectId)->fetchColumn();
        $stmt = $pdo->prepare('INSERT INTO project_images (project_id, image, caption, position) VALUES (:pid, :image, :caption, :position)');
        $stmt->execute([
            ':pid' => $projectId,
            ':image' => $imagePath,
            ':caption' => $caption,
            ':position' => $maxPos + 1,
        ]);
        setFlash('success', 'Image ajoutée à la galerie.');
        header('Location: ' . BASE_URL . '/admin/project_gallery.php?project_id=' . $projectId);
        exit;
    }
}

$stmt = $pdo->prepare('SELECT * FROM project_images WHERE project_id = :pid ORDER BY position ASC, id ASC');
$stmt->execute([':pid' => $projectId]);
$images = $stmt->fetchAll();

$activeNav = 'projects';
$pageTitle = 'Galerie — ' . $project['title'];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="page-head">
  <h1>Galerie du projet</h1>
</div>

<p style="margin-top:-10px; color:var(--ink-soft);">
  Projet : <strong><?= h($project['title']) ?></strong>
  &middot; <a href="<?= BASE_URL ?>/admin/project_form.php?id=<?= (int)$project['id'] ?>">&larr; Retour à la fiche projet</a>
</p>

<div class="card" style="max-width:640px;">
  <h2 style="font-size:1.05rem;">Ajouter une image</h2>

  <?php foreach ($errors as $err): ?>
    <div class="flash flash-error"><?= h($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="<?= BASE_URL ?>/admin/project_gallery.php?project_id=<?= (int)$projectId ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
    <input type="hidden" name="project_id" value="<?= (int)$projectId ?>">

    <div class="form-row">
      <label for="image">Image</label>
      <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp,image/gif" required>
      <p class="hint">Formats acceptés : JPG, PNG, WEBP, GIF — 5 Mo maximum. Elle sera automatiquement convertie en WebP (plus léger, plus rapide à charger).</p>
    </div>

    <div class="form-row">
      <label for="caption">Légende (facultatif)</label>
      <input type="text" id="caption" name="caption" placeholder="Ex : Version finale, avant/après, détail typographique...">
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Ajouter à la galerie</button>
    </div>
  </form>
</div>

<div class="card">
  <h2 style="font-size:1.05rem;">Images de la galerie (<?= count($images) ?>)</h2>

  <?php if (empty($images)): ?>
    <p style="color:var(--ink-soft)">Aucune image dans la galerie pour le moment.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th></th><th>Légende</th><th>Ordre</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($images as $img): ?>
            <tr>
              <td data-label=""><img src="<?= BASE_URL ?>/<?= h($img['image']) ?>" alt="" class="thumb-sm" style="width:72px;height:52px;"></td>
              <td data-label="Légende"><?= h($img['caption']) ?: '<span style="color:var(--ink-soft);">—</span>' ?></td>
              <td data-label="Ordre">
                <form method="post" action="<?= BASE_URL ?>/admin/gallery_reorder.php" style="display:flex; gap:6px; align-items:center;">
                  <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                  <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                  <input type="hidden" name="project_id" value="<?= (int)$projectId ?>">
                  <input type="number" name="position" value="<?= (int)$img['position'] ?>" style="width:70px;">
                  <button type="submit" class="btn btn-sm">OK</button>
                </form>
              </td>
              <td data-label="">
                <form method="post" action="<?= BASE_URL ?>/admin/gallery_delete.php" onsubmit="return confirm('Supprimer cette image de la galerie ?');">
                  <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                  <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                  <input type="hidden" name="project_id" value="<?= (int)$projectId ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="hint" style="margin-top:12px;">L'ordre détermine la position dans la galerie affichée sur le site (du plus petit au plus grand nombre).</p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
