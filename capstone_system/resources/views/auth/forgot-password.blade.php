<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <h2>Forgot Password</h2>
        <p class="contact-subtitle">Enter your email address and we'll send you a link to reset your password.</p>
        
        <!-- Display Success Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Display Status Messages -->
        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
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

        <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" 
                       placeholder="Enter your email" 
                       value="{{ old('email') }}"
                       required autofocus>
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn" id="resetBtn">Send Reset Link</button>

            <div class="extra-links">
                <a href="{{ route('login') }}">← Back to Login</a>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
