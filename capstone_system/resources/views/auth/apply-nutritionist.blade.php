<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply as Staff - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/register-parent.css') }}?v={{ filemtime(public_path('css/register-parent.css')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Header -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="{{ asset('img/shares-logo.png') }}" alt="SHARES Logo" style="height: 45px; width: auto;">
            </div>
            <div class="nav-links">
                <a href="{{ route('staff.login') }}">Already have an account? Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Application -->
    <section id="home" class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Join Our<br>Professional Team</h1>
                <p class="hero-subtitle">Apply as a nutritionist or health worker and make a difference in community health and nutrition</p>
                
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Professional dashboard access</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Manage patient assessments</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Create personalized meal plans</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Track community health metrics</span>
                    </div>
                </div>
            </div>

            <div class="login-card wizard-container" role="main" aria-label="Staff Application Form">
                <div class="wizard-header">
                    <h2>Apply as Staff</h2>
                    <p>Join our team of professional nutritionists and health workers</p>
                </div>
        
        <!-- Progress Indicator -->
        <div class="wizard-progress">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Personal Info</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Professional Details</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Account Setup</div>
            </div>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="error-alert" id="errorAlert" style="position: relative; background: linear-gradient(135deg, #fee, #fdd); border-left: 4px solid #dc3545; padding: 1rem 1.5rem; margin-bottom: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(220, 53, 69, 0.15); animation: slideInDown 0.3s ease-out;">
                <button type="button" onclick="dismissError()" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 1.2rem; color: #dc3545; cursor: pointer; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    <i class="fas fa-times"></i>
                </button>
                <div style="display: flex; gap: 1rem;">
                    <i class="fas fa-exclamation-circle" style="color: #dc3545; font-size: 1.5rem; flex-shrink: 0; margin-top: 2px;"></i>
                    <div style="flex: 1;">
                        <strong style="color: #721c24; font-size: 1.05rem; display: block; margin-bottom: 0.5rem;">Please fix the following errors:</strong>
                        <ul style="margin: 0; padding-left: 1.2rem; color: #721c24;">
                            @foreach($errors->all() as $error)
                                <li style="margin-bottom: 0.3rem;">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div style="width: 100%; height: 3px; background: #f8d7da; border-radius: 3px; margin-top: 0.8rem; overflow: hidden;">
                    <div id="errorTimer" style="width: 100%; height: 100%; background: linear-gradient(90deg, #dc3545, #c82333); animation: shrinkWidth 8s linear forwards;"></div>
                </div>
            </div>
            <style>
                @keyframes slideInDown {
                    from {
                        opacity: 0;
                        transform: translateY(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                @keyframes shrinkWidth {
                    from { width: 100%; }
                    to { width: 0%; }
                }
                @keyframes fadeOut {
                    from { opacity: 1; transform: translateY(0); }
                    to { opacity: 0; transform: translateY(-20px); }
                }
            </style>
        @endif

                <form method="POST" action="{{ route('apply.nutritionist.post') }}" id="nutritionistWizard" class="wizard-form" enctype="multipart/form-data" novalidate>
                    @csrf

                    <!-- Step 1: Personal Information -->
                    <div class="wizard-step active" data-step="1">
                        <h3 class="step-title">
                            <i class="fas fa-user"></i>
                            Personal Information
                        </h3>
                        <p class="step-description">Please provide your personal details</p>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="first_name">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" id="first_name" 
                                           placeholder="Enter your first name" 
                                           value="{{ old('first_name') }}" 
                                           maxlength="255"
                                           required autofocus>
                                    @error('first_name')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="middle_name">Middle Name (Optional)</label>
                                    <input type="text" class="form-control" name="middle_name" id="middle_name" 
                                           placeholder="Enter your middle name" 
                                           value="{{ old('middle_name') }}"
                                           maxlength="255">
                                    @error('middle_name')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="last_name">Last Name *</label>
                                    <input type="text" class="form-control" name="last_name" id="last_name" 
                                           placeholder="Enter your last name" 
                                           value="{{ old('last_name') }}" 
                                           maxlength="255"
                                           required>
                                    @error('last_name')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="contact_number">Contact Number *</label>
                                    <input type="tel" class="form-control" name="contact_number" id="contact_number" 
                                           placeholder="09123456789" 
                                           value="{{ old('contact_number') }}" 
                                           maxlength="11"
                                           pattern="09[0-9]{9}"
                                           inputmode="numeric"
                                           title="Enter a valid 11-digit Philippine mobile number (format: 09XXXXXXXXX)"
                                           required>
                                    <small class="form-text">Enter your 11-digit Philippine mobile number (format: 09XXXXXXXXX)</small>
                                    @error('contact_number')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="sex">Gender (Optional)</label>
                            <select class="form-control form-select" name="sex" id="sex">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('sex') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('sex')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="step-navigation">
                            <button type="button" class="btn btn-secondary prev-step" style="visibility: hidden;">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary next-step">
                                Next Step <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Professional Information -->
                    <div class="wizard-step" data-step="2">
                        <h3 class="step-title">
                            <i class="fas fa-briefcase"></i>
                            Professional Information
                        </h3>
                        <p class="step-description">Tell us about your professional background</p>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="years_experience">Years of Experience (Optional)</label>
                                    <input type="number" class="form-control" name="years_experience" id="years_experience" 
                                           placeholder="0" min="0" max="50" step="1"
                                           value="{{ old('years_experience') }}">
                                    @error('years_experience')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="qualifications">Educational Qualifications *</label>
                            <select class="form-control" name="qualifications" id="qualifications" required>
                                <option value="">Select your highest qualification</option>
                                <option value="BS Nutrition" {{ old('qualifications') == 'BS Nutrition' ? 'selected' : '' }}>BS Nutrition</option>
                                <option value="BS Dietetics" {{ old('qualifications') == 'BS Dietetics' ? 'selected' : '' }}>BS Dietetics</option>
                                <option value="BS Food Technology" {{ old('qualifications') == 'BS Food Technology' ? 'selected' : '' }}>BS Food Technology</option>
                                <option value="MS Nutrition" {{ old('qualifications') == 'MS Nutrition' ? 'selected' : '' }}>MS Nutrition</option>
                                <option value="PhD Nutrition" {{ old('qualifications') == 'PhD Nutrition' ? 'selected' : '' }}>PhD Nutrition</option>
                                <option value="Other" {{ old('qualifications') == 'Other' ? 'selected' : '' }}>Other (please specify below)</option>
                            </select>
                            <input type="text" class="form-control" name="qualifications_other" id="qualifications_other" placeholder="If Other, please specify" style="margin-top: 0.5rem; display: none;" value="{{ old('qualifications_other') }}">
                            @error('qualifications')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="professional_experience">Professional Experience *</label>
                            <textarea class="form-control" name="professional_experience" id="professional_experience" 
                                   placeholder="e.g. Nutritionist at ABC Clinic, 3 years" 
                                   minlength="10" maxlength="1000" rows="3" required>{{ old('professional_experience') }}</textarea>
                            <small class="form-text">Briefly describe your work experience (minimum 10 characters)</small>
                            @error('professional_experience')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="step-navigation">
                            <button type="button" class="btn btn-secondary prev-step">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary next-step">
                                Next Step <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Account Setup -->
                    <div class="wizard-step" data-step="3">
                        <h3 class="step-title">
                            <i class="fas fa-lock"></i>
                            Account Setup
                        </h3>
                        <p class="step-description">Create your secure account credentials</p>

                        <div class="form-group">
                            <label class="form-label" for="email">Email Address *</label>
                            <input type="email" class="form-control" name="email" id="email" 
                                   placeholder="Enter your professional email" 
                                   value="{{ old('email') }}" 
                                   maxlength="255"
                                   autocomplete="email"
                                   required>
                            @error('email')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="password">Password *</label>
                                    <div style="position: relative;">
                                        <input type="password" class="form-control" name="password" id="password" 
                                               placeholder="Create a strong password (minimum 8 characters)" 
                                               minlength="8" 
                                               autocomplete="new-password"
                                               required
                                               style="padding-right: 40px;">
                                        <button type="button" class="toggle-password" data-target="password" 
                                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div style="margin-top: 0.75rem; padding: 0.75rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px;">
                                        <div style="font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Password must contain:</div>
                                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                            <div id="req-length" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: #dc3545;">
                                                <i class="fas fa-times" style="width: 14px;"></i>
                                                <span>At least 8 characters</span>
                                            </div>
                                            <div id="req-uppercase" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: #dc3545;">
                                                <i class="fas fa-times" style="width: 14px;"></i>
                                                <span>One uppercase letter (A-Z)</span>
                                            </div>
                                            <div id="req-lowercase" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: #dc3545;">
                                                <i class="fas fa-times" style="width: 14px;"></i>
                                                <span>One lowercase letter (a-z)</span>
                                            </div>
                                            <div id="req-number" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: #dc3545;">
                                                <i class="fas fa-times" style="width: 14px;"></i>
                                                <span>One number (0-9)</span>
                                            </div>
                                            <div id="req-special" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: #dc3545;">
                                                <i class="fas fa-times" style="width: 14px;"></i>
                                                <span>One special character (@$!%*?&#.-"'(){}[]:;<>,.~`/+=)</span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('password')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="password_confirmation">Confirm Password *</label>
                                    <div style="position: relative;">
                                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" 
                                               placeholder="Confirm your password" 
                                               minlength="8" 
                                               autocomplete="new-password"
                                               required
                                               style="padding-right: 40px;">
                                        <button type="button" class="toggle-password" data-target="password_confirmation" 
                                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Account Review:</strong> Your application will be reviewed by our admin team. 
                            You'll receive an email notification once your account is approved and activated.
                        </div>

                        <div class="step-navigation">
                            <button type="button" class="btn btn-secondary prev-step">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Submit Application
                            </button>
                        </div>
                    </div>
                </form>

                <div class="form-footer">
                    <p>Already have an account? <a href="{{ route('staff.login') }}" class="register-link">Sign In</a></p>
                </div>
            </div>
        </div>

        <!-- Decorative Background -->
        <div class="hero-decoration">
            <div class="particle-network"></div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-logo">
                <img src="{{ asset('img/shares-logo.png') }}" alt="SHARES Logo" style="height: 60px; width: auto;">
            </div>
            <p class="footer-text">© 2025 Nutrition Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="{{ asset('js/staff-application.js') }}?v={{ filemtime(public_path('js/staff-application.js')) }}"></script>
    <script>
    // Keep only qualifications show/hide logic (removed file upload + date JS)
    document.addEventListener('DOMContentLoaded', function() {
        // Navigate to step with errors if validation failed
        @if($errors->any())
            navigateToErrorStep();
        @endif

        function navigateToErrorStep() {
            // Define which fields belong to which step
            const stepFields = {
                1: ['first_name', 'middle_name', 'last_name', 'contact_number', 'sex', 'address'],
                2: ['years_experience', 'qualifications', 'qualifications_other', 'professional_experience'],
                3: ['email', 'password', 'password_confirmation']
            };

            // Get all error field names from Laravel errors
            const errorFields = [
                @foreach($errors->keys() as $field)
                    '{{ $field }}',
                @endforeach
            ];

            // Find which step has the first error
            let targetStep = 1;
            for (let step = 1; step <= 3; step++) {
                for (let field of errorFields) {
                    if (stepFields[step].includes(field)) {
                        targetStep = step;
                        break;
                    }
                }
                if (targetStep === step) break;
            }

            // Navigate to the step with errors
            if (targetStep > 1 && typeof window.showStep === 'function') {
                setTimeout(() => {
                    window.showStep(targetStep, false);
                }, 100);
            }
        }

        // Auto-capitalize name fields
        const nameFields = ['first_name', 'middle_name', 'last_name'];
        nameFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function(e) {
                    let value = e.target.value;
                    // Capitalize first letter of each word
                    e.target.value = value.replace(/\b\w/g, function(char) {
                        return char.toUpperCase();
                    });
                });
            }
        });

        const qualSelect = document.getElementById('qualifications');
        const qualOther = document.getElementById('qualifications_other');
        if (qualSelect && qualOther) {
            qualSelect.addEventListener('change', function() {
                if (this.value === 'Other') {
                    qualOther.style.display = 'block';
                } else {
                    qualOther.style.display = 'none';
                }
            });
            if (qualSelect.value === 'Other') {
                qualOther.style.display = 'block';
            }
        }

        // Password visibility toggle
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password validation checker
        const passwordInput = document.getElementById('password');
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqLowercase = document.getElementById('req-lowercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                // Check each requirement
                updateRequirement(reqLength, password.length >= 8);
                updateRequirement(reqUppercase, /[A-Z]/.test(password));
                updateRequirement(reqLowercase, /[a-z]/.test(password));
                updateRequirement(reqNumber, /[0-9]/.test(password));
                updateRequirement(reqSpecial, /[@$!%*?&#.\-"'(){}\[\]:;<>,.~`\/+=]/.test(password));
            });
        }

        function updateRequirement(element, isMet) {
            const icon = element.querySelector('i');
            if (isMet) {
                element.style.color = '#10b981';
                icon.classList.remove('fa-times');
                icon.classList.add('fa-check');
            } else {
                element.style.color = '#dc3545';
                icon.classList.remove('fa-check');
                icon.classList.add('fa-times');
            }
        }

        // Auto-dismiss error alert after 8 seconds
        const errorAlert = document.getElementById('errorAlert');
        if (errorAlert) {
            setTimeout(function() {
                dismissError();
            }, 8000);
        }
    });

    // Function to dismiss error alert
    function dismissError() {
        const errorAlert = document.getElementById('errorAlert');
        if (errorAlert) {
            errorAlert.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(function() {
                errorAlert.remove();
            }, 300);
        }
    }
    </script>
</body>
</html>
