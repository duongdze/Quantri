<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Travel Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f7fa;
            position: relative;
            overflow: hidden;
        }

        /* Subtle geometric background */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 70%);
            animation: float 25s ease-in-out infinite reverse;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            50% {
                transform: translate(30px, 30px) rotate(180deg);
            }
        }

        .login-container {
            position: relative;
            z-index: 1;
            background: white;
            border-radius: 24px;
            box-shadow:
                0 0 0 1px rgba(0, 0, 0, 0.05),
                0 20px 60px rgba(0, 0, 0, 0.08);
            width: 90%;
            max-width: 480px;
            padding: 60px 50px;
            animation: slideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 45px;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .logo-icon i {
            font-size: 32px;
            color: white;
        }

        .login-title {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            font-size: 15px;
            color: #64748b;
            font-weight: 400;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 28px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s;
            border: 1px solid;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-8px);
            }

            75% {
                transform: translateX(8px);
            }
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .input-group {
            position: relative;
            margin-bottom: 24px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 10px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon-left {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            transition: color 0.3s;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px 16px 54px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
            background: white;
            color: #1e293b;
        }

        /* Hide browser default password reveal icon */
        .form-input::-ms-reveal,
        .form-input::-ms-clear {
            display: none;
        }

        .form-input::-webkit-credentials-auto-fill-button,
        .form-input::-webkit-contacts-auto-fill-button {
            visibility: hidden;
            display: none !important;
            pointer-events: none;
            height: 0;
            width: 0;
            margin: 0;
        }

        .form-input.has-toggle {
            padding-right: 54px;
        }

        .form-input::placeholder {
            color: #cbd5e1;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .form-input:focus~.input-icon-left {
            color: #3b82f6;
        }

        .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            font-size: 18px;
            transition: color 0.3s;
            z-index: 10;
        }

        .toggle-password:hover {
            color: #3b82f6;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: #64748b;
            font-weight: 500;
        }

        .remember-me input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #3b82f6;
        }

        .forgot-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: #2563eb;
        }

        .login-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(59, 130, 246, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 32px 0;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .social-login {
            display: flex;
            gap: 12px;
        }

        .social-btn {
            flex: 1;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            background: white;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .social-btn:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .social-btn i {
            font-size: 20px;
        }

        .social-btn.google:hover {
            border-color: #ea4335;
            color: #ea4335;
        }

        .social-btn.facebook:hover {
            border-color: #1877f2;
            color: #1877f2;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 45px 35px;
            }

            .login-title {
                font-size: 28px;
            }

            .social-login {
                flex-direction: column;
            }
        }

        /* Loading animation */
        .login-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
    <style>
        /* Decorative grid pattern */
        .bg-decoration {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(59, 130, 246, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.4;
            animation: floatShape 20s ease-in-out infinite;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(99, 102, 241, 0.1));
            top: 15%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(59, 130, 246, 0.08));
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(99, 102, 241, 0.12));
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.06), rgba(59, 130, 246, 0.06));
            top: 30%;
            right: 25%;
            animation-delay: 6s;
        }

        @keyframes floatShape {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            25% {
                transform: translate(20px, -20px) scale(1.1);
            }

            50% {
                transform: translate(-15px, 15px) scale(0.9);
            }

            75% {
                transform: translate(15px, 20px) scale(1.05);
            }
        }
    </style>
</head>

<body>
    <!-- Decorative background elements -->
    <div class="bg-decoration"></div>
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>
    <div class="floating-shape shape-4"></div>

    <div class="login-container">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-plane-departure"></i>
            </div>
            <h1 class="login-title">Chào mừng trở lại</h1>
            <p class="login-subtitle">Đăng nhập vào hệ thống quản trị du lịch</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL_ADMIN ?>&action=loginProcess" method="POST" id="loginForm">
            <div class="input-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="Nhập email của bạn"
                        required
                        autocomplete="email">
                    <i class="fas fa-envelope input-icon-left"></i>
                </div>
            </div>

            <div class="input-group">
                <label for="password">Mật khẩu</label>
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input has-toggle"
                        placeholder="Nhập mật khẩu"
                        required
                        autocomplete="current-password">
                    <i class="fas fa-lock input-icon-left"></i>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>

            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <a href="#" class="forgot-link">Quên mật khẩu?</a>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Đăng nhập
            </button>

            <!-- <div class="divider">hoặc đăng nhập với</div>

            <div class="social-login">
                <button type="button" class="social-btn google">
                    <i class="fab fa-google"></i>
                    Google
                </button>
                <button type="button" class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i>
                    Facebook
                </button>
            </div> -->
        </form>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form submission with loading state
        const loginForm = document.getElementById('loginForm');
        const loginBtn = loginForm.querySelector('.login-btn');

        loginForm.addEventListener('submit', function() {
            loginBtn.classList.add('loading');
            loginBtn.innerHTML = '<span>Đang đăng nhập...</span>';
        });

        // Input focus animation
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                const leftIcon = this.parentElement.querySelector('.input-icon-left');
                if (leftIcon) {
                    leftIcon.style.color = '#3b82f6';
                }
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    const leftIcon = this.parentElement.querySelector('.input-icon-left');
                    if (leftIcon) {
                        leftIcon.style.color = '#94a3b8';
                    }
                }
            });
        });

        // Social login buttons (placeholder)
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                alert('Tính năng đăng nhập bằng ' + this.textContent.trim() + ' đang được phát triển');
            });
        });
    </script>
</body>

</html>