<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Verified Successfully - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/auth/verification-success.css') }}">
</head>
<body>

<div class="verification-success-wrapper">
    <div class="verification-success-card">
        <div class="content-wrapper">
            <div class="text-center">
                <div class="success-animation">
                    <div class="checkmark">
                        <div class="checkmark-circle"></div>
                        <div class="checkmark-stem"></div>
                        <div class="checkmark-kick"></div>
                    </div>
                </div>

                <h2 class="success-title">
                    🎉 Email Verified Successfully{{ session('user_name') ? ', ' . session('user_name') : '' }}!
                </h2>
                <p class="success-subtitle">
                    Your email address has been verified. You can now access all features of your account.
                </p>

                <div class="success-info">
                    <div class="verified-email-box">
                        <h5>✅ Verified Email</h5>
                        <p class="email-display">{{ session('verified_email', 'Email verified successfully') }}</p>
                        <p class="text-muted small">Verified on {{ session('verified_at', now()->format('F j, Y \a\t g:i A')) }}</p>
                    </div>

                    <div class="next-steps">
                        <h5>🚀 What's Next?</h5>
                        <ul class="next-steps-list">
                            <li>Complete your profile information</li>
                            <li>Explore the dashboard features</li>
                            <li>Start using the monitoring system</li>
                        </ul>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('login') }}" class="btn btn-outline-success btn-lg" style="text-decoration: none;">
                        🔐 Login Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/auth/verification-success.js') }}"></script>
</body>
</html>
