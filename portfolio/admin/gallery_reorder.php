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
$position = (int)($_POST['position'] ?? 0);

$stmt = $pdo->prepare('UPDATE project_images SET position = :pos WHERE id = :id');
$stmt->execute([':pos' => $position, ':id' => $imageId]);

setFlash('success', 'Ordre mis à jour.');
header('Location: ' . BASE_URL . '/admin/project_gallery.php?project_id=' . $projectId);
exit;
