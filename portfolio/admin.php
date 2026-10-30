<?php
/**
 * ============================================================
 *  /admin.php — point d'entrée unique du back-office
 *  - Si non connecté : affiche le formulaire de connexion
 *  - Si connecté     : redirige vers le tableau de bord
 * ============================================================
 */
session_start();
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Merci de renseigner votre identifiant et votre mot de passe.';
    } elseif (attemptLogin($username, $password)) {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    } else {
        $error = 'Identifiant ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion — Back-office</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="login-screen">
  <div class="login-box">
    <h1>Back-office</h1>
    <p class="subtitle">Connectez-vous pour gérer votre portfolio</p>

    <?php if ($error): ?>
      <div class="flash flash-error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/admin.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
      <div class="form-row">
        <label for="username">Identifiant</label>
        <input type="text" id="username" name="username" autocomplete="username" required autofocus>
      </div>
      <div class="form-row">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
    </form>
  </div>
</div>
</body>
</html>
