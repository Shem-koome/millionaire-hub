<?php
define('APP_STARTED', true);
require_once __DIR__ . '/../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (full_name, email, password) VALUES (:name, :email, :pass)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name'  => $full_name,
            'email' => $email,
            'pass'  => $hashed_password
        ]);
  
        
        $_SESSION['success'] = "Registration successful! Welcome to the elite circle.";
        header("Location: ../../../public/login.php");
        exit();
    } catch (PDOException $e) {
        // Store 'form_type' so login.php knows to stay on the Sign Up side
        $_SESSION['form_type'] = 'register'; 
        
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "This email is already registered.";
        } else {
            $_SESSION['error'] = "A system error occurred. Please try again.";
        }
        
        header("Location: ../../../public/login.php");
        exit();
    }
}