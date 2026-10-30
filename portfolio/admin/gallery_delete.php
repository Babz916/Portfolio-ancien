<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/projects.php');
    exit;
}
checkCsrf();

$pdo = getPDO();
$imageId = (int)($_POST['image_id'] ?? 0);
$projectId = (int)($_POST['project_id'] ?? 0);

$stmt = $pdo->prepare('SELECT image FROM project_images WHERE id = :id');
$stmt->execute([':id' => $imageId]);
if ($row = $stmt->fetch()) {
    deleteUploadedImage($row['image']);
}

$stmt = $pdo->prepare('DELETE FROM project_images WHERE id = :id');
$stmt->execute([':id' => $imageId]);

setFlash('success', 'Image supprimée de la galerie.');
header('Location: ' . BASE_URL . '/admin/project_gallery.php?project_id=' . $projectId);
exit;
