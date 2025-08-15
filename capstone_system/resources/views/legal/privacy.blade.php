<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Nutrition System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <style>
        .privacy-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        .privacy-content {
            line-height: 1.6;
            color: #333;
        }
        .privacy-content h1 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 30px;
        }
        .privacy-content h2 {
            color: #2e7d32;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .privacy-content p {
            margin-bottom: 15px;
        }
        .privacy-content ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }
        .privacy-content li {
            margin-bottom: 8px;
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
        .highlight-box {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="privacy-container">
        <div class="privacy-content">
            <h1>Privacy Policy</h1>
            
            <p><strong>Last Updated:</strong> {{ date('F d, Y') }}</p>
            
            <div class="highlight-box">
                <strong>🔒 Your Privacy Matters:</strong> We are committed to protecting your personal health information and respecting your privacy rights. This policy explains how we collect, use, and protect your data.
            </div>
            
            <h2>1. Information We Collect</h2>
            <p>We collect the following types of information to provide our nutrition services:</p>
            
            <h3>Personal Information:</h3>
            <ul>
                <li>Name (first, middle, last)</li>
                <li>Date of birth</li>
                <li>Sex/gender information</li>
                <li>Contact information (phone number, email, address)</li>
                <li>Emergency contact details</li>
            </ul>
            
            <h3>Health Information:</h3>
            <ul>
                <li>Nutritional assessment data</li>
                <li>Height, weight, and growth measurements</li>
                <li>Dietary preferences and restrictions</li>
                <li>Medical history relevant to nutrition</li>
                <li>Progress tracking information</li>
            </ul>
            
            <h3>Technical Information:</h3>
            <ul>
                <li>Login credentials (encrypted passwords)</li>
                <li>Platform usage data</li>
                <li>Device and browser information</li>
                <li>IP addresses for security purposes</li>
            </ul>
            
            <h2>2. How We Use Your Information</h2>
            <p>Your information is used exclusively for:</p>
            <ul>
                <li>Providing nutritional assessments and recommendations</li>
                <li>Tracking health progress and development</li>
                <li>Facilitating communication between parents and nutritionists</li>
                <li>Improving our services and platform functionality</li>
                <li>Ensuring platform security and preventing unauthorized access</li>
                <li>Complying with legal and regulatory requirements</li>
            </ul>
            
            <h2>3. Information Sharing and Disclosure</h2>
            <p>We maintain strict confidentiality and only share your information in these limited circumstances:</p>
            
            <h3>Authorized Healthcare Providers:</h3>
            <ul>
                <li>Licensed nutritionists providing your care</li>
                <li>Healthcare professionals involved in your treatment</li>
                <li>Medical staff requiring access for continuity of care</li>
            </ul>
            
            <h3>Legal Requirements:</h3>
            <ul>
                <li>When required by Philippine law or court orders</li>
                <li>To protect the safety of individuals or the public</li>
                <li>For regulatory compliance and reporting</li>
            </ul>
            
            <h3>We Never Share Information For:</h3>
            <ul>
                <li>Marketing or advertising purposes</li>
                <li>Commercial gain or profit</li>
                <li>Non-healthcare related activities</li>
                <li>Third-party data brokers</li>
            </ul>
            
            <h2>4. Data Security Measures</h2>
            <p>We implement comprehensive security measures to protect your information:</p>
            <ul>
                <li><strong>Encryption:</strong> All data is encrypted in transit and at rest</li>
                <li><strong>Access Controls:</strong> Role-based access with multi-factor authentication</li>
                <li><strong>Regular Audits:</strong> Security assessments and vulnerability testing</li>
                <li><strong>Staff Training:</strong> Privacy and security training for all personnel</li>
                <li><strong>Secure Infrastructure:</strong> Protected servers and network security</li>
                <li><strong>Backup Systems:</strong> Secure data backup and recovery procedures</li>
            </ul>
            
            <h2>5. Your Privacy Rights</h2>
            <p>Under Philippine law and our commitment to privacy, you have the right to:</p>
            <ul>
                <li><strong>Access:</strong> View and obtain copies of your personal information</li>
                <li><strong>Correction:</strong> Request correction of inaccurate or incomplete data</li>
                <li><strong>Deletion:</strong> Request deletion of your personal information (subject to legal requirements)</li>
                <li><strong>Portability:</strong> Obtain your data in a portable format</li>
                <li><strong>Restriction:</strong> Request limitations on how your data is processed</li>
                <li><strong>Objection:</strong> Object to certain types of data processing</li>
            </ul>
            
            <h2>6. Data Retention</h2>
            <p>We retain your information according to these principles:</p>
            <ul>
                <li>Active accounts: Data retained while account is active</li>
                <li>Inactive accounts: Data retained for 3 years after last activity</li>
                <li>Medical records: Retained according to Philippine healthcare regulations</li>
                <li>Legal requirements: Some data may be retained longer for compliance</li>
                <li>Upon request: Data can be deleted earlier upon user request (where legally permissible)</li>
            </ul>
            
            <h2>7. Children's Privacy</h2>
            <p>Special protection for children's information:</p>
            <ul>
                <li>Parental consent required for children under 18</li>
                <li>Additional security measures for pediatric records</li>
                <li>Limited access to child information</li>
                <li>Enhanced data protection protocols</li>
                <li>Regular review of children's data handling practices</li>
            </ul>
            
            <h2>8. International Data Transfers</h2>
            <p>Your data is primarily stored and processed in the Philippines. Any international transfers will:</p>
            <ul>
                <li>Only occur when necessary for service provision</li>
                <li>Be subject to adequate privacy protections</li>
                <li>Require your explicit consent</li>
                <li>Comply with Philippine data protection laws</li>
            </ul>
            
            <h2>9. Changes to This Privacy Policy</h2>
            <p>We may update this privacy policy to reflect:</p>
            <ul>
                <li>Changes in laws or regulations</li>
                <li>Improvements to our services</li>
                <li>Enhanced security measures</li>
                <li>User feedback and suggestions</li>
            </ul>
            
            <p>You will be notified of significant changes via email or platform notification.</p>
            
            <h2>10. Contact Us</h2>
            <p>For privacy-related questions or to exercise your rights, contact our Data Protection Officer:</p>
            <ul>
                <li><strong>Email:</strong> privacy@nutritionsystem.ph</li>
                <li><strong>Phone:</strong> +63 (XXX) XXX-XXXX</li>
                <li><strong>Address:</strong> [Your Address]</li>
                <li><strong>Office Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM (Philippine Time)</li>
            </ul>
            
            <h2>11. Compliance</h2>
            <p>This privacy policy complies with:</p>
            <ul>
                <li>Data Privacy Act of 2012 (Republic Act No. 10173)</li>
                <li>Implementing Rules and Regulations of the DPA</li>
                <li>National Privacy Commission guidelines</li>
                <li>Philippine healthcare privacy regulations</li>
                <li>International privacy best practices</li>
            </ul>
        </div>
        
        <div class="back-link">
            <a href="javascript:history.back()">← Back to Registration</a>
        </div>
    </div>
</body>
</html>
