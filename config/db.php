<?php
// These getenv() calls will look for the keys we set in Render's dashboard
$host = getenv('DB_HOST') ?: 'localhost'; 
$db   = getenv('DB_NAME') ?: 'tourism_db'; 
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ATTR_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // In production, we don't want to show the full error to users
     die("Database Connection Error. Please try again later.");
}