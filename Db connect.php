<?php
/**
 * db_connect.php
 * ------------------------------------------------------------
 * Central database connection for Car-Mwas Automotive Sales.
 * Uses PDO with prepared statements everywhere to prevent
 * SQL injection.
 * ------------------------------------------------------------
 */

// ---- Update these to match your environment -----------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'carmwas_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
// ---------------------------------------------------------------

function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never leak credentials or raw exception details to the browser
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('Sorry, something went wrong connecting to the database. Please try again later.');
        }
    }

    return $pdo;
}