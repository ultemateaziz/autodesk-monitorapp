<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArchEng Pro | Secure Login</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-bg: #0b0f19;
            --accent-color: #3b82f6;
            --text-primary: #ffffff;
            --text-muted: #94a3b8;
            --white: #ffffff;
            --border-color: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            height: 100vh;
            display: flex;
            background-color: var(--white);
            overflow: hidden;
        }

        .login-container {
            display: flex;
            width: 100%;
            height: 100%;
        }

        /* Left Side: Creative Image */
        .login-side-image {
            width: 55%;
            background: linear-gradient(rgba(11, 15, 25, 0.7), rgba(11, 15, 25, 0.7)),
                url('{{ asset('images/login-bg.png') }}');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
        }

        .login-side-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 50%, rgba(59, 130, 246, 0.15) 0%, transparent 60%);
        }

        .brand-logo {
            position: absolute;
            top: 60px;
            left: 80px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 2;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.5px;
        }

        .slogan-content {
            position: relative;
            z-index: 2;
            max-width: 500px;
        }

        .slogan-title {
            font-size: 48px;
            font-weight: 700;
            line-height: 1.1;
            color: white;
            margin-bottom: 24px;
        }

        .slogan-desc {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.6;
        }

        /* Right Side: Form */
        .login-side-form {
            width: 45%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: white;
        }

        .form-card {
            width: 100%;
            max-width: 400px;
        }

        .welcome-header {
            margin-bottom: 40px;
        }

        .welcome-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .welcome-header p {
            color: #64748b;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.2s;
            outline: none;
            color: #1e293b;
        }

        .form-input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .form-extras {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            font-size: 14px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #0b0f19;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 24px;
        }

        .btn-login:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 32px 0;
            color: #94a3b8;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 16px;
        }

        .social-login {
            display: flex;
            justify-content: center;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: white;
            color: #334155;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .social-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .footer-text {
            text-align: center;
            margin-top: 32px;
            font-size: 14px;
            color: #64748b;
        }

        .footer-text a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 700;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 24px;
            border: 1px solid #fee2e2;
        }

        @media (max-width: 1024px) {
            .login-side-image {
                display: none;
            }

            .login-side-form {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Visualization Panel -->
        <div class="login-side-image">
            <div class="brand-logo">
                <div class="logo-icon">
                    <i class="fas fa-compass-drafting"></i>
                </div>
                <span class="logo-text">ArchEng Pro</span>
            </div>

            <div class="slogan-content">
                <h1 class="slogan-title">Optimizing AEC Collection Software License.</h1>
                <p class="slogan-desc">Monitor real-time license usage across all branches. Maximize efficiency,
                    minimize costs—from design to export.</p>
            </div>
        </div>

        <!-- Login Form Panel -->
        <div class="login-side-form">
            <div class="form-card">
                <div class="welcome-header">
                    <h1>Welcome Back!</h1>
                    <p>Enter your corporate credentials to manage the platform.</p>
                </div>

                @if ($errors->any())
                    <div class="alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-input" placeholder="name@company.com"
                                required value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-extras">
                        <label class="checkbox-group">
                            <input type="checkbox" name="remember">
                            <span>Remember Me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-login">Login with Credentials</button>

                    <div class="divider">
                        <span>Or continue with</span>
                    </div>

                    <div class="social-login">
                        <button type="button" class="social-btn">
                            <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" width="20"
                                alt="">
                            <span>Continue with Google</span>
                        </button>
                    </div>

                    <p class="footer-text">
                        Don't have an account? <a href="#">Contact Master Admin</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
