<?php
/* Renders one admin screen as a read-only demo page for the GitHub Pages review copy.
   Usage: php tools/render-admin.php <page> [edit-id] [show]   — called by tools/build-preview.php */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function demo_file(array $q): string
{
    $p = preg_replace('/[^a-z]/', '', (string) ($q['p'] ?? 'products')) ?: 'products';
    if (!empty($q['edit'])) return $p . '-edit-' . preg_replace('/[^a-z0-9-]/i', '', (string) $q['edit']) . '.html';
    if (!empty($q['show']) && $q['show'] !== 'all') return $p . '-' . preg_replace('/[^a-z]/', '', (string) $q['show']) . '.html';
    return $p . '.html';
}

$_GET = array_filter(['p' => $argv[1] ?? 'products', 'edit' => $argv[2] ?? '', 'show' => $argv[3] ?? ''], 'strlen');
$_SERVER['REQUEST_METHOD'] = 'GET';
define('ADMIN_DEMO', true);

ob_start();
require dirname(__DIR__) . '/admin/index.php';
$html = ob_get_clean();

// Point admin links at the matching demo pages.
$html = preg_replace_callback('/href="\?([^"]*)"/', function ($m) {
    parse_str(html_entity_decode($m[1]), $q);
    return 'href="' . demo_file($q) . '"';
}, $html);
$html = str_replace('href="index.php"', 'href="products.html"', $html);

$note = ($_GET['p'] ?? '') === 'login'
    ? 'Review demo: type any username and password and press Log in to look around. The real admin page has a secure login.'
    : 'Review demo: you can click through every screen, but saving is switched off here. On the real website every button works.';
$html = str_replace('<main class="wrap">', '<main class="wrap">' . "\n" . '<div class="flash flash-info" role="note">' . htmlspecialchars($note) . '</div>', $html);

$script = <<<'JS'
<script>
document.querySelectorAll("[required]").forEach(el => el.removeAttribute("required"));
document.querySelectorAll("form").forEach(form => form.addEventListener("submit", event => {
  event.preventDefault();
  if (form.querySelector('[name="action"][value="login"]')) { location.href = "products.html"; return; }
  if (form.querySelector('[name="action"][value="logout"]')) { location.href = "index.html"; return; }
  alert("Review demo: saving is switched off here. On the real website this saves your change straight away.");
}));
</script>
JS;
echo str_replace('</body>', $script . "\n</body>", $html);
