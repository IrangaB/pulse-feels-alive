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
touch("$out/.nojekyll");
echo "Review preview built in $out\n";
