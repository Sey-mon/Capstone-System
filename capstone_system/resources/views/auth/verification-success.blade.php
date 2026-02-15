<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Verified Successfully - Nutrition System</title>
    
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/verification-success.css') }}?v={{ filemtime(public_path('css/auth/verification-success.css')) }}">
</head>
<body>
    <!-- Navigation Header -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="{{ asset('img/shares-logo.png') }}" alt="SHARES Logo" style="height: 45px; width: auto;">
            </div>
            <div class="nav-links">
                <a href="{{ route('login') }}">Parent Portal</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-content">
            <div class="hero-text" style="display: flex; align-items: center; justify-content: center;">
                <div class="verification-success-wrapper">
                    <div class="verification-success-card">
                        <div class="checkmark">
                            <div class="checkmark-circle">
                                <i class="fas fa-check" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 40px; color: white; font-weight: bold;"></i>
                            </div>
                        </div>
                        
                        <h2 class="success-title">🎉 Email Verified Successfully!</h2>
                        <p class="success-subtitle">Your email address has been verified. You can now access all features of your account.</p>

                        <div class="verified-email-box">
                            <h5>✅ Verified Email</h5>
                            <p class="email-display">{{ session('verified_email', 'Email verified') }}</p>
                            <p class="verified-at">Verified on {{ session('verified_at', now()->format('F j, Y \a\t g:i A')) }}</p>
                        </div>

                        <div class="next-steps">
                            <h5>🚀 What's Next?</h5>
                            <ul class="next-steps-list">
                                <li>Complete your profile information</li>
                                <li>Explore the dashboard features</li>
                                <li>Start using the system</li>
                            </ul>
                        </div>

                        <div class="action-buttons">
                            <a href="{{ route('login') }}" class="btn-login">
                                <i class="fas fa-sign-in-alt"></i> Go to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decorative Background -->
        <div class="hero-decoration">
            <div class="particle-network"></div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-logo">
                <img src="{{ asset('img/shares-logo.png') }}" alt="SHARES Logo" style="height: 60px; width: auto;">
            </div>
            <p class="footer-text">© 2025 San Pedro City Health Office - Nutrition Program. All rights reserved.</p>
        </div>
    </footer>

    <script src="{{ asset('js/auth/verification-success.js') }}?v={{ filemtime(public_path('js/auth/verification-success.js')) }}"></script>
</body>
</html>
