<?php
require_once __DIR__ . '/includes/header.php';

// Projets groupés par catégorie
$stmt = $pdo->query('
    SELECT p.*, c.name AS category_name
    FROM projects p
    JOIN categories c ON c.id = p.category_id
    ORDER BY c.position ASC, c.name ASC, p.featured DESC, p.position ASC, p.created_at DESC
');
$allProjects = $stmt->fetchAll();

$byCategory = [];
foreach ($allProjects as $p) {
    $byCategory[$p['category_id']][] = $p;
}
?>

<section class="hero" id="profil">
  <div class="container hero-grid">
    <div>
      <p class="hero-tagline"><?= h($profile['tagline'] ?? '') ?></p>
      <h1 class="hero-title"><?= h($profile['full_name'] ?? '') ?></h1>
      <p class="hero-bio"><?= nl2br(h($profile['bio'] ?? '')) ?></p>

      <div class="hero-meta">
        <?php if (!empty($profile['location'])): ?><span><?= h($profile['location']) ?></span><?php endif; ?>
        <?php if (!empty($profile['email'])): ?><span><?= h($profile['email']) ?></span><?php endif; ?>
        <?php if (!empty($profile['phone'])): ?><span><?= h($profile['phone']) ?></span><?php endif; ?>
      </div>

      <div class="hero-socials">
        <?php if (!empty($profile['github_url'])): ?><a href="<?= h($profile['github_url']) ?>" target="_blank" rel="noopener">GitHub</a><?php endif; ?>
        <?php if (!empty($profile['linkedin_url'])): ?><a href="<?= h($profile['linkedin_url']) ?>" target="_blank" rel="noopener">LinkedIn</a><?php endif; ?>
        <?php if (!empty($profile['twitter_url'])): ?><a href="<?= h($profile['twitter_url']) ?>" target="_blank" rel="noopener">Twitter / X</a><?php endif; ?>
        <?php if (!empty($profile['website_url'])): ?><a href="<?= h($profile['website_url']) ?>" target="_blank" rel="noopener">Site web</a><?php endif; ?>
      </div>
    </div>

    <div class="hero-portrait">
      <?php if (!empty($profile['avatar'])): ?>
        <img src="<?= BASE_URL ?>/<?= h($profile['avatar']) ?>" alt="Photo de <?= h($profile['full_name'] ?? '') ?>">
      <?php else: ?>
        <div class="placeholder" aria-hidden="true"><?= h(mb_substr($profile['full_name'] ?? '?', 0, 1)) ?></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if (empty($categories)): ?>

  <section class="section container">
    <p class="empty-state">Aucune catégorie n'a encore été créée. Rendez-vous dans le back-office (<code>/admin.php</code>) pour ajouter vos premières catégories et projets.</p>
  </section>

<?php else: ?>

  <?php foreach ($categories as $cat): ?>
    <section class="section container" id="cat-<?= (int)$cat['id'] ?>">
      <div class="section-head">
        <h2><?= h($cat['name']) ?></h2>
      </div>
      <?php if (!empty($cat['description'])): ?>
        <p class="section-desc"><?= h($cat['description']) ?></p>
      <?php endif; ?>

      <?php $projects = $byCategory[$cat['id']] ?? []; ?>

      <?php if (empty($projects)): ?>
        <p class="empty-state">Aucun projet dans cette catégorie pour le moment.</p>
      <?php else: ?>
        <div class="project-grid">
          <?php foreach ($projects as $p): ?>
            <article class="project-card" data-slug="<?= h($p['slug']) ?>">
              <div class="project-thumb <?= empty($p['image']) ? 'empty' : '' ?>">
                <?php if (!empty($p['image'])): ?>
                  <img src="<?= BASE_URL ?>/<?= h($p['image']) ?>" alt="<?= h($p['title']) ?>" loading="lazy">
                <?php else: ?>
                  <span>Pas d'image</span>
                <?php endif; ?>
              </div>
              <div class="project-body">
                <?php if ($p['featured']): ?><span class="badge-featured">Mis en avant</span><?php endif; ?>
                <h3><a href="<?= BASE_URL ?>/project.php?slug=<?= urlencode($p['slug']) ?>" class="stretched-link"><?= h($p['title']) ?></a></h3>
                <?php if (!empty($p['short_description'])): ?>
                  <p><?= h($p['short_description']) ?></p>
                <?php endif; ?>
                <?php if (!empty($p['github_url'])): ?>
                  <div class="project-links">
                    <a href="<?= h($p['github_url']) ?>" target="_blank" rel="noopener">Code source &#8599;</a>
                  </div>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>