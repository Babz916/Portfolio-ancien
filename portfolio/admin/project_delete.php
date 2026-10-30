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
$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT image FROM projects WHERE id = :id');
$stmt->execute([':id' => $id]);
if ($row = $stmt->fetch()) {
    deleteUploadedImage($row['image']);
}

// Supprimer aussi les fichiers de la galerie (les lignes en base partent en cascade)
$stmt = $pdo->prepare('SELECT image FROM project_images WHERE project_id = :id');
$stmt->execute([':id' => $id]);
foreach ($stmt->fetchAll() as $row) {
    deleteUploadedImage($row['image']);
}

$stmt = $pdo->prepare('DELETE FROM projects WHERE id = :id');
$stmt->execute([':id' => $id]);

setFlash('success', 'Projet supprimé.');
header('Location: ' . BASE_URL . '/admin/projects.php');
exit;
