<?php
/**
 * contact.php
 * ------------------------------------------------------------
 * Contact details + working contact form.
 * Messages are saved to `contact_messages` (created in
 * schema_services.sql) and optionally emailed to the business.
 * ------------------------------------------------------------
 */
session_start();
require_once __DIR__ . '/db_connect.php';

// ---- Business details: edit these in one place ---------------
const BUSINESS_EMAIL    = 'car-mwas@gmail.com';
const BUSINESS_PHONE    = '+254 757353607';
const BUSINESS_WHATSAPP = '254757353607';           // digits only, with country code
const BUSINESS_ADDRESS  = '123 Automotive Lane, Car City, CA 12345';
const BUSINESS_HOURS    = 'Monday - Friday, 9:00 AM - 6:00 PM';
const NOTIFY_EMAIL      = BUSINESS_EMAIL;           // '' = don't send email, only save to DB

function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$errors  = [];
$old     = ['name' => '', 'email' => '', 'message' => ''];
$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']    = trim($_POST['name'] ?? '');
    $old['email']   = trim($_POST['email'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    }

    // Honeypot: real visitors never fill this hidden field
    if (!empty($_POST['website'])) {
        http_response_code(400);
        exit;
    }

    if ($old['name'] === '' || mb_strlen($old['name']) > 100) {
        $errors[] = 'Please enter your name (max 100 characters).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 2000) {
        $errors[] = 'Your message must be between 10 and 2000 characters.';
    }

    if (!$errors) {
        try {
            $stmt = getDbConnection()->prepare(
                'INSERT INTO contact_messages (name, email, message, ip_address)
                 VALUES (:name, :email, :message, :ip)'
            );
            $stmt->execute([
                ':name'    => $old['name'],
                ':email'   => $old['email'],
                ':message' => $old['message'],
                ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            if (NOTIFY_EMAIL !== '') {
                $subject = 'New website enquiry from ' . str_replace(["\r", "\n"], '', $old['name']);
                $body    = "Name: {$old['name']}\nEmail: {$old['email']}\n\n{$old['message']}";
                @mail(NOTIFY_EMAIL, $subject, $body, 'Reply-To: ' . $old['email']);
            }

            // Post/Redirect/Get: refreshing the page won't resend the form
            $_SESSION['csrf']          = bin2hex(random_bytes(32));
            $_SESSION['flash_success'] = 'Thank you! Your message has been sent. We will get back to you soon.';
            header('Location: contact.php#contact-form');
            exit;
        } catch (PDOException $e) {
            error_log('Contact insert failed: ' . $e->getMessage());
            $errors[] = 'Sorry, we could not send your message. Please try again later.';
        }
    }
}

$telHref = 'tel:' . preg_replace('/[^0-9+]/', '', BUSINESS_PHONE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Car-Mwas Automotive Sales</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert { padding:12px 16px; border-radius:4px; margin:12px 0; }
        .alert-success { background:#e6f9f0; border:1px solid #9fdcc0; color:#17603f; }
        .alert-error   { background:#fff3f3; border:1px solid #f3c1c1; color:#a33; }
        .contact-form  { display:grid; gap:8px; max-width:480px; }
        .contact-form input, .contact-form textarea { padding:10px; border:1px solid #ccc; border-radius:4px; font:inherit; }
        .contact-form button { background:#22e1e1; border:none; padding:12px; border-radius:4px; font-weight:bold; cursor:pointer; }
        .hp { position:absolute; left:-9999px; }
        .social a { margin-right:14px; }
        footer { text-align:center; padding:20px; }
    </style>
</head>
<body>
    <div class="contact">
        <header>
            <h1>Contact Us</h1>
        </header>

        <img src="contacts.jpg" alt="Contact Us Image" class="contact-image">

        <p>If you have any questions or need assistance, please feel free to reach out to us:</p>
        <ul>
            <li>Email: <a href="mailto:<?= h(BUSINESS_EMAIL) ?>"><?= h(BUSINESS_EMAIL) ?></a></li>
            <li>Phone: <a href="<?= h($telHref) ?>"><?= h(BUSINESS_PHONE) ?></a></li>
            <li>Address: <?= h(BUSINESS_ADDRESS) ?></li>
            <li>Business Hours: <?= h(BUSINESS_HOURS) ?></li>
            <li>Follow us on social media: CAR-MWAS AUTOMOTIVE <img src="connect.png" alt="Social media"></li>
            <li>WhatsApp: <a href="https://wa.me/<?= h(BUSINESS_WHATSAPP) ?>" target="_blank" rel="noopener"><?= h(BUSINESS_PHONE) ?></a></li>
            <li class="social">
                <a href="https://www.facebook.com/carmwas"  target="_blank" rel="noopener">Facebook</a>
                <a href="https://www.twitter.com/carmwas"   target="_blank" rel="noopener">Twitter</a>
                <a href="https://www.instagram.com/carmwas" target="_blank" rel="noopener">Instagram</a>
            </li>
        </ul>

        <p>We value your feedback and are here to assist you with any inquiries you may have. Thank you for choosing Car-Mwas Automotive Sales!</p>

        <?php if ($success): ?>
            <div class="alert alert-success" id="contact-form"><?= h($success) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-error" id="contact-form">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="contact-form" action="contact.php#contact-form" method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
            <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= h($old['name']) ?>" required maxlength="100">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= h($old['email']) ?>" required maxlength="150">

            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="4" required maxlength="2000"><?= h($old['message']) ?></textarea>

            <button type="submit">Send Message</button>
        </form>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> Car-Mwas Automotive Sales. All rights reserved.</p>
        <p>Designed by CJ</p>
    </footer>
</body>
</html>