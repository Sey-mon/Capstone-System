<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply as Nutritionist - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="register-container" style="max-width: 600px;">
        <h2>Apply as Nutritionist</h2>
        <p>Join our team of professional nutritionists</p>
        
        <div class="info-box">
            <h4>Application Process:</h4>
            <ul>
                <li>Submit your application with credentials</li>
                <li>Admin will review your qualifications</li>
                <li>You'll receive email notification upon approval</li>
                <li>Access your nutritionist dashboard once approved</li>
            </ul>
        </div>
        
        <!-- Display Error Messages -->
        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('apply.nutritionist.post') }}" id="applicationForm">
            @csrf

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
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" 
                       placeholder="Enter your professional email address" 
                       value="{{ old('email') }}"
                       required>
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="tel" name="contact_number" id="contact_number" 
                       placeholder="Enter your contact number" 
                       value="{{ old('contact_number') }}"
                       required>
                @error('contact_number')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="license_number">Professional License Number</label>
                <input type="text" name="license_number" id="license_number" 
                       placeholder="Enter your nutritionist license number" 
                       value="{{ old('license_number') }}"
                       required>
                @error('license_number')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="qualifications">Educational Qualifications</label>
                <textarea name="qualifications" id="qualifications" 
                          placeholder="Please list your educational background, degrees, certifications, and any relevant training in nutrition or dietetics..."
                          required>{{ old('qualifications') }}</textarea>
                @error('qualifications')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="experience">Professional Experience</label>
                <textarea name="experience" id="experience" 
                          placeholder="Please describe your work experience in nutrition, dietetics, or related fields. Include years of experience, previous positions, and areas of specialization..."
                          required>{{ old('experience') }}</textarea>
                @error('experience')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="register-btn">Submit Application</button>
        </form>

        <div class="form-footer">
            <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
            <p><a href="{{ route('register') }}">← Choose different account type</a></p>
        </div>
    </div>

    <script src="{{ asset('js/apply-nutritionist.js') }}"></script>
</body>
</html>
