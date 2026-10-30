<?php
/**
 * Fonctions utilitaires partagées par le site public et le back-office
 */

/** Nettoie une chaîne pour l'affichage HTML */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Génère un slug URL-friendly ("Mon Projet !" -> "mon-projet") */
function makeSlug(string $text): string
{
    $text = trim($text);
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : uniqid('item-');
}

/** Rend un slug unique dans une table donnée */
function uniqueSlug(PDO $pdo, string $table, string $slug, ?int $ignoreId = null): string
{
    $base = $slug;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = :slug" . ($ignoreId ? ' AND id != :id' : '');
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($ignoreId) {
            $stmt->bindValue(':id', $ignoreId, PDO::PARAM_INT);
        }
        $stmt->execute();
        if (!$stmt->fetch()) {
            return $slug;
        }
        $i++;
        $slug = $base . '-' . $i;
    }
}

/** Upload sécurisé d'une image : convertit automatiquement en WebP, retourne le chemin relatif enregistré ou null */
function handleImageUpload(array $file, string $subDir): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Erreur lors de l'envoi du fichier (code " . $file['error'] . ').');
    }
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        throw new RuntimeException('Image trop volumineuse (5 Mo maximum).');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Fichier invalide.');
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('Format non autorisé (jpg, png, webp, gif uniquement).');
    }

    $targetDir = __DIR__ . '/../' . UPLOAD_DIR . '/' . $subDir;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Toutes les images sont enregistrées au format .webp, quel que soit le format envoyé
    $filename = bin2hex(random_bytes(8)) . '.webp';
    $destination = $targetDir . '/' . $filename;

    if (!convertImageToWebp($file['tmp_name'], $mime, $destination)) {
        throw new RuntimeException(
            "Impossible de convertir l'image en WebP. Vérifiez que l'extension GD (avec support WebP) "
            . 'est activée sur votre hébergement, ou réessayez avec une autre image.'
        );
    }

    return UPLOAD_DIR . '/' . $subDir . '/' . $filename;
}

/** Convertit une image (jpeg/png/gif/webp) en WebP via GD, en conservant la transparence */
function convertImageToWebp(string $sourcePath, string $mime, string $destination): bool
{
    if (!function_exists('imagewebp')) {
        return false;
    }

    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            $image = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false;
            break;
        default:
            $image = false;
    }

    if (!$image) {
        return false;
    }

    // Conserve la transparence des PNG/GIF lors de la conversion
    imagepalettetotruecolor($image);
    imagealphablending($image, true);
    imagesavealpha($image, true);

    $ok = imagewebp($image, $destination, 82); // qualité 82 : bon compromis poids/qualité
    imagedestroy($image);

    return $ok;
}

/**
 * Upload sécurisé d'un document non-image (ex: CV en PDF).
 * Contrairement à handleImageUpload, ne fait aucune conversion : copie tel quel.
 */
function handleDocumentUpload(array $file, string $subDir, array $allowedMimes, string $extension): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Erreur lors de l'envoi du fichier (code " . $file['error'] . ').');
    }
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        throw new RuntimeException('Fichier trop volumineux (5 Mo maximum).');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Fichier invalide.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('Format de fichier non autorisé.');
    }

    $targetDir = __DIR__ . '/../' . UPLOAD_DIR . '/' . $subDir;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException("Impossible d'enregistrer le fichier.");
    }

    return UPLOAD_DIR . '/' . $subDir . '/' . $filename;
}

/** Supprime un fichier image précédemment uploadé (si présent) */
function deleteUploadedImage(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }
    $full = __DIR__ . '/../' . $relativePath;
    if (is_file($full)) {
        @unlink($full);
    }
}

/** Génère (ou récupère) un jeton CSRF pour le formulaire courant */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Vérifie le jeton CSRF envoyé par un formulaire */
function checkCsrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(403);
        die('Jeton de sécurité invalide. Merci de recharger la page et de réessayer.');
    }
}

/** Message flash (succès/erreur) affiché après une redirection */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
