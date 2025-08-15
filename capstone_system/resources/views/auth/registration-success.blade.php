<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="success-overlay"></div>
    <div class="success-message">
        <span class="success-icon">✅</span>
        <h2>Registration Complete!</h2>
        <p>Your parent account has been successfully created.</p>
        <p><strong>Please login with your credentials to access your account.</strong></p>
        <p>You will be redirected to the login page in <span class="countdown" id="countdown">5</span> seconds...</p>
        <p><small>You can also <a href="{{ route('login') }}" style="color: #e8f5e8; text-decoration: underline;">click here to login now</a></small></p>
    </div>

    <script>
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = "{{ route('login') }}";
            }
        }, 1000);
        
        // Allow clicking anywhere to go to login immediately
        document.addEventListener('click', (e) => {
            if (e.target.tagName !== 'A') {
                clearInterval(timer);
                window.location.href = "{{ route('login') }}";
            }
        });
    </script>
</body>
</html>
