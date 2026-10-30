# Portfolio PHP — guide d'installation

Site portfolio complet en PHP natif + MySQL, avec back-office.

## Contenu

```
/config.php              → identifiants de connexion à la BDD (à modifier)
/index.php                → page d'accueil publique (profil + catégories + projets)
/project.php               → page de détail d'un projet
/includes/                → connexion BDD, fonctions, header/footer publics
/assets/css, /assets/js    → styles et scripts publics
/admin.php                 → connexion au back-office (point d'entrée unique)
/admin/                    → pages du back-office (catégories, projets, profil)
/assets/uploads/           → images uploadées (projets + avatar)
/sql/schema.sql            → script de création des tables
```

## Installation

1. **Créer la base de données**
   Importez `sql/schema.sql` dans votre base MySQL (via phpMyAdmin, Adminer, ou en ligne de commande) :
   ```
   mysql -u votre_utilisateur -p < sql/schema.sql
   ```
   Cela crée la base `portfolio`, les tables (`categories`, `projects`, `profile`, `admin_users`)
   et un compte admin par défaut.

2. **Renseigner les identifiants de connexion**
   Ouvrez `config.php` et remplissez `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` avec les
   informations fournies par votre hébergeur.

3. **Déposer les fichiers sur votre serveur**
   Uploadez l'intégralité du dossier à la racine de votre hébergement (ex. `public_html/`).
   Vérifiez que le dossier `assets/uploads/` est accessible en écriture par PHP
   (droits 755 ou 775 selon l'hébergeur).

4. **Se connecter au back-office**
   Rendez-vous sur `https://votredomaine.com/admin.php`.

   Identifiants par défaut :
   - **Identifiant** : `admin`
   - **Mot de passe** : `admin123`

   ⚠️ **Changez ce mot de passe immédiatement** depuis le back-office
   (menu "Mon profil" → section "Changer mon mot de passe").

## Utilisation

- **Catégories** (`/admin/categories.php`) : créez vos catégories de projets
  (ex. "Sites web", "Applications mobiles", "Design"...).
- **Projets** (`/admin/projects.php`) : ajoutez vos projets un par un, chacun rattaché
  à une catégorie, avec image principale, description, liens vers le site et le code source.
- **Galerie d'un projet** (`/admin/project_gallery.php?project_id=X`, accessible depuis la fiche
  du projet une fois celui-ci créé) : ajoutez autant d'images que nécessaire (montages, détails,
  variantes...), chacune avec sa propre légende, et réglez leur ordre d'affichage. Idéal pour les
  projets de graphisme ou de photo qui ont besoin de plusieurs visuels.
- **Mon profil** (`/admin/profile.php`) : modifiez les informations affichées dans la
  bannière du site public (nom, accroche, bio, photo, réseaux sociaux, contact).

## Mise à jour d'une installation existante (ajout de la galerie)

Si votre base de données a déjà été créée avant l'ajout de la galerie multi-images,
importez simplement `sql/add_gallery.sql` (ne réimportez pas `schema.sql`, cela ne
touchera pas à vos données existantes) :
```
mysql -u votre_utilisateur -p votre_base < sql/add_gallery.sql
```

Tout le contenu (catégories, projets, profil) est stocké en base de données et peut
être modifié à tout moment depuis le back-office — aucune intervention dans le code
n'est nécessaire au quotidien.

## Sécurité

- Mots de passe hashés avec `password_hash()` / vérifiés avec `password_verify()`.
- Protection CSRF sur tous les formulaires du back-office.
- Requêtes SQL préparées (PDO) partout — pas d'injection SQL possible.
- Le dossier `assets/uploads/` interdit l'exécution de scripts PHP (`.htaccess`).
- Le dossier `sql/` est bloqué à l'accès web (`.htaccess`).

## Prérequis serveur

- PHP 8.0 ou supérieur, avec l'extension **PDO MySQL** et **fileinfo** activées.
- MySQL 5.7+ / MariaDB 10.3+.
- Apache avec `mod_rewrite` (pour les fichiers `.htaccess`), ou configuration équivalente sur Nginx.
