@extends('layouts.auth')

@section('title', 'Verify Your Account')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="verification-gate-card">
            <div class="card-header text-center">
                <div class="lock-icon">
                    <i class="fas fa-lock" style="font-size: 60px; color: #dc3545;"></i>
                </div>
                <h3 class="mt-3 text-danger">Account Verification Required</h3>
            </div>
            <div class="card-body text-center">
                <!-- User info -->
                <div class="user-info mb-4">
                    <h5 class="text-success mb-2">Welcome, {{ $user->first_name }}!</h5>
                    <p class="text-muted">
                        <i class="fas fa-envelope"></i>
                        Registered email: <strong>{{ $user->email }}</strong>
                    </p>
                </div>

                <!-- Verification requirement notice -->
                <div class="verification-notice mb-4">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Account Access Restricted</strong>
                        <p class="mb-0 mt-2">
                            You must verify your email address before accessing your dashboard. 
                            This security measure protects your account and ensures you receive important notifications.
                        </p>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="instructions mb-4">
                    <h6 class="text-success mb-3">
                        <i class="fas fa-check-circle"></i> How to Verify Your Account
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="step-card">
                                <i class="fas fa-envelope-open-text text-primary" style="font-size: 30px;"></i>
                                <h6 class="mt-2">1. Check Email</h6>
                                <p class="small text-muted">Look for our verification email in your inbox</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="step-card">
                                <i class="fas fa-mouse-pointer text-info" style="font-size: 30px;"></i>
                                <h6 class="mt-2">2. Click Link</h6>
                                <p class="small text-muted">Click the verification link in the email</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="step-card">
                                <i class="fas fa-unlock text-success" style="font-size: 30px;"></i>
                                <h6 class="mt-2">3. Access Granted</h6>
                                <p class="small text-muted">Login again to access your dashboard</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="action-buttons">
                    <!-- Resend and logout button -->
                    <form method="POST" action="{{ route('resend.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg mb-3">
                            <i class="fas fa-paper-plane"></i> 
                            Resend Verification Email & Logout
                        </button>
                    </form>
                    
                    <br>
                    
                    <!-- Manual logout button -->
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt"></i> 
                            Logout Without Resending
                        </button>
                    </form>
                </div>

                <!-- Help text -->
                <div class="help-text mt-4">
                    <p class="text-muted small">
                        <i class="fas fa-question-circle"></i>
                        <strong>Didn't receive the email?</strong><br>
                        Check your spam folder or click "Resend" above. If you continue having issues, please contact our support team.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Verification Gate Styles */
.verification-gate-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 3px solid #dc3545;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(220, 53, 69, 0.2);
    overflow: hidden;
}

.verification-gate-card .card-header {
    background: linear-gradient(135deg, #fff5f5, #ffe6e6);
    border-bottom: 2px solid #dc3545;
    padding: 30px;
    border-radius: 20px 20px 0 0 !important;
}

.verification-gate-card .card-body {
    padding: 40px;
}

.lock-icon {
    margin-bottom: 10px;
}

.user-info {
    background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
    border: 2px solid #4CAF50;
    border-radius: 15px;
    padding: 20px;
}

.verification-notice .alert {
    border: 2px solid #ffc107;
    background: linear-gradient(135deg, #fff8e1, #ffecb3);
    color: #8b6914;
}

.instructions {
    background: rgba(248, 249, 250, 0.8);
    border-radius: 15px;
    padding: 25px;
    border: 1px solid #e9ecef;
}

.step-card {
    padding: 20px 10px;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.step-card:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: translateY(-5px);
}

/* Button Styling */
.btn-success {
    background: linear-gradient(135deg, #4CAF50, #66BB6A);
    border: none;
    border-radius: 25px;
    padding: 15px 30px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    transition: all 0.3s ease;
}

.btn-success:hover {
    background: linear-gradient(135deg, #388E3C, #4CAF50);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
}

.btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
    background: transparent;
    border-radius: 25px;
    padding: 10px 25px;
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-2px);
}

.help-text {
    background: rgba(248, 249, 250, 0.5);
    border-radius: 10px;
    padding: 15px;
}

/* Override auth layout for red/white theme */
.auth-container {
    background: linear-gradient(135deg, #ffebee 0%, #ffffff 50%, #f3e5f5 100%);
}

/* Responsive */
@media (max-width: 768px) {
    .verification-gate-card .card-body {
        padding: 25px;
    }
    
    .step-card {
        margin-bottom: 20px;
    }
}
</style>
@endsection
