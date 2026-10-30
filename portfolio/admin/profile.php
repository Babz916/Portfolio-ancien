<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pdo = getPDO();

$profile = $pdo->query('SELECT * FROM profile WHERE id = 1')->fetch();
if (!$profile) {
    $pdo->exec("INSERT INTO profile (id, full_name, tagline) VALUES (1, '', '')");
    $profile = $pdo->query('SELECT * FROM profile WHERE id = 1')->fetch();
}

$errors = [];
$pwdErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $formType = $_POST['form_type'] ?? '';

    // ---- Formulaire "informations du profil" ----
    if ($formType === 'profile') {
        $fields = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'tagline' => trim($_POST['tagline'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'github_url' => trim($_POST['github_url'] ?? ''),
            'linkedin_url' => trim($_POST['linkedin_url'] ?? ''),
            'twitter_url' => trim($_POST['twitter_url'] ?? ''),
            'website_url' => trim($_POST['website_url'] ?? ''),
        ];

        if ($fields['full_name'] === '') {
            $errors[] = 'Le nom complet est obligatoire.';
        }

                $newAvatar = null;
        $newLogo = null;
        $newCv = null;
        if (empty($errors)) {
            try {
                $newAvatar = handleImageUpload($_FILES['avatar'] ?? [], 'avatar');
                $newLogo = handleImageUpload($_FILES['logo'] ?? [], 'logo');
                $newCv = handleDocumentUpload($_FILES['cv'] ?? [], 'cv', ['application/pdf'], 'pdf');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($errors)) {
            if ($newAvatar) {
                deleteUploadedImage($profile['avatar']);
                $fields['avatar'] = $newAvatar;
            } else {
                $fields['avatar'] = $profile['avatar'];
            }
            if ($newLogo) {
                deleteUploadedImage($profile['logo']);
                $fields['logo'] = $newLogo;
            } else {
                $fields['logo'] = $profile['logo'];
            }
            if ($newCv) {
                deleteUploadedImage($profile['cv']);
                $fields['cv'] = $newCv;
            } else {
                $fields['cv'] = $profile['cv'];
            }
            $stmt = $pdo->prepare('
                UPDATE profile SET full_name=:full_name, tagline=:tagline, bio=:bio, email=:email,
                    phone=:phone, location=:location, avatar=:avatar, logo=:logo, cv=:cv,
                    github_url=:github_url, linkedin_url=:linkedin_url, twitter_url=:twitter_url, website_url=:website_url
                WHERE id = 1
            ');
            $stmt->execute($fields);
            setFlash('success', 'Profil mis à jour.');
            header('Location: ' . BASE_URL . '/admin/profile.php');
            exit;
        }
        $profile = array_merge($profile, $fields);
    }

    // ---- Formulaire "changer le mot de passe" ----
    if ($formType === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = :id');
        $stmt->execute([':id' => $_SESSION['admin_id']]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current, $hash)) {
            $pwdErrors[] = 'Le mot de passe actuel est incorrect.';
        } elseif (strlen($new) < 8) {
            $pwdErrors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif ($new !== $confirm) {
            $pwdErrors[] = 'La confirmation ne correspond pas au nouveau mot de passe.';
        }

        if (empty($pwdErrors)) {
            $stmt = $pdo->prepare('UPDATE admin_users SET password_hash = :h WHERE id = :id');
            $stmt->execute([':h' => password_hash($new, PASSWORD_DEFAULT), ':id' => $_SESSION['admin_id']]);
            setFlash('success', 'Mot de passe modifié.');
            header('Location: ' . BASE_URL . '/admin/profile.php');
            exit;
        }
    }
}

$activeNav = 'profile';
$pageTitle = 'Mon profil';
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="page-head">
  <h1>Mon profil</h1>
</div>

<div class="card" style="max-width:760px;">
  <h2 style="font-size:1.05rem;">Informations affichées en bannière du site</h2>

  <?php foreach ($errors as $err): ?>
    <div class="flash flash-error"><?= h($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="<?= BASE_URL ?>/admin/profile.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
    <input type="hidden" name="form_type" value="profile">

    <div class="form-grid">
      <div class="form-row">
        <label for="full_name">Nom complet</label>
        <input type="text" id="full_name" name="full_name" value="<?= h($profile['full_name']) ?>" required>
      </div>
      <div class="form-row">
        <label for="tagline">Titre / accroche</label>
        <input type="text" id="tagline" name="tagline" value="<?= h($profile['tagline']) ?>" placeholder="Ex : Développeuse web full-stack">
      </div>
    </div>

    <div class="form-row">
      <label for="bio">Biographie</label>
      <textarea id="bio" name="bio" rows="5"><?= h($profile['bio']) ?></textarea>
    </div>

    <div class="form-grid">
      <div class="form-row">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= h($profile['email']) ?>">
      </div>
      <div class="form-row">
        <label for="phone">Téléphone</label>
        <input type="tel" id="phone" name="phone" value="<?= h($profile['phone']) ?>">
      </div>
    </div>

    <div class="form-row">
      <label for="location">Localisation</label>
      <input type="text" id="location" name="location" value="<?= h($profile['location']) ?>" placeholder="Ex : Bordeaux, France">
    </div>

    <div class="form-grid">
      <div class="form-row">
        <label for="github_url">Lien GitHub</label>
        <input type="url" id="github_url" name="github_url" value="<?= h($profile['github_url']) ?>">
      </div>
      <div class="form-row">
        <label for="linkedin_url">Lien LinkedIn</label>
        <input type="url" id="linkedin_url" name="linkedin_url" value="<?= h($profile['linkedin_url']) ?>">
      </div>
      <div class="form-row">
        <label for="twitter_url">Lien Twitter / X</label>
        <input type="url" id="twitter_url" name="twitter_url" value="<?= h($profile['twitter_url']) ?>">
      </div>
      <div class="form-row">
        <label for="website_url">Autre site web</label>
        <input type="url" id="website_url" name="website_url" value="<?= h($profile['website_url']) ?>">
      </div>
    </div>

    <div class="form-row">
      <label for="avatar">Photo de profil (affichée en grand sur la page d'accueil)</label>
      <?php if (!empty($profile['avatar'])): ?>
        <div class="current-image"><img src="<?= BASE_URL ?>/<?= h($profile['avatar']) ?>" alt=""></div>
      <?php endif; ?>
      <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif">
    </div>

    <div class="form-row">
      <label for="logo">Logo (affiché dans la navigation et l'onglet du navigateur)</label>
      <?php if (!empty($profile['logo'])): ?>
        <div class="current-image"><img src="<?= BASE_URL ?>/<?= h($profile['logo']) ?>" alt="" style="width:64px; background:var(--paper); padding:6px; border-radius:8px;"></div>
      <?php endif; ?>
      <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp,image/gif">
      <p class="hint">Idéalement une image carrée (ex : 256×256px), fond transparent si possible.</p>
    </div>
        <div class="form-row">
      <label for="cv">CV (PDF)</label>
      <?php if (!empty($profile['cv'])): ?>
        <p class="hint" style="margin-top:0;">
          Fichier actuel : <a href="<?= BASE_URL ?>/<?= h($profile['cv']) ?>" target="_blank" rel="noopener">voir le CV en ligne</a>
        </p>
      <?php endif; ?>
      <input type="file" id="cv" name="cv" accept="application/pdf">
      <p class="hint">Format PDF uniquement, 5 Mo maximum. Un nouvel envoi remplace l'ancien fichier.</p>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Enregistrer le profil</button>
    </div>
  </form>
</div>

<div class="card" style="max-width:760px;">
  <h2 style="font-size:1.05rem;">Changer mon mot de passe</h2>

  <?php foreach ($pwdErrors as $err): ?>
    <div class="flash flash-error"><?= h($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="<?= BASE_URL ?>/admin/profile.php">
    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
    <input type="hidden" name="form_type" value="password">

    <div class="form-row">
      <label for="current_password">Mot de passe actuel</label>
      <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
    </div>
    <div class="form-grid">
      <div class="form-row">
        <label for="new_password">Nouveau mot de passe</label>
        <input type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="8" required>
      </div>
      <div class="form-row">
        <label for="confirm_password">Confirmer le mot de passe</label>
        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="8" required>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>