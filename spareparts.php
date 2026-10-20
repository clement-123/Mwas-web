<?php
/**
 * spareparts.php
 * ------------------------------------------------------------
 * Lists spare parts from the `spare_parts` table.
 * Optional ?q=term filters by name/description.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/db_connect.php';

function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

/** Encode each path segment so file names with spaces (e.g. "brake pad.jpeg") work */
function img_src(?string $file): string
{
    if (!$file) {
        return 'images/placeholder-part.jpg';
    }
    return implode('/', array_map('rawurlencode', explode('/', $file)));
}

$q     = trim($_GET['q'] ?? '');
$parts = [];
$error = null;

try {
    $pdo = getDbConnection();

    if ($q !== '' && mb_strlen($q) <= 100) {
        $stmt = $pdo->prepare(
            'SELECT id, part_name, price, stock_qty, description, image_url
             FROM spare_parts
             WHERE part_name LIKE :like OR description LIKE :like
                OR compatible_make LIKE :like OR compatible_model LIKE :like
             ORDER BY part_name'
        );
        $stmt->execute([':like' => '%' . $q . '%']);
    } else {
        $q    = '';
        $stmt = $pdo->query(
            'SELECT id, part_name, price, stock_qty, description, image_url
             FROM spare_parts ORDER BY id'
        );
    }
    $parts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Spare parts query failed: ' . $e->getMessage());
    $error = 'Sorry, we could not load the spare parts right now. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spare Parts | Car-Mwas Automotive Sales</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .parts-search { display:flex; gap:10px; justify-content:center; margin:16px 0; }
        .parts-search input { padding:10px 14px; border:1px solid #22e1e1; border-radius:4px; min-width:240px; }
        .parts-search button { background:#22e1e1; border:none; padding:10px 20px; border-radius:4px; font-weight:bold; cursor:pointer; }
        .spare-part-card a.btn { display:inline-block; background:#22e1e1; color:#111; padding:8px 16px; border-radius:4px; text-decoration:none; font-weight:bold; }
        .stock { font-size:13px; color:#17603f; }
        .stock.out { color:#a33; }
        .notice { text-align:center; padding:16px; }
    </style>
</head>
<body>
    <header>
        <h1>Spare Parts</h1>
    </header>

    <form class="parts-search" action="spareparts.php" method="get">
        <input type="text" name="q" placeholder="Search spare parts..." value="<?= h($q) ?>" maxlength="100">
        <button type="submit">Search</button>
    </form>

    <?php if ($error): ?>
        <p class="notice"><?= h($error) ?></p>
    <?php elseif (!$parts): ?>
        <p class="notice">
            No spare parts found<?= $q !== '' ? ' for "' . h($q) . '"' : '' ?>.
            <?php if ($q !== ''): ?><a href="spareparts.php">View all parts</a><?php endif; ?>
        </p>
    <?php else: ?>
        <main class="spare-parts-list">
            <?php foreach ($parts as $p): ?>
                <div class="spare-part-card">
                    <img src="<?= h(img_src($p['image_url'])) ?>" alt="<?= h($p['part_name']) ?>">
                    <h2><?= h($p['part_name']) ?></h2>
                    <p>Price: <?= number_format((float)$p['price']) ?>Ksh</p>
                    <p><?= h($p['description']) ?></p>
                    <p class="stock <?= (int)$p['stock_qty'] > 0 ? '' : 'out' ?>">
                        <?= (int)$p['stock_qty'] > 0 ? 'In stock' : 'Out of stock' ?>
                    </p>
                    <a class="btn" href="part_details.php?id=<?= (int)$p['id'] ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        </main>
    <?php endif; ?>
</body>
</html>