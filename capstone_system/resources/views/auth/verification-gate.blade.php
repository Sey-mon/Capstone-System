<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account - BMI Malnutrition Monitoring System</title>
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
    <link rel="stylesheet" href="{{ asset('css/auth/verification-gate.css') }}">
</head>
<body>
    <!-- Display Success Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-floating">
            {{ session('success') }}
        </div>
    @endif

    <!-- Display Error Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-floating">
            @foreach($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif
    
    <!-- Display Session Messages -->
    @if(session('message'))
        <div class="alert alert-info alert-floating">
            {{ session('message') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-floating">
            {{ session('error') }}
        </div>
    @endif
<div class="verification-wrapper">
    <div class="verification-gate-card">
            <div class="card-header text-center">
                <div class="lock-icon">
                    <i class="fas fa-lock" style="color: #dc3545;"></i>
                </div>
                <h3 class="mt-3 text-danger">Account Verification Required</h3>
            </div>
            <div class="card-body">
                <div class="content-container">
                    <!-- Left Side: User Info & Actions -->
                    <div class="left-side">
                        <!-- User info -->
                        <div class="user-info">
                            <h5 class="text-success">Welcome, {{ $user->first_name }}!</h5>
                            <p class="text-muted">
                                <i class="fas fa-envelope"></i>
                                Registered email: <strong>{{ $user->email }}</strong>
                            </p>
                        </div>

                        <!-- Verification requirement notice -->
                        <div class="verification-notice">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Account Access Restricted</strong>
                                <p>You must verify your email address before accessing your dashboard. This security measure protects your account and ensures you receive important notifications.</p>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="action-buttons">
                            <form method="POST" action="{{ route('resend.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> 
                                    Resend Verification Email & Logout
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fas fa-sign-out-alt"></i> 
                                    Logout Without Resending
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Side: Instructions -->
                    <div class="right-side">
                        <div class="instructions">
                            <h6 class="text-success">
                                <i class="fas fa-check-circle"></i> How to Verify Your Account
                            </h6>
                            
                            <div class="steps">
                                <div class="step">
                                    <div class="step-icon">
                                        <i class="fas fa-envelope-open-text text-primary"></i>
                                    </div>
                                    <div class="step-content">
                                        <h6>1. Check Email</h6>
                                        <p>Look for our verification email in your inbox</p>
                                    </div>
                                </div>
                                
                                <div class="step">
                                    <div class="step-icon">
                                        <i class="fas fa-mouse-pointer text-info"></i>
                                    </div>
                                    <div class="step-content">
                                        <h6>2. Click Link</h6>
                                        <p>Click the verification link in the email</p>
                                    </div>
                                </div>
                                
                                <div class="step">
                                    <div class="step-icon">
                                        <i class="fas fa-unlock text-success"></i>
                                    </div>
                                    <div class="step-content">
                                        <h6>3. Access Granted</h6>
                                        <p>Login again to access your dashboard</p>
                                    </div>
                                </div>
                            </div>

                            <div class="help-text">
                                <p>
                                    <i class="fas fa-question-circle"></i>
                                    <strong>Didn't receive the email?</strong><br>
                                    Check your spam folder or click "Resend" above. If you continue having issues, please contact our support team.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/auth/verification-gate.js') }}"></script>
</body>
</html>
