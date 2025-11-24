<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/verification-gate.css') }}">
</head>
<body>
    <!-- Display Success Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-floating">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Display Error Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-floating">
            @foreach($errors->all() as $error)
                <p class="mb-0"><i class="fas fa-exclamation-circle"></i> {{ $error }}</p>
            @endforeach
        </div>
    @endif
    
    @if(session('message'))
        <div class="alert alert-info alert-floating">
            <i class="fas fa-info-circle"></i>
            {{ session('message') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-floating">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

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
                <div class="verification-gate-wrapper">
                    <div class="verification-gate-card">
                        <div class="lock-icon-large">
                            <i class="fas fa-lock"></i>
                        </div>
                        
                        <h2>Account Verification Required</h2>
                        <p class="subtitle">Please verify your email to continue</p>

                        <div class="user-info-box">
                            <h5>Welcome, {{ $user->first_name }}!</h5>
                            <p><i class="fas fa-envelope" style="color: #28a745; margin-right: 0.5rem;"></i><strong>Registered email:</strong></p>
                            <p style="margin-left: 1.5rem; color: #1f2937; font-weight: 600;">{{ $user->email }}</p>
                        </div>

                        <div class="alert-box">
                            <p><i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i><strong>Access Restricted</strong></p>
                            <p>You must verify your email address before accessing your dashboard. This security measure protects your account and ensures you receive important notifications.</p>
                        </div>

                        <div class="verification-steps">
                            <h6><i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>How to Verify Your Account</h6>
                            <div class="step-item">
                                <div class="step-icon">1</div>
                                <div class="step-content">
                                    <h6>Check Your Email</h6>
                                    <p>Look for our verification email in your inbox</p>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-icon">2</div>
                                <div class="step-content">
                                    <h6>Click the Link</h6>
                                    <p>Click the verification link in the email</p>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-icon">3</div>
                                <div class="step-content">
                                    <h6>Access Granted</h6>
                                    <p>Login again to access your dashboard</p>
                                </div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <form method="POST" action="{{ route('resend.logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn-resend" style="width: 100%;">
                                    <i class="fas fa-paper-plane"></i> Resend Verification Email & Logout
                                </button>
                            </form>
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn-logout" style="width: 100%;">
                                    <i class="fas fa-sign-out-alt"></i> Logout Without Resending
                                </button>
                            </form>
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

    <script src="{{ asset('js/auth/verification-gate.js') }}"></script>
</body>
</html>
