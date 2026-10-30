<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pdo = getPDO();

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : null);
$category = ['name' => '', 'description' => '', 'position' => 0];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        setFlash('error', "Catégorie introuvable.");
        header('Location: ' . BASE_URL . '/admin/categories.php');
        exit;
    }
    $category = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $category['name'] = trim($_POST['name'] ?? '');
    $category['description'] = trim($_POST['description'] ?? '');
    $category['position'] = (int)($_POST['position'] ?? 0);

    if ($category['name'] === '') {
        $errors[] = 'Le nom de la catégorie est obligatoire.';
    }

    if (empty($errors)) {
        $slug = uniqueSlug($pdo, 'categories', makeSlug($category['name']), $id);

        if ($id) {
            $stmt = $pdo->prepare('UPDATE categories SET name=:name, slug=:slug, description=:description, position=:position WHERE id=:id');
            $stmt->execute([
                ':name' => $category['name'],
                ':slug' => $slug,
                ':description' => $category['description'],
                ':position' => $category['position'],
                ':id' => $id,
            ]);
            setFlash('success', 'Catégorie mise à jour.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description, position) VALUES (:name, :slug, :description, :position)');
            $stmt->execute([
                ':name' => $category['name'],
                ':slug' => $slug,
                ':description' => $category['description'],
                ':position' => $category['position'],
            ]);
            setFlash('success', 'Catégorie créée.');
        }
        header('Location: ' . BASE_URL . '/admin/categories.php');
        exit;
    }
}

$activeNav = 'categories';
$pageTitle = $id ? 'Modifier la catégorie' : 'Nouvelle catégorie';
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="page-head">
  <h1><?= $id ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?></h1>
</div>

<div class="card" style="max-width:640px;">
  <?php foreach ($errors as $err): ?>
    <div class="flash flash-error"><?= h($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="<?= BASE_URL ?>/admin/category_form.php<?= $id ? '?id=' . (int)$id : '' ?>">
    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
    <?php if ($id): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

    <div class="form-row">
      <label for="name">Nom de la catégorie</label>
      <input type="text" id="name" name="name" value="<?= h($category['name']) ?>" required>
    </div>

    <div class="form-row">
      <label for="description">Description (facultatif)</label>
      <textarea id="description" name="description" rows="3"><?= h($category['description']) ?></textarea>
    </div>

    <div class="form-row">
      <label for="position">Ordre d'affichage</label>
      <input type="number" id="position" name="position" value="<?= (int)$category['position'] ?>">
      <p class="hint">Les catégories avec un nombre plus petit s'affichent en premier.</p>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Enregistrer</button>
      <a href="<?= BASE_URL ?>/admin/categories.php" class="btn">Annuler</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
