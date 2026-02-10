<?php
// app/config/database.php

// Prevent direct access
if (!defined('APP_STARTED')) {
    exit('No direct access allowed');
}

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
| Uses environment variables if available (production),
| falls back to local XAMPP settings (development)
*/

$host     = getenv('DB_HOST') ?: 'localhost';
$db_name  = getenv('DB_NAME') ?: 'millionaires_hub';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$charset  = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db_name};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Real prepared statements
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);

    // Optional sanity check (safe to remove later)
    $pdo->query("SELECT 1");

} catch (PDOException $e) {
    // Never expose sensitive details in production
    die('Database connection failed.');
}
