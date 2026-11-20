<?php
/* Builds a static review copy of the website (no admin, form switched off) for GitHub Pages.
   Usage: php tools/build-preview.php <output-folder>   — run automatically by .github/workflows/review-preview.yml */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$out = rtrim($argv[1] ?? $root . '/_site', '/');
@mkdir($out, 0755, true);

define('STATIC_EXPORT', true);
ob_start();
require $root . '/index.php';
$html = ob_get_clean();
$html = str_replace('<meta name="theme-color"', '<meta name="robots" content="noindex, nofollow">' . "\n  " . '<meta name="theme-color"', $html);
file_put_contents("$out/index.html", $html);

foreach (['style.css', 'script.js'] as $file) copy("$root/$file", "$out/$file");
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/assets", FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    $target = $out . '/assets/' . substr($file->getPathname(), strlen("$root/assets/"));
    @mkdir(dirname($target), 0755, true);
    copy($file->getPathname(), $target);
}
/* Read-only admin screens, so the customer can see how the admin page works. */
@mkdir("$out/admin", 0755, true);
copy("$root/admin/admin.css", "$out/admin/admin.css");
$render = function (string $file, string ...$args) use ($root, $out) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg("$root/tools/render-admin.php");
    foreach ($args as $arg) $cmd .= ' ' . escapeshellarg($arg);
    exec($cmd, $lines, $status);
    if ($status !== 0) { fwrite(STDERR, "Failed to render admin page $file\n"); exit(1); }
    file_put_contents("$out/admin/$file", implode("\n", $lines));
};
$render('index.html', 'login');
$content = content();
foreach (['products', 'fabrics'] as $type) {
    $render("$type.html", $type);
    $render("$type-online.html", $type, '', 'online');
    $render("$type-drafts.html", $type, '', 'drafts');
    $render("$type-edit-new.html", $type, 'new');
    foreach ($content[$type] as $item) $render("$type-edit-" . preg_replace('/[^a-z0-9-]/i', '', $item['id']) . '.html', $type, $item['id']);
}
foreach (['texts', 'contact', 'enquiries', 'settings'] as $page) $render("$page.html", $page);

touch("$out/.nojekyll");
echo "Review preview built in $out\n";
