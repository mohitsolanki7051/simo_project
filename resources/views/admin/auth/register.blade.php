<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
/* ═══════════════════════════════════════
   100% copied from login page base styles
═══════════════════════════════════════ */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

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

/* ── WRAPPER — same as login ─────────── */
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

/* ── LEFT FORM SIDE — same as login ──── */
.login-left {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 28px 20px;
    background: #fff;
    overflow-y: auto;
}
.login-left::-webkit-scrollbar { width: 0; }

.login-card {
    width: 100%;
    max-width: 440px;
    animation: fadeIn .4s ease both;
}

/* ── BRAND — same as login ───────────── */
.brand {
    text-align: center;
    margin-bottom: 24px;
}
.brand img {
    width: 140px;
    max-height: 50px;
    object-fit: contain;
}
.brand-sub {
    font-size: 13px;
    color: #9ca3af;
    margin-top: 6px;
    font-weight: 500;
}

/* ── ALERTS — same as login ──────────── */
.alert {
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 16px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.5;
}
.alert-error   { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }
.alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; }

/* ── FORM LABELS — same as login ─────── */
.form-label {
    font-size: 13px;
    color: #374151;
    margin-bottom: 6px;
    display: block;
    font-weight: 600;
    letter-spacing: -.1px;
}

/* ── INPUT GROUP — same as login ─────── */
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
    transition: all .25s ease;
    font-family: 'Inter', sans-serif;
    -webkit-appearance: none;
}
.form-input:focus {
    border-color: #f97316;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(249,115,22,.12);
    outline: none;
}
.form-input::placeholder { color: #c4c9d4; font-size: 13px; }
.form-input.error { border-color:#dc2626; background:#fef2f2; }

/* ── ICON BOX — same as login ────────── */
.input-icon {
    position: absolute;
    right: 0; top: 0;
    height: 100%; width: 46px;
    background: linear-gradient(135deg, #f97316, #fb923c);
    border-radius: 0 10px 10px 0;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 15px;
    pointer-events: none;
}

/* ── ERROR MESSAGES — same as login ──── */
.error-message {
    color: #dc2626;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 10px;
    margin-top: 4px;
    min-height: 16px;
    display: block;
}

.field-wrap { margin-bottom: 4px; }

/* ── TWO COLUMN ROW ──────────────────── */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

/* ── EMAIL + OTP ROW ─────────────────── */
.email-otp-row {
    display: flex;
    gap: 8px;
    align-items: flex-start;
}
.email-otp-row .input-group { flex: 1; }

.btn-send-otp {
    height: 48px;
    padding: 0 16px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #f97316, #fb923c);
    color: #fff;
    font-weight: 700;
    font-size: 12.5px;
    cursor: pointer;
    transition: all .25s;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
    flex-shrink: 0;
    -webkit-tap-highlight-color: transparent;
    margin-top: 1px;
}
.btn-send-otp:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(249,115,22,.3);
}
.btn-send-otp:disabled {
    background: linear-gradient(135deg, #d1d5db, #9ca3af);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
.btn-send-otp.loading {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-send-otp.loading::after {
    content: '';
    width: 12px; height: 12px;
    border: 2px solid rgba(255,255,255,.5);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin .7s linear infinite;
}

/* OTP success message */
.otp-status {
    font-size: 12px;
    font-weight: 600;
    border-radius: 8px;
    padding: 7px 11px;
    margin-bottom: 10px;
    margin-top: 2px;
    display: none;
}
.otp-status.success { background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; }
.otp-status.error   { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }

/* OTP field hidden by default */
#otpField { display: none; }
#otpField.show { display: block; animation: fadeIn .25s ease both; }

/* ── DIVIDER — same as login ─────────── */
.divider {
    display: flex;
    align-items: center;
    margin: 18px 0;
    font-size: 12px;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.divider::before, .divider::after { content:''; flex:1; height:1px; background:#e5e7eb; }
.divider::before { margin-right:10px; }
.divider::after  { margin-left:10px; }

/* ── MAIN BUTTON — same as login ─────── */
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
    transition: all .25s ease;
    font-family: 'Inter', sans-serif;
    letter-spacing: -.1px;
    -webkit-tap-highlight-color: transparent;
}
.btn-login:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 8px 20px rgba(249,115,22,.35); }
.btn-login:active { transform:translateY(0); }
.btn-login:disabled { opacity:.65; cursor:not-allowed; transform:none; box-shadow:none; }

/* ── SIGNUP BUTTON — same as login ───── */
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
    transition: all .25s ease;
    font-family: 'Inter', sans-serif;
    -webkit-tap-highlight-color: transparent;
}
.btn-signup:hover { background:#f97316; color:#fff; }

/* ═══════════════════════════════════════
   RIGHT PANEL — exactly same as login
═══════════════════════════════════════ */
.login-right {
    display: none;
    width: 50%;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 50%, #fed7aa 100%);
    position: relative;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}
.login-right::before {
    content: '';
    position: absolute;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(249,115,22,.12) 0%, transparent 70%);
    top: 50%; left: 50%;
    transform: translate(-50%,-50%);
}
.login-right::after {
    content: '';
    position: absolute;
    bottom: -60px; right: -60px;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(249,115,22,.15) 0%, transparent 70%);
    border-radius: 50%;
}
.right-deco-top {
    position: absolute;
    top: -40px; left: -40px;
    width: 220px; height: 220px;
    background: radial-gradient(circle, rgba(251,146,60,.15) 0%, transparent 70%);
    border-radius: 50%;
}
.login-right img {
    position: relative;
    z-index: 1;
    width: 75%;
    max-width: 340px;
    filter: drop-shadow(0 24px 40px rgba(0,0,0,.14));
}
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
    background: rgba(249,115,22,.1);
    border: 1px solid rgba(249,115,22,.3);
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

/* ── ANIMATIONS ──────────────────────── */
@keyframes spin    { to { transform: rotate(360deg); } }
@keyframes fadeIn  { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

/* ══════════════════════════════════════
   RESPONSIVE — exactly same as login
══════════════════════════════════════ */
@media (min-width: 640px) {
    body { padding: 24px; }
    .login-left { width: 50%; padding: 32px 36px; }
    .login-right { display: flex; }
}
@media (min-width: 1024px) {
    .login-wrapper { max-width: 1040px; min-height: 600px; }
    .login-left { padding: 40px 52px; }
    .brand img { width: 160px; }
    .right-heading { font-size: 30px; }
}
@media (max-width: 480px) {
    .form-row { grid-template-columns: 1fr; gap: 0; }
}
@media (max-width: 400px) {
    body { padding: 0; align-items: flex-start; }
    .login-wrapper { border-radius: 0; min-height: 100vh; box-shadow: none; }
    .login-left { padding: 28px 16px; align-items: flex-start; padding-top: 36px; }
    .login-card { max-width: 100%; }
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
                <div class="brand-sub">Create your admin account</div>
            </div>

            <!-- Server alerts -->
            @if($errors->any())
            <div class="alert alert-error">
                <span>⚠️</span>
                <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif
            @if(session('success'))
            <div class="alert alert-success">
                <span>✅</span>
                <div>{{ session('success') }}</div>
            </div>
            @endif

            <form id="registerForm" method="POST" action="{{ route('admin.register.post') }}" novalidate>
                @csrf

                <!-- Row 1: Name + Phone -->
                <div class="form-row" style="margin-bottom:4px;">
                    <div class="field-wrap">
                        <label class="form-label" for="name">Full Name</label>
                        <div class="input-group">
                            <input type="text" id="name" name="name"
                                class="form-input" placeholder="Alex Morgan"
                                value="{{ old('name') }}" required>
                            <div class="input-icon">👤</div>
                        </div>
                        <span class="error-message" id="nameError"></span>
                    </div>
                    <div class="field-wrap">
                        <label class="form-label" for="phone">Phone <span style="color:#9ca3af;font-weight:400">(optional)</span></label>
                        <div class="input-group">
                            <input type="tel" id="phone" name="phone"
                                class="form-input" placeholder="+91 98765 43210"
                                value="{{ old('phone') }}">
                            <div class="input-icon">📱</div>
                        </div>
                        <span class="error-message" id="phoneError"></span>
                    </div>
                </div>

                <!-- Email + Send OTP -->
                <div class="field-wrap">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="email-otp-row">
                        <div class="input-group">
                            <input type="email" id="email" name="email"
                                class="form-input" placeholder="admin@example.com"
                                value="{{ old('email') }}" required>
                            <div class="input-icon">✉️</div>
                        </div>
                        <button type="button" id="sendOtpBtn" class="btn-send-otp" disabled>
                            Send OTP
                        </button>
                    </div>
                    <span class="error-message" id="emailError"></span>
                </div>

                <!-- OTP status -->
                <div class="otp-status" id="otpStatus"></div>

                <!-- OTP field (hidden until sent) -->
                <div id="otpField" class="field-wrap">
                    <label class="form-label" for="otp">OTP Code</label>
                    <div class="input-group">
                        <input type="text" id="otp" name="otp"
                            class="form-input" placeholder="Enter 6-digit OTP"
                            maxlength="6" inputmode="numeric">
                        <div class="input-icon">🔑</div>
                    </div>
                    <span class="error-message" id="otpError"></span>
                </div>

                <!-- Row 2: Password + Confirm -->
                <div class="form-row" style="margin-bottom:4px;">
                    <div class="field-wrap">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password"
                                class="form-input" placeholder="Min. 8 characters"
                                minlength="8" required>
                            <div class="input-icon">🔒</div>
                        </div>
                        <span class="error-message" id="passwordError"></span>
                    </div>
                    <div class="field-wrap">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" id="password_confirmation"
                                name="password_confirmation"
                                class="form-input" placeholder="Re-enter password" required>
                            <div class="input-icon">🔒</div>
                        </div>
                        <span class="error-message" id="confirmPasswordError"></span>
                    </div>
                </div>

                <!-- Create Account Button -->
                <button type="button" id="createAccountBtn" class="btn-login" disabled>
                    Create Account
                </button>

                <div class="divider">or</div>

                <a href="{{ route('admin.login') }}" class="btn-signup">
                    Already have an account? Login
                </a>

            </form>
        </div>
    </div>

    <!-- ── RIGHT: ILLUSTRATION — same as login ── -->
    <div class="login-right">
        <div class="right-deco-top"></div>
        <div class="right-content">
            <div class="right-badge">Admin Panel</div>
            <div class="right-heading">
                Get started<br>in <span>minutes</span>
            </div>
            <div class="right-desc">
                Complete ERP solution for invoicing, inventory, parties &amp; sales — all in one place.
            </div>
            <img src="{{ asset('images/admin-login/admin-login.png') }}"
                 alt="Admin Illustration"
                 onerror="this.style.display='none'">
        </div>
    </div>

</div>

<script>
(function () {
    'use strict';

    var nameInput    = document.getElementById('name');
    var emailInput   = document.getElementById('email');
    var passwordInput = document.getElementById('password');
    var confirmInput  = document.getElementById('password_confirmation');
    var otpInput      = document.getElementById('otp');
    var sendOtpBtn    = document.getElementById('sendOtpBtn');
    var createBtn     = document.getElementById('createAccountBtn');
    var otpField      = document.getElementById('otpField');
    var otpStatus     = document.getElementById('otpStatus');

    var nameError    = document.getElementById('nameError');
    var emailError   = document.getElementById('emailError');
    var passError    = document.getElementById('passwordError');
    var confirmError = document.getElementById('confirmPasswordError');
    var otpError     = document.getElementById('otpError');

    var otpSent = false;

    function validEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
    function validPass(v)  { return v.length >= 8; }

    function toggleSendOtp() {
        sendOtpBtn.disabled = !validEmail(emailInput.value.trim());
    }

    function toggleCreate() {
        if (!otpSent) { createBtn.disabled = true; return; }
        var ok = nameInput.value.trim() !== ''
              && validEmail(emailInput.value.trim())
              && otpInput.value.trim().length >= 4
              && validPass(passwordInput.value)
              && passwordInput.value === confirmInput.value;
        createBtn.disabled = !ok;
    }

    /* Real-time validation */
    nameInput.addEventListener('input', function () {
        nameError.textContent = nameInput.value.trim() === '' ? 'Full name is required' : '';
        toggleCreate();
    });

    emailInput.addEventListener('input', function () {
        var v = emailInput.value.trim();
        emailError.textContent = v === '' ? 'Email is required'
                               : !validEmail(v) ? 'Enter a valid email' : '';
        toggleSendOtp();
        toggleCreate();
    });

    passwordInput.addEventListener('input', function () {
        var v = passwordInput.value;
        passError.textContent = v === '' ? 'Password is required'
                              : !validPass(v) ? 'Minimum 8 characters' : '';
        if (confirmInput.value !== '')
            confirmError.textContent = v !== confirmInput.value ? 'Passwords do not match' : '';
        toggleCreate();
    });

    confirmInput.addEventListener('input', function () {
        confirmError.textContent = confirmInput.value === '' ? 'Please confirm your password'
                                 : passwordInput.value !== confirmInput.value ? 'Passwords do not match' : '';
        toggleCreate();
    });

    otpInput.addEventListener('input', function () {
        otpError.textContent = otpInput.value.trim() === '' ? 'OTP is required' : '';
        toggleCreate();
    });

    /* Send OTP */
    sendOtpBtn.addEventListener('click', function (e) {
        e.preventDefault();
        var email = emailInput.value.trim();
        if (!validEmail(email)) { emailError.textContent = 'Valid email required'; return; }

        sendOtpBtn.disabled = true;
        sendOtpBtn.classList.add('loading');
        sendOtpBtn.textContent = 'Sending';
        otpStatus.style.display = 'none';
        otpStatus.className = 'otp-status';

        fetch("{{ route('admin.send.otp') }}", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
            body: JSON.stringify({ email: email })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            sendOtpBtn.classList.remove('loading');
            sendOtpBtn.disabled = false;
            sendOtpBtn.textContent = 'Resend OTP';

            if (data.success) {
                otpField.classList.add('show');
                otpStatus.style.display = 'block';
                otpStatus.classList.add('success');
                otpStatus.textContent = '✅ OTP sent to ' + email;
                otpSent = true;
            } else {
                otpStatus.style.display = 'block';
                otpStatus.classList.add('error');
                otpStatus.textContent = '❌ ' + (data.message || 'Failed to send OTP');
                otpSent = false;
            }
            toggleCreate();
        })
        .catch(function () {
            sendOtpBtn.classList.remove('loading');
            sendOtpBtn.disabled = false;
            sendOtpBtn.textContent = 'Send OTP';
            otpStatus.style.display = 'block';
            otpStatus.classList.add('error');
            otpStatus.textContent = '❌ Network error. Please try again.';
            otpSent = false;
            toggleCreate();
        });
    });

    /* Create Account submit */
    createBtn.addEventListener('click', function () {
        var ok = true;
        if (nameInput.value.trim() === '')          { nameError.textContent = 'Full name required'; ok = false; }
        if (!validEmail(emailInput.value.trim()))   { emailError.textContent = 'Valid email required'; ok = false; }
        if (!otpSent)                               { otpError.textContent = 'Please send OTP first'; ok = false; }
        else if (otpInput.value.trim() === '')      { otpError.textContent = 'OTP cannot be empty'; ok = false; }
        if (!validPass(passwordInput.value))        { passError.textContent = 'Minimum 8 characters'; ok = false; }
        if (passwordInput.value !== confirmInput.value) { confirmError.textContent = 'Passwords do not match'; ok = false; }
        if (ok) {
            createBtn.disabled = true;
            createBtn.textContent = 'Creating account…';
            document.getElementById('registerForm').submit();
        }
    });

    /* Auto-hide server alerts */
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 500);
        }, 3000);
    });

    toggleSendOtp();
    toggleCreate();
})();
</script>
</body>
</html>