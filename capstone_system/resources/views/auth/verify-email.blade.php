<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}?v={{ filemtime(public_path('css/auth/verify-email.css')) }}">
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

    @if(session('info'))
        <div class="alert alert-info alert-floating">
            <i class="fas fa-info-circle"></i>
            {{ session('info') }}
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
                <div class="verify-email-wrapper">
                    <div class="verify-email-card">
                        <div class="verify-email-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        
                        <h2>Check Your Email</h2>
                        <p>We've sent a verification link to your email address. Please check your inbox and click the link to verify your account.</p>

                        <div class="email-box">
                            <p style="margin: 0;"><strong>Registered Email:</strong></p>
                            @if(auth()->check())
                                @php
                                    $encryptionService = app(\App\Services\DataEncryptionService::class);
                                    $userEmail = auth()->user()->email;
                                    $displayEmail = $encryptionService->isEncrypted($userEmail) 
                                        ? $encryptionService->decryptUserData($userEmail) 
                                        : $userEmail;
                                @endphp
                                <p style="margin: 0.5rem 0 0; font-weight: 600; color: #1f2937;">{{ $displayEmail ?? 'Email not available' }}</p>
                            @else
                                <p style="margin: 0.5rem 0 0; color: #6b7280;">Please check your registered email</p>
                            @endif
                        </div>

                        <!-- Resend verification email form -->
                        <div class="resend-section">
                            <p style="font-size: 0.9rem;">Didn't receive the email?</p>
                            @auth
                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <button type="submit" class="btn-resend">
                                        <i class="fas fa-paper-plane"></i> Resend Verification Email
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <div style="margin-bottom: 1rem;">
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               name="email" 
                                               placeholder="Enter your email address"
                                               value="{{ old('email') }}"
                                               required>
                                        @error('email')
                                            <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn-resend">
                                        <i class="fas fa-paper-plane"></i> Resend Verification Email
                                    </button>
                                </form>
                            @endauth
                        </div>

                        <div style="margin-top: 1.5rem;">
                            <a href="{{ url('/') }}" class="back-btn">
                                <i class="fas fa-arrow-left"></i> Back to Login
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

    <script src="{{ asset('js/auth/verify-email.js') }}?v={{ filemtime(public_path('js/auth/verify-email.js')) }}"></script>
</body>
</html>