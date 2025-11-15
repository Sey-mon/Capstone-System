<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - BMI Malnutrition Monitoring System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Preload fonts for better performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
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
    
    <!-- Display Session Messages -->
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
<div class="email-verification-wrapper">
    <div class="email-verification-card">
        <div class="card">
            <div class="card-header text-center bg-success text-white">
                <h4><i class="fas fa-envelope-check"></i> Verify Your Email</h4>
            </div>
            <div class="card-body">

                <div class="email-verification-notice text-center mb-4">
                    <div class="mb-3">
                        <i class="fas fa-envelope text-success envelope-icon"></i>
                    </div>
                    <h5 class="text-success mb-3">Check Your Email</h5>
                    <p class="text-muted mb-3 description-text">
                        We've sent a verification link to your email address. 
                        Please check your inbox and click the link to verify your account.
                    </p>
                    <p class="small text-muted email-display">
                        <strong>Email:</strong> 
                        @if(auth()->check())
                            @php
                                $encryptionService = app(\App\Services\DataEncryptionService::class);
                                $userEmail = auth()->user()->email;
                                // Check if email is encrypted and decrypt if needed
                                $displayEmail = $encryptionService->isEncrypted($userEmail) 
                                    ? $encryptionService->decryptUserData($userEmail) 
                                    : $userEmail;
                            @endphp
                            {{ $displayEmail ?? 'Email not available' }}
                        @else
                            Please check your registered email
                        @endif
                    </p>
                </div>

                <!-- Resend verification email form -->
                @auth
                <div class="text-center mb-4 resend-section">
                    <p class="text-muted mb-3">Didn't receive the email?</p>
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-paper-plane"></i> Resend Verification Email
                        </button>
                    </form>
                </div>
                @else
                <!-- Public resend form for logged-out users -->
                <div class="text-center mb-4 resend-section">
                    <p class="text-muted mb-3">Didn't receive the email?</p>
                    <form method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <div class="mb-3">
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" 
                                   placeholder="Enter your email address"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-paper-plane"></i> Resend Verification Email
                        </button>
                    </form>
                </div>
                @endauth

                <!-- Back to login button -->
                <div class="text-center">
                    <a href="{{ url('/') }}" class="btn btn-success">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/auth/verify-email.js') }}"></script>
</body>
</html>