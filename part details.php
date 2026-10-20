<?php
/**
 * part_details.php?id=5
 * Shows one spare part. Linked from the "View Details" buttons.
 */
require_once __DIR__ . '/db_connect.php';

function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function img_src(?string $file): string
{
    if (!$file) {
        return 'images/placeholder-part.jpg';
    }
    return implode('/', array_map('rawurlencode', explode('/', $file)));
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$part = null;

if ($id) {
    try {
        $stmt = getDbConnection()->prepare('SELECT * FROM spare_parts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $part = $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Part details failed: ' . $e->getMessage());
    }
}

if (!$part) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $part ? h($part['part_name']) : 'Part not found' ?> | Car-Mwas Automotive Sales</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .detail { max-width:800px; margin:30px auto; padding:0 16px; display:flex; gap:30px; flex-wrap:wrap; }
        .detail img { width:320px; max-width:100%; border-radius:6px; object-fit:cover; }
        .detail .info { flex:1; min-width:240px; }
        .price { font-size:22px; font-weight:bold; color:#0d7a7a; }
        .cta { display:inline-block; margin-top:14px; background:#22e1e1; color:#111; padding:10px 20px; border-radius:4px; text-decoration:none; font-weight:bold; }
    </style>
</head>
<body>
    <header><h1>Spare Part Details</h1></header>

    <div class="detail">
    <?php if ($part): ?>
        <img src="<?= h(img_src($part['image_url'])) ?>" alt="<?= h($part['part_name']) ?>">
        <div class="info">
            <h2><?= h($part['part_name']) ?></h2>
            <p class="price"><?= number_format((float)$part['price']) ?>Ksh</p>
            <p><?= h($part['description']) ?></p>
            <?php if (!empty($part['part_number'])): ?>
                <p>Part number: <?= h($part['part_number']) ?></p>
            <?php endif; ?>
            <?php if (!empty($part['compatible_make'])): ?>
                <p>Fits: <?= h(trim($part['compatible_make'] . ' ' . $part['compatible_model'])) ?></p>
            <?php endif; ?>
            <p><?= (int)$part['stock_qty'] > 0 ? 'In stock (' . (int)$part['stock_qty'] . ' available)' : 'Currently out of stock' ?></p>
            <a class="cta" href="services.php#contact">Enquire about this part</a>
            <p><a href="spareparts.php">&larr; Back to spare parts</a></p>
        </div>
    <?php else: ?>
        <div class="info">
            <h2>Part not found</h2>
            <p>We couldn't find that spare part.</p>
            <p><a href="spareparts.php">&larr; Back to spare parts</a></p>
        </div>
    <?php endif; ?>
    </div>
</body>
</html>