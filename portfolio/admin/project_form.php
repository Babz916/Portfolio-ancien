<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pdo = getPDO();

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : null);
$project = [
    'category_id' => '', 'title' => '', 'short_description' => '', 'content' => '',
    'image' => '', 'project_url' => '', 'github_url' => '', 'featured' => 0, 'position' => 0,
];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        setFlash('error', 'Projet introuvable.');
        header('Location: ' . BASE_URL . '/admin/projects.php');
        exit;
    }
    $project = $found;
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $project['category_id'] = (int)($_POST['category_id'] ?? 0);
    $project['title'] = trim($_POST['title'] ?? '');
    $project['short_description'] = trim($_POST['short_description'] ?? '');
    $project['content'] = trim($_POST['content'] ?? '');
    $project['project_url'] = trim($_POST['project_url'] ?? '');
    $project['github_url'] = trim($_POST['github_url'] ?? '');
    $project['featured'] = isset($_POST['featured']) ? 1 : 0;
    $project['position'] = (int)($_POST['position'] ?? 0);

    if ($project['title'] === '') {
        $errors[] = 'Le titre du projet est obligatoire.';
    }
    if (!$project['category_id']) {
        $errors[] = 'Merci de choisir une catégorie.';
    }

    $newImage = null;
    if (empty($errors)) {
        try {
            $newImage = handleImageUpload($_FILES['image'] ?? [], 'projects');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $slug = uniqueSlug($pdo, 'projects', makeSlug($project['title']), $id);
        $imageToSave = $newImage ?? $project['image'];

        if ($id) {
            if ($newImage) {
                deleteUploadedImage($project['image']);
            }
            $stmt = $pdo->prepare('
                UPDATE projects SET category_id=:cat, title=:title, slug=:slug, short_description=:short,
                    content=:content, image=:image, project_url=:purl, github_url=:gurl,
                    featured=:featured, position=:position
                WHERE id=:id
            ');
            $stmt->execute([
                ':cat' => $project['category_id'], ':title' => $project['title'], ':slug' => $slug,
                ':short' => $project['short_description'], ':content' => $project['content'],
                ':image' => $imageToSave, ':purl' => $project['project_url'], ':gurl' => $project['github_url'],
                ':featured' => $project['featured'], ':position' => $project['position'], ':id' => $id,
            ]);
            setFlash('success', 'Projet mis à jour.');
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO projects (category_id, title, slug, short_description, content, image, project_url, github_url, featured, position)
                VALUES (:cat, :title, :slug, :short, :content, :image, :purl, :gurl, :featured, :position)
            ');
            $stmt->execute([
                ':cat' => $project['category_id'], ':title' => $project['title'], ':slug' => $slug,
                ':short' => $project['short_description'], ':content' => $project['content'],
                ':image' => $imageToSave, ':purl' => $project['project_url'], ':gurl' => $project['github_url'],
                ':featured' => $project['featured'], ':position' => $project['position'],
            ]);
            setFlash('success', 'Projet créé.');
        }
        header('Location: ' . BASE_URL . '/admin/projects.php');
        exit;
    }
}

$activeNav = 'projects';
$pageTitle = $id ? 'Modifier le projet' : 'Nouveau projet';
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="page-head">
  <h1><?= $id ? 'Modifier le projet' : 'Nouveau projet' ?></h1>
</div>

<div class="card" style="max-width:760px;">
  <?php foreach ($errors as $err): ?>
    <div class="flash flash-error"><?= h($err) ?></div>
  <?php endforeach; ?>

  <?php if (empty($categories)): ?>
    <p style="color:var(--ink-soft)">Vous devez d'abord créer une catégorie. <a href="<?= BASE_URL ?>/admin/category_form.php">Créer une catégorie &rarr;</a></p>
  <?php else: ?>
    <form method="post" action="<?= BASE_URL ?>/admin/project_form.php<?= $id ? '?id=' . (int)$id : '' ?>" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
      <?php if ($id): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

      <div class="form-grid">
        <div class="form-row">
          <label for="title">Titre du projet</label>
          <input type="text" id="title" name="title" value="<?= h($project['title']) ?>" required>
        </div>
        <div class="form-row">
          <label for="category_id">Catégorie</label>
          <select id="category_id" name="category_id" required>
            <option value="">— Choisir —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (int)$project['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <label for="short_description">Description courte (affichée sur la carte du projet)</label>
        <input type="text" id="short_description" name="short_description" value="<?= h($project['short_description']) ?>">
      </div>

      <div class="form-row">
        <label for="content">Description complète (affichée sur la page du projet)</label>
        <textarea id="content" name="content" rows="6"><?= h($project['content']) ?></textarea>
      </div>

      <div class="form-grid">
        <div class="form-row">
          <label for="project_url">Lien du site / de la démo</label>
          <input type="url" id="project_url" name="project_url" value="<?= h($project['project_url']) ?>" placeholder="https://...">
        </div>
        <div class="form-row">
          <label for="github_url">Lien du code source</label>
          <input type="url" id="github_url" name="github_url" value="<?= h($project['github_url']) ?>" placeholder="https://github.com/...">
        </div>
      </div>

      <div class="form-row">
        <label for="image">Image principale (affichée sur la carte du projet)</label>
        <?php if (!empty($project['image'])): ?>
          <div class="current-image"><img src="<?= BASE_URL ?>/<?= h($project['image']) ?>" alt=""></div>
        <?php endif; ?>
        <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp,image/gif">
        <p class="hint">Formats acceptés : JPG, PNG, WEBP, GIF — 5 Mo maximum. Elle sera automatiquement convertie en WebP (plus léger, plus rapide à charger).</p>
      </div>

      <?php if ($id): ?>
        <div class="form-row">
          <label>Galerie d'images (montages, variantes, détails...)</label>
          <a href="<?= BASE_URL ?>/admin/project_gallery.php?project_id=<?= (int)$id ?>" class="btn">Gérer la galerie de ce projet &rarr;</a>
          <p class="hint">Ajoutez autant de photos que nécessaire, chacune avec sa propre légende.</p>
        </div>
      <?php else: ?>
        <div class="form-row">
          <label>Galerie d'images</label>
          <p class="hint">Enregistrez d'abord ce projet pour pouvoir y ajouter une galerie de plusieurs images légendées.</p>
        </div>
      <?php endif; ?>

      <div class="form-grid">
        <div class="form-row checkbox-row">
          <input type="checkbox" id="featured" name="featured" value="1" <?= $project['featured'] ? 'checked' : '' ?>>
          <label for="featured" style="margin:0;">Mettre ce projet en avant</label>
        </div>
        <div class="form-row">
          <label for="position">Ordre d'affichage</label>
          <input type="number" id="position" name="position" value="<?= (int)$project['position'] ?>">
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="<?= BASE_URL ?>/admin/projects.php" class="btn">Annuler</a>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
