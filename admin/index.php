<?php
/* PULSE website admin: products, fabrics, texts & photos, contact & footer, enquiries, settings. */
require dirname(__DIR__) . '/includes/bootstrap.php';

/* ---------- Session & security ---------- */
/* ADMIN_DEMO is only set by tools/render-admin.php, which builds read-only screens for the
   GitHub Pages review copy. It is never set on the real website. */
$demo = defined('ADMIN_DEMO');
const SESSION_IDLE_SECONDS = 4 * 3600;
if ($demo) {
    $_SESSION = ['user' => 'demo', 'csrf' => 'demo', 'seen' => time()];
} else {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_name('pulse_admin');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict', 'secure' => $https]);
    session_start();
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');

    if (!empty($_SESSION['user']) && time() - ($_SESSION['seen'] ?? 0) > SESSION_IDLE_SECONDS) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['flash'] = ['info', 'You were logged out after 4 hours of inactivity. Please log in again.'];
    }
    $_SESSION['seen'] = time();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [$type, $message];
}

function go(string $query = ''): void
{
    header('Location: index.php' . ($query !== '' ? '?' . $query : ''), true, 303);
    exit;
}

function post(string $key, int $max = 300): string
{
    return text_limit(trim((string) ($_POST[$key] ?? '')), $max);
}

/* Admin pages show site images from one folder up. */
function site_url(string $path): string
{
    return '../' . ltrim($path, '/');
}

/* ---------- Editable text fields (label, type) ---------- */
$TEXT_GROUPS = [
    'texts' => [
        'hero' => ['Top banner', [
            'eyebrow' => ['Small red heading', 'text'],
            'title_line1' => ['Headline, first line (white)', 'text'],
            'title_line2' => ['Headline, second line (red)', 'text'],
            'intro' => ['Text under the headline', 'textarea'],
            'caption_left' => ['Bottom caption, left', 'text'],
            'caption_right' => ['Bottom caption, right', 'text'],
            'video' => ['Background video (MP4, max 60 MB)', 'video'],
            'poster' => ['Background image (shown while the video loads)', 'image'],
        ]],
        'intro' => ['Introduction (below the banner)', [
            'eyebrow' => ['Small red heading', 'text'],
            'title' => ['Heading', 'text'],
            'title_accent' => ['Heading, red ending', 'text'],
            'text' => ['Text', 'textarea'],
        ]],
        'story' => ['Our Story', [
            'eyebrow' => ['Small red heading', 'text'],
            'title' => ['Heading', 'text'],
            'title_accent' => ['Heading, red ending', 'text'],
            'heading_line1' => ['Sub-heading, first line', 'text'],
            'heading_line2' => ['Sub-heading, second line (red)', 'text'],
            'text' => ['Story text (leave an empty line between paragraphs)', 'textarea'],
            'highlight' => ['Highlighted line', 'text'],
            'image' => ['Photo', 'image'],
        ]],
        'features' => ['Feature bar (image strip below Our Story)', [
            'image' => ['Image', 'image'],
            'alt' => ['What the image says (for search engines and screen readers)', 'textarea'],
        ]],
        'fabrics_section' => ['Fabrics section heading', [
            'eyebrow' => ['Small red heading', 'text'],
            'title' => ['Heading', 'text'],
            'title_accent' => ['Heading, red ending', 'text'],
            'text' => ['Text', 'textarea'],
        ]],
        'products_section' => ['Product Categories heading (home page)', [
            'eyebrow' => ['Small red heading', 'text'],
            'title' => ['Heading', 'text'],
            'title_accent' => ['Heading, red ending', 'text'],
            'text' => ['Text', 'textarea'],
        ]],
        'gallery_page' => ['Product Gallery page', [
            'eyebrow' => ['Small red heading', 'text'],
            'title' => ['Heading', 'text'],
            'title_accent' => ['Heading, red ending', 'text'],
            'text' => ['Text', 'textarea'],
        ]],
        'customers_page' => ['Our Customers page', [
            'eyebrow' => ['Small red heading', 'text'],
            'title' => ['Heading', 'text'],
            'title_accent' => ['Heading, red ending', 'text'],
            'text' => ['Text', 'textarea'],
            'button_label' => ['Button text (leads to the enquiry form)', 'text'],
        ]],
    ],
    'contact' => [
        'contact' => ['Contact section', [
            'eyebrow' => ['Small red heading', 'text'],
            'title_line1' => ['Heading, first line', 'text'],
            'title_line2' => ['Heading, second line (red)', 'text'],
            'text' => ['Text', 'textarea'],
            'bullets' => ['Bullet points (one per line)', 'textarea'],
        ]],
        'footer' => ['Footer', [
            'tagline' => ['Short text next to the logo', 'text'],
            'company_details' => ['Company details (name, Y-Tunnus)', 'text'],
        ]],
    ],
];

/* The lists the customer manages. Each entry describes the labels and extra fields of its form. */
$ITEM_TYPES = [
    'products' => [
        'plural' => 'Product categories', 'singular' => 'category',
        'name_label' => 'Category name',
        'name_help' => 'Used in the enquiry form product list and for search engines. On the website, the name shown is the one inside the image.',
        'image_label' => 'Image',
        'image_help' => 'Square image, about 1000 × 1000 px. Put the category name and any badge (e.g. "Coming Soon") inside the image.',
        'preview' => 'square-contain',
    ],
    'gallery' => [
        'plural' => 'Product gallery', 'singular' => 'gallery photo',
        'name_label' => 'Club or customer name',
        'name_help' => 'Shown under the photo on the Product Gallery page.',
        'image_label' => 'Product photo',
        'image_help' => 'Square photos look best, about 1000 × 1000 px. The photo is cropped to a square, so keep the product in the centre.',
        'preview' => 'square',
    ],
    'fabrics' => [
        'plural' => 'Fabrics', 'singular' => 'fabric',
        'name_label' => 'Fabric name',
        'name_help' => 'Shown under the picture.',
        'image_label' => 'Image',
        'image_help' => 'Upright image, about 800 × 1000 px. The picture is cropped to this shape.',
        'description' => true,
        'preview' => 'portrait',
    ],
    'customers' => [
        'plural' => 'Customers', 'singular' => 'customer',
        'name_label' => 'Customer name',
        'name_help' => 'Shown under the logo in the logo grid on the Our Customers page.',
        'image_label' => 'Logo',
        'image_help' => 'PNG with a transparent or white background works best, at least 400 px wide.',
        'url' => true,
        'contain' => true,
        'preview' => 'square-contain',
    ],
];

$config = config();
$installed = !empty($config['password_hash']);
$loggedIn = $installed && !empty($_SESSION['user']);
$page = (string) ($_GET['p'] ?? 'products');
if ($demo) {
    $installed = true;
    $loggedIn = $page !== 'login';
}

/* ---------- Form actions ---------- */
if (!$demo && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        flash('error', 'Your session expired. Please try again.');
        go();
    }
    $action = (string) ($_POST['action'] ?? '');

    try {
        if (!$installed && $action === 'setup') {
            $setup = store_read('setup-code', []);
            if (empty($setup['code']) || !hash_equals((string) $setup['code'], post('code', 100))) {
                rate_limit('login', 5, 900);
                throw new RuntimeException('The setup code is not correct.');
            }
            $username = post('username', 60);
            $password = (string) ($_POST['password'] ?? '');
            if ($username === '') throw new RuntimeException('Please choose a username.');
            if (strlen($password) < 10) throw new RuntimeException('The password must be at least 10 characters long.');
            if ($password !== ($_POST['password_confirm'] ?? '')) throw new RuntimeException('The two passwords do not match.');
            $email = post('notify_email', 300);
            store_write('config', [
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'notify_email' => $email,
                'from_email' => '',
            ]);
            @unlink(store_path('setup-code'));
            session_regenerate_id(true);
            $_SESSION['user'] = $username;
            flash('success', 'Your admin login is ready. Welcome!');
            go();
        }

        if ($installed && $action === 'login') {
            if (!rate_limit('login', 5, 900, false)) {
                throw new RuntimeException('Too many failed login attempts. Please wait 15 minutes and try again.');
            }
            $userOk = hash_equals((string) $config['username'], post('username', 60));
            $passOk = password_verify((string) ($_POST['password'] ?? ''), (string) $config['password_hash']);
            if (!$userOk || !$passOk) {
                rate_limit('login', 5, 900);
                throw new RuntimeException('The username or password is not correct.');
            }
            rate_limit_clear('login');
            session_regenerate_id(true);
            $_SESSION['user'] = $config['username'];
            go();
        }

        if (!$loggedIn) go();

        switch ($action) {
            case 'logout':
                $_SESSION = [];
                session_regenerate_id(true);
                flash('info', 'You have been logged out.');
                go();

            case 'item_save':
                $type = (string) ($_POST['type'] ?? '');
                if (!isset($ITEM_TYPES[$type])) go();
                $id = (string) ($_POST['id'] ?? '');
                $_SESSION['old'] = $_POST;
                $name = post('name', 120);
                if ($name === '') throw new RuntimeException('Please enter a name.');
                $orderRaw = post('order', 10);
                if ($orderRaw !== '' && !is_numeric($orderRaw)) throw new RuntimeException('Display order must be a number.');
                $c = content();
                $index = null;
                foreach ($c[$type] as $i => $item) if (($item['id'] ?? '') === $id) $index = $i;
                $image = handle_image_upload('image');
                if ($index === null && $image === null) throw new RuntimeException('Please choose an image.');
                $item = $index === null ? ['id' => bin2hex(random_bytes(6))] : $c[$type][$index];
                $item['name'] = $name;
                if (!empty($ITEM_TYPES[$type]['description'])) $item['description'] = post('description', 600);
                if (!empty($ITEM_TYPES[$type]['url'])) {
                    $url = post('url', 300);
                    if ($url !== '' && !preg_match('~^https?://~i', $url)) $url = 'https://' . $url;
                    if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) throw new RuntimeException('The website address is not valid. Example: https://www.example.fi');
                    $item['url'] = $url;
                }
                $item['order'] = $orderRaw === '' ? null : $orderRaw + 0;
                $item['online'] = !empty($_POST['online']);
                if ($image !== null) $item['image'] = $image;
                if ($index === null) $c[$type][] = $item; else $c[$type][$index] = $item;
                save_content($c);
                unset($_SESSION['old']);
                flash('success', '"' . $name . '" was saved' . ($item['online'] ? ' and is online.' : ' as a draft (not visible on the website).'));
                go('p=' . $type);

            case 'item_toggle':
            case 'item_delete':
                $type = (string) ($_POST['type'] ?? '');
                if (!isset($ITEM_TYPES[$type])) go();
                $c = content();
                foreach ($c[$type] as $i => $item) {
                    if (($item['id'] ?? '') !== ($_POST['id'] ?? '')) continue;
                    if ($action === 'item_delete') {
                        array_splice($c[$type], $i, 1);
                        flash('success', '"' . $item['name'] . '" was deleted.');
                    } else {
                        $c[$type][$i]['online'] = empty($item['online']);
                        flash('success', '"' . $item['name'] . '" is now ' . ($c[$type][$i]['online'] ? 'online.' : 'a draft (hidden from the website).'));
                    }
                    save_content($c);
                    break;
                }
                go('p=' . $type);

            case 'texts_save':
                $group = (string) ($_POST['group'] ?? '');
                if (!isset($TEXT_GROUPS[$group])) go();
                $c = content();
                foreach ($TEXT_GROUPS[$group] as $section => [, $fields]) {
                    foreach ($fields as $key => [$label, $kind]) {
                        $input = "{$section}__{$key}";
                        if ($kind === 'image' || $kind === 'video') {
                            try {
                                $file = $kind === 'image' ? handle_image_upload($input) : handle_video_upload($input);
                            } catch (RuntimeException $e) {
                                throw new RuntimeException($label . ': ' . $e->getMessage());
                            }
                            if ($file !== null) $c[$section][$key] = $file;
                        } elseif (isset($_POST[$input])) {
                            $c[$section][$key] = text_limit(trim(str_replace("\r\n", "\n", (string) $_POST[$input])), $kind === 'textarea' ? 5000 : 300);
                        }
                    }
                }
                save_content($c);
                flash('success', 'Your changes are saved and live on the website.');
                go('p=' . $group);

            case 'enquiry_delete':
                store_update('enquiries', [], fn($list) => array_values(array_filter($list, fn($e) => ($e['id'] ?? '') !== ($_POST['id'] ?? ''))));
                flash('success', 'The enquiry was deleted.');
                go('p=enquiries');

            case 'settings_save':
                $emails = array_filter(array_map('trim', explode(',', post('notify_email', 500))));
                foreach ($emails as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('"' . $email . '" is not a valid email address.');
                }
                $from = post('from_email', 200);
                if ($from !== '' && !filter_var($from, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('The sender address is not a valid email address.');
                store_update('config', [], fn($cfg) => array_merge($cfg, ['notify_email' => implode(', ', $emails), 'from_email' => $from]));
                flash('success', 'Email settings saved.');
                go('p=settings');

            case 'test_email':
                $ok = send_notification('PULSE website: test email', "This is a test email from the PULSE website admin page.\n\nIf you can read this, enquiry notifications are working.");
                flash($ok ? 'success' : 'error', $ok
                    ? 'A test email was sent. If it does not arrive within a few minutes, check the spam folder.'
                    : 'The test email could not be sent. Check the notification address, or ask the hosting company to enable PHP mail. Enquiries are still saved under Enquiries.');
                go('p=settings');

            case 'login_save':
                if (!password_verify((string) ($_POST['current_password'] ?? ''), (string) $config['password_hash'])) {
                    throw new RuntimeException('The current password is not correct.');
                }
                $username = post('username', 60);
                if ($username === '') throw new RuntimeException('Please enter a username.');
                $new = (string) ($_POST['new_password'] ?? '');
                $update = ['username' => $username];
                if ($new !== '') {
                    if (strlen($new) < 10) throw new RuntimeException('The new password must be at least 10 characters long.');
                    if ($new !== ($_POST['new_password_confirm'] ?? '')) throw new RuntimeException('The two new passwords do not match.');
                    $update['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
                }
                store_update('config', [], fn($cfg) => array_merge($cfg, $update));
                $_SESSION['user'] = $username;
                session_regenerate_id(true);
                flash('success', $new !== '' ? 'Your username and password were updated.' : 'Your username was updated.');
                go('p=settings');
        }
        go();
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
        $back = $_POST['back'] ?? '';
        go(is_string($back) && preg_match('/^[a-z0-9=&_-]*$/i', $back) ? $back : '');
    }
}

/* ---------- Page rendering ---------- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$old = $_SESSION['old'] ?? null;
unset($_SESSION['old']);
$enquiries = $loggedIn ? store_read('enquiries', []) : [];
if ($demo) {
    $enquiries = [
        ['id' => 'demo1', 'date' => date('c', strtotime('-2 hours')), 'new' => true, 'name' => 'Example: Matti Virtanen', 'email' => 'matti@example.com', 'phone' => '+358 40 000 0000', 'organisation' => 'Example Ice Hockey Club', 'product' => 'T-Shirts', 'quantity' => '40', 'message' => "This is an example enquiry for the review demo.\nWe would like 40 training shirts in our club colours with the logo on the chest."],
        ['id' => 'demo2', 'date' => date('c', strtotime('-3 days')), 'new' => false, 'name' => 'Example: Anna Korhonen', 'email' => 'anna@example.com', 'phone' => '', 'organisation' => 'Example Company Oy', 'product' => 'Neck Warmers', 'quantity' => '120', 'message' => 'This is an example enquiry for the review demo. Branded neck warmers for our winter event.'],
    ];
    $config = ['username' => 'demo', 'notify_email' => 'sales@example.com', 'from_email' => ''];
}
$newCount = count(array_filter($enquiries, fn($e) => !empty($e['new'])));

function text_field(string $name, string $label, string $value, string $kind, string $help = ''): string
{
    $id = 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', $name);
    $control = $kind === 'textarea'
        ? '<textarea id="' . $id . '" name="' . h($name) . '" rows="' . max(3, min(10, substr_count($value, "\n") + 3)) . '">' . h($value) . '</textarea>'
        : '<input id="' . $id . '" name="' . h($name) . '" type="text" value="' . h($value) . '">';
    return '<div class="field"><label for="' . $id . '">' . h($label) . '</label>' . $control . ($help ? '<p class="help">' . h($help) . '</p>' : '') . '</div>';
}

function media_field(string $name, string $label, string $value, string $kind): string
{
    $id = 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', $name);
    $preview = $kind === 'video'
        ? '<video src="' . h(site_url($value)) . '" muted playsinline preload="metadata" class="thumb thumb-wide"></video>'
        : '<img src="' . h(site_url($value)) . '" alt="" class="thumb thumb-wide">';
    $accept = $kind === 'video' ? 'video/mp4' : 'image/jpeg,image/png,image/webp,image/gif';
    return '<div class="field"><label for="' . $id . '">' . h($label) . '</label>'
        . '<div class="media-row">' . ($value !== '' ? $preview : '') . '<div><input id="' . $id . '" name="' . h($name) . '" type="file" accept="' . $accept . '" data-preview="' . ($kind === 'video' ? 'video' : 'wide') . '">'
        . '<p class="help">Leave empty to keep the current ' . ($kind === 'video' ? 'video' : 'image') . '.</p></div></div></div>';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>PULSE Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<header class="topbar">
  <a href="index.php" class="brand"><img src="../assets/images/pulse-logo.png" alt="PULSE"><span>Admin</span></a>
  <?php if ($loggedIn): ?>
  <div class="topbar-actions">
    <a href="../" target="_blank" rel="noopener">View website ↗</a>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button class="link-button" type="submit">Log out</button></form>
  </div>
  <?php endif; ?>
</header>

<?php if ($loggedIn): ?>
<nav class="tabs" aria-label="Admin sections">
  <?php foreach (['products' => 'Categories', 'gallery' => 'Gallery', 'fabrics' => 'Fabrics', 'customers' => 'Customers', 'texts' => 'Texts & photos', 'contact' => 'Contact & footer', 'enquiries' => 'Enquiries', 'settings' => 'Settings'] as $key => $label): ?>
  <a href="?p=<?= $key ?>" class="<?= $page === $key ? 'active' : '' ?>"><?= h($label) ?><?php if ($key === 'enquiries' && $newCount): ?> <span class="badge"><?= $newCount ?></span><?php endif; ?></a>
  <?php endforeach; ?>
</nav>
<?php endif; ?>

<main class="wrap">
<?php if ($flash): ?><div class="flash flash-<?= h($flash[0]) ?>" role="status"><?= h($flash[1]) ?></div><?php endif; ?>

<?php if (!$installed): /* ---------- First-time setup ---------- */ ?>
  <section class="panel narrow">
    <h1>Set up your admin login</h1>
    <?php if (!store_read('setup-code')): ?>
      <p>Setup is locked. Ask your website developer for a setup code file (<code>data/setup-code.php</code>).</p>
    <?php else: ?>
      <p>Enter the setup code from your developer, then choose your username and password.</p>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="action" value="setup">
        <div class="field"><label for="code">Setup code</label><input id="code" name="code" type="text" autocomplete="off" required></div>
        <div class="field"><label for="username">Username</label><input id="username" name="username" type="text" autocomplete="username" required></div>
        <div class="field"><label for="password">Password (at least 10 characters)</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="10" required></div>
        <div class="field"><label for="password_confirm">Repeat password</label><input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" minlength="10" required></div>
        <div class="field"><label for="notify_email">Email address for new enquiries</label><input id="notify_email" name="notify_email" type="email" autocomplete="email" value="<?= h(DEFAULT_NOTIFY_EMAIL) ?>"></div>
        <button class="btn" type="submit">Create admin login</button>
      </form>
    <?php endif; ?>
  </section>

<?php elseif (!$loggedIn): /* ---------- Login ---------- */ ?>
  <section class="panel narrow">
    <h1>Log in</h1>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="login">
      <div class="field"><label for="username">Username</label><input id="username" name="username" type="text" autocomplete="username" required autofocus></div>
      <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
      <button class="btn" type="submit">Log in</button>
    </form>
  </section>

<?php elseif (isset($ITEM_TYPES[$page])): /* ---------- Categories / Gallery / Fabrics / Customers ---------- */
    $type = $page;
    $meta = $ITEM_TYPES[$type];
    $c = content();
    $items = sort_items($c[$type]);
    $editId = (string) ($_GET['edit'] ?? '');
    $editing = null;
    if ($editId === 'new') $editing = ['id' => '', 'name' => '', 'description' => '', 'url' => '', 'image' => '', 'order' => '', 'online' => false];
    foreach ($items as $item) if ($editId !== '' && ($item['id'] ?? '') === $editId) $editing = $item;
    if ($editing && $old && ($old['id'] ?? '') === $editing['id']) {
        $editing = array_merge($editing, array_intersect_key($old, array_flip(['name', 'description', 'url', 'order'])), ['online' => !empty($old['online'])]);
    }
?>
  <?php if ($editing !== null): ?>
  <section class="panel narrow">
    <h1><?= $editing['id'] === '' ? 'Add a ' . $meta['singular'] : 'Edit ' . h($editing['name']) ?></h1>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="item_save">
      <input type="hidden" name="type" value="<?= $type ?>">
      <input type="hidden" name="id" value="<?= h($editing['id']) ?>">
      <input type="hidden" name="back" value="p=<?= $type ?>&edit=<?= h($editing['id'] === '' ? 'new' : $editing['id']) ?>">
      <?= text_field('name', $meta['name_label'], (string) $editing['name'], 'text', $meta['name_help']) ?>
      <?php if (!empty($meta['description'])): ?><?= text_field('description', 'Description', (string) ($editing['description'] ?? ''), 'textarea', 'Shown under the name.') ?><?php endif; ?>
      <?php if (!empty($meta['url'])): ?><?= text_field('url', 'Website (optional)', (string) ($editing['url'] ?? ''), 'text', 'If filled in, clicking the logo opens this website. Example: https://www.example.fi') ?><?php endif; ?>
      <div class="field">
        <label for="f-image"><?= h($meta['image_label']) ?><?= $editing['id'] === '' ? ' *' : '' ?></label>
        <div class="media-row">
          <?php if ($editing['image']): ?><img src="<?= h(site_url($editing['image'])) ?>" alt="" class="thumb thumb-large<?= !empty($meta['contain']) ? ' thumb-contain' : '' ?>"><?php endif; ?>
          <div>
            <input id="f-image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-preview="<?= h($meta['preview']) ?>"<?= $editing['id'] === '' ? ' required' : '' ?>>
            <p class="help"><?= h($meta['image_help']) ?><?= $editing['id'] !== '' ? ' Leave empty to keep the current image.' : '' ?></p>
          </div>
        </div>
      </div>
      <div class="field field-short"><label for="f-order">Display order</label><input id="f-order" name="order" type="number" step="any" value="<?= h((string) ($editing['order'] ?? '')) ?>"><p class="help">Lower numbers are shown first (e.g. 10, 20, 30).</p></div>
      <label class="check"><input type="checkbox" name="online" value="1"<?= !empty($editing['online']) ? ' checked' : '' ?>> <span><strong>Online</strong>: show on the website. Leave unticked to save as a draft.</span></label>
      <div class="actions"><button class="btn" type="submit">Save</button><a class="btn btn-ghost" href="?p=<?= $type ?>">Cancel</a></div>
    </form>
  </section>
  <?php else:
      $show = (string) ($_GET['show'] ?? 'all');
      $online = array_filter($items, fn($i) => !empty($i['online']));
      $listed = $show === 'online' ? $online : ($show === 'drafts' ? array_filter($items, fn($i) => empty($i['online'])) : $items);
  ?>
  <section class="panel">
    <div class="panel-head">
      <div><h1><?= $meta['plural'] ?></h1><p class="muted"><?= count($online) ?> online · <?= count($items) - count($online) ?> draft</p></div>
      <a class="btn" href="?p=<?= $type ?>&edit=new">+ Add <?= $meta['singular'] ?></a>
    </div>
    <div class="filters">
      <?php foreach (['all' => 'All', 'online' => 'Online', 'drafts' => 'Drafts'] as $key => $label): ?>
      <a href="?p=<?= $type ?>&show=<?= $key ?>" class="<?= $show === $key ? 'active' : '' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
    <?php if (!$listed): ?>
      <p class="empty">Nothing here yet. <?= $show === 'drafts' ? 'Unticked items appear here.' : 'Use "+ Add ' . $meta['singular'] . '" to add the first one.' ?></p>
    <?php else: ?>
    <ul class="item-list">
      <?php foreach ($listed as $item): ?>
      <li class="item">
        <img src="<?= h(site_url($item['image'] ?? '')) ?>" alt="" class="thumb<?= !empty($meta['contain']) ? ' thumb-contain' : '' ?>">
        <div class="item-main">
          <strong><?= h($item['name']) ?></strong>
          <?php if (!empty($meta['description']) && !empty($item['description'])): ?><span class="muted clamp"><?= h($item['description']) ?></span><?php endif; ?>
          <?php if (!empty($meta['url']) && !empty($item['url'])): ?><span class="muted clamp"><?= h($item['url']) ?></span><?php endif; ?>
          <span class="muted">Order: <?= h((string) ($item['order'] ?? '–')) ?></span>
        </div>
        <span class="pill <?= !empty($item['online']) ? 'pill-online' : 'pill-draft' ?>"><?= !empty($item['online']) ? 'Online' : 'Draft' ?></span>
        <div class="item-actions">
          <a class="btn btn-small btn-ghost" href="?p=<?= $type ?>&edit=<?= h($item['id']) ?>">Edit</a>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="item_toggle"><input type="hidden" name="type" value="<?= $type ?>"><input type="hidden" name="id" value="<?= h($item['id']) ?>">
            <button class="btn btn-small btn-ghost" type="submit"><?= !empty($item['online']) ? 'Set to draft' : 'Put online' ?></button></form>
          <form method="post" onsubmit="return confirm('Delete &quot;<?= h(addslashes($item['name'])) ?>&quot; permanently?');"><?= csrf_field() ?><input type="hidden" name="action" value="item_delete"><input type="hidden" name="type" value="<?= $type ?>"><input type="hidden" name="id" value="<?= h($item['id']) ?>">
            <button class="btn btn-small btn-danger" type="submit">Delete</button></form>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </section>
  <?php endif; ?>

<?php elseif (isset($TEXT_GROUPS[$page])): /* ---------- Texts & photos / Contact & footer ---------- */
    $c = content();
?>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="action" value="texts_save"><input type="hidden" name="group" value="<?= $page ?>"><input type="hidden" name="back" value="p=<?= $page ?>">
    <?php foreach ($TEXT_GROUPS[$page] as $section => [$title, $fields]): ?>
    <section class="panel">
      <h2><?= h($title) ?></h2>
      <?php foreach ($fields as $key => [$label, $kind]):
          $value = (string) ($c[$section][$key] ?? '');
          echo in_array($kind, ['image', 'video'], true) ? media_field("{$section}__{$key}", $label, $value, $kind) : text_field("{$section}__{$key}", $label, $value, $kind);
      endforeach; ?>
    </section>
    <?php endforeach; ?>
    <div class="sticky-save"><button class="btn" type="submit">Save changes</button><span class="muted">Changes go live as soon as you save.</span></div>
  </form>

<?php elseif ($page === 'enquiries'): /* ---------- Enquiries ---------- */ ?>
  <section class="panel">
    <div class="panel-head"><div><h1>Enquiries</h1><p class="muted"><?= count($enquiries) ?> received<?= $newCount ? ' · ' . $newCount . ' new' : '' ?></p></div></div>
    <?php if (!$enquiries): ?>
      <p class="empty">No enquiries yet. Messages sent through the website's contact form will appear here.</p>
    <?php endif; ?>
    <?php foreach ($enquiries as $e): ?>
    <article class="enquiry<?= !empty($e['new']) ? ' enquiry-new' : '' ?>">
      <header>
        <div><strong><?= h($e['name']) ?></strong><?php if (!empty($e['new'])): ?> <span class="pill pill-online">New</span><?php endif; ?><br>
          <span class="muted"><?= h(date('j M Y, H:i', strtotime($e['date']) ?: time())) ?></span></div>
        <form method="post" onsubmit="return confirm('Delete this enquiry permanently?');"><?= csrf_field() ?><input type="hidden" name="action" value="enquiry_delete"><input type="hidden" name="id" value="<?= h($e['id']) ?>"><button class="btn btn-small btn-danger" type="submit">Delete</button></form>
      </header>
      <dl>
        <dt>Email</dt><dd><a href="mailto:<?= h($e['email']) ?>"><?= h($e['email']) ?></a></dd>
        <?php foreach (['phone' => 'Phone', 'organisation' => 'Team / company', 'product' => 'Product', 'quantity' => 'Quantity'] as $key => $label): if (($e[$key] ?? '') === '') continue; ?>
        <dt><?= $label ?></dt><dd><?= h($e[$key]) ?></dd>
        <?php endforeach; ?>
      </dl>
      <p class="message"><?= nl2br(h($e['message'])) ?></p>
    </article>
    <?php endforeach; ?>
  </section>
  <?php if ($newCount && !$demo) store_update('enquiries', [], fn($list) => array_map(fn($e) => ['new' => false] + $e, $list)); ?>

<?php elseif ($page === 'settings'): /* ---------- Settings ---------- */
    if (!$demo) $config = config();
?>
  <section class="panel narrow">
    <h2>Enquiry emails</h2>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="settings_save"><input type="hidden" name="back" value="p=settings">
      <div class="field"><label for="notify_email">Send new enquiries to</label><input id="notify_email" name="notify_email" type="text" value="<?= h($config['notify_email'] ?? '') ?>"><p class="help">One address, or several separated by commas.</p></div>
      <div class="field"><label for="from_email">Sender address (optional)</label><input id="from_email" name="from_email" type="email" value="<?= h($config['from_email'] ?? '') ?>"><p class="help">Leave empty to use noreply@ plus your website domain. If emails go to spam, use an address on your own domain.</p></div>
      <button class="btn" type="submit">Save email settings</button>
    </form>
    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="test_email"><button class="btn btn-ghost" type="submit">Send a test email</button></form>
  </section>
  <section class="panel narrow">
    <h2>Your login</h2>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="login_save"><input type="hidden" name="back" value="p=settings">
      <div class="field"><label for="s-username">Username</label><input id="s-username" name="username" type="text" autocomplete="username" value="<?= h($config['username'] ?? '') ?>" required></div>
      <div class="field"><label for="s-new">New password (leave empty to keep the current one)</label><input id="s-new" name="new_password" type="password" autocomplete="new-password" minlength="10"></div>
      <div class="field"><label for="s-new2">Repeat new password</label><input id="s-new2" name="new_password_confirm" type="password" autocomplete="new-password" minlength="10"></div>
      <div class="field"><label for="s-current">Current password (required to save)</label><input id="s-current" name="current_password" type="password" autocomplete="current-password" required></div>
      <button class="btn" type="submit">Update login</button>
    </form>
  </section>

<?php else: ?>
  <section class="panel"><p>Page not found. <a href="index.php">Go to Products</a></p></section>
<?php endif; ?>
</main>
<script src="admin.js"></script>
</body>
</html>
