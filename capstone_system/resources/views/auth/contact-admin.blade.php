<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Admin - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <h2>Contact Admin</h2>
        <p class="contact-subtitle">If you need help, send a message to the system administrator.</p>
        
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

        <form method="POST" action="{{ route('contact.admin.send') }}" id="contactForm">
            @csrf

            <div class="form-group">
                <label for="email">Your Email</label>
                <input type="email" name="email" id="email" 
                       placeholder="Enter your email" 
                       value="{{ old('email') }}"
                       required autofocus>
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea name="message" id="message" 
                          placeholder="Type your message here..." 
                          rows="4" 
                          required>{{ old('message') }}</textarea>
                @error('message')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn" id="contactBtn">Send Message</button>

            <div class="extra-links">
                <a href="{{ route('login') }}">← Back to Login</a>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
