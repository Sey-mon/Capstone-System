<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Admin - Nutrition System</title>
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
                <i class="fas fa-heartbeat"></i>
                <span>SHARES</span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content" style="display: flex; justify-content: center; align-items: center;">
            <div class="login-card" style="max-width: 500px; margin: 0 auto;">
                <div class="login-header">
                    <div class="icon-wrapper" style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(52, 211, 153, 0.1)); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-headset" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                    </div>
                    <h2>Contact Admin</h2>
                    <p class="subtitle">If you need help, send a message to the system administrator.</p>
                </div>
                
                <!-- Display Success Messages -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
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

                <form method="POST" action="{{ route('contact.admin.send') }}" class="login-form">
            @csrf
            
            <!-- Honeypot field for bot detection -->
            <input type="text" name="website" id="website" style="display:none !important;" tabindex="-1" autocomplete="off">

                    <div class="form-group">
                        <label for="email">Your Email</label>
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

                    <div class="form-group">
                        <label for="message">Message</label>
                        <div class="input-wrapper" style="align-items: flex-start;">
                            <i class="fas fa-comment-dots input-icon" style="top: 1rem;"></i>
                            <textarea name="message" id="message" 
                                      placeholder="Type your message here..." 
                                      rows="5" 
                                      style="width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem; border: 2px solid var(--border-color); border-radius: var(--border-radius); font-size: 0.95rem; font-family: inherit; resize: vertical; min-height: 120px; transition: var(--transition);"
                                      required>{{ old('message') }}</textarea>
                        </div>
                        @error('message')
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                                </div>

            <!-- Google reCAPTCHA v2 Widget -->
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" 
                 style="margin: 20px 0; display: flex; justify-content: center;"></div>
            
            @error('g-recaptcha-response')
                <div style="color: #ef4444; font-size: 0.875rem; margin-top: -10px; margin-bottom: 15px; text-align: center;">
                    {{ $message }}
                </div>
            @enderror

            <button type="submit" class="login-btn" id="contactBtn">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>

                    <div class="form-footer" style="margin-top: 1.5rem;">
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
    <script>
        // Add focus effect for textarea
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('message');
            if (textarea) {
                textarea.addEventListener('focus', function() {
                    this.style.borderColor = 'var(--primary-color)';
                    this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
                });
                textarea.addEventListener('blur', function() {
                    this.style.borderColor = 'var(--border-color)';
                    this.style.boxShadow = 'none';
                });
            }
        });
        
        // Honeypot validation script
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
