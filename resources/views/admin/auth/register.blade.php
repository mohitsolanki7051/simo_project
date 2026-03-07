<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register | E‑Commerce Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        /* Wrapper – same structure */
        .register-wrapper {
            display: flex;
            width: 100%;
            flex-direction: row-reverse;
            height: 100vh;
        }

        /* LEFT – same layout */
        .register-left {
            width: 60%;
            background: linear-gradient(to bottom right, #ffffff, #f9fafb);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
        }

        .register-card {
            width: 100%;
            max-width: 600px;
            background: #ffffff;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
        }

        /* Brand / logo */
        .brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand img {
            width: 160px;
        }

        /* Form labels – modern, clean */
        .form-label {
            font-size: 13px;
            color: #1f2937;
            margin-bottom: 6px;
            display: block;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        /* Input group */
        .input-group {
            position: relative;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 14px;
            border: 1.5px solid #e5e7eb;
            font-size: 14px;
            background: #f9fafb;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus {
            border-color: #f97316;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.08);
            outline: none;
        }

        /* special row for email + send otp button */
        .email-otp-row {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            margin-bottom: 16px;
        }

        .email-field-wrapper {
            flex: 1;
        }

        .btn-send-otp {
            background: linear-gradient(135deg, #f97316, #fb923c);
            border: none;
            border-radius: 14px;
            padding: 14px 22px;
            font-weight: 600;
            font-size: 14px;
            color: white;
            cursor: pointer;
            transition: 0.2s ease;
            height: 52px;
            min-width: 130px;
            letter-spacing: 0.3px;
            box-shadow: 0 6px 14px rgba(249,115,22,0.2);
            border: 1px solid rgba(255,255,255,0.2);
            font-family: 'Inter', sans-serif;
            margin-bottom: 10px
        }

        .btn-send-otp:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(249,115,22,0.3);
        }

        .btn-send-otp:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            background: #d1d5db;
            background: linear-gradient(135deg, #9ca3af, #9ca3af);
        }

        /* password row – two columns */
        .row {
            display: flex;
            gap: 18px;
            margin-top: 18px;
            margin-bottom: 16px;
        }

        .col {
            flex: 1;
        }

        /* error & success messages – exactly under each field */
        .error-message {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 500;
            padding-left: 2px;
        }

        .success-message {
            color: #0b8c5c;
            font-size: 13px;
            margin-top: 8px;
            margin-bottom: 4px;
            font-weight: 500;
            background: #ecfdf5;
            padding: 10px 14px;
            border-radius: 12px;
            border-left: 4px solid #10b981;
        }

        /* OTP field container – smooth reveal */
        #otpField {
            margin-bottom: 12px;
            animation: fadeSlide 0.2s ease;
        }

        @keyframes fadeSlide {
            0% { opacity: 0; transform: translateY(-6px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* main action buttons: create account & login */
        .action-buttons {
            display: flex;
            gap: 16px;
            margin-top: 28px;
            margin-bottom: 8px;
        }

        .btn-create {
            flex: 1;
            padding: 15px 10px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #f97316, #fb923c);
            color: white;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 8px 18px rgba(249,115,22,0.25);
            border: 1px solid rgba(255,255,255,0.2);
            font-family: 'Inter', sans-serif;
        }

        .btn-create:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 14px 24px rgba(249,115,22,0.35);
        }

        .btn-create:disabled {
            opacity: 0.55;
            background: #d1d5db;
            background: linear-gradient(135deg, #9ca3af, #b0b7c2);
            box-shadow: none;
            transform: none;
            cursor: not-allowed;
        }

        .btn-login-alt {
            flex: 1;
            padding: 15px 10px;
            border-radius: 16px;
            border: 2px solid #f97316;
            background: white;
            color: #f97316;
            font-weight: 700;
            font-size: 15px;
            text-align: center;
            display: inline-block;
            text-decoration: none;
            transition: 0.2s;
            letter-spacing: 0.2px;
            font-family: 'Inter', sans-serif;
        }

        .btn-login-alt:hover {
            background: #f97316;
            color: white;
            border-color: #f97316;
        }

        /* no extra ornaments, clean design */
        .divider {
            display: none; /* removed, not needed */
        }

        /* right side – unchanged */
        .register-right {
            width: 65%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: radial-gradient(circle at 30% 50%, #fff8f0, #fffefc);
        }

        .register-right::before {
            content: '';
            position: absolute;
            width: 650px;
            height: 650px;
            background: radial-gradient(circle, rgba(255,245,240,1) 0%, rgba(255,249,242,0.6) 40%, transparent 75%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
        }

        .register-right::after {
            content: '';
            position: absolute;
            bottom: -120px;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 350px;
            background: radial-gradient(circle, rgba(255,255,255,0.9), transparent 70%);
            filter: blur(60px);
            z-index: 0;
        }

        .register-right img {
            position: relative;
            z-index: 2;
            width: 65%;
            max-width: 400px;
            filter: drop-shadow(0 30px 50px rgba(0,0,0,0.12));
        }

        /* loading state for send otp */
        .btn-send-otp.loading {
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-send-otp.loading::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* responsive */
        @media(max-width:992px) {
            .register-left { width: 100%; }
            .register-right { display: none; }
        }

        @media(max-width:640px) {
            .row { flex-direction: column; gap: 6px; }
            .email-otp-row { flex-direction: column; align-items: stretch; }
            .btn-send-otp { width: 100%; }
            .action-buttons { flex-direction: column; }
        }

        /* extra polish */
        .field-hint {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        input::placeholder {
            color: #9ca3af;
            font-weight: 400;
            font-size: 13.5px;
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <!-- LEFT SIDE (register form) -->
        <div class="register-left">
            <div class="register-card">
                <!-- Brand logo (same) -->
                <div class="brand">
                    <img src="{{ asset('images/logos/logo.png') }}" alt="Logo">
                </div>

                <form id="registerForm" method="POST" action="{{ route('admin.register.post') }}">
                    @csrf

                    <!-- 1. FULL NAME (standalone) -->
                    <label class="form-label">Full name</label>
                    <div class="input-group">
                        <input type="text" name="name" id="name" class="form-input" placeholder="e.g., Alex Morgan" required>
                    </div>
                    <div class="error-message" id="nameError"></div>

                    <!-- 2. EMAIL + SEND OTP BUTTON (small row) -->
                    <div style="margin-top: 20px;">
                        <label class="form-label">Email address</label>
                        <div class="email-otp-row">
                            <div class="email-field-wrapper">
                                <input type="email" name="email" id="email" class="form-input" placeholder="admin@example.com" required style="height:52px;">
                                <div class="error-message" id="emailError"></div>
                            </div>
                            <!-- Send OTP button – small, enabled only when email valid -->
                            <button type="button" id="sendOtpBtn" class="btn-send-otp" disabled>Send OTP</button>
                        </div>
                    </div>

                    <!-- OTP field (hidden until OTP sent) -->
                    <div id="otpField" style="display: none;">
                        <label class="form-label" style="margin-bottom: 2px;">OTP code</label>
                        <div class="input-group">
                            <input type="text" name="otp" id="otp" class="form-input" placeholder="6-digit code">
                        </div>
                        <div class="error-message" id="otpError"></div>
                    </div>

                    <!-- OTP success / status message (green) -->
                    <div id="otpMessage" class="success-message" style="display: none;"></div>

                    <!-- 3. PASSWORD ROW (two columns) -->
                    <div class="row">
                        <div class="col">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-input" placeholder="Min. 8 characters" required>
                            </div>
                            <div class="error-message" id="passwordError"></div>
                        </div>
                        <div class="col">
                            <label class="form-label">Confirm password</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" placeholder="Re-enter password" required>
                            </div>
                            <div class="error-message" id="confirmPasswordError"></div>
                        </div>
                    </div>

                    <!-- 4. ACTION BUTTONS: CREATE ACCOUNT & EXISTING ACCOUNT LOGIN -->
                    <div class="action-buttons">
                        <!-- Create account button – disabled until OTP sent & verified flow -->
                        <a href="{{ route('admin.login') }}" class="btn-login-alt">Existing account? Login</a>
                        <button type="button" id="createAccountBtn" class="btn-create" disabled>Create account</button>

                    </div>

                </form>
            </div>
        </div>

        <!-- RIGHT SIDE (illustration – same) -->
        <div class="register-right">
            <img src="{{ asset('images/admin-login/admin-login.png') }}" alt="Admin dashboard illustration">
        </div>
    </div>

    <script>
        (function() {
            "use strict";

            // DOM elements
            const emailInput = document.getElementById('email');
            const sendOtpBtn = document.getElementById('sendOtpBtn');
            const otpField = document.getElementById('otpField');
            const otpMessageDiv = document.getElementById('otpMessage');
            const createAccountBtn = document.getElementById('createAccountBtn');
            const registerForm = document.getElementById('registerForm');

            const nameInput = document.getElementById('name');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');

            // Error containers
            const nameError = document.getElementById('nameError');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');
            const confirmPasswordError = document.getElementById('confirmPasswordError');
            const otpError = document.getElementById('otpError');

            // State
            let otpSent = false;        // becomes true after successful OTP send
            let otpVerified = false;    // we trust OTP field presence (backend will check)

            // ---------- UTILITIES ----------
            function validateEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            function validatePassword(password) {
                return password.length >= 8;
            }

            // Clear all dynamic error messages (optional per field)
            function clearFieldErrors() {
                nameError.innerText = '';
                emailError.innerText = '';
                passwordError.innerText = '';
                confirmPasswordError.innerText = '';
                otpError.innerText = '';
            }

            // ---------- ENABLE / DISABLE SEND OTP (based ONLY on email) ----------
            function toggleSendOtpButton() {
                const emailValid = validateEmail(emailInput.value.trim());
                sendOtpBtn.disabled = !emailValid;
            }

            // Enable/disable create account button:
            // Required: OTP sent + all fields non-empty + password >=8 + passwords match + name + email filled
            function toggleCreateAccountButton() {
                if (!otpSent) {
                    createAccountBtn.disabled = true;
                    return;
                }

                const nameFilled = nameInput.value.trim() !== '';
                const emailFilled = validateEmail(emailInput.value.trim());
                const otpFilled = document.getElementById('otp')?.value.trim() !== '';
                const passwordValid = validatePassword(passwordInput.value);
                const passwordsMatch = passwordInput.value === confirmPasswordInput.value;

                if (nameFilled && emailFilled && otpFilled && passwordValid && passwordsMatch) {
                    createAccountBtn.disabled = false;
                } else {
                    createAccountBtn.disabled = true;
                }
            }

            // ---------- REAL-TIME VALIDATION & ERROR MESSAGES (under each field) ----------
            // Full name validation (just presence)
            nameInput.addEventListener('input', function() {
                if (nameInput.value.trim() === '') {
                    nameError.innerText = 'Full name is required';
                } else {
                    nameError.innerText = '';
                }
                toggleCreateAccountButton();
            });

            // Email validation (format)
            emailInput.addEventListener('input', function() {
                const email = emailInput.value.trim();
                if (email === '') {
                    emailError.innerText = 'Email is required';
                } else if (!validateEmail(email)) {
                    emailError.innerText = 'Enter a valid email address';
                } else {
                    emailError.innerText = '';
                }
                toggleSendOtpButton();
                toggleCreateAccountButton();
            });

            // Password validation (min 8)
            passwordInput.addEventListener('input', function() {
                const pwd = passwordInput.value;
                if (pwd === '') {
                    passwordError.innerText = 'Password is required';
                } else if (!validatePassword(pwd)) {
                    passwordError.innerText = 'At least 8 characters';
                } else {
                    passwordError.innerText = '';
                }
                toggleCreateAccountButton();
            });

            // Confirm password match
            confirmPasswordInput.addEventListener('input', function() {
                const pwd = passwordInput.value;
                const confirm = confirmPasswordInput.value;
                if (confirm === '') {
                    confirmPasswordError.innerText = 'Please confirm your password';
                } else if (pwd !== confirm) {
                    confirmPasswordError.innerText = 'Passwords do not match';
                } else {
                    confirmPasswordError.innerText = '';
                }
                toggleCreateAccountButton();
            });

            // OTP field presence validation (simple)
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.addEventListener('input', function() {
                    if (otpInput.value.trim() === '') {
                        otpError.innerText = 'OTP is required';
                    } else {
                        otpError.innerText = '';
                    }
                    toggleCreateAccountButton();
                });
            }

            // ---------- SEND OTP BUTTON CLICK ----------
            sendOtpBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const email = emailInput.value.trim();

                // final email validation
                if (!validateEmail(email)) {
                    emailError.innerText = 'Valid email required to send OTP';
                    return;
                }

                // Disable button, show loading
                sendOtpBtn.disabled = true;
                sendOtpBtn.classList.add('loading');
                sendOtpBtn.innerText = 'Sending';

                // Clear previous OTP messages
                otpMessageDiv.style.display = 'none';
                otpMessageDiv.innerText = '';

                // Simulate fetch (YOUR EXISTING BACKEND ROUTE)
                fetch("{{ route('admin.send.otp') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(res => res.json())
                .then(data => {
                    sendOtpBtn.classList.remove('loading');

                    if (data.success) {
                        // show OTP field, success message
                        otpField.style.display = 'block';
                        otpMessageDiv.style.display = 'block';
                        otpMessageDiv.innerText = '✅ OTP sent to ' + email;
                        otpSent = true;

                        // Re-enable send OTP button – now it will be enabled again because email is still valid.
                        // But we want it clickable again (if needed). So enable it.
                        sendOtpBtn.disabled = false;  // will be toggled by email validity anyway
                        sendOtpBtn.innerText = 'Send OTP';   // reset text

                        // OTP sent successfully → trigger create account button check
                        toggleCreateAccountButton();
                    } else {
                        // error from backend
                        otpMessageDiv.style.display = 'block';
                        otpMessageDiv.innerText = '❌ ' + (data.message || 'Failed to send OTP');
                        sendOtpBtn.disabled = false;   // enable to retry
                        sendOtpBtn.innerText = 'Send OTP';
                        otpSent = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    sendOtpBtn.classList.remove('loading');
                    otpMessageDiv.style.display = 'block';
                    otpMessageDiv.innerText = '❌ Network error. Try again.';
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.innerText = 'Send OTP';
                    otpSent = false;
                });
            });

            // ---------- CREATE ACCOUNT BUTTON ----------
            createAccountBtn.addEventListener('click', function() {
                // front-end final validation before submit
                clearFieldErrors();

                let isValid = true;

                // name
                if (nameInput.value.trim() === '') {
                    nameError.innerText = 'Full name is required';
                    isValid = false;
                }

                // email
                if (!validateEmail(emailInput.value.trim())) {
                    emailError.innerText = 'Valid email required';
                    isValid = false;
                }

                // otp
                if (!otpSent) {
                    otpError.innerText = 'Please request OTP first';
                    isValid = false;
                } else if (document.getElementById('otp').value.trim() === '') {
                    otpError.innerText = 'OTP cannot be empty';
                    isValid = false;
                }

                // password
                if (!validatePassword(passwordInput.value)) {
                    passwordError.innerText = 'Minimum 8 characters';
                    isValid = false;
                }

                // confirm
                if (passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordError.innerText = 'Passwords do not match';
                    isValid = false;
                }

                if (isValid) {
                    // submit the form
                    registerForm.submit();
                }
            });

            // initial toggle states
            toggleSendOtpButton();
            toggleCreateAccountButton();

            // also listen on otp input for create btn state
            if (otpInput) {
                otpInput.addEventListener('input', toggleCreateAccountButton);
            }

            // ensure password confirmation re-checks on password change
            passwordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value !== '') {
                    if (passwordInput.value !== confirmPasswordInput.value) {
                        confirmPasswordError.innerText = 'Passwords do not match';
                    } else {
                        confirmPasswordError.innerText = '';
                    }
                }
                toggleCreateAccountButton();
            });

            // if user changes name/email after OTP sent, still validate create button
            nameInput.addEventListener('input', toggleCreateAccountButton);
            emailInput.addEventListener('input', toggleCreateAccountButton);
        })();
    </script>


</body>
</html>
