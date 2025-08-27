@extends('layouts.auth')

@section('title', 'Verify Your Email')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <style>
            .alert-success {
                background: linear-gradient(135deg, #d4edda, #c3e6cb);
                border: 2px solid #4CAF50;
                border-radius: 10px;
                color: #155724;
            }
            .alert-info {
                background: linear-gradient(135deg, #d1ecf1, #bee5eb);
                border: 2px solid #17a2b8;
                border-radius: 10px;
                color: #0c5460;
            }
            .alert-danger {
                background: linear-gradient(135deg, #f8d7da, #f5c6cb);
                border: 2px solid #dc3545;
                border-radius: 10px;
                color: #721c24;
            }
            .form-control {
                border-radius: 15px;
                padding: 12px 16px;
                border: 2px solid #e9ecef;
                transition: all 0.3s ease;
            }
            .form-control:focus {
                border-color: #4CAF50;
                box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
            }
        </style>

        <div class="card">
            <div class="card-header text-center bg-success text-white">
                <h4><i class="fas fa-envelope-check"></i> Verify Your Email</h4>
            </div>
            <div class="card-body">
                <!-- Success/Error Messages -->
                @if (session('success'))
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if (session('info'))
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i>
                        {{ session('info') }}
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Success message if verification link was sent -->
                @if (session('message'))
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle"></i>
                        {{ session('message') }}
                    </div>
                @endif

                <div class="email-verification-notice text-center mb-4">
                    <div class="mb-3">
                        <i class="fas fa-envelope text-success" style="font-size: 48px;"></i>
                    </div>
                    <h5 class="text-success mb-3">Check Your Email</h5>
                    <p class="text-muted mb-3">
                        We've sent a verification link to your email address. 
                        Please check your inbox and click the link to verify your account.
                    </p>
                    <p class="small text-muted">
                        <strong>Email:</strong> {{ auth()->check() ? auth()->user()->email : 'Please check your registered email' }}
                    </p>
                </div>

                <!-- Resend verification email form -->
                @auth
                <div class="text-center mb-4">
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
                <div class="text-center mb-4">
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
                    
                    <!-- Development panel link -->
                    {{-- Removed Development Panel (Quick Verify) button for production --}}
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

<style>
/* Email verification specific styles */
.email-verification-notice {
    padding: 20px;
    background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
    border-radius: 10px;
    border: 2px solid #4CAF50;
    margin: 20px 0;
}

/* Button styling */
.btn-success {
    background: linear-gradient(135deg, #4CAF50, #66BB6A);
    border: none;
    border-radius: 25px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background: linear-gradient(135deg, #388E3C, #4CAF50);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.btn-outline-success {
    border: 2px solid #4CAF50;
    color: #4CAF50;
    background: transparent;
    border-radius: 25px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-outline-success:hover {
    background: #4CAF50;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

/* Alert styling */
.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border: 2px solid #4CAF50;
    border-radius: 10px;
    color: #155724;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    border: 2px solid #17a2b8;
    border-radius: 10px;
    color: #0c5460;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    border: 2px solid #dc3545;
    border-radius: 10px;
    color: #721c24;
}

/* Form styling */
.form-control {
    border-radius: 15px;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    font-size: 0.875rem;
    margin-top: 5px;
}

/* Auth card styling */
.auth-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(76, 175, 80, 0.1);
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(76, 175, 80, 0.1);
}

.auth-card .card-header {
    border-radius: 15px 15px 0 0 !important;
    background: linear-gradient(135deg, #4CAF50, #66BB6A) !important;
    border: none;
    padding: 20px;
}

.auth-card .card-body {
    padding: 30px;
}

/* Override auth layout for white/green theme */
.auth-container {
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 50%, #ffffff 100%);
}
</style>
@endsection