<!DOCTYPE html>
<html lang="en">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>


*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family: 'Inter', sans-serif;
    min-height:100vh;
}

html, body{
    height:100%;
    overflow:hidden;
}
/* Wrapper */
.login-wrapper{
    display:flex;
    width:100%;
    flex-direction: row-reverse;
    height:100vh;
}
/* LEFT */
.login-left{
    width:50%;
    background:linear-gradient(to bottom right,#ffffff,#f9fafb);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:60px;
}

.login-card{
    width:100%;
    max-width:450px;
    background:#ffffff;
    padding:40px;
    border-radius:16px;
    box-shadow:0 20px 40px rgba(0,0,0,0.06);
}

/* Logo */
.brand{
    text-align:center;
    margin-bottom:40px;
}

.brand img{
    width:160px;
}

/* Label */
.form-label{
    font-size:14px;
    color:#374151;
    margin-bottom:6px;
    display:block;
    font-weight:500;
}

/* Input */
.input-group{
    position:relative;
    margin-bottom:18px;
}

.form-input{
    width:100%;
    padding:14px 55px 14px 16px;
    border-radius:10px;
    border:1px solid #e5e7eb;
    font-size:14px;
    background:#f3f4f6;
    transition:all 0.3s ease;
}

.form-input:focus{
    border-color:#f97316;
    background:#ffffff;
    box-shadow:0 0 0 3px rgba(249,115,22,0.15);
    outline:none;
}

/* Icon Box */
.input-icon{
    position:absolute;
    right:0;
    top:0;
    height:100%;
    width:50px;
    background:linear-gradient(135deg,#f97316,#fb923c);
    border-radius:0 10px 10px 0;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:16px;
}

/* Forgot */
.forgot{
    text-align:right;
    margin-bottom:20px;
}

.forgot a{
    font-size:13px;
    color:#2563eb;
    text-decoration:none;
}

/* Login Button */
.btn-login{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:linear-gradient(135deg,#f97316,#fb923c);
    color:#fff;
    font-weight:600;
    cursor:pointer;
    transition:all 0.3s ease;
    margin-top:10px;
}

.btn-login:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(249,115,22,0.35);
}

/* Divider */
.divider{
    display:flex;
    align-items:center;
    text-align:center;
    margin:25px 0;
    font-size:13px;
    color:#9ca3af;
}

.divider::before,
.divider::after{
    content:'';
    flex:1;
    border-bottom:1px solid #e5e7eb;
}

.divider::before{margin-right:10px;}
.divider::after{margin-left:10px;}

/* Signup Button */
.btn-signup{
    width:100%;
    padding:10px;
    border-radius:10px;
    border:2px solid #f97316;
    background:#ffffff;
    color:#f97316;
    font-weight:500;
    text-align:center;
    display:block;
    text-decoration:none;
    transition:0.3s;
    font-size: 14px;
}

.btn-signup:hover{
    background:#f97316;
    color:#fff;
}

.login-right{
    width:65%;
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}


/* Main center glow */
.login-right::before{
    content:'';
    position:absolute;
    width:650px;
    height:650px;
    background:radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(255,255,255,0.6) 40%, transparent 75%);
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    z-index:0;
}


/* Bottom light fade */
.login-right::after{
    content:'';
    position:absolute;
    bottom:-120px;
    left:50%;
    transform:translateX(-50%);
    width:800px;
    height:350px;
    background:radial-gradient(circle, rgba(255,255,255,0.9), transparent 70%);
    filter:blur(60px);
    z-index:0;
}

.login-right img{
    position:relative;
    z-index:1;
    width:65%;
    max-width:400px;
    filter:drop-shadow(0 30px 50px rgba(0,0,0,0.18));
}

/* Error */
.error{
    border-color:#dc2626 !important;
    background:#fef2f2;
}

.error-message{
    color:#dc2626;
    font-size:13px;
    margin-bottom:10px;
}

/* Responsive */
@media(max-width:992px){
    .login-left{width:100%;}
    .login-right{display:none;}
}
    </style>
</head>

<body>

<div class="login-wrapper">

    <!-- LEFT SIDE -->
    <div class="login-left">
        <div class="login-card">

            <!-- Logo -->
            <div class="brand">
                <img src="{{ asset('images/logos/logo.png') }}" alt="Logo">
            </div>
            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success"> {{ session('success') }}
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('admin.login.post') }}" novalidate >
                @csrf

                <!-- Email -->
                <label class="form-label">Email address</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" class="form-input"
                        placeholder="admin@email.com" value="{{ old('email') }}" autocomplete="email" autofocus required>
                    <div class="input-icon">✉</div>

                </div>
                <div class="error-message" id="emailError"></div>


                <!-- Password -->
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password"
                        class="form-input" placeholder="Enter your password" autocomplete="current-password" minlength="8" required>
                    <div class="input-icon">🔒</div>

                </div>
                <div class="error-message" id="passwordError"></div>

                <!-- Forgot -->
                <div class="forgot">
                    <a href="#">Forgot password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login" id="submitBtn">
                    Login now
                </button>

            <div class="divider">   OR</div>

            <a href="{{ route('admin.register') }}" class="btn-signup">
                Signup
            </a>


        </form>

        </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="login-right">
        <img src="{{ asset('images/admin-login/admin-login.png') }}" alt="Illustration">
    </div>

</div>

    <script>

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
                }, 2000); // 5 seconds
            });
        });


       document.getElementById('loginForm').addEventListener('submit', function(e) {

    const email = document.getElementById('email');
    const password = document.getElementById('password');

    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    const submitBtn = document.getElementById('submitBtn');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Reset errors
    emailError.textContent = '';
    passwordError.textContent = '';
    email.classList.remove('error');
    password.classList.remove('error');

    let isValid = true;

    // Email empty
    if (!email.value.trim()) {
        emailError.textContent = 'Email is required.';
        email.classList.add('error');
        isValid = false;
    }
    // Email format
    else if (!emailRegex.test(email.value)) {
        emailError.textContent = 'Please enter a valid email address.';
        email.classList.add('error');
        isValid = false;
    }

    // Password empty
    if (!password.value.trim()) {
        passwordError.textContent = 'Password is required.';
        password.classList.add('error');
        isValid = false;
    }
    // Password length
    else if (password.value.length < 8) {
        passwordError.textContent = 'Password must be at least 8 characters.';
        password.classList.add('error');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
        return;
    }

    // If valid → show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Logging in...';
});
        // Auto-focus first input field
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });


    </script>
</body>
</html>
