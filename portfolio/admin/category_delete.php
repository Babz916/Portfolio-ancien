<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/categories.php');
    exit;
}
checkCsrf();

$pdo = getPDO();
$id = (int)($_POST['id'] ?? 0);

// Supprimer les images des projets de cette catégorie avant suppression en cascade
$stmt = $pdo->prepare('SELECT id, image FROM projects WHERE category_id = :id');
$stmt->execute([':id' => $id]);
$projectRows = $stmt->fetchAll();
foreach ($projectRows as $row) {
    deleteUploadedImage($row['image']);
}

// Supprimer aussi les images de galerie de ces projets
if (!empty($projectRows)) {
    $projectIds = array_column($projectRows, 'id');
    $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
    $stmt = $pdo->prepare("SELECT image FROM project_images WHERE project_id IN ($placeholders)");
    $stmt->execute($projectIds);
    foreach ($stmt->fetchAll() as $row) {
        deleteUploadedImage($row['image']);
    }
}

$stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
$stmt->execute([':id' => $id]);

setFlash('success', 'Catégorie supprimée.');
header('Location: ' . BASE_URL . '/admin/categories.php');
exit;
