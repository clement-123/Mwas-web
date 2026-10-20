<?php
/**
 * services.php
 * ------------------------------------------------------------
 * - Loads the services list from the `services` table
 * - Handles the Contact Us form (validation, CSRF, honeypot,
 *   saves to `contact_messages`, optional email notification)
 * ------------------------------------------------------------
 */
session_start();
require_once __DIR__ . '/db_connect.php';

// Set to an address to also receive an email for each message ('' = disabled)
const NOTIFY_EMAIL = 'carmwas@gmail.com';

function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// ---- CSRF token ----------------------------------------------
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$errors = [];
$old    = ['name' => '', 'email' => '', 'phone' => '', 'message' => ''];

// One-time success message (set after redirect)
$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

// ---- Handle contact form submission --------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']    = trim($_POST['name'] ?? '');
    $old['email']   = trim($_POST['email'] ?? '');
    $old['phone']   = trim($_POST['phone'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

    // CSRF check
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    }

    // Honeypot: real users never fill this hidden field
    if (!empty($_POST['website'])) {
        http_response_code(400);
        exit;
    }

    // Validation
    if ($old['name'] === '' || mb_strlen($old['name']) > 100) {
        $errors[] = 'Please enter your name (max 100 characters).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($old['phone'] !== '' && !preg_match('/^[0-9+\-\s()]{7,30}$/', $old['phone'])) {
        $errors[] = 'Please enter a valid phone number or leave it blank.';
    }
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 2000) {
        $errors[] = 'Your message must be between 10 and 2000 characters.';
    }

    if (!$errors) {
        try {
            $pdo  = getDbConnection();
            $stmt = $pdo->prepare(
                'INSERT INTO contact_messages (name, email, phone, message, ip_address)
                 VALUES (:name, :email, :phone, :message, :ip)'
            );
            $stmt->execute([
                ':name'    => $old['name'],
                ':email'   => $old['email'],
                ':phone'   => $old['phone'] !== '' ? $old['phone'] : null,
                ':message' => $old['message'],
                ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            // Optional email notification (needs a configured mail server)
            if (NOTIFY_EMAIL !== '') {
                $subject = 'New website enquiry from ' . str_replace(["\r", "\n"], '', $old['name']);
                $body    = "Name: {$old['name']}\nEmail: {$old['email']}\nPhone: {$old['phone']}\n\n{$old['message']}";
                $headers = 'Reply-To: ' . str_replace(["\r", "\n"], '', $old['email']);
                @mail(NOTIFY_EMAIL, $subject, $body, $headers);
            }

            // Post/Redirect/Get so a refresh doesn't resubmit the form
            $_SESSION['csrf']          = bin2hex(random_bytes(32));
            $_SESSION['flash_success'] = 'Thank you! Your message has been sent. We will get back to you soon.';
            header('Location: services.php#contact');
            exit;
        } catch (PDOException $e) {
            error_log('Contact insert failed: ' . $e->getMessage());
            $errors[] = 'Sorry, we could not send your message. Please try again later.';
        }
    }
}

// ---- Load services from the database -------------------------
$services = [];
try {
    $pdo      = getDbConnection();
    $services = $pdo->query(
        'SELECT title, description FROM services WHERE is_active = 1 ORDER BY sort_order, id'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Services query failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services | Car-Mwas Automotive Sales</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Minimal styles for the form/alerts; move into style.css if you prefer */
        .alert { padding: 12px 16px; border-radius: 4px; margin: 12px 0; }
        .alert-success { background: #e6f9f0; border: 1px solid #9fdcc0; color: #17603f; }
        .alert-error   { background: #fff3f3; border: 1px solid #f3c1c1; color: #a33; }
        .contact form  { max-width: 480px; display: grid; gap: 10px; }
        .contact input, .contact textarea { padding: 10px; border: 1px solid #ccc; border-radius: 4px; font: inherit; }
        .contact button { background: #22e1e1; border: none; padding: 12px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .hp { position: absolute; left: -9999px; }
    </style>
</head>
<body>

    <div class="services">
        <header>
            <h1>Our Services</h1>
        </header>

        <img src="serve.webp" alt="Services Image" class="services-image">

        <p>At Car-Mwas Automotive Sales, we offer a wide range of services to meet all your automotive needs. Our team of experts is dedicated to providing you with the best service possible.</p>

        <?php if ($services): ?>
            <ul>
                <?php foreach ($services as $s): ?>
                    <li><strong><?= h($s['title']) ?>:</strong> <?= h($s['description']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Our services list is currently unavailable. Please check back soon.</p>
        <?php endif; ?>

        <p>Whether you're looking to buy a new car, need repairs on your current vehicle, or just want to learn more about our services, we've got you covered. Explore our website to learn more about what we offer and how we can help you.</p>
        <p>Thank you for choosing Car-Mwas Automotive Sales. We look forward to serving you!</p>
    </div>

    <div class="contact" id="contact">
        <h2>Contact Us</h2>
        <p>If you have any questions or need assistance, please feel free to reach out to us:</p>
        <ul>
            <li>Email: <a href="mailto:carmwas@gmail.com">carmwas@gmail.com</a></li>
        </ul>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="services.php#contact" method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
            <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

            <input type="text"  name="name"  placeholder="Your name"  value="<?= h($old['name']) ?>"  required maxlength="100">
            <input type="email" name="email" placeholder="Your email" value="<?= h($old['email']) ?>" required maxlength="150">
            <input type="tel"   name="phone" placeholder="Phone (optional)" value="<?= h($old['phone']) ?>" maxlength="30">
            <textarea name="message" rows="5" placeholder="How can we help?" required maxlength="2000"><?= h($old['message']) ?></textarea>

            <button type="submit">Send Message</button>
        </form>
    </div>

</body>
</html>