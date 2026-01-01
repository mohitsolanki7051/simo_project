<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register | E-Commerce Dashboard</title>
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        /* Container - Smaller */
        .register-container {
            width: 100%;
            max-width: 420px; /* Reduced from 480px */
            min-width: 300px;
        }

        /* Card - Reduced padding */
        .register-card {
            background: white;
            border-radius: 20px; /* Reduced */
            padding: clamp(25px, 4vw, 36px); /* Reduced */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Animation */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Logo Section - Smaller */
        .logo-section {
            text-align: center;
            margin-bottom: clamp(25px, 3vw, 35px);
        }

        .logo-icon {
            width: clamp(50px, 6vw, 65px);
            height: clamp(50px, 6vw, 65px);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(24px, 3vw, 30px);
            color: white;
            margin: 0 auto clamp(10px, 1.5vw, 14px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .logo-text {
            font-size: clamp(20px, 2.5vw, 24px);
            font-weight: 700;
            color: #1a202c;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .logo-subtitle {
            font-size: clamp(12px, 1.3vw, 14px);
            color: #718096;
            font-weight: 500;
        }

        /* Alert Messages - Smaller */
        .alert {
            padding: clamp(10px, 1.5vw, 14px);
            border-radius: 10px;
            margin-bottom: clamp(20px, 2.5vw, 28px);
            font-size: clamp(12px, 1.3vw, 14px);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .alert-error {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: #9b2c2c;
            border-left: 4px solid #fc8181;
        }

        .alert-success {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #276749;
            border-left: 4px solid #68d391;
        }

        /* Form Styles - More compact */
        .form-grid {
            display: grid;
            gap: clamp(14px, 1.8vw, 18px);
            margin-bottom: clamp(20px, 2.5vw, 28px);
        }

        .form-group {
            position: relative;
        }

        .form-label {
            display: block;
            font-size: clamp(12px, 1.4vw, 14px);
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: clamp(11px, 1.8vw, 14px);
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: clamp(13px, 1.6vw, 15px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
            transform: translateY(-1px);
        }

        .form-input.error {
            border-color: #fc8181;
            background: #fff5f5;
        }

        .form-input.success {
            border-color: #68d391;
            background: #f0fff4;
        }

        .error-message {
            color: #e53e3e;
            font-size: clamp(11px, 1.2vw, 13px);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
            animation: slideDown 0.3s ease;
        }

        .success-message {
            color: #276749;
            font-size: clamp(11px, 1.2vw, 13px);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Password Strength Indicator - Smaller */
        .password-strength {
            margin-top: 6px;
            height: 3px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
            position: relative;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-weak {
            width: 25%;
            background: #fc8181;
        }
        .strength-fair {
            width: 50%;
            background: #f6ad55;
        }
        .strength-good {
            width: 75%;
            background: #68d391;
        }
        .strength-strong {
            width: 100%;
            background: #276749;
        }

        .strength-text {
            font-size: clamp(10px, 1.1vw, 12px);
            margin-top: 4px;
            color: #718096;
        }

        /* Button - Smaller */
        .btn {
            width: 100%;
            padding: clamp(12px, 1.8vw, 16px);
            border: none;
            border-radius: 10px;
            font-size: clamp(14px, 1.8vw, 16px);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Link Section - Smaller */
        .login-link {
            text-align: center;
            margin-top: clamp(20px, 2.5vw, 28px);
            padding-top: clamp(16px, 2.5vw, 22px);
            border-top: 1px solid #e2e8f0;
            font-size: clamp(12px, 1.4vw, 14px);
            color: #718096;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .login-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .register-card {
                padding: clamp(20px, 5vw, 28px);
            }

            .form-input {
                padding: 12px;
            }

            .btn {
                padding: 14px;
            }
        }

        @media (max-width: 360px) {
            body {
                padding: 10px;
            }

            .register-card {
                padding: 18px;
                border-radius: 18px;
            }

            .logo-icon {
                width: 48px;
                height: 48px;
                font-size: 22px;
            }
        }

        /* Loading State */
        .btn.loading {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .btn.loading::before {
            content: '';
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 8px;
            border: 2px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 20%;
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            transition: color 0.2s ease;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="logo-section">
                <div class="logo-icon">👤</div>
                <h1 class="logo-text">Create Account</h1>
                <p class="logo-subtitle">Join our admin panel</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form id="registerForm" method="POST" action="{{ route('admin.register.post') }}">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-input @error('name') error @enderror"
                               placeholder="John Doe"
                               value="{{ old('name') }}"
                               required
                               autocomplete="name"
                               autofocus>
                        @error('name')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-input @error('email') error @enderror"
                               placeholder="admin@example.com"
                               value="{{ old('email') }}"
                               required
                               autocomplete="email">
                        @error('email')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div style="position: relative;">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-input @error('password') error @enderror"
                                   placeholder="Minimum 8 characters"
                                   required
                                   autocomplete="new-password"
                                   minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText">Password strength</div>
                        @error('password')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="form-input"
                                   placeholder="Confirm your password"
                                   required
                                   autocomplete="new-password"
                                   minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">👁️</button>
                        </div>
                        <div class="success-message" id="passwordMatchMessage" style="display: none;">✓ Passwords match</div>
                        <div class="error-message" id="passwordMismatchMessage" style="display: none;">⚠️ Passwords do not match</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Create Account
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="{{ route('admin.login') }}">Login here</a>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggleBtn = field.nextElementSibling;

            if (field.type === 'password') {
                field.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                field.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');

            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';

                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000); // 5 seconds
            });
        });

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            // Length check
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;

            // Character variety checks
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 10;

            // Update strength bar
            strengthBar.className = 'strength-bar';
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = 'Password strength';
                return;
            }

            if (strength < 50) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.style.color = '#e53e3e';
            } else if (strength < 75) {
                strengthBar.classList.add('strength-fair');
                strengthText.textContent = 'Fair password';
                strengthText.style.color = '#f6ad55';
            } else if (strength < 90) {
                strengthBar.classList.add('strength-good');
                strengthText.textContent = 'Good password';
                strengthText.style.color = '#68d391';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.style.color = '#276749';
            }
        }

        // Password match checker
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const matchMessage = document.getElementById('passwordMatchMessage');
            const mismatchMessage = document.getElementById('passwordMismatchMessage');
            const confirmField = document.getElementById('password_confirmation');

            if (confirmPassword.length === 0) {
                matchMessage.style.display = 'none';
                mismatchMessage.style.display = 'none';
                confirmField.classList.remove('error', 'success');
                return;
            }

            if (password === confirmPassword) {
                matchMessage.style.display = 'flex';
                mismatchMessage.style.display = 'none';
                confirmField.classList.remove('error');
                confirmField.classList.add('success');
            } else {
                matchMessage.style.display = 'none';
                mismatchMessage.style.display = 'flex';
                confirmField.classList.remove('success');
                confirmField.classList.add('error');
            }
        }

        // Event listeners
        document.getElementById('password').addEventListener('input', function(e) {
            checkPasswordStrength(e.target.value);
            checkPasswordMatch();
        });

        document.getElementById('password_confirmation').addEventListener('input', checkPasswordMatch);

        // Email validation
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email && !emailRegex.test(email)) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });

        // Form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Email validation
            if (email && !emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }

            // Password match validation
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }

            // Password length validation
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return;
            }

            // Add loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Creating account...';
        });

        // Auto-focus name field
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            if (nameInput && !nameInput.value) {
                nameInput.focus();
            }
        });

        // Enter key to submit form
        document.getElementById('registerForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('submitBtn').click();
            }
        });
    </script>
</body>
</html>
