<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to BMI Malnutrition Monitoring System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .welcome-message {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .info-box {
            background-color: #e8f5e8;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .verification-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">🏥 BMI Malnutrition Monitoring System</div>
            <p>Healthcare Excellence Through Technology</p>
        </div>

        <div class="welcome-message">
            <h2>Welcome, {{ $userName }}! 👋</h2>
            <p>Thank you for registering with our BMI Malnutrition Monitoring System. We're excited to have you join our community dedicated to improving child nutrition and health outcomes.</p>
        </div>

        <div class="info-box">
            <h3>📧 Your Account Details:</h3>
            <p><strong>Email:</strong> {{ $userEmail }}</p>
            <p><strong>Registration Date:</strong> {{ date('F j, Y') }}</p>
        </div>

        <div class="verification-note">
            <h4>📋 Next Steps:</h4>
            <p><strong>Important:</strong> Please verify your email address by clicking the verification link we've sent to your email. This step is required to activate your account and ensure secure access.</p>
            
            <p>If you don't see the verification email in your inbox, please check your spam/junk folder.</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="button">Go to Login Page</a>
        </div>

        <div class="info-box">
            <h4>🔐 Account Security Tips:</h4>
            <ul>
                <li>Keep your password secure and don't share it with others</li>
                <li>Always log out from shared computers</li>
                <li>Contact support if you notice any suspicious activity</li>
            </ul>
        </div>

        <div class="footer">
            <p>📞 Need help? Contact our support team</p>
            <p>Email: veriiemail22@gmail.com</p>
            <p>&copy; {{ date('Y') }} BMI Malnutrition Monitoring System. All rights reserved.</p>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
            <p style="font-size: 12px; color: #999;">
                This email was sent automatically. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
