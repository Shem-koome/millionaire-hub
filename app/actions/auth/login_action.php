<?php
define('APP_STARTED', true);
require_once __DIR__ . '/../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        // Successful Login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];

        // Capture the login event
    require_once __DIR__ . '/../../helpers/logger.php'; 
    logActivity($pdo, $user['id'], 'LOGIN', 'User logged into the system.');
        
        header("Location: ../../../public/dashboard.php");
        exit();
    } else {
        // Set error message for the UI
        $_SESSION['error'] = "Invalid email or password. Please try again.";
        header("Location: ../../../public/login.php");
        exit();
    }
}