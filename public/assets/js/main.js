// =========================
// MAIN.JS — MILLIONAIRE'S HUB
// Handles: alerts, auth toggles, password visibility, smooth scrolling
// =========================

document.addEventListener('DOMContentLoaded', () => {

    // =========================
    // 1️⃣ ALERTS AUTO-HIDE
    // =========================
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 1s';
            alert.style.opacity = 0;
            setTimeout(() => alert.remove(), 1000);
        }, 5000); // 5 seconds
    });

    // =========================
    // 2️⃣ REGISTER / LOGIN TOGGLE
    // =========================
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');

    if (container && registerBtn && loginBtn) {
        registerBtn.addEventListener('click', () => container.classList.add('active'));
        loginBtn.addEventListener('click', () => container.classList.remove('active'));
    }

    // =========================
    // 3️⃣ PASSWORD VISIBILITY TOGGLE
    // =========================
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', () => {
            const input = document.querySelector(icon.getAttribute('toggle'));
            if (!input) return;

            const isPassword = input.getAttribute('type') === 'password';

            // Bounce effect
            icon.style.transform = 'translateY(-50%) scale(0.8)';

            setTimeout(() => {
                input.setAttribute('type', isPassword ? 'text' : 'password');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
                icon.style.transform = 'translateY(-50%) scale(1)';
            }, 200);
        });
    });

    // =========================
    // 4️⃣ SMOOTH SCROLLING (CONTENT AREA)
    // =========================
    const content = document.querySelector('.content');
    if (content) {
        // Enable smooth scrolling inside .content
        content.style.scrollBehavior = 'smooth';

        // Smooth scroll for anchor links inside .content
        content.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = content.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Optional: smooth wheel scrolling (nice touch)
        content.addEventListener('wheel', e => {
            content.scrollBy({
                top: e.deltaY,
                behavior: 'smooth'
            });
        });
    }

    // =========================
    // 5️⃣ BASIC SIGN-UP VALIDATION (OPTIONAL)
    // =========================
    const signUpForm = document.querySelector('.sign-up form');
    if (signUpForm) {
        signUpForm.addEventListener('submit', (e) => {
            const passwordInput = signUpForm.querySelector('input[name="password"]');
            const confirmInput = signUpForm.querySelector('input[name="confirm_password"]');
            if (passwordInput && confirmInput) {
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    confirmInput.focus();
                }
            }
        });
    }

});
