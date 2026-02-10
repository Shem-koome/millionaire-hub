<?php
// Get the current file name to handle paths correctly
$current_page = basename($_SERVER['PHP_SELF']);

// If you are inside the /public/ folder, you need to go up one level for assets
$asset_path = ($current_page == 'login.php' || $current_page == 'dashboard.php') ? '../assets/' : 'assets/';

// Link to home page changes if you are inside a subfolder
$home_path = ($current_page == 'login.php' || $current_page == 'dashboard.php') ? '../index.php' : 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Millionaire's Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" type="image/png" href="<?= $asset_path ?>images/favicon.png">
    
    <link rel="stylesheet" href="<?= $asset_path ?>css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <header>
        <div class="header-content">
            <a href="<?= $home_path ?>" class="logo-text">Millionaire's Hub</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-nav">
                    <span>Hi, <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
                    <a href="<?= ($current_page == 'index.php') ? 'public/logout.php' : 'logout.php' ?>" class="btn-logout">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <div class="main-wrapper">
    <script src="<?= $asset_path ?>js/main.js"></script>