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
            <div class="hero-text">
                <div class="verification-gate-wrapper">
                    <div class="verification-gate-card">
                        <!-- Animated Icon Header -->
                        <div class="verification-header">
                            <div class="icon-container">
                                <div class="icon-bg-circle pulse"></div>
                                <div class="icon-bg-circle pulse-delayed"></div>
                                <div class="lock-icon-modern">
                                    <i class="fas fa-envelope-open-text"></i>
                                </div>
                            </div>
                            <h1 class="verification-title">Check Your Email</h1>
                            <p class="verification-subtitle">We've sent a verification link to complete your registration</p>
                        </div>

                        <!-- User Welcome Card -->
                        <div class="user-welcome-card">
                            <div class="welcome-badge">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="welcome-content">
                                <h3>Welcome, {{ $user->first_name }}! 👋</h3>
                                <div class="email-display">
                                    <i class="fas fa-envelope"></i>
                                    <span>{{ $user->email }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Methods -->
                        <div class="verification-methods">
                            <h4 class="methods-title">
                                <i class="fas fa-shield-alt"></i>
                                Two Ways to Verify
                            </h4>
                            <div class="methods-grid">
                                <div class="method-card primary-method">
                                    <div class="method-icon">
                                        <i class="fas fa-at"></i>
                                    </div>
                                    <h5>Email Verification</h5>
                                    <p>Click the link in your inbox to verify instantly</p>
                                    <span class="method-badge">Recommended</span>
                                </div>
                                <div class="method-card">
                                    <div class="method-icon">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <h5>Admin Activation</h5>
                                    <p>Wait for administrator approval (for staff accounts)</p>
                                    <span class="method-badge secondary">Manual</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Steps -->
                        <div class="quick-steps">
                            <h4 class="steps-title">
                                <i class="fas fa-list-check"></i>
                                Quick Steps
                            </h4>
                            <div class="steps-timeline">
                                <div class="timeline-step">
                                    <div class="step-marker">
                                        <span class="step-number">1</span>
                                        <div class="step-connector"></div>
                                    </div>
                                    <div class="step-details">
                                        <h5>Check your inbox</h5>
                                        <p>Look for an email from San Pedro Health Office</p>
                                    </div>
                                </div>
                                <div class="timeline-step">
                                    <div class="step-marker">
                                        <span class="step-number">2</span>
                                        <div class="step-connector"></div>
                                    </div>
                                    <div class="step-details">
                                        <h5>Click the verification link</h5>
                                        <p>This will activate your account immediately</p>
                                    </div>
                                </div>
                                <div class="timeline-step">
                                    <div class="step-marker">
                                        <span class="step-number">3</span>
                                    </div>
                                    <div class="step-details">
                                        <h5>Start using your account</h5>
                                        <p>Login and access your personalized dashboard</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Helpful Tips -->
                        <div class="helpful-tips">
                            <div class="tip-header">
                                <i class="fas fa-lightbulb"></i>
                                <span>Helpful Tips</span>
                            </div>
                            <ul class="tips-list">
                                <li><i class="fas fa-check-circle"></i> Check your spam/junk folder if you don't see the email</li>
                                <li><i class="fas fa-check-circle"></i> The verification link expires in 24 hours</li>
                                <li><i class="fas fa-check-circle"></i> Make sure to click the button in the email</li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons-modern">
                            <form method="POST" action="{{ route('resend.logout') }}" class="action-form">
                                @csrf
                                <button type="submit" class="btn-primary-modern">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Resend Verification Email</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('logout') }}" class="action-form">
                                @csrf
                                <button type="submit" class="btn-secondary-modern">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>

                        <!-- Help Section -->
                        <div class="help-section">
                            <p>
                                <i class="fas fa-question-circle"></i>
                                Need help? Contact our support team at 
                                <a href="mailto:support@sanpedrohealthoffice.gov.ph">support@sanpedrohealthoffice.gov.ph</a>
                            </p>
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
