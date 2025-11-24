<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="{{ asset('img/shares-logo.png') }}" alt="SHARES Logo" style="height: 45px; width: auto;">
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content" style="display: flex; justify-content: center; align-items: center;">
            <div class="login-card" style="max-width: 480px; margin: 0 auto;">
                <div class="login-header">
                    <div class="icon-wrapper" style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(52, 211, 153, 0.1)); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-key" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                    </div>
                    <h2>Forgot Password</h2>
                    <p class="subtitle">Enter your email address and we'll send you a link to reset your password.</p>
                </div>
                
                <!-- Display Success Messages -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Display Status Messages -->
                @if(session('status'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Display Error Messages -->
                @if($errors->any())
                    <div class="alert alert-error">
                        @foreach($errors->all() as $error)
                            <p><i class="fas fa-exclamation-circle"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" class="login-form">
                    @csrf
                    
                    <!-- Honeypot field for bot detection -->
                    <input type="text" name="website" id="website" style="display:none !important;" tabindex="-1" autocomplete="off">

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" id="email" 
                                   placeholder="Enter your email" 
                                   value="{{ old('email') }}"
                                   required autofocus
                                   autocomplete="email">
                        </div>
                        @error('email')
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Google reCAPTCHA v2 Widget -->
                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" 
                         style="margin: 20px 0; display: flex; justify-content: center;"></div>
                    
                    @error('g-recaptcha-response')
                        <div style="color: #ef4444; font-size: 0.875rem; margin-top: -10px; margin-bottom: 15px; text-align: center;">
                            {{ $message }}
                        </div>
                    @enderror

                    <button type="submit" class="btn-primary" id="resetBtn">
                        <span class="btn-text">
                            <i class="fas fa-paper-plane"></i>
                            Send Reset Link
                        </span>
                        <div class="loading-spinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>

                    <div class="form-footer" style="margin-top: 1.5rem;">
                        <p style="margin-bottom: 0.75rem; color: var(--text-secondary); font-size: 0.875rem;">
                            <i class="fas fa-info-circle" style="color: var(--primary-color);"></i>
                            Remember your password?
                        </p>
                        <a href="{{ route('login') }}" class="back-link" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--primary-color); text-decoration: none; font-weight: 500; transition: var(--transition);">
                            <i class="fas fa-arrow-left"></i>
                            Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Decorative Background -->
        <div class="hero-decoration">
            <div class="particle-network"></div>
        </div>
    </section>

    <script src="{{ asset('js/login.js') }}"></script>
    
    <!-- Honeypot validation script -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            if (document.getElementById('website').value !== '') {
                e.preventDefault();
                alert('Bot detected. Submission blocked.');
                return false;
            }
        });
    </script>
</body>
</html>
