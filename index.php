<?php
define('APP_STARTED', true);
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: public/dashboard.php');
    exit;
}
?>
<?php include 'includes/header.php'; ?>

<div class="welcome-container">
    <h1>Welcome to <span>Millionaire's Hub</span></h1>
    <p>
        Master your capital. Track <strong>expenses</strong>, manage your <strong>wishlist</strong>,
        and secure your financial future in one elite dashboard.
    </p>
    <div style="margin-top: 40px;">
        <a href="public/login.php" class="btn-primary">GET STARTED</a>
    </div>
    <p style="margin-top: 30px; font-size: 0.9em; color: #888;">
        Discipline is the bridge between goals and accomplishment.
    </p>
</div>

<?php include 'includes/footer.php'; ?>