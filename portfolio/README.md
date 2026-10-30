# Portfolio — site vitrine avec back-office (PHP / MySQL)

Mon portfolio en ligne, avec un **espace d'administration** : je crée mes catégories, j'ajoute mes projets un par un (texte, image, galerie légendée, liens), je modifie mon profil et mon CV, et le site se met à jour tout seul, sans toucher au code.

Le projet est écrit en **PHP natif (sans framework)** avec une base **MySQL**, un peu de JavaScript « maison » et du CSS écrit à la main.

- Site en ligne : https://babz.alwaysdata.net/portfolio/
- Back-office : `/admin.php`

---

## Sommaire

1. [Fonctionnalités](#fonctionnalités)
2. [Technologies](#technologies)
3. [Structure du projet](#structure-du-projet)
4. [Base de données](#base-de-données)
5. [Installation](#installation)
6. [Les choix de conception](#les-choix-de-conception)
7. [Limites et pistes d'amélioration](#limites-et-pistes-damélioration)

---

## Fonctionnalités

### Côté visiteur

- **Page d'accueil** : bannière de profil (photo, accroche, bio, contact, réseaux), puis une section par catégorie avec ses projets sous forme de cartes. Les projets « mis en avant » passent en premier et portent un badge.
- **Cartes cliquables en entier** : toute la carte ouvre le projet, mais le lien « Code source » reste un lien à part.
- **Fiche projet** : description, image principale, boutons vers la démo ou la vidéo (le libellé change si le lien est un lien YouTube) et vers le code, puis une **galerie** d'images avec légendes.
- **Visionneuse plein écran** pour la galerie : navigation avec les flèches du clavier, `Échap` pour fermer, boutons adaptés au téléphone.
- **Bouton « Télécharger mon CV »** dans la navigation (il n'apparaît que si un PDF a été envoyé depuis le back-office).
- **Bouton « Projet aléatoire »** : une petite animation fait défiler les titres de projets, puis redirige vers l'un d'eux (jamais celui qu'on est déjà en train de regarder).
- **Barre de progression des projets consultés** : sous la navigation, elle se remplit à chaque projet ouvert. Au survol, elle grossit et indique combien de projets il reste à voir. Les cartes déjà ouvertes reçoivent un repère « Consulté ».
- **Barre d'accessibilité** (bouton flottant) :
  - taille du texte : `A-`, `A` (par défaut), `A+` ;
  - **mode dyslexie** (police adaptée, espacement des lettres et des mots, interligne plus grand) ;
  - **traduction en anglais** en un clic, avec retour au français.
- **Fond interactif** : une « lampe torche » suit la souris (ou le doigt sur téléphone) et éclaire un sol craquelé (voir [Le fond](#le-fond-trois-couches-empilées)).
- **Responsive** : menu repliable, cartes en deux colonnes sur téléphone, galerie compacte, bannière de profil réduite.
- Le titre de l'onglet est « Portfolio de *mon nom* » et le favicon est mon logo, tous deux modifiables depuis le back-office.

### Côté administrateur (`/admin.php`)

- **Connexion** sécurisée, puis **tableau de bord** (nombre de catégories et de projets, derniers projets ajoutés).
- **Catégories** : créer, modifier, supprimer, choisir l'ordre d'affichage.
- **Projets** : créer, modifier, supprimer ; choix de la catégorie, descriptions courte et longue, liens, image principale, mise en avant, ordre d'affichage.
- **Galerie d'un projet** : ajouter autant d'images que nécessaire, chacune avec sa légende ; changer l'ordre ; supprimer.
- **Profil** : nom, accroche, bio, e-mail, téléphone, localisation, réseaux sociaux, **photo**, **logo**, **CV en PDF**, et changement du mot de passe.

---

## Technologies

| Domaine | Choix |
|---|---|
| Langage serveur | PHP 8+ (natif, sans framework) |
| Base de données | MySQL / MariaDB via **PDO** |
| Front | HTML, CSS (variables CSS, grid, flexbox), JavaScript sans bibliothèque |
| Images | GD (conversion en WebP) |
| Polices | Fraunces, Public Sans, Atkinson Hyperlegible (mode dyslexie) |
| Hébergement | AlwaysData (hébergement mutualisé) |

Aucune dépendance à installer : pas de Composer, pas de npm.

---

## Structure du projet

```
portfolio/
├── index.php                  Page d'accueil (profil + catégories + projets)
├── project.php                Fiche d'un projet (+ galerie et visionneuse)
├── admin.php                  Point d'entrée du back-office (connexion)
├── config.php                 Identifiants de la base + réglages du site
│
├── includes/                  Code partagé
│   ├── db.php                 Connexion PDO (une seule par requête)
│   ├── auth.php               Session et connexion de l'administrateur
│   ├── functions.php          Outils : échappement, slugs, uploads, CSRF, messages flash
│   ├── header.php             En-tête public : profil, navigation, barre d'accessibilité, fond
│   └── footer.php             Pied de page + chargement des scripts
│
├── admin/                     Pages du back-office
│   ├── dashboard.php          Tableau de bord
│   ├── categories.php         Liste des catégories
│   ├── category_form.php      Création / modification d'une catégorie
│   ├── category_delete.php    Suppression d'une catégorie
│   ├── projects.php           Liste des projets (avec filtre par catégorie)
│   ├── project_form.php       Création / modification d'un projet
│   ├── project_delete.php     Suppression d'un projet
│   ├── project_gallery.php    Galerie d'un projet (ajout d'images + légendes)
│   ├── gallery_reorder.php    Changement de l'ordre d'une image
│   ├── gallery_delete.php     Suppression d'une image de la galerie
│   ├── profile.php            Profil, photo, logo, CV, mot de passe
│   ├── logout.php             Déconnexion
│   └── includes/              Gabarit commun du back-office (menu latéral, pied de page)
│
└── assets/
    ├── css/
    │   ├── style.css          Feuille de style du site public
    │   └── admin.css          Feuille de style du back-office
    ├── js/
    │   ├── main.js            Menu, barre d'accessibilité, progression, projet aléatoire
    │   └── fond.js            Lampe torche du fond
    ├── img/
    │   └── fond_craquele.svg  Motif craquelé du fond
    └── uploads/               Fichiers envoyés depuis le back-office
        └── avatar/  logo/  projects/  gallery/  cv/


```

**Principe général** : chaque page publique commence par `includes/header.php` et finit par `includes/footer.php` ; chaque page du back-office utilise le même gabarit (`admin/includes/`). Les pages de formulaire suivent toujours le même schéma : *vérifier la connexion → charger les données → traiter le formulaire (POST) → rediriger avec un message → afficher*.

---

## Base de données

Cinq tables, reliées par des clés étrangères :

```
categories  1 ──── N  projects  1 ──── N  project_images
profile (une seule ligne, id = 1)
admin_users
```

| Table | Rôle |
|---|---|
| `admin_users` | Compte(s) du back-office (mot de passe haché) |
| `profile` | Mes informations : nom, accroche, bio, contacts, réseaux, photo, logo, CV |
| `categories` | Nom, slug, description, ordre d'affichage |
| `projects` | Projet rattaché à une catégorie : textes, liens, image, « mis en avant », ordre |
| `project_images` | Images de la galerie d'un projet : fichier, légende, ordre |

Les clés étrangères sont en `ON DELETE CASCADE` : supprimer une catégorie supprime ses projets, et supprimer un projet supprime les images de sa galerie. La base reste donc toujours cohérente.

---

## Les choix de conception

### Sécurité

- **Requêtes préparées partout** (PDO) : aucune donnée n'est collée directement dans une requête SQL, donc pas d'injection SQL.
- **Mots de passe hachés** avec `password_hash()` / `password_verify()` ; l'identifiant de session est régénéré à la connexion.
- **Jeton CSRF** sur tous les formulaires d'écriture (connexion, ajout, modification, suppression), vérifié côté serveur.
- **Échappement systématique** de tout ce qui est affiché, via une petite fonction `h()` : pas de faille XSS avec les textes saisis dans le back-office.
- **Chaque page du back-office est protégée** par un contrôle de session, y compris les pages de suppression.
- **Dossiers sensibles protégés** par `.htaccess` : pas de listing de dossiers, `sql/` inaccessible depuis le web, exécution de PHP interdite dans `assets/uploads/`.

### Envoi de fichiers

- Le type du fichier est vérifié par son **contenu réel** (`finfo`), pas par son extension, et la taille est limitée (5 Mo).
- Les fichiers sont **renommés avec un nom aléatoire** : pas de collision, pas de nom imprévisible à deviner ou à exploiter.
- **Toutes les images sont converties automatiquement en WebP** (qualité 82, transparence conservée) : pages plus légères, sans rien faire de spécial lors de l'envoi.
- **Pas de fichiers orphelins** : remplacer une image, ou supprimer un projet, une catégorie ou une image de galerie, efface aussi les fichiers correspondants sur le disque.

### Fonctionne partout : `BASE_URL`

Beaucoup de sites cassent quand on les déplace dans un sous-dossier, car les liens commencent par `/`. Ici, `config.php` **détecte tout seul le dossier d'installation** en comparant l'URL appelée avec le chemin réel du fichier, et tous les liens, redirections, images et feuilles de style en tiennent compte. Le site marche donc à la racine d'un domaine comme dans `/portfolio/`, sans rien modifier. La détection tient compte des liens symboliques (cas fréquent chez les hébergeurs) et peut être forcée à la main.

### Code organisé et sans répétition

- Un seul fichier de connexion à la base, une seule bibliothèque de fonctions utilitaires, un en-tête et un pied de page partagés.
- **Une seule requête** dans l'en-tête alimente à la fois la barre de progression et le bouton « Projet aléatoire » : les données sont déposées dans la page au format JSON, sans requête supplémentaire côté navigateur.
- Les **slugs** des URL (`project.php?slug=mon-projet`) sont générés automatiquement à partir du titre, sans accents ni caractères spéciaux, et rendus uniques (`mon-projet-2`) si besoin.

### Accessibilité

Le site vise le niveau **AA des WCAG** :

- **Contrastes calculés** et non estimés : texte blanc sur le fond 17,3:1 ; jaune d'accent sur le fond 13,8:1 ; et même au pire endroit du fond animé (une fissure éclairée au maximum), le texte reste à **5,0:1** minimum, pour 4,5:1 exigé. L'intensité de la lumière est d'ailleurs réglée pour ça.
- Les tailles sont en `rem` : le bouton `A+` agrandit **tout le site** d'un coup en modifiant une seule variable.
- Les préférences (taille du texte, mode dyslexie) sont **mémorisées** et réappliquées **avant l'affichage de la page**, pour éviter un clignotement.
- `prefers-reduced-motion` est respecté : si le visiteur demande moins de mouvement, la lumière du fond reste fixe et les animations sont coupées.
- Navigation au clavier (menus, visionneuse, barre d'accessibilité avec `Échap`), attributs `aria-*`, barre de progression exposée comme `progressbar`, zones tactiles d'au moins 44 px sur téléphone.

### Respect de la vie privée

- **Aucun outil de statistiques, aucune publicité, aucun pisteur.**
- La progression « projets consultés » est stockée **dans le navigateur du visiteur** (`localStorage`) : rien n'est envoyé au serveur. Elle ignore les projets supprimés depuis.
- Le seul cookie du site est la session de connexion, côté back-office. Un cookie de langue n'est posé que si le visiteur choisit la traduction.

### Le fond : trois couches empilées

1. la **couleur de base** `#1b1b17` ;
2. une **lampe torche** : un halo jaune qui suit la souris (le doigt sur téléphone) ;
3. un **motif SVG craquelé** par-dessus.

Le détail malin : les plaques du motif ont **exactement la couleur du fond**. Elles cachent donc la lumière, qui n'apparaît que **dans les fissures** : sans lumière la page est unie, avec la lumière le sol craquelé se révèle autour du curseur.

- Le motif est conçu pour se **raccorder sans coupure** sur ses bords opposés : il se répète vers le bas sur toute la longueur de la page et **défile avec elle**, alors que la lumière reste fixe à l'écran : en faisant défiler, les fissures glissent sous la lumière.
- Le SVG n'a qu'un `viewBox`, sans largeur ni hauteur. Le CSS donne donc **les deux dimensions de la tuile**, sinon certains navigateurs étirent l'image au lieu de la répéter. Les tailles de tuile sont choisies par paliers pour que leur hauteur tombe sur un nombre entier de pixels.
- **La lumière ne coûte rien quand la souris est immobile** : le script ne demande plus aucune image d'animation une fois le halo arrivé, et le déplacement passe par `transform`, qui ne déclenche aucun recalcul de mise en page.
- Sur téléphone, sans souris, la lumière descend doucement avec le défilement ; avec le doigt, elle le suit même pendant le défilement.

### Responsive

Points de rupture à 1024, 860, 720, 600 et 480 px : la navigation devient un menu déroulant avant que les boutons ne débordent (1024 px), la photo de profil devient une pastille ronde sur mobile pour ne pas repousser le texte, les cartes passent en deux colonnes, les légendes de galerie sont limitées à deux lignes. La marge latérale des pages est une variable commune (`--gutter`), donc la navigation, les titres, les cartes et le pied de page restent alignés à toutes les tailles.

---

## Limites et pistes d'amélioration

- **Services Google** : les polices (Google Fonts) et le script de traduction sont chargés depuis Google à chaque visite, ce qui transmet l'adresse IP du visiteur. Piste : héberger les polices sur le serveur et ne charger le script de traduction qu'au clic.
- **Traduction** : c'est de la traduction automatique, de qualité moyenne. Une vraie version anglaise demanderait des champs « EN » en base de données.
- **Authentification** : un seul compte administrateur, pas de « mot de passe oublié », pas de limitation du nombre de tentatives de connexion. Les options du cookie de session (`HttpOnly`, `SameSite`) ne sont pas réglées explicitement.
- **Ordre des éléments** : il se règle avec un numéro, pas encore par glisser-déposer.
- **Pas de tests automatisés** : les vérifications ont été faites à la main et par des mesures ponctuelles (contrastes, comportements du fond).
- **Fond animé** : il ne coûte rien à l'arrêt mais demande un peu de processeur pendant les mouvements ; sur un appareil ancien, un interrupteur « fond animé » dans la barre d'accessibilité serait pertinent.

---

## Auteur

**Bastien Roubille**

Projet personnel. © 2026 Bastien Roubille — Tous droits réservés.

Le code source et le design de ce projet ne peuvent pas être réutilisés, modifiés ou redistribués sans mon autorisation préalable.
