<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Approved - BMI Malnutrition Monitoring System</title>
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
        .approval-badge {
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
        .security-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .security-note h4 {
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

        <div class="success-icon">✅</div>

        <div style="text-align: center;">
            <div class="approval-badge">
                Account Approved & Activated
            </div>
        </div>

        <div class="welcome-message">
            <h2>Congratulations, {{ $userName }}! 🎉</h2>
            <p>We are pleased to inform you that your nutritionist account application has been <strong>approved</strong> and your account has been <strong>activated</strong>.</p>
            <p>You can now access the BMI Malnutrition Monitoring System using your registered credentials and begin contributing to improving child nutrition and health outcomes in your community.</p>
        </div>

        <div class="info-box">
            <h3>🔑 Account Information</h3>
            <p><strong>Email Address:</strong> {{ $userEmail }}</p>
            <p><strong>Status:</strong> <span style="color: #4CAF50; font-weight: bold;">Active</span></p>
            <p>Use the password you created during registration to log in.</p>
        </div>

        <div class="next-steps">
            <h4>📋 Next Steps:</h4>
            <ol>
                <li>Click the "Login Now" button below or visit the login page</li>
                <li>Enter your registered email and password</li>
                <li>Complete your profile information if needed</li>
                <li>Start using the system to manage patient assessments and nutrition programs</li>
            </ol>
        </div>

        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">Login Now</a>
        </div>

        <div class="info-box">
            <h4>📌 What You Can Do:</h4>
            <ul>
                <li>Manage patient records and assessments</li>
                <li>Create and track nutrition plans</li>
                <li>Monitor feeding programs</li>
                <li>Access AI-powered nutrition recommendations</li>
                <li>Generate reports and analytics</li>
                <li>Collaborate with other healthcare professionals</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>Need Help?</strong></p>
            <p>If you have any questions or need assistance, please contact the system administrator or submit a support ticket through the system.</p>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
            <p>This is an automated message from the BMI Malnutrition Monitoring System.</p>
            <p>© {{ date('Y') }} BMI Malnutrition Monitoring System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
