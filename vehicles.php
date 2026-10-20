<?php
/**
 * vehicles.php
 * ------------------------------------------------------------
 * Lists vehicles from the `vehicles` table.
 * Optional GET filters:  q (search text), body (body type), sort
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/helpers.php';

$q    = trim($_GET['q'] ?? '');
$body = trim($_GET['body'] ?? '');
$sort = $_GET['sort'] ?? 'default';

// Whitelist of sort options (never put user input straight into ORDER BY)
$sorts = [
    'default'    => 'id ASC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'year_desc'  => 'year DESC',
];
if (!isset($sorts[$sort])) {
    $sort = 'default';
}

$vehicles   = [];
$bodyTypes  = [];
$error      = null;

try {
    $pdo = getDbConnection();

    // Body types for the filter dropdown
    $bodyTypes = $pdo->query(
        'SELECT DISTINCT body_type FROM vehicles WHERE body_type IS NOT NULL ORDER BY body_type'
    )->fetchAll(PDO::FETCH_COLUMN);

    if ($body !== '' && !in_array($body, $bodyTypes, true)) {
        $body = '';
    }
    if (mb_strlen($q) > 100) {
        $q = mb_substr($q, 0, 100);
    }

    $where  = [];
    $params = [];

    if ($q !== '') {
        // Lets people search things like "2017 toyota", "suv", "prado"
        $where[] = "(CONCAT_WS(' ', year, make, model) LIKE :like
                     OR body_type LIKE :like
                     OR description LIKE :like)";
        $params[':like'] = '%' . addcslashes($q, '%_\\') . '%';
    }
    if ($body !== '') {
        $where[] = 'body_type = :body';
        $params[':body'] = $body;
    }

    $sql = 'SELECT id, make, model, year, price, description, image_url, status
            FROM vehicles'
         . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
         . ' ORDER BY ' . $sorts[$sort];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Vehicles query failed: ' . $e->getMessage());
    $error = 'Sorry, we could not load the vehicles right now. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Vehicles | Car-Mwas Automotive Sales</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filters { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin:16px 0; }
        .filters input, .filters select { padding:10px 12px; border:1px solid #22e1e1; border-radius:4px; }
        .filters button { background:#22e1e1; border:none; padding:10px 20px; border-radius:4px; font-weight:bold; cursor:pointer; }
        .view-details-btn { display:inline-block; background:#22e1e1; color:#111; padding:8px 16px; border-radius:4px; text-decoration:none; font-weight:bold; }
        .badge { display:inline-block; font-size:12px; padding:2px 8px; border-radius:10px; background:#eee; color:#444; }
        .badge.sold { background:#f8d7d7; color:#a33; }
        .badge.reserved { background:#fff1cc; color:#8a6100; }
        .notice { text-align:center; padding:16px; }
        footer { text-align:center; padding:20px; }
    </style>
</head>
<body>
    <header>
        <h1>Our Vehicles</h1>
    </header>

    <form class="filters" action="vehicles.php" method="get">
        <input type="text" name="q" placeholder="Search make, model, year..." value="<?= h($q) ?>" maxlength="100">

        <select name="body">
            <option value="">All body types</option>
            <?php foreach ($bodyTypes as $bt): ?>
                <option value="<?= h($bt) ?>" <?= $bt === $body ? 'selected' : '' ?>><?= h($bt) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="sort">
            <option value="default"    <?= $sort === 'default'    ? 'selected' : '' ?>>Default order</option>
            <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: low to high</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
            <option value="year_desc"  <?= $sort === 'year_desc'  ? 'selected' : '' ?>>Newest first</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <?php if ($error): ?>
        <p class="notice"><?= h($error) ?></p>

    <?php elseif (!$vehicles): ?>
        <p class="notice">No vehicles matched your search. <a href="vehicles.php">View all vehicles</a></p>

    <?php else: ?>
        <main class="vehicle-list">
            <?php foreach ($vehicles as $v): ?>
                <div class="vehicle-card">
                    <img src="<?= h(img_src($v['image_url'])) ?>" alt="<?= h($v['year'] . ' ' . $v['make'] . ' ' . $v['model']) ?>">
                    <h2><?= h($v['year'] . ' ' . $v['make'] . ' ' . $v['model']) ?></h2>
                    <p>Price: <?= ksh($v['price']) ?></p>
                    <p><?= h($v['description']) ?></p>
                    <?php if ($v['status'] !== 'available'): ?>
                        <p><span class="badge <?= h($v['status']) ?>"><?= h(ucfirst($v['status'])) ?></span></p>
                    <?php endif; ?>
                    <a class="view-details-btn" href="vehicle_details.php?id=<?= (int)$v['id'] ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        </main>
    <?php endif; ?>

    <footer>
        <p>&copy; <?= date('Y') ?> Car-Mwas Automotive Sales. All rights reserved.</p>
    </footer>
</body>
</html>