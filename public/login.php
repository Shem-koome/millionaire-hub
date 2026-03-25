<?php
define('APP_STARTED', true);
require_once __DIR__ . '/../app/middleware/auth.php';
redirectIfLoggedIn();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activePanel = (isset($_SESSION['form_type']) && $_SESSION['form_type'] === 'register') ? 'active' : '';
unset($_SESSION['form_type']);

// Inject auth.css into <head> via header.php hook
// assets/ is relative to public/ where login.php lives
$extraStyles = '<link rel="stylesheet" href="assets/css/auth.css">';
?>
<?php include '../includes/header.php'; ?>

<div class="auth-page-container">

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" id="alert-box">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" id="alert-box">
            <i class="fa-solid fa-circle-check"></i>
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="container <?= $activePanel ?>" id="container">

        <div class="form-container sign-up">
            <form action="../app/actions/auth/register_action.php" method="POST">
                <h1>Create Account</h1>
                <span>Join the elite circle</span>
                <input type="text" name="full_name" placeholder="Full Name" required />
                <input type="email" name="email" placeholder="Email" required />
                <div class="password-field">
                    <input type="password" name="password" id="signUpPassword" placeholder="Password" required />
                    <i class="fa-solid fa-eye toggle-password" toggle="#signUpPassword"></i>
                </div>
                <button type="submit">Sign Up</button>
            </form>
        </div>

        <div class="form-container sign-in">
            <form action="../app/actions/auth/login_action.php" method="POST">
                <h1>Sign In</h1>
                <span>Enter your credentials</span>
                <input type="email" name="email" placeholder="Email" required />
                <div class="password-field">
                    <input type="password" name="password" id="signInPassword" placeholder="Password" required />
                    <i class="fa-solid fa-eye toggle-password" toggle="#signInPassword"></i>
                </div>
                <a href="#">Forgot your password?</a>
                <button type="submit">Sign In</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us please login with your personal info</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details and start your journey with us</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="assets/js/main.js"></script>
<script>
    const alertBox = document.getElementById('alert-box');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.style.display = 'none', 500);
        }, 5000);
    }
</script>

<?php include '../includes/footer.php'; ?>