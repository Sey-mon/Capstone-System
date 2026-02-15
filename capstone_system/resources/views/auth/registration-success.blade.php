<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/registration-success.css') }}?v={{ filemtime(public_path('css/registration-success.css')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

    <!-- Hero Section with Success Message -->
    <section id="home" class="hero-section">
        <div class="hero-content">
            <div class="hero-text" style="display: flex; align-items: center; justify-content: center;">
                <div class="success-content-wrapper">
                    <!-- Success Banner Notification -->
                    <div class="success-banner" id="successBanner">
                        <div class="success-banner-content">
                            <div class="success-banner-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="success-banner-text">
                                <h3><i class="fas fa-party-horn"></i> Congratulations! Your Application Has Been Submitted!</h3>
                                <p>We've received your application and our team will review it shortly. Check your email for updates.</p>
                            </div>
                            <button class="success-banner-close" onclick="dismissBanner()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="success-banner-timer">
                            <div class="success-banner-timer-bar"></div>
                        </div>
                    </div>

                    <div class="success-card">
                        <div class="success-icon-large">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        
                        <h2>Application Submitted Successfully!</h2>
                        <p><strong>Thank you for applying to join our professional team.</strong></p>
                        <p>Your application has been submitted and is now under review by our admin team.</p>

                        <div class="info-section">
                            <strong>Application ID:</strong>
                            <span>#{{ session('application_id', 'N/A') }}</span>
                            <p style="font-size: 0.85rem; color: #9ca3af; margin-top: 0.5rem;">Please keep this ID for your records</p>
                        </div>

                        <div class="info-section">
                            <strong>What happens next?</strong>
                            <ul style="list-style: none; padding: 0; margin: 0.5rem 0 0 0; text-align: left; font-size: 0.95rem;">
                                <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #28a745; margin-right: 0.5rem;"></i> Our admin team will review your credentials</li>
                                <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #28a745; margin-right: 0.5rem;"></i> Process typically takes 2-3 business days</li>
                                <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #28a745; margin-right: 0.5rem;"></i> You'll receive an email notification</li>
                                <li><i class="fas fa-check" style="color: #28a745; margin-right: 0.5rem;"></i> Access your nutritionist dashboard if approved</li>
                            </ul>
                        </div>

                        <div class="countdown-text">
                            You will be redirected to the login page in <span class="countdown" id="countdown">3</span> seconds...
                        </div>

                        <div class="success-buttons">
                            <a href="{{ route('staff.login') }}" class="btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Go to Login
                            </a>
                            <a href="{{ route('home') }}" class="btn-secondary">
                                <i class="fas fa-home"></i> Back to Home
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

    <!-- Hidden input to store login URL for JavaScript -->
    <input type="hidden" id="loginUrl" value="{{ route('staff.login') }}">

    <script src="{{ asset('js/registration-success.js') }}?v={{ filemtime(public_path('js/registration-success.js')) }}"></script>
</body>
</html>
