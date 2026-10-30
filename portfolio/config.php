<?php
/**
 * ============================================================
 *  CONFIGURATION — Identifiants de connexion à la base de données
 * ============================================================
 *  Modifiez uniquement les valeurs ci-dessous avec les infos
 *  fournies par votre hébergeur (souvent visibles dans
 *  cPanel / phpMyAdmin / l'espace "Bases de données").
 * ============================================================
 */

// Adresse du serveur MySQL (le plus souvent "localhost")
define('DB_HOST', 'mysql-babz.alwaysdata.net');

// Nom de la base de données (créée avec sql/schema.sql)
define('DB_NAME', 'babz_portfolio');

// Utilisateur MySQL
define('DB_USER', 'babz');

// Mot de passe MySQL
define('DB_PASS', 'Zdm916sp*');

// Port MySQL (laisser 3306 sauf indication contraire)
define('DB_PORT', 3306);

/**
 * ------------------------------------------------------------
 *  Réglages généraux du site (facultatif à modifier)
 * ------------------------------------------------------------
 */

// Nom affiché dans l'onglet du navigateur
define('SITE_NAME', 'Mon Portfolio');

// Dossier où sont stockées les images uploadées (chemin relatif depuis la racine)
define('UPLOAD_DIR', 'assets/uploads');

// Taille max des images uploadées, en octets (ici 5 Mo)
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

/**
 * ------------------------------------------------------------
 *  BASE_URL — détectée automatiquement, ou forcée manuellement
 * ------------------------------------------------------------
 *  Si malgré tout le CSS ou les liens ne fonctionnent pas,
 *  décommentez la ligne ci-dessous et indiquez le chemin exact
 *  après le nom de domaine, sans le "/" final
 *  (ex: '/portfolio' pour https://babz.alwaysdata.net/portfolio/) :
 */
// define('BASE_URL_OVERRIDE', '/portfolio');

function detectBaseUrl(): string
{
    if (defined('BASE_URL_OVERRIDE')) {
        return BASE_URL_OVERRIDE;
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if (!isset($_SERVER['SCRIPT_FILENAME'])) {
        return '';
    }

    // On tente la comparaison avec le chemin réel (résout les liens symboliques),
    // puis, si ça échoue, avec le chemin brut (au cas où seul l'un des deux est symlinké).
    $candidates = array_unique(array_filter([
        realpath(__DIR__),
        __DIR__,
    ]));

    $scriptFileReal = realpath($_SERVER['SCRIPT_FILENAME']);
    $scriptFileRaw  = $_SERVER['SCRIPT_FILENAME'];

    foreach ($candidates as $appRoot) {
        $appRoot = rtrim(str_replace('\\', '/', $appRoot), '/');
        foreach ([$scriptFileReal, $scriptFileRaw] as $scriptFile) {
            if (!$scriptFile) {
                continue;
            }
            $scriptFile = str_replace('\\', '/', $scriptFile);
            if (strpos($scriptFile, $appRoot) === 0) {
                $relative = substr($scriptFile, strlen($appRoot));
                if ($relative !== '' && substr($scriptName, -strlen($relative)) === $relative) {
                    return substr($scriptName, 0, strlen($scriptName) - strlen($relative));
                }
            }
        }
    }

    return '';
}

define('BASE_URL', detectBaseUrl());
