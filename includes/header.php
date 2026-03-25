<?php
// Detect if the current page is inside /public/ or at root
$in_public   = (strpos($_SERVER['PHP_SELF'], '/public/') !== false);
$asset_path  = $in_public ? 'assets/' : 'public/assets/';
$home_path   = $in_public ? '../index.php' : 'index.php';
$logout_path = $in_public ? 'logout.php' : 'public/logout.php';
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

    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="<?= $home_path ?>" class="logo-text">Millionaire's Hub</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-nav">
                    <span>Hi, <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
                    <a href="<?= $logout_path ?>" class="btn-logout">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="main-wrapper">