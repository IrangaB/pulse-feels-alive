<?php
/* Builds a static review copy of the website (form switched off, read-only admin demo) for GitHub Pages.
   Usage: php tools/build-preview.php <output-folder>   — run automatically by .github/workflows/review-preview.yml */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$out = rtrim($argv[1] ?? $root . '/_site', '/');
@mkdir($out, 0755, true);

/* Renders one PHP page in a separate process (each page declares its own functions). */
function run_php(array $args): string
{
    $cmd = escapeshellarg(PHP_BINARY);
    foreach ($args as $arg) $cmd .= ' ' . escapeshellarg($arg);
    exec($cmd, $lines, $status);
    if ($status !== 0) { fwrite(STDERR, "Failed: $cmd\n"); exit(1); }
    return implode("\n", $lines);
}

foreach (['index', 'gallery', 'customers'] as $page) {
    $html = run_php(['-r', 'define("STATIC_EXPORT", true); require $argv[1];', "$root/$page.php"]);
    file_put_contents("$out/$page.html", $html);
}

foreach (['style.css', 'script.js'] as $file) copy("$root/$file", "$out/$file");
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/assets", FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    $target = $out . '/assets/' . substr($file->getPathname(), strlen("$root/assets/"));
    @mkdir(dirname($target), 0755, true);
    copy($file->getPathname(), $target);
}

/* Read-only admin screens, so the customer can see how the admin page works. */
require $root . '/includes/bootstrap.php';
@mkdir("$out/admin", 0755, true);
copy("$root/admin/admin.css", "$out/admin/admin.css");
$render = function (string $file, string ...$args) use ($root, $out) {
    file_put_contents("$out/admin/$file", run_php(array_merge(["$root/tools/render-admin.php"], $args)));
};
$render('index.html', 'login');
$content = content();
foreach (LIST_SECTIONS as $type) {
    $render("$type.html", $type);
    $render("$type-online.html", $type, '', 'online');
    $render("$type-drafts.html", $type, '', 'drafts');
    $render("$type-edit-new.html", $type, 'new');
    foreach ($content[$type] as $item) $render("$type-edit-" . preg_replace('/[^a-z0-9-]/i', '', $item['id']) . '.html', $type, $item['id']);
}
foreach (['texts', 'contact', 'enquiries', 'settings'] as $page) $render("$page.html", $page);

touch("$out/.nojekyll");
echo "Review preview built in $out\n";
