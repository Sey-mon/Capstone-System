<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
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
            <div class="login-card" style="max-width: 480px; margin: 0 auto;">
                <div class="login-header">
                    <div class="icon-wrapper" style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(52, 211, 153, 0.1)); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-lock" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                    </div>
                    <h2>Reset Password</h2>
                    <p class="subtitle">Enter the verification code sent to your email and your new password.</p>
                </div>
                
                <!-- Display Success Messages -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <p><i class="fas fa-check-circle"></i> {{ session('success') }}</p>
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

                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm" class="login-form">
                    @csrf
                    
                    <!-- Honeypot field for bot detection -->
                    <input type="text" name="website" id="website" style="display:none !important;" tabindex="-1" autocomplete="off">

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" id="email" 
                                   value="{{ session('email') ?? old('email') }}"
                                   placeholder="Enter your email"
                                   required
                                   autocomplete="email"
                                   {{ session('email') ? 'readonly' : '' }}>
                        </div>
                        @error('email')
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="code">Verification Code</label>
                        <div class="input-wrapper">
                            <i class="fas fa-key input-icon"></i>
                            <input type="text" name="code" id="code" 
                                   placeholder="Enter 6-digit code" 
                                   required
                                   pattern="[0-9]{6}"
                                   maxlength="6"
                                   autocomplete="off"
                                   style="letter-spacing: 0.5rem; font-size: 1.25rem; font-weight: 600; text-align: center;">
                        </div>
                        <small style="display: block; margin-top: 0.5rem; color: #6b7280; font-size: 0.875rem;">
                            <i class="fas fa-info-circle"></i> Check your email for the 6-digit code (expires in 15 minutes)
                        </small>
                        @error('code')
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="password" 
                                   placeholder="Create a strong password (minimum 8 characters)" 
                                   required
                                   autocomplete="new-password"
                                   minlength="8"
                                   style="padding-right: 3rem !important;">
                            <i class="fas fa-eye" id="togglePassword" onclick="togglePassword1()" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280; z-index: 100; user-select: none;"></i>
                        </div>
                        <div style="margin-top: 0.75rem; padding: 0.75rem; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                            <small style="display: block; font-weight: 600; color: #374151; margin-bottom: 6px;">Password must contain:</small>
                            <ul style="margin: 0; padding-left: 20px; list-style: none; font-size: 0.875rem;">
                                <li class="password-requirement" data-requirement="length" style="margin: 3px 0; position: relative; padding-left: 20px; color: #6b7280;"><span class="req-icon" style="position: absolute; left: 0; color: #dc2626; font-weight: bold;">✗</span> At least 8 characters</li>
                                <li class="password-requirement" data-requirement="uppercase" style="margin: 3px 0; position: relative; padding-left: 20px; color: #6b7280;"><span class="req-icon" style="position: absolute; left: 0; color: #dc2626; font-weight: bold;">✗</span> One uppercase letter (A-Z)</li>
                                <li class="password-requirement" data-requirement="lowercase" style="margin: 3px 0; position: relative; padding-left: 20px; color: #6b7280;"><span class="req-icon" style="position: absolute; left: 0; color: #dc2626; font-weight: bold;">✗</span> One lowercase letter (a-z)</li>
                                <li class="password-requirement" data-requirement="number" style="margin: 3px 0; position: relative; padding-left: 20px; color: #6b7280;"><span class="req-icon" style="position: absolute; left: 0; color: #dc2626; font-weight: bold;">✗</span> One number (0-9)</li>
                                <li class="password-requirement" data-requirement="special" style="margin: 3px 0; position: relative; padding-left: 20px; color: #6b7280;"><span class="req-icon" style="position: absolute; left: 0; color: #dc2626; font-weight: bold;">✗</span> One special character (@$!%*?&#_-^(){}[]:;'"<>,.~`|/+=)</li>
                            </ul>
                        </div>
                        @error('password')
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   placeholder="Confirm new password" 
                                   required
                                   autocomplete="new-password"
                                   minlength="8"
                                   style="padding-right: 3rem !important;">
                            <i class="fas fa-eye" id="togglePasswordConfirm" onclick="togglePassword2()" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280; z-index: 100; user-select: none;"></i>
                        </div>
                        @error('password_confirmation')
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Google reCAPTCHA v3 (invisible) -->
                    <input type="hidden" name="recaptcha_token" id="recaptchaToken">
                    
                    @error('recaptcha_token')
                        <div style="color: #ef4444; font-size: 0.875rem; margin-top: -10px; margin-bottom: 15px; text-align: center;">
                            {{ $message }}
                        </div>
                    @enderror

                    <button type="submit" class="btn-primary" id="resetBtn">
                        <span class="btn-text">
                            <i class="fas fa-check-circle"></i>
                            Reset Password
                        </span>
                        <div class="loading-spinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>

                    <div class="form-footer" style="margin-top: 1.5rem;">
                        <p style="margin-bottom: 0.75rem; color: var(--text-secondary); font-size: 0.875rem;">
                            <i class="fas fa-info-circle" style="color: var(--primary-color);"></i>
                            Remember your password?
                        </p>
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
    
    <script>
        // Password toggle for first field
        function togglePassword1() {
            const input = document.getElementById('password');
            const icon = document.getElementById('togglePassword');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password toggle for confirm field
        function togglePassword2() {
            const input = document.getElementById('password_confirmation');
            const icon = document.getElementById('togglePasswordConfirm');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Auto-format verification code input (only allow numbers)
        const codeInput = document.getElementById('code');
        if (codeInput) {
            codeInput.addEventListener('input', function(e) {
                // Remove non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Limit to 6 digits
                if (this.value.length > 6) {
                    this.value = this.value.slice(0, 6);
                }
            });
            
            // Prevent paste of non-numeric content
            codeInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const numericOnly = pastedText.replace(/[^0-9]/g, '').slice(0, 6);
                this.value = numericOnly;
            });
        }

        // Password strength checking
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
        }

        function checkPasswordStrength(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[@$!%*?&#_\-^(){}\[\]:;'"<>,.~`|/+=]/.test(password)
            };

            // Update requirement indicators
            Object.keys(requirements).forEach(req => {
                const element = document.querySelector(`[data-requirement="${req}"]`);
                const icon = element?.querySelector('.req-icon');
                if (element && icon) {
                    if (requirements[req]) {
                        element.style.color = '#059669';
                        icon.textContent = '✓';
                        icon.style.color = '#059669';
                    } else {
                        element.style.color = '#6b7280';
                        icon.textContent = '✗';
                        icon.style.color = '#dc2626';
                    }
                }
            });
        }

        // Password match validation
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
        }

        // reCAPTCHA v3 - Generate token on form submit
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        if (resetPasswordForm) {
            resetPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'password_reset'})
                        .then(function(token) {
                            document.getElementById('recaptchaToken').value = token;
                            resetPasswordForm.submit();
                        });
                });
            });
        }

        // Honeypot validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            if (document.getElementById('website').value !== '') {
                e.preventDefault();
                alert('Bot detected. Submission blocked.');
                return false;
            }
        });
    </script>
</body>
</html>
