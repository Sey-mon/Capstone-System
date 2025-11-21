<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/terms.css') }}">
</head>
<body>
    <div class="terms-container">
        <div class="terms-content">
            <h1>Terms and Conditions</h1>
            
            <p><strong>Last Updated:</strong> {{ date('F d, Y') }}</p>
            
            <h2>1. Agreement to Terms</h2>
            <p>By using the Nutrition System, you agree to these terms. If you disagree, please do not use our services.</p>
            
            <h2>2. Our Service</h2>
            <p>We provide a nutrition tracking platform that helps monitor children's nutritional health, connects parents with nutritionists, and maintains health records.</p>
            
            <h2>3. Your Responsibilities</h2>
            <p>You agree to:</p>
            <ul>
                <li>Provide accurate information</li>
                <li>Keep your password secure and not share it with others</li>
                <li>Use the platform for its intended purpose only</li>
                <li>Report any security concerns immediately</li>
            </ul>
            
            <h2>4. Medical Disclaimer</h2>
            <p><strong>Important:</strong> This platform provides nutritional information and guidance only. It does not replace professional medical advice. Always consult healthcare professionals for medical decisions. In emergencies, call emergency services immediately.</p>
            
            <h2>5. What You Cannot Do</h2>
            <p>You may not:</p>
            <ul>
                <li>Provide false health information</li>
                <li>Access other users' accounts</li>
                <li>Attempt to disrupt or harm the platform</li>
                <li>Use the service for unauthorized commercial purposes</li>
            </ul>
            
            <h2>6. Service Availability</h2>
            <p>We strive to keep the platform available but cannot guarantee uninterrupted access. We are not responsible for technical issues, service interruptions, or decisions made based on platform information.</p>
            
            <h2>7. Changes to Terms</h2>
            <p>We may update these terms as needed. Significant changes will be communicated to users. Continued use after changes means you accept the updated terms.</p>
            
            <h2>8. Contact Us</h2>
            <p>For questions or concerns:</p>
            <ul>
                <li><strong>Email:</strong> support@nutritionsystem.ph</li>
                <li><strong>Phone:</strong> +63 (XXX) XXX-XXXX</li>
            </ul>
            
            <h2>9. Governing Law</h2>
            <p>These terms are governed by Philippine law. Disputes will be resolved in accordance with the laws of the Republic of the Philippines.</p>
        </div>
        
        <div class="back-link">
            <a href="javascript:history.back()">← Back to Registration</a>
        </div>
    </div>
</body>
</html>
