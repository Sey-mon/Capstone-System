<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/register-option.css') }}">
</head>
<body>
    <div class="register-container">
        <h2>Join Our Nutrition System</h2>
        <p>Choose your account type to get started with personalized nutrition care</p>
        
        <div class="registration-options">
            <a href="{{ route('register.parent') }}" class="option-card parent-option">
                <div class="role-badge">Free Account</div>
                <h3>👨‍👩‍👧‍👦 Parent</h3>
                <div class="benefits">
                    <p>✅ Instant access</p>
                    <p>✅ Track children's health</p>
                    <p>✅ Connect with nutritionists</p>I. 
                    <p>✅ Schedule assessments</p>
                </div>
                <span class="btn primary">Create Account Now</span>
            </a>
            
            <a href="{{ route('apply.nutritionist') }}" class="option-card nutritionist-option">
                <div class="role-badge professional">Professional Application</div>
                <h3>🥗 Nutritionist</h3>
                <div class="benefits">
                    <p>⏳ Requires admin approval</p>
                    <p>📋 License verification needed</p>
                    <p>💼 Professional dashboard</p>
                    <p>👥 Manage patient assessments</p>
                </div>
                <span class="btn secondary">Apply Now</span>
            </a>
        </div>
        
        <div class="back-link">
            <a href="{{ route('login') }}">← Back to Login</a>
        </div>
    </div>

    <script src="{{ asset('js/register-options.js') }}"></script>
</body>
</html>