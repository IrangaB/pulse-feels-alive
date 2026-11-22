<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$c = content();
$items = visible_items($c['gallery']);
$page = $c['gallery_page'];
render_head('Product Gallery | PULSE · FEELS ALIVE', 'Custom apparel designed and produced by PULSE · FEELS ALIVE for clubs, teams and businesses.');
render_header('gallery');
?>
  <main>
    <section class="section page-section" id="gallery">
      <div class="section-title">
        <p class="eyebrow"><?= h($page['eyebrow']) ?></p>
        <h1 class="page-title"><?= title_html($page['title'], $page['title_accent']) ?></h1>
        <?php if (trim($page['text']) !== ''): ?><p><?= h($page['text']) ?></p><?php endif; ?>
      </div>
<?php if ($items): ?>
      <div class="fabric-grid gallery-grid">
<?php foreach ($items as $item): ?>
        <article class="fabric-card gallery-card">
          <img src="<?= h($item['image']) ?>" alt="Custom apparel for <?= h($item['name']) ?>" loading="lazy">
          <div class="card-body"><h3><?= h($item['name']) ?></h3></div>
        </article>
<?php endforeach; ?>
      </div>
<?php else: ?>
      <p class="empty-note">Photos of our finished products will appear here soon.</p>
<?php endif; ?>
    </section>
  </main>
<?php render_footer($c, 'gallery'); ?>
