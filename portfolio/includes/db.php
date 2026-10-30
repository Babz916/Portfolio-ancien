<?php
/**
 * Connexion PDO à la base de données, à partir de config.php
 */

require_once __DIR__ . '/../config.php';

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(
                '<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:24px;'
                . 'border:1px solid #e5b3a6;background:#fff4f1;border-radius:8px;color:#7a2e1d;">'
                . '<h1 style="margin-top:0;font-size:1.2rem;">Connexion à la base de données impossible</h1>'
                . '<p>Vérifiez les identifiants dans <code>config.php</code> et que la base '
                . '<code>' . htmlspecialchars(DB_NAME) . '</code> existe (voir <code>sql/schema.sql</code>).</p>'
                . '</div>'
            );
        }
    }

    return $pdo;
}
