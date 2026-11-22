<?php
/* Shared page parts: <head>, header with menu, and footer. Used by index.php, gallery.php and customers.php. */

/* True when building the static review copy for GitHub Pages. */
function is_static(): bool
{
    return defined('STATIC_EXPORT');
}

/* Link to a page: index.php / gallery.php / customers.php (or .html in the review copy). */
function page_url(string $page): string
{
    return ($page === 'home' ? 'index' : $page) . (is_static() ? '.html' : '.php');
}

/* Link to a section of the home page: "#story" on the home page, "index.php#story" elsewhere. */
function home_link(string $anchor, string $current): string
{
    return ($current === 'home' ? '' : page_url('home')) . '#' . $anchor;
}

function render_head(string $title, string $description): void
{
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= h($description) ?>">
<?php if (is_static()): ?>
  <meta name="robots" content="noindex, nofollow">
<?php endif; ?>
  <meta name="theme-color" content="#171717">
  <title><?= h($title) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
}

function render_header(string $current): void
{
    $links = [
        'home' => ['Home', home_link('home', $current)],
        'story' => ['Our Story', home_link('story', $current)],
        'fabrics' => ['Fabrics', home_link('fabrics', $current)],
        'products' => ['Products', home_link('products', $current)],
        'gallery' => ['Gallery', page_url('gallery')],
        'customers' => ['Customers', page_url('customers')],
    ];
?>
  <header class="header">
    <a class="logo-link" href="<?= h(home_link('home', $current)) ?>" aria-label="PULSE home">
      <img src="assets/images/pulse-logo.png" alt="PULSE · FEELS ALIVE" class="logo">
    </a>
    <button class="menu-button" id="menuButton" aria-label="Open menu" aria-expanded="false">☰</button>
    <nav class="navigation" id="navigation" aria-label="Main navigation">
<?php foreach ($links as $key => [$label, $href]): ?>
      <a href="<?= h($href) ?>"<?= $key === $current && $current !== 'home' ? ' aria-current="page"' : '' ?>><?= h($label) ?></a>
<?php endforeach; ?>
      <a class="nav-quote" href="<?= h(home_link('contact', $current)) ?>">Get a Quote</a>
    </nav>
  </header>
<?php
}

function render_footer(array $c, string $current): void
{
    $footer = $c['footer'];
?>
  <footer class="footer">
    <a href="<?= h(home_link('home', $current)) ?>" class="footer-logo"><img src="assets/images/pulse-logo.png" alt="PULSE · FEELS ALIVE"></a>
    <p><?= h($footer['tagline']) ?></p>
    <div class="footer-links"><a href="<?= h(home_link('story', $current)) ?>">Our Story</a><a href="<?= h(home_link('products', $current)) ?>">Products</a><a href="<?= h(page_url('gallery')) ?>">Gallery</a><a href="<?= h(page_url('customers')) ?>">Customers</a><a href="<?= h(home_link('contact', $current)) ?>">Get a Quote</a><a href="admin/" rel="nofollow">Admin</a></div>
    <p class="company-details"><?= str_replace('  ', '&nbsp; ', h($footer['company_details'])) ?></p>
    <p class="copyright">© <span id="year"><?= date('Y') ?></span> PULSE · FEELS ALIVE. All rights reserved.</p>
  </footer>
  <script src="script.js"></script>
</body>
</html>
<?php
}
