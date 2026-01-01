<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | E-Commerce Dashboard</title>
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

        /* Container - Made Smaller */
        .login-container {
            width: 100%;
            max-width: 380px; /* Reduced from 420px */
            min-width: 280px;
        }

        /* Card - Reduced padding */
        .login-card {
            background: white;
            border-radius: 20px; /* Reduced from 24px */
            padding: clamp(25px, 4vw, 36px); /* Reduced padding */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2); /* Reduced shadow */
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

        /* Logo Section - Reduced size */
        .logo-section {
            text-align: center;
            margin-bottom: clamp(25px, 3vw, 35px); /* Reduced */
        }

        .logo-icon {
            width: clamp(50px, 6vw, 65px); /* Smaller */
            height: clamp(50px, 6vw, 65px);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px; /* Smaller */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(24px, 3vw, 30px); /* Smaller */
            color: white;
            margin: 0 auto clamp(10px, 1.5vw, 14px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3); /* Reduced */
        }

        .logo-text {
            font-size: clamp(20px, 2.5vw, 24px); /* Smaller */
            font-weight: 700;
            color: #1a202c;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .logo-subtitle {
            font-size: clamp(12px, 1.3vw, 14px); /* Smaller */
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
        .form-group {
            margin-bottom: clamp(16px, 2vw, 20px); /* Reduced */
            position: relative;
        }

        .form-label {
            display: block;
            font-size: clamp(12px, 1.4vw, 14px); /* Smaller */
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px; /* Reduced */
        }

        .form-input {
            width: 100%;
            padding: clamp(11px, 1.8vw, 14px); /* Smaller */
            border: 2px solid #e2e8f0;
            border-radius: 10px; /* Smaller */
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

        .error-message {
            color: #e53e3e;
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

        /* Utility & Checkbox - More compact */
        .remember-forgot {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: clamp(18px, 2.5vw, 24px); /* Reduced */
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 6px; /* Smaller */
            cursor: pointer;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 16px; /* Smaller */
            height: 16px;
            border-radius: 4px;
            border: 2px solid #cbd5e0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .checkbox-wrapper input[type="checkbox"]:checked {
            background: #667eea;
            border-color: #667eea;
        }

        .checkbox-label {
            font-size: clamp(12px, 1.4vw, 14px);
            color: #4a5568;
            user-select: none;
        }

        .forgot-link {
            font-size: clamp(12px, 1.4vw, 14px);
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .forgot-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Button - Smaller */
        .btn {
            width: 100%;
            padding: clamp(12px, 1.8vw, 16px); /* Smaller */
            border: none;
            border-radius: 10px; /* Smaller */
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
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); /* Reduced */
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4); /* Reduced */
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Link Section - Smaller */
        .register-link {
            text-align: center;
            margin-top: clamp(20px, 2.5vw, 28px); /* Reduced */
            padding-top: clamp(16px, 2.5vw, 22px); /* Reduced */
            border-top: 1px solid #e2e8f0;
            font-size: clamp(12px, 1.4vw, 14px);
            color: #718096;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .register-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 20%; /* Adjusted for smaller input */
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
            font-size: 16px; /* Smaller */
            padding: 4px;
            transition: color 0.2s ease;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-card {
                padding: clamp(20px, 5vw, 28px);
            }

            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
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

            .login-card {
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
            width: 14px; /* Smaller */
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">🔐</div>
                <h1 class="logo-text">Admin Login</h1>
                <p class="logo-subtitle">Access your dashboard</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('admin.login.post') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-input @error('email') error @enderror"
                           placeholder="admin@example.com"
                           value="{{ old('email') }}"
                           required
                           autocomplete="email"
                           autofocus>
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
                               placeholder="Enter your password"
                               required
                               autocomplete="current-password"
                               minlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                    </div>
                    @error('password')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <div class="remember-forgot">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkbox-label">Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Sign In
                </button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="{{ route('admin.register') }}">Register here</a>
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


        // Form validation and loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Email validation
            if (email && !emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }

            // Password validation
            if (password && password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return;
            }

            // Basic validation
            if (!email || !password) {
                e.preventDefault();
                return;
            }

            // Add loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Signing in...';
        });

        // Real-time email validation
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email && !emailRegex.test(email)) {
                this.classList.add('error');
                // Show error message if not already shown
                if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('error-message')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.innerHTML = '⚠️ Please enter a valid email address';
                    this.parentNode.insertBefore(errorDiv, this.nextSibling);
                }
            } else {
                this.classList.remove('error');
                const errorMsg = this.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.remove();
                }
            }
        });

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            if (password.length > 0 && password.length < 8) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });

        // Auto-focus first input field
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });

        // Enter key to submit form
        document.getElementById('loginForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('submitBtn').click();
            }
        });
    </script>
</body>
</html>
