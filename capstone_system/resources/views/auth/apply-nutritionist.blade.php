<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply as Nutritionist - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/nutritionist-wizard.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="wizard-container">
            <!-- Header -->
            <div class="wizard-header">
                <h2><i class="fas fa-user-md me-2"></i>Apply as Nutritionist</h2>
                <p>Join our team of professional nutritionists</p>
                <div class="wizard-progress">
                    <div class="wizard-progress-bar" style="width: 33.33%"></div>
                </div>
            </div>

            <div class="wizard-content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step-progress" style="width: 0%"></div>
                    <div class="step active">
                        <div class="step-number">1</div>
                        <div class="step-title">Personal Info</div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-title">Professional Details</div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-title">ID Verification</div>
                    </div>
                </div>

                <div class="info-box">
                    <h4>Application Process:</h4>
                    <ul>
                        <li>Complete the 3-step application form</li>
                        <li>Upload your professional ID for verification</li>
                        <li>Admin will review your qualifications</li>
                        <li>You'll receive email notification upon approval</li>
                        <li>Access your nutritionist dashboard once approved</li>
                    </ul>
                </div>
                <!-- Display Error Messages -->
                @if($errors->any())
                    <div class="alert alert-error">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('apply.nutritionist.post') }}" id="nutritionistWizard" enctype="multipart/form-data">
                    @csrf

                    <!-- Step 1: Personal Information -->
                    <div class="step-content active" id="step-1">
                        <div class="step-header">
                            <h3><i class="fas fa-user me-2"></i>Personal Information</h3>
                            <p>Please provide your personal details</p>
                        </div>

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
                    </div>

                    <!-- Step 2: Professional Information -->
                    <div class="step-content" id="step-2">
                        <div class="step-header">
                            <h3><i class="fas fa-briefcase me-2"></i>Professional Information</h3>
                            <p>Tell us about your professional background</p>
                        </div>

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
                            <textarea class="form-control" name="qualifications" id="qualifications" 
                                      placeholder="Please list your educational background, degrees, certifications, and any relevant training in nutrition or dietetics..."
                                      required>{{ old('qualifications') }}</textarea>
                            @error('qualifications')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="professional_experience">Professional Experience *</label>
                            <textarea class="form-control" name="professional_experience" id="professional_experience" 
                                      placeholder="Please describe your work experience in nutrition, dietetics, or related fields. Include years of experience, previous positions, and areas of specialization..."
                                      required>{{ old('professional_experience') }}</textarea>
                            @error('professional_experience')
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Step 3: ID Verification -->
                    <div class="step-content" id="step-3">
                        <div class="step-header">
                            <h3><i class="fas fa-shield-alt me-2"></i>Professional ID Verification</h3>
                            <p>Upload your professional ID or license for verification</p>
                        </div>

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
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="wizard-buttons">
                        <button type="button" class="btn btn-outline btn-prev" style="display: none;">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        
                        <div class="button-group">
                            <button type="button" class="btn btn-primary btn-next">
                                Next Step <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn btn-success btn-submit" style="display: none;">
                                <i class="fas fa-check"></i> Submit Application
                            </button>
                        </div>
                    </div>
                </form>

                <div class="form-footer" style="margin-top: 2rem; text-align: center; padding-top: 2rem; border-top: 1px solid #e9ecef;">
                    <p>Already have an account? <a href="{{ route('login') }}" style="color: #667eea; text-decoration: none;">Login here</a></p>
                    <p><a href="{{ route('register') }}" style="color: #6c757d; text-decoration: none;">← Choose different account type</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/nutritionist-wizard.js') }}"></script>
</body>
</html>
