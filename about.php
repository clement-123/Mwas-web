<?php
/**
 * about.php
 * ------------------------------------------------------------
 * About Us page. The wording lives in the arrays below so it is
 * easy to edit, and the "in stock" numbers come from the database.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/helpers.php';

// ---- Page content: edit here --------------------------------
$tagline = 'Drive the future. Today.';

$intro = [
    'At Car-Mwas Automotive, we bring over 15 years of experience in helping customers find their perfect vehicle.',
    'From brand new sedans to pre-owned SUVs, our commitment is to quality and customer satisfaction.',
];

$mission = 'To provide exceptional automotive sales and services, ensuring customer satisfaction through quality vehicles and expert support.';
$vision  = 'To be the leading automotive sales company, recognized for our integrity, innovation, and commitment to excellence in the automotive industry.';

$values = [
    'Integrity'      => 'We uphold the highest standards of honesty and transparency in all our dealings.',
    'Customer Focus' => 'Our customers are at the heart of everything we do.',
    'Quality'        => 'We are committed to providing top-quality vehicles and services.',
    'Innovation'     => 'We embrace change and continuously seek to improve our offerings.',
    'Teamwork'       => 'We believe in the power of collaboration and support each other to achieve our goals.',
];

// ---- Live stats from the database (page still works without them) ----
$stats = null;
try {
    $pdo = getDbConnection();
    $stats = [
        'vehicles' => (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'available'")->fetchColumn(),
        'parts'    => (int)$pdo->query('SELECT COUNT(*) FROM spare_parts WHERE stock_qty > 0')->fetchColumn(),
    ];
} catch (PDOException $e) {
    error_log('About stats failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Car-Mwas Automotive Sales</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats { display:flex; gap:20px; flex-wrap:wrap; margin:20px 0; }
        .stat { background:#f2fbfb; border:1px solid #bff3f3; border-radius:6px; padding:14px 22px; text-align:center; text-decoration:none; color:inherit; }
        .stat strong { display:block; font-size:28px; color:#0d7a7a; }
        .cta { display:inline-block; margin-top:14px; background:#22e1e1; color:#111; padding:10px 20px; border-radius:4px; text-decoration:none; font-weight:bold; }
        footer { text-align:center; padding:20px; }
    </style>
</head>
<body>
    <div class="about us">
        <header>
            <h1>About Us</h1>
        </header>

        <img src="about.jpg" alt="About Us Image" class="about-image">

        <h1><?= h($tagline) ?></h1>
        <p>
            <?= h($intro[0]) ?><br>
            <?= h($intro[1]) ?>
        </p>

        <?php if ($stats): ?>
            <div class="stats">
                <a class="stat" href="vehicles.php">
                    <strong><?= $stats['vehicles'] ?></strong> vehicles available
                </a>
                <a class="stat" href="spareparts.php">
                    <strong><?= $stats['parts'] ?></strong> spare parts in stock
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="mission">
        <h2>Our Mission</h2>
        <p><?= h($mission) ?></p>

        <h3>Our Vision</h3>
        <p><?= h($vision) ?></p>

        <h3>Our Values</h3>
        <ul>
            <?php foreach ($values as $name => $text): ?>
                <li><?= h($name) ?>: <?= h($text) ?></li>
            <?php endforeach; ?>
        </ul>

        <a class="cta" href="contact.php">Get in touch</a>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> Car-Mwas Automotive Sales. All rights reserved.</p>
    </footer>
</body>
</html>