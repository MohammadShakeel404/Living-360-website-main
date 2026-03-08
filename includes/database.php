<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u934543595_living360');
define('DB_USER', 'u934543595_living360');
define('DB_PASS', 'Living@360@#');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>