<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Registration - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register-parent.css') }}">
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
                <a href="{{ route('login') }}">Already have an account? Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Registration -->
    <section id="home" class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Join Our<br>Nutrition Community</h1>
                <p class="hero-subtitle">Create your parent account to monitor your child's health and nutrition progress with professional guidance</p>
                
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Track your child's nutrition</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Get personalized meal plans</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Access health assessments</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Connect with nutritionists</span>
                    </div>
                </div>
            </div>

            <div class="login-card wizard-container" role="main" aria-label="Parent Registration Form">
                <div class="wizard-header">
                    <h2>Create Parent Account</h2>
                    <p>Follow these simple steps to join our nutrition community</p>
                </div>
        
        <!-- Progress Indicator -->
        <div class="wizard-progress">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Personal Info</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Account Setup</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Child Info</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-number">4</div>
                <div class="step-label">Review</div>
            </div>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.parent.post') }}" id="parentRegistrationForm">
            @csrf
            
            <!-- Hidden fields to store wizard data -->
            <input type="hidden" id="hidden_first_name" name="first_name" value="{{ old('first_name') }}">
            <input type="hidden" id="hidden_middle_name" name="middle_name" value="{{ old('middle_name') }}">
            <input type="hidden" id="hidden_last_name" name="last_name" value="{{ old('last_name') }}">
            <input type="hidden" id="hidden_suffix" name="suffix" value="{{ old('suffix') }}">
            <input type="hidden" id="hidden_birth_date" name="birth_date" value="{{ old('birth_date') }}">
            <input type="hidden" id="hidden_sex" name="sex" value="{{ old('sex') }}">
            <input type="hidden" id="hidden_house_street" name="house_street" value="{{ old('house_street') }}">
            <input type="hidden" id="hidden_barangay" name="barangay" value="{{ old('barangay') }}">
            <input type="hidden" id="hidden_city" name="city" value="{{ old('city', 'San Pedro') }}">
            <input type="hidden" id="hidden_province" name="province" value="{{ old('province', 'Laguna') }}">
            <input type="hidden" id="hidden_address" name="address" value="{{ old('address') }}">
            <input type="hidden" id="hidden_contact_number" name="contact_number" value="{{ old('contact_number') }}">
            <input type="hidden" id="hidden_email" name="email" value="{{ old('email') }}">
            <input type="hidden" id="hidden_password" name="password" value="">
            <input type="hidden" id="hidden_password_confirmation" name="password_confirmation" value="">
            <input type="hidden" id="hidden_child_first_name" name="child_first_name" value="{{ old('child_first_name') }}">
            <input type="hidden" id="hidden_child_last_name" name="child_last_name" value="{{ old('child_last_name') }}">
            <input type="hidden" id="hidden_child_age_months" name="child_age_months" value="{{ old('child_age_months') }}">
            <!-- Removed duplicate hidden terms field. The visible checkbox now submits the terms acceptance. -->

            <!-- Step 1: Personal Information -->
            <div class="wizard-step active" id="step-1" data-step="1">
                <h3>Personal Information</h3>
                <p class="step-description">Tell us about yourself</p>
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" 
                           placeholder="Enter your first name" 
                           value="{{ old('first_name') }}"
                           required autofocus>
                    @error('first_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="middle_name">Middle Name (Optional)</label>
                    <input type="text" name="middle_name" id="middle_name" 
                           placeholder="Enter your middle name" 
                           value="{{ old('middle_name') }}">
                    @error('middle_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" 
                           placeholder="Enter your last name" 
                           value="{{ old('last_name') }}"
                           required>
                    @error('last_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="suffix">Suffix (Optional)</label>
                    <select name="suffix" id="suffix">
                        <option value="">Select suffix</option>
                        <option value="Jr." {{ old('suffix') == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                        <option value="Sr." {{ old('suffix') == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                        <option value="II" {{ old('suffix') == 'II' ? 'selected' : '' }}>II</option>
                        <option value="III" {{ old('suffix') == 'III' ? 'selected' : '' }}>III</option>
                        <option value="IV" {{ old('suffix') == 'IV' ? 'selected' : '' }}>IV</option>
                        <option value="V" {{ old('suffix') == 'V' ? 'selected' : '' }}>V</option>
                    </select>
                    @error('suffix')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="birth_date">Date of Birth</label>
                    <input type="date" name="birth_date" id="birth_date" 
                           value="{{ old('birth_date') }}"
                           required>
                    @error('birth_date')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="sex">Sex</label>
                    <select name="sex" id="sex" required>
                        <option value="">Select your sex</option>
                        <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('sex') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('sex')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="tel" name="contact_number" id="contact_number" 
                           placeholder="09123456789" 
                           value="{{ old('contact_number') }}"
                           maxlength="11"
                           pattern="09[0-9]{9}"
                           inputmode="numeric"
                           required>
                    <small class="field-help">Enter your 11-digit Philippine mobile number (format: 09XXXXXXXXX)</small>
                    @error('contact_number')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="address-section">
                    <h4>Address Information</h4>
                    
                    <div class="form-group">
                        <label for="house_street">House/Street Address</label>
                        <input type="text" name="house_street" id="house_street" 
                               placeholder="Enter house number and street name" 
                               value="{{ old('house_street') }}"
                               required>
                        <small class="field-help">Example: 123 Rizal Street, Block 5 Lot 10</small>
                        @error('house_street')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="barangay">Barangay</label>
                        <select name="barangay" id="barangay" required>
                            <option value="">Select your barangay</option>
                            <option value="Bagong Silang" {{ old('barangay') == 'Bagong Silang' ? 'selected' : '' }}>Bagong Silang</option>
                            <option value="Calendola" {{ old('barangay') == 'Calendola' ? 'selected' : '' }}>Calendola</option>
                            <option value="Chrysanthemum" {{ old('barangay') == 'Chrysanthemum' ? 'selected' : '' }}>Chrysanthemum</option>
                            <option value="Cuyab" {{ old('barangay') == 'Cuyab' ? 'selected' : '' }}>Cuyab</option>
                            <option value="Estrella" {{ old('barangay') == 'Estrella' ? 'selected' : '' }}>Estrella</option>
                            <option value="Fatima" {{ old('barangay') == 'Fatima' ? 'selected' : '' }}>Fatima</option>
                            <option value="G.S.I.S." {{ old('barangay') == 'G.S.I.S.' ? 'selected' : '' }}>G.S.I.S.</option>
                            <option value="Landayan" {{ old('barangay') == 'Landayan' ? 'selected' : '' }}>Landayan</option>
                            <option value="Langgam" {{ old('barangay') == 'Langgam' ? 'selected' : '' }}>Langgam</option>
                            <option value="Laram" {{ old('barangay') == 'Laram' ? 'selected' : '' }}>Laram</option>
                            <option value="Magsaysay" {{ old('barangay') == 'Magsaysay' ? 'selected' : '' }}>Magsaysay</option>
                            <option value="Maharlika" {{ old('barangay') == 'Maharlika' ? 'selected' : '' }}>Maharlika</option>
                            <option value="Nueva" {{ old('barangay') == 'Nueva' ? 'selected' : '' }}>Nueva</option>
                            <option value="Pacita I" {{ old('barangay') == 'Pacita I' ? 'selected' : '' }}>Pacita I</option>
                            <option value="Pacita II" {{ old('barangay') == 'Pacita II' ? 'selected' : '' }}>Pacita II</option>
                            <option value="Poblacion" {{ old('barangay') == 'Poblacion' ? 'selected' : '' }}>Poblacion</option>
                            <option value="Rosario" {{ old('barangay') == 'Rosario' ? 'selected' : '' }}>Rosario</option>
                            <option value="San Antonio" {{ old('barangay') == 'San Antonio' ? 'selected' : '' }}>San Antonio</option>
                            <option value="San Lorenzo Ruiz" {{ old('barangay') == 'San Lorenzo Ruiz' ? 'selected' : '' }}>San Lorenzo Ruiz</option>
                            <option value="San Roque" {{ old('barangay') == 'San Roque' ? 'selected' : '' }}>San Roque</option>
                            <option value="San Vicente" {{ old('barangay') == 'San Vicente' ? 'selected' : '' }}>San Vicente</option>
                            <option value="Santo Niño" {{ old('barangay') == 'Santo Niño' ? 'selected' : '' }}>Santo Niño</option>
                            <option value="United Bayanihan" {{ old('barangay') == 'United Bayanihan' ? 'selected' : '' }}>United Bayanihan</option>
                            <option value="United Better Living" {{ old('barangay') == 'United Better Living' ? 'selected' : '' }}>United Better Living</option>
                        </select>
                        @error('barangay')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="address-readonly-fields">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" name="city" id="city" 
                                   value="San Pedro" 
                                   readonly>
                            @error('city')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="province">Province</label>
                            <input type="text" name="province" id="province" 
                                   value="Laguna" 
                                   readonly>
                            @error('province')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Step 1 Navigation -->
                <div class="wizard-navigation">
                    <div></div> <!-- Empty div for spacing -->
                    <button type="button" class="btn primary next-step">Continue</button>
                </div>
            </div>

            <!-- Step 2: Account Setup -->
            <div class="wizard-step" id="step-2" data-step="2">
                <h3>Account Setup</h3>
                <p class="step-description">Create your login credentials</p>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" 
                           placeholder="Enter your email address" 
                           value="{{ old('email') }}"
                           required>
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field-wrapper">
                        <input type="password" name="password" id="password" 
                               placeholder="Create a strong password (minimum 8 characters)" 
                               value="{{ old('password') }}"
                               required>
                        <button type="button" class="password-visibility-toggle" data-target="password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye show-icon"></i>
                            <i class="fas fa-eye-slash hide-icon" style="display: none;"></i>
                        </button>
                    </div>
                    <div class="password-strength-info">
                        <small>Password must contain:</small>
                        <ul class="password-requirements-list">
                            <li class="requirement" data-requirement="length">At least 8 characters</li>
                            <li class="requirement" data-requirement="uppercase">One uppercase letter (A-Z)</li>
                            <li class="requirement" data-requirement="lowercase">One lowercase letter (a-z)</li>
                            <li class="requirement" data-requirement="number">One number (0-9)</li>
                            <li class="requirement" data-requirement="special">One special character (@$!%*?&#)</li>
                        </ul>
                    </div>
                    @error('password')
                        <div class="field-error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="password-field-wrapper">
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               placeholder="Confirm your password" 
                               value="{{ old('password_confirmation') }}"
                               required>
                        <button type="button" class="password-visibility-toggle" data-target="password_confirmation" aria-label="Toggle password visibility">
                            <i class="fas fa-eye show-icon"></i>
                            <i class="fas fa-eye-slash hide-icon" style="display: none;"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2 Navigation -->
                <div class="wizard-navigation">
                    <button type="button" class="btn secondary prev-step">Previous</button>
                    <button type="button" class="btn primary next-step">Continue</button>
                </div>
            </div>

            <!-- Step 3: Child Information -->
            <div class="wizard-step" id="step-3" data-step="3">
                <h3>Child Information</h3>
                <p class="step-description">Help us find your child in our records (Optional)</p>
                
                <div class="info-box">
                    <strong>🔒 Privacy Protected:</strong> We use this information only to securely link your account to your child's records. This information is kept confidential and secure.
                </div>

                <div class="form-group">
                    <label for="child_first_name">Child's First Name</label>
                    <input type="text" name="child_first_name" id="child_first_name" 
                           placeholder="Enter your child's first name" 
                           value="{{ old('child_first_name') }}">
                    @error('child_first_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="child_last_name">Child's Last Name</label>
                    <input type="text" name="child_last_name" id="child_last_name" 
                           placeholder="Enter your child's last name" 
                           value="{{ old('child_last_name') }}">
                    @error('child_last_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="child_age_months">Child's Age (in months)</label>
                    <input type="number" name="child_age_months" id="child_age_months" 
                           min="0" max="60" placeholder="e.g., 24 for 2 years old"
                           value="{{ old('child_age_months') }}">
                    <small class="field-help">Enter age in months (0-60 months / 0-5 years)</small>
                    @error('child_age_months')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="skip-option">
                    <p><em>💡 You can skip this step and link your child later through your dashboard.</em></p>
                </div>
                
                <!-- Step 3 Navigation -->
                <div class="wizard-navigation">
                    <button type="button" class="btn secondary prev-step">Previous</button>
                    <div>
                        <button type="button" class="btn secondary skip-child-info">Skip This Step</button>
                        <button type="button" class="btn primary next-step">Continue</button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Review -->
            <div class="wizard-step" id="step-4" data-step="4">
                <h3>Review Your Information</h3>
                <p class="step-description">Please review your details before creating your account</p>
                
                <div class="review-section">
                    <h4>Personal Information</h4>
                    <div class="review-item">
                        <span class="label">Full Name:</span>
                        <span class="value" id="review-full-name"></span>
                    </div>
                    <div class="review-item">
                        <span class="label">Birth Date:</span>
                        <span class="value" id="review-birth-date"></span>
                    </div>
                    <div class="review-item">
                        <span class="label">Sex:</span>
                        <span class="value" id="review-sex"></span>
                    </div>
                    <div class="review-item">
                        <span class="label">Phone:</span>
                        <span class="value" id="review-phone"></span>
                    </div>
                    <div class="review-item">
                        <span class="label">Address:</span>
                        <span class="value" id="review-address"></span>
                    </div>
                </div>
                
                <div class="review-section">
                    <h4>Account Information</h4>
                    <div class="review-item">
                        <span class="label">Email:</span>
                        <span class="value" id="review-email"></span>
                    </div>
                </div>

                <div class="review-section">
                    <h4>Child Information</h4>
                    <div class="review-item">
                        <span class="label">Child's Name:</span>
                        <span class="value" id="review-child-name"></span>
                    </div>
                    <div class="review-item">
                        <span class="label">Age:</span>
                        <span class="value" id="review-child-age"></span>
                    </div>
                </div>

                <div class="terms-section">
                    <div class="terms-view-notice" id="termsNotice">
                        <i class="fas fa-info-circle"></i>
                        <p>Please click to read: <a href="{{ route('terms') }}" target="_blank" id="termsLink" class="terms-link">Terms and Conditions</a> and <a href="{{ route('privacy') }}" target="_blank" id="privacyLink" class="terms-link">Privacy Policy</a> before proceeding.</p>
                    </div>
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" id="terms" value="1" required>
                        <span>I have read and agree to the <a href="{{ route('terms') }}" target="_blank" class="inline-link">Terms and Conditions</a> and <a href="{{ route('privacy') }}" target="_blank" class="inline-link">Privacy Policy</a></span>
                    </label>
                </div>
                
                <!-- Step 4 Navigation -->
                <div class="wizard-navigation">
                    <button type="button" class="btn secondary prev-step">Previous</button>
                    <button type="submit" class="btn primary next-step" id="submitBtn" disabled>Complete Registration</button>
                </div>
            </div>
        </form>

                <div class="form-footer">
                    <p>Already have an account? <a href="{{ route('login') }}" class="register-link">Sign In</a></p>
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
            <div class="footer-links">
                <a href="{{ route('staff.login') }}" class="footer-staff-link">
                    <i class="fas fa-user-shield"></i>
                    Staff Portal
                </a>
            </div>
            <p class="footer-text">© 2025 Nutrition Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="{{ asset('js/login.js') }}"></script>
    <script src="{{ asset('js/register-parent.js') }}"></script>
    
    <script>
        // New Password Field Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add CSRF token refresh functionality
            window.refreshCSRFToken = function() {
                fetch('/csrf-token', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.csrf_token) {
                        // Update the CSRF token in the form
                        const csrfInput = document.querySelector('input[name="_token"]');
                        if (csrfInput) {
                            csrfInput.value = data.csrf_token;
                        }
                        // Update meta tag
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        if (csrfMeta) {
                            csrfMeta.content = data.csrf_token;
                        }
                        console.log('CSRF token refreshed');
                    }
                })
                .catch(error => {
                    console.error('Failed to refresh CSRF token:', error);
                });
            };

            // Debug form submission
            const form = document.getElementById('parentRegistrationForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submission intercepted');
                    console.log('Form action:', this.action);
                    console.log('Form method:', this.method);
                    
                    // Verify CSRF token is present
                    const csrfToken = document.querySelector('input[name="_token"]');
                    console.log('CSRF token validation:', csrfToken ? 'OK' : 'Missing');
                    
                    // Check required fields
                    const requiredFields = ['first_name', 'last_name', 'email', 'password'];
                    const missingFields = [];
                    
                    requiredFields.forEach(field => {
                        const input = document.querySelector(`input[name="${field}"]`);
                        if (!input || !input.value.trim()) {
                            missingFields.push(field);
                        }
                    });
                    
                    if (missingFields.length > 0) {
                        console.error('Missing required fields:', missingFields);
                    }
                    
                    // Form submission validation passed
                    console.log('Form validation completed successfully');
                });
            }

            // Password visibility toggle
            const passwordToggles = document.querySelectorAll('.password-visibility-toggle');
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const targetId = this.getAttribute('data-target');
                    const targetField = document.getElementById(targetId);
                    const showText = this.querySelector('.show-text');
                    const hideText = this.querySelector('.hide-text');
                    
                    if (targetField && showText && hideText) {
                        if (targetField.type === 'password') {
                            targetField.type = 'text';
                            showText.style.display = 'none';
                            hideText.style.display = 'block';
                        } else {
                            targetField.type = 'password';
                            showText.style.display = 'block';
                            hideText.style.display = 'none';
                        }
                    }
                });
            });

            // Password strength checking
            const passwordField = document.getElementById('password');
            const confirmField = document.getElementById('password_confirmation');

            if (passwordField) {
                passwordField.addEventListener('input', function() {
                    checkPasswordStrength(this.value);
                });
            }

            function checkPasswordStrength(password) {
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[@$!%*?&#]/.test(password)
                };

                // Update requirement indicators
                Object.keys(requirements).forEach(req => {
                    const element = document.querySelector(`[data-requirement="${req}"]`);
                    if (element) {
                        if (requirements[req]) {
                            element.classList.add('met');
                        } else {
                            element.classList.remove('met');
                        }
                    }
                });
            }

            // Enhanced date input styling
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#66bb6a';
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.style.borderColor = '#e2e8f0';
                    }
                });
            });

            // Terms checkbox: enable submit button when checked
            const termsCheckbox = document.getElementById('terms');
            const submitBtn = document.getElementById('submitBtn');

            function updateSubmitState() {
                if (!submitBtn) return;
                submitBtn.disabled = !(termsCheckbox && termsCheckbox.checked);
            }

            if (termsCheckbox) {
                termsCheckbox.addEventListener('change', updateSubmitState);
                // initialize state on load
                updateSubmitState();
            }
        });
    </script>
</body>
</html>
