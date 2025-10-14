<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="logo-container">
                    <i class="fas fa-leaf logo-icon"></i>
                </div>
                <h1>Welcome Back</h1>
                <p class="subtitle">Sign in to your Nutrition System account</p>
            </div>
        
            <!-- Display Success Messages -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Display Error Messages -->
            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" id="loginForm" class="login-form">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="email" 
                               placeholder="Enter your email address" 
                               value="{{ old('email') }}"
                               required autofocus
                               aria-describedby="email-error"
                               autocomplete="email">
                    </div>
                    @error('email')
                        <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="password" 
                               placeholder="Enter your password" 
                               required
                               aria-describedby="password-error"
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword" 
                                aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-primary" id="loginBtn" aria-describedby="login-status">
                    <span class="btn-text">Sign In</span>
                    <div class="loading-spinner" style="display: none;" aria-hidden="true">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </button>
                
                <div id="login-status" class="sr-only" aria-live="polite" aria-atomic="true"></div>

                <div class="divider">
                    <span>or</span>
                </div>

                <div class="extra-links">
                    <a href="{{ route('contact.admin') }}" class="contact-admin-btn">
                        <i class="fas fa-headset"></i>
                        Contact Admin
                    </a>
                </div>
            </form>
            
            <div class="form-footer">
                <p>Don't have an account? <a href="{{ route('register') }}" class="register-link">Create one here</a></p>
            </div>
        </div>

        <!-- Background decoration -->
        <div class="background-decoration">
            <div class="decoration-circle circle-1"></div>
            <div class="decoration-circle circle-2"></div>
            <div class="decoration-circle circle-3"></div>
        </div>
    </div>
    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
