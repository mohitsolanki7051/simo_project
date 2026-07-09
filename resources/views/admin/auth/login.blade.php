<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
*, *::before, *::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    height: 100%;
    font-family: 'Inter', sans-serif;
}

body {
    min-height: 100vh;
    background: #f7f8fc;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

/* ── WRAPPER ─────────────────────────── */
.login-wrapper {
    display: flex;
    width: 100%;
    max-width: 960px;
    min-height: 560px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,0.12);
    background: #fff;
}

/* ── LEFT: FORM SIDE ─────────────────── */
.login-left {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 20px;
    background: #fff;
}

.login-card {
    width: 100%;
    max-width: 400px;
}

/* ── BRAND ───────────────────────────── */
.brand {
    text-align: center;
    margin-bottom: 28px;
}

.brand img {
    width: 140px;
    max-height: 50px;
    object-fit: contain;
}

.brand-title {
    font-size: 22px;
    font-weight: 800;
    color: #111827;
    letter-spacing: -.4px;
    margin-top: 6px;
}

.brand-sub {
    font-size: 13px;
    color: #9ca3af;
    margin-top: 3px;
    font-weight: 500;
}

/* ── ALERTS ──────────────────────────── */
.alert {
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 18px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.5;
}

.alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.alert-success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #16a34a;
}

/* ── FORM ELEMENTS ───────────────────── */
.form-label {
    font-size: 13px;
    color: #374151;
    margin-bottom: 6px;
    display: block;
    font-weight: 600;
    letter-spacing: -.1px;
}

.input-group {
    position: relative;
    margin-bottom: 5px;
}

.form-input {
    width: 100%;
    padding: 13px 52px 13px 14px;
    border-radius: 10px;
    border: 1.5px solid #e5e7eb;
    font-size: 14px;
    background: #f9fafb;
    color: #111827;
    transition: all 0.25s ease;
    font-family: 'Inter', sans-serif;
    -webkit-appearance: none;
}

.form-input:focus {
    border-color: #f97316;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(249,115,22,0.12);
    outline: none;
}

.form-input::placeholder {
    color: #c4c9d4;
    font-size: 13px;
}

/* Icon box */
.input-icon {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 46px;
    background: linear-gradient(135deg, #f97316, #fb923c);
    border-radius: 0 10px 10px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 15px;
    pointer-events: none;
}

/* Error state */
.form-input.error {
    border-color: #dc2626;
    background: #fef2f2;
}

.error-message {
    color: #dc2626;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 12px;
    margin-top: 4px;
    min-height: 16px;
    display: block;
}

/* Field wrapper */
.field-wrap {
    margin-bottom: 4px;
}

/* ── FORGOT ───────────────────────────── */
.forgot {
    text-align: right;
    margin: 4px 0 20px;
}

.forgot a {
    font-size: 12.5px;
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.forgot a:hover {
    text-decoration: underline;
}

/* ── LOGIN BUTTON ────────────────────── */
.btn-login {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #f97316, #fb923c);
    color: #fff;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.25s ease;
    font-family: 'Inter', sans-serif;
    letter-spacing: -.1px;
    position: relative;
    overflow: hidden;
    -webkit-tap-highlight-color: transparent;
}

.btn-login:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(249,115,22,0.35);
}

.btn-login:active {
    transform: translateY(0);
}

.btn-login:disabled {
    opacity: 0.75;
    cursor: not-allowed;
    transform: none;
}

/* Loading spinner inside button */
.btn-login.loading::after {
    content: '';
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255,255,255,0.5);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    vertical-align: middle;
    margin-left: 8px;
}

/* ── DIVIDER ─────────────────────────── */
.divider {
    display: flex;
    align-items: center;
    margin: 20px 0;
    font-size: 12px;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}

.divider::before { margin-right: 10px; }
.divider::after  { margin-left:  10px; }

/* ── SIGNUP BUTTON ───────────────────── */
.btn-signup {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 2px solid #f97316;
    background: #fff;
    color: #f97316;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
    display: block;
    text-decoration: none;
    transition: all 0.25s ease;
    font-family: 'Inter', sans-serif;
    -webkit-tap-highlight-color: transparent;
}

.btn-signup:hover {
    background: #f97316;
    color: #fff;
}

/* ── RIGHT: ILLUSTRATION SIDE ────────── */
.login-right {
    display: none; /* hidden on mobile */
    width: 50%;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 50%, #fed7aa 100%);
    position: relative;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

/* Decorative circles */
.login-right::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(249,115,22,0.12) 0%, transparent 70%);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.login-right::after {
    content: '';
    position: absolute;
    bottom: -60px;
    right: -60px;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(249,115,22,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.right-deco-top {
    position: absolute;
    top: -40px;
    left: -40px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(251,146,60,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.login-right img {
    position: relative;
    z-index: 1;
    width: 75%;
    max-width: 340px;
    filter: drop-shadow(0 24px 40px rgba(0,0,0,0.14));
}

/* Right side branding text */
.right-content {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    padding: 40px 32px;
    text-align: center;
}

.right-badge {
    background: rgba(249,115,22,0.1);
    border: 1px solid rgba(249,115,22,0.3);
    border-radius: 100px;
    padding: 6px 16px;
    font-size: 11px;
    font-weight: 700;
    color: #ea580c;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.right-heading {
    font-size: 26px;
    font-weight: 800;
    color: #111827;
    line-height: 1.25;
    letter-spacing: -.4px;
}

.right-heading span { color: #f97316; }

.right-desc {
    font-size: 13.5px;
    color: #6b7280;
    line-height: 1.6;
    max-width: 260px;
}

/* Feature pills */
.right-features {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
    max-width: 260px;
}

.right-feat {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.7);
    border: 1px solid rgba(249,115,22,0.15);
    border-radius: 10px;
    padding: 10px 14px;
    backdrop-filter: blur(4px);
}

.right-feat-icon {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #f97316, #fb923c);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

.right-feat-text {
    font-size: 12.5px;
    font-weight: 600;
    color: #374151;
}

/* ── ANIMATIONS ──────────────────────── */
@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.login-card {
    animation: fadeIn 0.4s ease both;
}

/* ══════════════════════════════════════
   RESPONSIVE BREAKPOINTS
══════════════════════════════════════ */

/* Tablet: show right panel */
@media (min-width: 640px) {
    body {
        padding: 24px;
    }
    .login-left {
        width: 50%;
        padding: 40px 36px;
    }
    .login-right {
        display: flex;
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .login-wrapper {
        max-width: 1040px;
        min-height: 600px;
    }
    .login-left {
        padding: 60px 52px;
    }
    .brand img {
        width: 160px;
    }
    .brand-title {
        font-size: 24px;
    }
    .right-heading {
        font-size: 30px;
    }
}

/* Small phones */
@media (max-width: 400px) {
    body {
        padding: 0;
        align-items: flex-start;
    }
    .login-wrapper {
        border-radius: 0;
        min-height: 100vh;
        box-shadow: none;
    }
    .login-left {
        padding: 28px 16px;
        align-items: flex-start;
        padding-top: 48px;
    }
    .login-card {
        max-width: 100%;
    }
}
    </style>
</head>

<body>

<div class="login-wrapper">

    <!-- ── LEFT: FORM ──────────────────── -->
    <div class="login-left">
        <div class="login-card">

            <!-- Brand -->
            <div class="brand">
                <img src="{{ asset('images/logos/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
                <br>
               
                <div class="brand-sub">Sign in to your account</div>
            </div>

            <!-- Server-side alerts -->
            @if($errors->any())
            <div class="alert alert-error">
                <span>⚠️</span>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success">
                <span>✅</span>
                <div>{{ session('success') }}</div>
            </div>
            @endif

            <!-- Form -->
            <form id="loginForm" method="POST" action="{{ route('admin.login.post') }}" novalidate>
                @csrf

                <!-- Email -->
                <div class="field-wrap">
                    <label class="form-label" for="email">Email address</label>
                    <div class="input-group">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="admin@example.com"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            autofocus
                            required
                        >
                        <div class="input-icon">✉️</div>
                    </div>
                    <span class="error-message" id="emailError"></span>
                </div>

                <!-- Password -->
                <div class="field-wrap">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            minlength="8"
                            required
                        >
                        <div class="input-icon">🔒</div>
                    </div>
                    <span class="error-message" id="passwordError"></span>
                </div>

                <!-- Forgot -->
                <div class="forgot">
                    <a href="#">Forgot password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login" id="submitBtn">
                    Login Now
                </button>

                <div class="divider">or</div>

                <a href="{{ route('admin.register') }}" class="btn-signup">
                    Create Account
                </a>

            </form>

        </div>
    </div>

    <!-- ── RIGHT: ILLUSTRATION ─────────── -->
    <div class="login-right">
        <div class="right-deco-top"></div>
        <div class="right-content">

            <div class="right-badge">Admin Panel</div>

            <div class="right-heading">
                Manage your<br>business <span>smarter</span>
            </div>

            <div class="right-desc">
                Complete ERP solution for invoicing, inventory, parties & sales — all in one place.
            </div>

            <img
                src="{{ asset('images/admin-login/admin-login.png') }}"
                alt="Admin Illustration"
                onerror="this.style.display='none'"
            >
        </div>
    </div>

</div>

<script>
// Auto-hide alerts after 3 seconds
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 3000);
    });

    // Auto-focus email if empty
    const emailInput = document.getElementById('email');
    if (emailInput && !emailInput.value) {
        emailInput.focus();
    }
});

// Form validation
document.getElementById('loginForm').addEventListener('submit', function (e) {
    const email    = document.getElementById('email');
    const password = document.getElementById('password');
    const emailErr = document.getElementById('emailError');
    const passErr  = document.getElementById('passwordError');
    const submitBtn = document.getElementById('submitBtn');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Reset
    emailErr.textContent = '';
    passErr.textContent  = '';
    email.classList.remove('error');
    password.classList.remove('error');

    let isValid = true;

    if (!email.value.trim()) {
        emailErr.textContent = 'Email is required.';
        email.classList.add('error');
        isValid = false;
    } else if (!emailRegex.test(email.value)) {
        emailErr.textContent = 'Please enter a valid email address.';
        email.classList.add('error');
        isValid = false;
    }

    if (!password.value.trim()) {
        passErr.textContent = 'Password is required.';
        password.classList.add('error');
        isValid = false;
    } else if (password.value.length < 8) {
        passErr.textContent = 'Password must be at least 8 characters.';
        password.classList.add('error');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
        return;
    }

    // Show loading state
    submitBtn.disabled  = true;
    submitBtn.textContent = 'Signing in...';
    submitBtn.classList.add('loading');
});
</script>

</body>
</html>