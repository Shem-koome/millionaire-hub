<?php
define('APP_STARTED', true);
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="text-align:center; margin: 100px auto; max-width:800px; background: white; padding: 50px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
    <h1 style="font-size: 2.5rem; color: #002366;">Welcome to <span style="color:#D4AF37;">Millionaire's Hub</span></h1>
    <p style="margin-top:20px; font-size:1.1em; color: #555; line-height:1.6;">
        Master your capital. Track <strong>expenses</strong>, manage your <strong>wishlist</strong>, 
        and secure your financial future in one elite dashboard.
    </p>

    <div style="margin-top:40px;">
        <a href="public/login.php" 
           style="padding:15px 40px; margin-right:15px; font-weight: bold; text-decoration:none; color:white; background-color:#002366; border-radius:8px; display: inline-block; transition: 0.3s;">
           GET STARTED
        </a>
    </div>

    <p style="margin-top:30px; font-size:0.9em; color:#888;">
        Discipline is the bridge between goals and accomplishment.
    </p>
</div>

<?php include 'includes/footer.php'; ?>