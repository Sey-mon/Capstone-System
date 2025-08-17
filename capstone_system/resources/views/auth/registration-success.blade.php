<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/nutritionist-wizard.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="success-container">
        <div class="success-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Application Submitted Successfully!</h2>
            <p><strong>Thank you for applying to join our team of nutritionists.</strong></p>
            <p>Your application has been submitted and is now under review by our admin team.</p>
            
            <div class="info-box" style="margin: 2rem 0; text-align: left;">
                <h4><i class="fas fa-info-circle me-2"></i>What happens next?</h4>
                <ul>
                    <li>Our admin team will review your credentials and professional ID</li>
                    <li>This process typically takes 2-3 business days</li>
                    <li>You'll receive an email notification once your application is reviewed</li>
                    <li>If approved, you'll be able to access your nutritionist dashboard</li>
                </ul>
            </div>

            <p><strong>Application ID:</strong> #{{ session('application_id', 'N/A') }}</p>
            <p class="text-muted">Please keep this ID for your records</p>
            
            <p>You will be redirected to the login page in <span class="countdown" id="countdown">10</span> seconds...</p>
            
            <div style="margin-top: 2rem;">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-1"></i> Go to Login Now
                </a>
                <a href="{{ route('register') }}" class="btn btn-outline" style="margin-left: 1rem;">
                    <i class="fas fa-home me-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = "{{ route('login') }}";
            }
        }, 1000);
        
        // Allow clicking anywhere (except buttons) to go to login immediately
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.btn')) {
                clearInterval(timer);
                window.location.href = "{{ route('login') }}";
            }
        });
    </script>
</body>
</html>
