<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <style>
        .terms-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        .terms-content {
            line-height: 1.8;
            color: #333;
        }
        .terms-content h1 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e8f5e8;
        }
        .terms-content h2 {
            color: #2e7d32;
            margin-top: 40px;
            margin-bottom: 20px;
            padding-top: 15px;
        }
        .terms-content p {
            margin-bottom: 20px;
            text-align: justify;
        }
        .terms-content ul {
            margin-bottom: 25px;
            padding-left: 25px;
        }
        .terms-content li {
            margin-bottom: 12px;
            padding-left: 5px;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="terms-container">
        <div class="terms-content">
            <h1>Terms and Conditions</h1>
            
            <p><strong>Last Updated:</strong> {{ date('F d, Y') }}</p>
            
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing and using the Nutrition System platform, you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.</p>
            
            <h2>2. Description of Service</h2>
            <p>The Nutrition System is a healthcare platform designed to:</p>
            <ul>
                <li>Track nutritional assessments of children</li>
                <li>Provide nutritional guidance and recommendations</li>
                <li>Connect parents with nutritionists</li>
                <li>Maintain health records and progress tracking</li>
            </ul>
            
            <h2>3. User Responsibilities</h2>
            <p>As a user of our platform, you agree to:</p>
            <ul>
                <li>Provide accurate and truthful information</li>
                <li>Keep your account credentials secure</li>
                <li>Use the platform only for its intended purposes</li>
                <li>Respect the privacy and confidentiality of other users</li>
                <li>Follow all applicable laws and regulations</li>
            </ul>
            
            <h2>4. Healthcare Disclaimer</h2>
            <p>Important healthcare information:</p>
            <ul>
                <li>This platform provides nutritional guidance but does not replace professional medical advice</li>
                <li>Always consult with healthcare professionals for medical decisions</li>
                <li>In case of medical emergencies, contact emergency services immediately</li>
                <li>The platform is designed to support, not replace, the relationship between patient and healthcare provider</li>
            </ul>
            
            <h2>5. Privacy and Data Protection</h2>
            <p>We are committed to protecting your privacy:</p>
            <ul>
                <li>Personal health information is handled in accordance with applicable privacy laws</li>
                <li>Data is collected only for the purposes of providing nutritional services</li>
                <li>Information is shared only with authorized healthcare professionals</li>
                <li>Users have the right to access, correct, and delete their personal information</li>
            </ul>
            
            <h2>6. Account Security</h2>
            <p>To maintain account security:</p>
            <ul>
                <li>Create strong passwords with at least 8 characters</li>
                <li>Do not share your login credentials with others</li>
                <li>Log out after each session, especially on shared devices</li>
                <li>Report any suspected unauthorized access immediately</li>
            </ul>
            
            <h2>7. Prohibited Uses</h2>
            <p>You may not use this platform to:</p>
            <ul>
                <li>Violate any local, national, or international laws</li>
                <li>Share false or misleading health information</li>
                <li>Access other users' accounts without authorization</li>
                <li>Interfere with the platform's security or functionality</li>
                <li>Use the platform for commercial purposes without permission</li>
            </ul>
            
            <h2>8. Limitation of Liability</h2>
            <p>The Nutrition System platform is provided "as is" without warranties. We are not liable for:</p>
            <ul>
                <li>Decisions made based on platform information</li>
                <li>Technical issues or service interruptions</li>
                <li>Loss of data due to technical problems</li>
                <li>Any indirect or consequential damages</li>
            </ul>
            
            <h2>9. Changes to Terms</h2>
            <p>We reserve the right to modify these terms at any time. Users will be notified of significant changes, and continued use of the platform constitutes acceptance of updated terms.</p>
            
            <h2>10. Contact Information</h2>
            <p>For questions about these terms or our services, please contact:</p>
            <ul>
                <li>Email: support@nutritionsystem.ph</li>
                <li>Phone: +63 (XXX) XXX-XXXX</li>
                <li>Address: [Your Address]</li>
            </ul>
            
            <h2>11. Governing Law</h2>
            <p>These terms are governed by the laws of the Republic of the Philippines. Any disputes will be resolved in Philippine courts.</p>
        </div>
        
        <div class="back-link">
            <a href="javascript:history.back()">← Back to Registration</a>
        </div>
    </div>
</body>
</html>
