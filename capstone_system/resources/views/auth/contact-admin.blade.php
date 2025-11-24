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
    
    <style>
        /* Modern Button Styles */
        .modern-btn {
            position: relative;
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .modern-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
        }

        .modern-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-content {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            z-index: 1;
        }

        .btn-content i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .modern-btn:hover .btn-content i {
            transform: translateX(3px);
        }

        .btn-shimmer {
            position: absolute;
            top: -50%;
            left: -100%;
            width: 100%;
            height: 200%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transform: skewX(-20deg);
            transition: left 0.6s ease;
        }

        .modern-btn:hover .btn-shimmer {
            left: 100%;
        }

        /* Loading state */
        .modern-btn.loading {
            pointer-events: none;
        }

        .modern-btn.loading .btn-content span {
            opacity: 0;
        }

        .modern-btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Ripple effect on click */
        .modern-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .modern-btn:active::before {
            width: 300px;
            height: 300px;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="{{ asset('img/shares-logo.png') }}" alt="SHARES Logo" style="height: 45px; width: auto;">
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

                    <!-- Google reCAPTCHA v2 Widget -->
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" 
                             style="display: flex; justify-content: center; transform: scale(0.95); transform-origin: center;"></div>
                        
                        @error('g-recaptcha-response')
                            <div style="color: #ef4444; font-size: 0.875rem; margin-top: 10px; text-align: center;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="login-btn modern-btn" id="contactBtn">
                        <span class="btn-content">
                            <i class="fas fa-paper-plane"></i>
                            <span>Send Message</span>
                        </span>
                        <span class="btn-shimmer"></span>
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
            const contactBtn = document.getElementById('contactBtn');
            const form = document.querySelector('form');
            
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

            // Enhanced button loading state
            if (form && contactBtn) {
                form.addEventListener('submit', function(e) {
                    // Honeypot validation
                    if (document.getElementById('website').value !== '') {
                        e.preventDefault();
                        alert('Bot detected. Submission blocked.');
                        return false;
                    }

                    // Check if reCAPTCHA is completed
                    const recaptchaResponse = grecaptcha.getResponse();
                    if (!recaptchaResponse) {
                        return; // Let the form validation handle it
                    }

                    // Add loading state
                    contactBtn.classList.add('loading');
                    contactBtn.disabled = true;
                    
                    // Add a slight delay to show the loading animation
                    setTimeout(() => {
                        // Form will submit naturally
                    }, 300);
                });
            }

            // Add keyboard accessibility
            if (contactBtn) {
                contactBtn.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            }
        });
    </script>
</body>
</html>
