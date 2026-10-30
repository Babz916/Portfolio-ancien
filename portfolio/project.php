<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getPDO();

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('
    SELECT p.*, c.name AS category_name, c.id AS category_id
    FROM projects p
    JOIN categories c ON c.id = p.category_id
    WHERE p.slug = :slug
    LIMIT 1
');
$stmt->execute([':slug' => $slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    $pageTitle = 'Projet introuvable';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="section container"><h1>Projet introuvable</h1><p><a href="' . BASE_URL . '/index.php" class="back-link">&larr; Retour à l\'accueil</a></p></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $project['title'] . ' — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->prepare('SELECT * FROM project_images WHERE project_id = :pid ORDER BY position ASC, id ASC');
$stmt->execute([':pid' => $project['id']]);
$gallery = $stmt->fetchAll();
?>

<section class="project-detail-head container" id="projectMeta" data-slug="<?= h($project['slug']) ?>">
  <a href="<?= BASE_URL ?>/index.php#cat-<?= (int)$project['category_id'] ?>" class="back-link">&larr; Retour à l'accueil</a>
  <p class="project-detail-cat"><?= h($project['category_name']) ?></p>
  <h1><?= h($project['title']) ?></h1>
  <?php if (!empty($project['short_description'])): ?>
    <p class="hero-bio"><?= h($project['short_description']) ?></p>
  <?php endif; ?>

  <?php if (!empty($project['image'])): ?>
    <img src="<?= BASE_URL ?>/<?= h($project['image']) ?>" alt="<?= h($project['title']) ?>" class="project-detail-img">
  <?php endif; ?>

  <div class="project-detail-links">
    <?php if (!empty($project['project_url'])): ?>
      <?php if (str_contains($project['project_url'], 'youtube')): ?>
        <a href="<?= h($project['project_url']) ?>" class="btn btn-primary" target="_blank" rel="noopener">Voir la vidéo</a>
      <?php else: ?>
        <a href="<?= h($project['project_url']) ?>" class="btn btn-primary" target="_blank" rel="noopener">Voir le site / la démo</a>
      <?php endif; ?>
    <?php endif; ?>
    <?php if (!empty($project['github_url'])): ?>
      <a href="<?= h($project['github_url']) ?>" class="btn btn-ghost" target="_blank" rel="noopener">Code source</a>
    <?php endif; ?>
  </div>

  <?php if (!empty($project['content'])): ?>
    <div class="project-detail-content"><?= nl2br(h($project['content'])) ?></div>
  <?php endif; ?>

  <?php if (!empty($gallery)): ?>
    <div class="project-gallery">
      <h2 class="gallery-title">Galerie</h2>
      <div class="gallery-grid">
        <?php foreach ($gallery as $i => $img): ?>
          <figure class="gallery-item">
            <button type="button" class="gallery-trigger" data-index="<?= (int)$i ?>">
              <img src="<?= BASE_URL ?>/<?= h($img['image']) ?>" alt="<?= h($img['caption'] ?: $project['title']) ?>" loading="lazy">
            </button>
            <?php if (!empty($img['caption'])): ?>
              <figcaption><?= h($img['caption']) ?></figcaption>
            <?php endif; ?>
          </figure>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="lightbox" id="lightbox" hidden>
      <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Fermer">&times;</button>
      <button type="button" class="lightbox-nav lightbox-prev" id="lightboxPrev" aria-label="Image précédente">&#8249;</button>
      <img class="lightbox-img" id="lightboxImg" src="" alt="">
      <p class="lightbox-caption" id="lightboxCaption"></p>
      <button type="button" class="lightbox-nav lightbox-next" id="lightboxNext" aria-label="Image suivante">&#8250;</button>
    </div>

    <script>
      (function () {
        var images = <?= json_encode(array_map(function ($img) {
            return ['src' => BASE_URL . '/' . $img['image'], 'caption' => $img['caption']];
        }, $gallery), JSON_UNESCAPED_UNICODE) ?>;
        var current = 0;
        var lightbox = document.getElementById('lightbox');
        var lightboxImg = document.getElementById('lightboxImg');
        var lightboxCaption = document.getElementById('lightboxCaption');

        function show(index) {
          current = (index + images.length) % images.length;
          lightboxImg.src = images[current].src;
          lightboxCaption.textContent = images[current].caption || '';
        }
        function open(index) {
          show(index);
          lightbox.hidden = false;
          document.body.style.overflow = 'hidden';
        }
        function close() {
          lightbox.hidden = true;
          document.body.style.overflow = '';
        }

        document.querySelectorAll('.gallery-trigger').forEach(function (btn) {
          btn.addEventListener('click', function () {
            open(parseInt(btn.getAttribute('data-index'), 10));
          });
        });
        document.getElementById('lightboxClose').addEventListener('click', close);
        document.getElementById('lightboxNext').addEventListener('click', function () { show(current + 1); });
        document.getElementById('lightboxPrev').addEventListener('click', function () { show(current - 1); });
        lightbox.addEventListener('click', function (e) { if (e.target === lightbox) close(); });
        document.addEventListener('keydown', function (e) {
          if (lightbox.hidden) return;
          if (e.key === 'Escape') close();
          if (e.key === 'ArrowRight') show(current + 1);
          if (e.key === 'ArrowLeft') show(current - 1);
        });
      })();
    </script>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>