<?php
/* PULSE website core: storage, content, uploads and security helpers.
   All data is stored as files in /data (no database needed). */
declare(strict_types=1);

if (PHP_VERSION_ID < 80000) {
    http_response_code(500);
    exit('This website needs PHP 8.0 or newer. Please change the PHP version in your hosting control panel.');
}

define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', ROOT . '/uploads');
const STORE_GUARD = "<?php exit; ?>\n"; // stops data files from being read in a browser
const MAX_IMAGE_BYTES = 10 * 1024 * 1024;
const MAX_VIDEO_BYTES = 60 * 1024 * 1024;

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* Shortens text to $max characters (works with or without the mbstring extension). */
function text_limit(string $text, int $max): string
{
    return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
}

/* ---------- File storage ---------- */

function store_path(string $name): string
{
    return DATA_DIR . '/' . preg_replace('/[^a-z0-9-]/', '', $name) . '.php';
}

function store_read(string $name, $default = null)
{
    $file = store_path($name);
    if (!is_file($file)) return $default;
    $raw = file_get_contents($file);
    if ($raw === false) return $default;
    if (str_starts_with($raw, STORE_GUARD)) $raw = substr($raw, strlen(STORE_GUARD));
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $default;
}

function store_write(string $name, array $data): void
{
    if (!is_dir(DATA_DIR) && !mkdir(DATA_DIR, 0755, true)) {
        throw new RuntimeException('The data folder could not be created. Check the folder permissions on the hosting.');
    }
    $file = store_path($name);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if ($json === false || file_put_contents($tmp, STORE_GUARD . $json, LOCK_EX) === false) {
        @unlink($tmp);
        throw new RuntimeException('Changes could not be saved. Check that the "data" folder is writable on the hosting.');
    }
    if (!rename($tmp, $file)) {
        @unlink($tmp);
        throw new RuntimeException('Changes could not be saved. Check that the "data" folder is writable on the hosting.');
    }
}

/* Read-modify-write under a lock, so two simultaneous saves cannot overwrite each other. */
function store_update(string $name, $default, callable $change)
{
    if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0755, true);
    $lock = fopen(DATA_DIR . '/.lock-' . preg_replace('/[^a-z0-9-]/', '', $name), 'c');
    if ($lock) flock($lock, LOCK_EX);
    try {
        $data = $change(store_read($name, $default));
        store_write($name, $data);
        return $data;
    } finally {
        if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
    }
}

/* ---------- Website content ---------- */

function default_content(): array
{
    return require __DIR__ . '/defaults.php';
}

/* Saved content, with any missing parts filled from the defaults. */
function content(): array
{
    $defaults = default_content();
    $saved = store_read('content');
    if (!is_array($saved)) return $defaults;
    $result = $defaults;
    foreach ($defaults as $section => $value) {
        if (!array_key_exists($section, $saved)) continue;
        if (in_array($section, ['products', 'fabrics'], true)) {
            $result[$section] = is_array($saved[$section]) ? array_values($saved[$section]) : $value;
        } elseif (is_array($saved[$section])) {
            $result[$section] = array_merge($value, $saved[$section]);
        }
    }
    return $result;
}

function save_content(array $content): void
{
    store_write('content', $content);
    cleanup_uploads($content);
}

/* Online items, sorted by display order, then name. */
function visible_items(array $items): array
{
    $items = array_values(array_filter($items, fn($i) => !empty($i['online']) && !empty($i['image'])));
    return sort_items($items);
}

function sort_items(array $items): array
{
    usort($items, function ($a, $b) {
        $oa = is_numeric($a['order'] ?? null) ? (float) $a['order'] : INF;
        $ob = is_numeric($b['order'] ?? null) ? (float) $b['order'] : INF;
        return $oa <=> $ob ?: strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
    });
    return $items;
}

/* Turns text with blank lines into paragraphs; single line breaks become <br>. */
function paragraphs(string $text, string $class = ''): string
{
    $out = '';
    foreach (preg_split('/\R\s*\R/', trim($text)) as $para) {
        if (trim($para) === '') continue;
        $out .= '<p' . ($class ? ' class="' . h($class) . '"' : '') . '>' . nl2br(h(trim($para)), false) . '</p>';
    }
    return $out;
}

function lines(string $text): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', $text)), 'strlen'));
}

/* Title with an optional red part, e.g. "Our <span>Story</span>". */
function title_html(string $main, string $accent, bool $lineBreak = false): string
{
    $html = h($main);
    if (trim($accent) !== '') $html .= ($lineBreak ? '<br>' : ' ') . '<span>' . h($accent) . '</span>';
    return $html;
}

/* ---------- Uploads ---------- */

function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The file is too large for this hosting (limit: ' . ini_get('upload_max_filesize') . '). Please use a smaller file.',
        UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
        default => 'The file could not be uploaded (error ' . $code . '). Please try again.',
    };
}

function new_upload_name(string $ext): string
{
    return date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
}

function store_uploaded_file(array $file, string $ext): string
{
    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
        throw new RuntimeException('The uploads folder could not be created. Check the folder permissions on the hosting.');
    }
    $name = new_upload_name($ext);
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        throw new RuntimeException('The file could not be saved. Check that the "uploads" folder is writable on the hosting.');
    }
    @chmod(UPLOAD_DIR . '/' . $name, 0644);
    return 'uploads/' . $name;
}

/* Returns the new image path, or null when no file was chosen. */
function handle_image_upload(string $field): ?string
{
    $file = $_FILES[$field] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException(upload_error_message((int) $file['error']));
    if ($file['size'] > MAX_IMAGE_BYTES) throw new RuntimeException('The image is larger than 10 MB. Please use a smaller image.');
    $info = @getimagesize($file['tmp_name']);
    $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'][$info[2] ?? 0] ?? null;
    if (!$info || !$ext) throw new RuntimeException('Please upload a JPG, PNG, WebP or GIF image.');
    return store_uploaded_file($file, $ext);
}

function handle_video_upload(string $field): ?string
{
    $file = $_FILES[$field] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException(upload_error_message((int) $file['error']));
    if ($file['size'] > MAX_VIDEO_BYTES) throw new RuntimeException('The video is larger than 60 MB. Please use a shorter or compressed video.');
    $head = (string) file_get_contents($file['tmp_name'], false, null, 0, 12);
    if (substr($head, 4, 4) !== 'ftyp') throw new RuntimeException('Please upload an MP4 video.');
    return store_uploaded_file($file, 'mp4');
}

/* Deletes uploaded files that are no longer used anywhere on the website. */
function cleanup_uploads(array $content): void
{
    if (!is_dir(UPLOAD_DIR)) return;
    $used = json_encode($content, JSON_UNESCAPED_SLASHES);
    foreach (scandir(UPLOAD_DIR) ?: [] as $file) {
        if (!preg_match('/^\d{8}-[0-9a-f]{12}\.(jpg|png|webp|gif|mp4)$/', $file)) continue;
        if (!str_contains($used, 'uploads/' . $file)) @unlink(UPLOAD_DIR . '/' . $file);
    }
}

/* ---------- Settings ---------- */

function config(): array
{
    return store_read('config', []) ?: [];
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

/* Simple per-IP limiter. Returns false when the limit is reached. */
function rate_limit(string $bucket, int $max, int $seconds, bool $record = true): bool
{
    $ok = true;
    store_update('rate-' . $bucket, [], function (array $data) use ($max, $seconds, $record, &$ok) {
        $now = time();
        foreach ($data as $ip => $times) {
            $data[$ip] = array_values(array_filter($times, fn($t) => $t > $now - $seconds));
            if (!$data[$ip]) unset($data[$ip]);
        }
        $ip = hash('sha256', client_ip());
        $count = count($data[$ip] ?? []);
        if ($count >= $max) { $ok = false; return $data; }
        if ($record) $data[$ip][] = $now;
        return $data;
    });
    return $ok;
}

function rate_limit_clear(string $bucket): void
{
    store_update('rate-' . $bucket, [], function (array $data) {
        unset($data[hash('sha256', client_ip())]);
        return $data;
    });
}

/* ---------- Email ---------- */

/* Sends a plain-text email to the notification address(es) set in the admin page.
   Returns true when at least one address was accepted by the hosting's mail server. */
function send_notification(string $subject, string $body, string $replyTo = ''): bool
{
    $config = config();
    $recipients = array_filter(array_map('trim', explode(',', (string) ($config['notify_email'] ?? ''))),
        fn($a) => filter_var($a, FILTER_VALIDATE_EMAIL));
    if (!$recipients) return false;
    $clean = fn(string $s) => str_replace(["\r", "\n"], ' ', $s);
    $host = strtolower(preg_replace('/[^a-z0-9.-]/i', '', explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0]));
    $host = preg_replace('/^www\./', '', $host) ?: 'localhost';
    $from = filter_var($config['from_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: 'noreply@' . $host;
    $headers = ['From: PULSE Website <' . $from . '>', 'MIME-Version: 1.0', 'Content-Type: text/plain; charset=UTF-8', 'Content-Transfer-Encoding: 8bit'];
    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $headers[] = 'Reply-To: ' . $clean($replyTo);
    $encodedSubject = '=?UTF-8?B?' . base64_encode($clean($subject)) . '?=';
    $sent = false;
    foreach ($recipients as $address) {
        $ok = @mail($address, $encodedSubject, $body, implode("\r\n", $headers), '-f' . $from)
            || @mail($address, $encodedSubject, $body, implode("\r\n", $headers)); // some hosts refuse -f
        $sent = $ok || $sent;
    }
    return $sent;
}
