<?php
/**
 * helpers.php - small shared functions (used by vehicles.php and vehicle_details.php)
 */

/** Escape text for safe HTML output */
function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

/** Build an image URL; encodes spaces etc. (e.g. "prado tx.jpg") */
function img_src(?string $file, string $fallback = 'images/placeholder-car.jpg'): string
{
    if (!$file) {
        return $fallback;
    }
    return implode('/', array_map('rawurlencode', explode('/', $file)));
}

/** 850000 -> "850,000Ksh" */
function ksh($amount): string
{
    return number_format((float)$amount) . 'Ksh';
}