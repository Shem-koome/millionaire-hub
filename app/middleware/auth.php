<?php
if (!defined('APP_STARTED')) { die('Direct access not allowed'); }

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to PROTECT pages (like dashboard.php)
function protectPage() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php'); // Note: Path depends on where you call this
        exit;
    }
}

// Function to REDIRECT away from login if already signed in
function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit;
    }
}