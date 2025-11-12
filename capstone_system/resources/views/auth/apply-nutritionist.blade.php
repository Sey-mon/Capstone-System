<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply as Staff - Nutrition System</title>
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
                <i class="fas fa-heartbeat"></i>
                <span>Nutrition System</span>
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
                <div class="step-label">ID Verification</div>
            </div>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="error-alert">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

                <form method="POST" action="{{ route('apply.nutritionist.post') }}" id="nutritionistWizard" class="wizard-form" enctype="multipart/form-data">
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
                                           value="{{ old('first_name') }}" required autofocus>
                                    @error('first_name')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="middle_name">Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name" id="middle_name" 
                                           placeholder="Enter your middle name" 
                                           value="{{ old('middle_name') }}">
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
                                           value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email Address *</label>
                                    <input type="email" class="form-control" name="email" id="email" 
                                           placeholder="Enter your professional email" 
                                           value="{{ old('email') }}" required>
                                    @error('email')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="contact_number">Contact Number *</label>
                                    <input type="tel" class="form-control" name="contact_number" id="contact_number" 
                                           placeholder="Enter your contact number" 
                                           value="{{ old('contact_number') }}" required>
                                    @error('contact_number')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="birth_date">Date of Birth</label>
                                    <input type="date" class="form-control" name="birth_date" id="birth_date" 
                                           value="{{ old('birth_date') }}">
                                    @error('birth_date')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="sex">Gender</label>
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
                                    <label class="form-label" for="license_number">Professional License Number *</label>
                                    <input type="text" class="form-control" name="license_number" id="license_number" 
                                           placeholder="Enter your nutritionist license number" 
                                           value="{{ old('license_number') }}" required>
                                    @error('license_number')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="years_experience">Years of Experience</label>
                                    <input type="number" class="form-control" name="years_experience" id="years_experience" 
                                           placeholder="0" min="0" max="50"
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
                            <input type="text" class="form-control" name="professional_experience" id="professional_experience" 
                                   placeholder="e.g. Nutritionist at ABC Clinic, 3 years" required value="{{ old('professional_experience') }}">
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

                    <!-- Step 3: ID Verification -->
                    <div class="wizard-step" data-step="3">
                        <h3 class="step-title">
                            <i class="fas fa-shield-alt"></i>
                            Professional ID Verification
                        </h3>
                        <p class="step-description">Upload your professional ID or license for verification</p>

                        <div class="form-group">
                            <label class="form-label">Professional ID/License Document *</label>
                            <div class="upload-area" id="uploadArea">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-text">Drag and drop your document here</div>
                                <div class="upload-subtext">or click to browse files</div>
                                <input type="file" class="form-control" name="professional_id_path" id="professional_id_path" 
                                       accept=".jpg,.jpeg,.png,.pdf" required style="display: none;">
                                <button type="button" class="btn btn-outline" onclick="document.getElementById('professional_id_path').click();">
                                    <i class="fas fa-folder-open"></i> Browse Files
                                </button>
                                <div class="upload-requirements">
                                    Supported formats: JPG, PNG, PDF • Maximum size: 5MB
                                </div>
                            </div>
                            <div class="file-info" id="fileInfo"></div>
                            @error('professional_id_path')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Verification Process:</strong> Your professional ID will be reviewed by our admin team. 
                            This process typically takes 2-3 business days. You'll receive an email notification once 
                            your credentials are verified and your account is approved.
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="password">Password *</label>
                                    <input type="password" class="form-control" name="password" id="password" 
                                           placeholder="Create a strong password" required>
                                    <small class="form-text">Minimum 6 characters</small>
                                    @error('password')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="password_confirmation">Confirm Password *</label>
                                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" 
                                           placeholder="Confirm your password" required>
                                    @error('password_confirmation')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
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
                <i class="fas fa-heartbeat"></i>
                <span>Nutrition System</span>
            </div>
            <p class="footer-text">© 2025 Nutrition Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="{{ asset('js/staff-application.js') }}"></script>
    <script>
    // File upload preview
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('professional_id_path');
        const fileInfo = document.getElementById('fileInfo');
        const uploadArea = document.getElementById('uploadArea');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    fileInfo.innerHTML = `
                        <div class="selected-file">
                            <i class="fas fa-file-${file.type.includes('pdf') ? 'pdf' : 'image'}"></i>
                            <span>${file.name}</span>
                            <small>(${fileSize} MB)</small>
                        </div>
                    `;
                    uploadArea.classList.add('has-file');
                }
            });
        }

        // Show/hide 'Other' field for qualifications
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
    });
    </script>
</body>
</html>
