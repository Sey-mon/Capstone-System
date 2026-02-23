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
        .welcome-badge {
            background-color: #4CAF50;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            display: inline-block;
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
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
        .info-box h3, .info-box h4 {
            margin-top: 0;
            color: #2e7d32;
        }
        .credentials-box {
            background-color: #fff9e6;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .credentials-box h3 {
            color: #ff6f00;
            margin-top: 0;
        }
        .credential-item {
            margin: 10px 0;
            padding: 10px;
            background-color: white;
            border-radius: 5px;
        }
        .credential-label {
            font-weight: bold;
            color: #666;
            display: block;
            margin-bottom: 5px;
        }
        .credential-value {
            font-size: 16px;
            color: #333;
            font-family: 'Courier New', monospace;
            background-color: #f5f5f5;
            padding: 8px;
            border-radius: 4px;
            display: inline-block;
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
        .button:hover {
            background-color: #45a049;
        }
        .verification-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .verification-note h4 {
            margin-top: 0;
            color: #ff6f00;
        }
        .next-steps {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
        }
        .next-steps h4 {
            margin-top: 0;
            color: #1976d2;
        }
        .next-steps ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin: 8px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .success-icon {
            font-size: 60px;
            text-align: center;
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

        <div class="success-icon">🎉</div>

        <div style="text-align: center;">
            <div class="welcome-badge">
                Registration Successful
            </div>
        </div>

        <div class="welcome-message">
            <h2>Welcome, {{ $userName }}! 👋</h2>
            <p>Thank you for registering with our BMI Malnutrition Monitoring System. We're excited to have you join our community dedicated to improving child nutrition and health outcomes.</p>
            <p>Your account has been successfully created. Please follow the steps below to get started.</p>
        </div>

        <div class="info-box">
            <h3>🔑 Account Information</h3>
            <p><strong>Email Address:</strong> {{ $userEmail }}</p>
            <p><strong>Registration Date:</strong> {{ date('F j, Y') }}</p>
            <p><strong>Status:</strong> <span style="color: #ff9800; font-weight: bold;">Pending Email Verification</span></p>
        </div>

        <div class="verification-note">
            <h4>⚠️ Email Verification Required</h4>
            <p><strong>Important:</strong> Please verify your email address by clicking the verification link we've sent to your inbox. This step is required to activate your account and ensure secure access.</p>
            <p>If you don't see the verification email in your inbox, please check your spam/junk folder.</p>
        </div>

        <div class="next-steps">
            <h4>📋 Next Steps:</h4>
            <ol>
                <li>Check your inbox for the email verification link</li>
                <li>Click the verification link to activate your account</li>
                <li>Return to the login page and sign in with your credentials</li>
                <li>Start monitoring your children's nutrition and health progress</li>
            </ol>
        </div>

        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="button">Go to Login Page</a>
        </div>

        <div class="info-box">
            <h4>📌 What You Can Do as a Parent:</h4>
            <ul>
                <li>View your children's nutrition assessments and health records</li>
                <li>Access personalized meal plans created by nutritionists</li>
                <li>Monitor your children's growth and BMI progress</li>
                <li>Stay updated with feeding program schedules</li>
                <li>Communicate with assigned nutritionists</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>Need Help?</strong></p>
            <p>If you have any questions or need assistance, please contact our support team.</p>
            <p>Email: veriiemail22@gmail.com</p>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
            <p>This is an automated message from the BMI Malnutrition Monitoring System.</p>
            <p>&copy; {{ date('Y') }} BMI Malnutrition Monitoring System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
