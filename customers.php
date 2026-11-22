<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$c = content();
$customers = visible_items($c['customers']);
$page = $c['customers_page'];

/* One logo, linked to the customer's website when one is set. */
function customer_logo(array $customer, bool $hidden = false): string
{
    $img = '<img src="' . h($customer['image']) . '" alt="' . ($hidden ? '' : h($customer['name'])) . '" loading="lazy">';
    if (empty($customer['url'])) return '<span class="logo-item">' . $img . '</span>';
    return '<a class="logo-item" href="' . h($customer['url']) . '" target="_blank" rel="noopener"' . ($hidden ? ' tabindex="-1"' : '') . '>' . $img . '</a>';
}

/* The band repeats the logos so it is always wider than the screen, then shows that set twice
   for a seamless loop. */
$bandSet = [];
if ($customers) {
    while (count($bandSet) < 8) $bandSet = array_merge($bandSet, $customers);
}
$duration = max(20, count($bandSet) * 3);

render_head('Our Customers | PULSE · FEELS ALIVE', 'Clubs, teams and businesses that wear custom apparel by PULSE · FEELS ALIVE.');
render_header('customers');
?>
  <main>
    <section class="section page-section">
      <div class="section-title">
        <p class="eyebrow"><?= h($page['eyebrow']) ?></p>
        <h1 class="page-title"><?= title_html($page['title'], $page['title_accent']) ?></h1>
        <?php if (trim($page['text']) !== ''): ?><p><?= h($page['text']) ?></p><?php endif; ?>
      </div>
<?php if (!$customers): ?>
      <p class="empty-note">Our customers' logos will appear here soon.</p>
<?php endif; ?>
    </section>

<?php if ($customers): ?>
    <div class="logo-band" aria-label="Customer logos">
      <div class="logo-track" style="--band-duration: <?= (int) $duration ?>s">
        <div class="logo-set"><?php foreach ($bandSet as $i => $customer) echo customer_logo($customer, $i >= count($customers)); ?></div>
        <div class="logo-set" aria-hidden="true"><?php foreach ($bandSet as $customer) echo customer_logo($customer, true); ?></div>
      </div>
    </div>

    <section class="section">
      <div class="customer-grid">
<?php foreach ($customers as $customer): ?>
        <?php $tag = empty($customer['url']) ? 'div' : 'a'; ?>
        <<?= $tag ?> class="customer-tile"<?= $tag === 'a' ? ' href="' . h($customer['url']) . '" target="_blank" rel="noopener"' : '' ?>>
          <img src="<?= h($customer['image']) ?>" alt="" loading="lazy">
          <span><?= h($customer['name']) ?></span>
        </<?= $tag ?>>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

    <?php if (trim($page['button_label']) !== ''): ?>
    <div class="page-cta"><a class="button button-red" href="<?= h(home_link('contact', 'customers')) ?>"><?= h($page['button_label']) ?> <span>↗</span></a></div>
    <?php endif; ?>
  </main>
<?php render_footer($c, 'customers'); ?>
