<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Registration - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="register-container wizard-container">
        <h2>Create Parent Account</h2>
        <p>Follow these simple steps to join our nutrition community</p>
        
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
            <input type="hidden" id="hidden_birth_date" name="birth_date" value="{{ old('birth_date') }}">
            <input type="hidden" id="hidden_sex" name="sex" value="{{ old('sex') }}">
            <input type="hidden" id="hidden_address" name="address" value="{{ old('address') }}">
            <input type="hidden" id="hidden_contact_number" name="contact_number" value="{{ old('contact_number') }}">
            <input type="hidden" id="hidden_email" name="email" value="{{ old('email') }}">
            <input type="hidden" id="hidden_password" name="password" value="">
            <input type="hidden" id="hidden_password_confirmation" name="password_confirmation" value="">
            <input type="hidden" id="hidden_child_first_name" name="child_first_name" value="{{ old('child_first_name') }}">
            <input type="hidden" id="hidden_child_last_name" name="child_last_name" value="{{ old('child_last_name') }}">
            <input type="hidden" id="hidden_child_age_months" name="child_age_months" value="{{ old('child_age_months') }}">
            <input type="hidden" id="hidden_terms" name="terms" value="0">

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
                           placeholder="09XX-XXX-XXXX" 
                           value="{{ old('contact_number') }}"
                           maxlength="13"
                           required>
                    @error('contact_number')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Complete Address</label>
                    <textarea name="address" id="address" 
                              placeholder="Enter your complete address (Street, Barangay, City, Province)" 
                              rows="2"
                              required>{{ old('address') }}</textarea>
                    @error('address')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
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
                    <div class="password-input-container">
                        <input type="password" name="password" id="password" 
                               placeholder="Create a strong password (minimum 8 characters)" 
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            👁️
                        </button>
                    </div>
                    <div class="password-requirements">
                        <small>Password must contain:</small>
                        <ul>
                            <li>At least 8 characters</li>
                            <li>One uppercase letter (A-Z)</li>
                            <li>One lowercase letter (a-z)</li>
                            <li>One number (0-9)</li>
                            <li>One special character (@$!%*?&#)</li>
                        </ul>
                    </div>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="password-input-container">
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               placeholder="Confirm your password" 
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                            👁️
                        </button>
                    </div>
                    @error('password_confirmation')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
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
                        <span class="label">First Name:</span>
                        <span class="value" id="review-first-name"></span>
                    </div>
                    <div class="review-item">
                        <span class="label">Last Name:</span>
                        <span class="value" id="review-last-name"></span>
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
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" id="terms" required>
                        I agree to the <a href="{{ route('terms') }}" target="_blank">Terms and Conditions</a> and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <!-- Step 4 Navigation -->
                <div class="wizard-navigation">
                    <button type="button" class="btn secondary prev-step">Previous</button>
                    <button type="submit" class="btn primary next-step">Complete Registration</button>
                </div>
            </div>
        </form>

        <div class="form-footer">
            <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
            <p><a href="{{ route('register') }}">← Choose different account type</a></p>
        </div>
    </div>

    <script src="{{ asset('js/wizard-register.js') }}"></script>
    
    <script>
        // Password toggle functionality
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                button.innerHTML = '🙈';
                button.setAttribute('aria-label', 'Hide password');
            } else {
                field.type = 'password';
                button.innerHTML = '👁️';
                button.setAttribute('aria-label', 'Show password');
            }
        }

        // Enhanced date input styling
        document.addEventListener('DOMContentLoaded', function() {
            // Add custom styling to date inputs
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
        });
    </script>
</body>
</html>
