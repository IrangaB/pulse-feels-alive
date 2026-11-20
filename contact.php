<?php
/* Receives the enquiry form: saves it (visible in /admin → Enquiries) and emails it to the customer. */
require __DIR__ . '/includes/bootstrap.php';

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

function respond(bool $ok, string $message, int $status = 200): void
{
    global $wantsJson;
    if ($wantsJson) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $message]);
    } elseif ($ok) {
        header('Location: index.php?sent=1#contact', true, 303);
    } else {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Enquiry not sent</title>'
            . '<p style="font-family:Arial,sans-serif;max-width:560px;margin:60px auto;padding:0 20px">' . h($message) . '<br><br><a href="index.php#contact">Back to the form</a></p>';
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') respond(false, 'Please use the enquiry form on the website.', 405);

$field = fn(string $key, int $max) => text_limit(trim((string) ($_POST[$key] ?? '')), $max);
$data = [
    'name' => $field('name', 120),
    'email' => $field('email', 200),
    'phone' => $field('phone', 40),
    'organisation' => $field('organisation', 160),
    'product' => $field('product', 160),
    'quantity' => $field('quantity', 12),
    'message' => $field('message', 5000),
];

// Spam protection: hidden field must stay empty, and real people take a few seconds to fill the form.
$started = (int) ($_POST['started'] ?? 0);
if ($field('website', 200) !== '' || ($started && time() - $started < 3)) {
    respond(true, 'Thank you for contacting PULSE. We have received your enquiry and will get back to you.');
}

if ($data['name'] === '' || $data['message'] === '') respond(false, 'Please fill in your name and message.', 422);
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) respond(false, 'Please enter a valid email address.', 422);
if ($data['quantity'] !== '' && !ctype_digit($data['quantity'])) respond(false, 'Please enter the quantity as a whole number.', 422);
if (!rate_limit('contact', 5, 600)) respond(false, 'Too many enquiries were sent from your connection. Please wait a few minutes and try again.', 429);

$enquiry = ['id' => bin2hex(random_bytes(6)), 'date' => date('c'), 'new' => true] + $data;

try {
    store_update('enquiries', [], function (array $list) use ($enquiry) {
        array_unshift($list, $enquiry);
        return array_slice($list, 0, 2000);
    });
} catch (Throwable $e) {
    error_log('PULSE enquiry could not be saved: ' . $e->getMessage());
}

/* Email notification */
$body = "A new enquiry was sent from the PULSE website.\n\n"
    . "Name: {$data['name']}\nEmail: {$data['email']}\nPhone: {$data['phone']}\n"
    . "Team / company: {$data['organisation']}\nProduct: {$data['product']}\nQuantity: {$data['quantity']}\n\n"
    . "Message:\n{$data['message']}\n\n"
    . "Reply to this email to answer the customer. All enquiries are also listed in the website admin page.";
if (!send_notification('New PULSE enquiry from ' . $data['name'], $body, $data['email'])) {
    error_log('PULSE enquiry email was not sent (no notification address set, or the mail server refused it). The enquiry is saved in the admin page.');
}

respond(true, 'Thank you for contacting PULSE. We have received your enquiry and will get back to you.');
