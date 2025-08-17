<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CSRF Test Page</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px;
            background-color: #f5f5f5;
        }
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .status { padding: 10px; border-radius: 5px; margin: 10px 0; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            font-size: 16px;
        }
        button:hover { background-color: #45a049; }
        button:disabled { background-color: #cccccc; cursor: not-allowed; }
        pre { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto;
            border: 1px solid #e9ecef;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🔒 CSRF Token Test Page</h1>
        <p>This page helps diagnose CSRF token issues with the registration system.</p>
        
        <div id="status" class="info">
            <strong>System Status:</strong> Ready for testing
        </div>

        <h2>📊 Current Configuration</h2>
        <pre><strong>CSRF Token:</strong> {{ csrf_token() }}
<strong>Session Driver:</strong> {{ config('session.driver') }}
<strong>Session Lifetime:</strong> {{ config('session.lifetime') }} minutes
<strong>Session Encrypt:</strong> {{ config('session.encrypt') ? 'Yes' : 'No' }}
<strong>App Environment:</strong> {{ config('app.env') }}
<strong>App Debug:</strong> {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
<strong>Current Time:</strong> {{ now()->format('Y-m-d H:i:s T') }}</pre>

        <h2>🧪 CSRF Token Tests</h2>
        
        <div class="form-group">
            <button onclick="testCSRFRefresh()">🔄 Test CSRF Token Refresh</button>
            <button onclick="testFormSubmission()">📝 Test Form Submission</button>
            <button onclick="checkSessionStatus()">⏱️ Check Session Status</button>
        </div>

        <h2>📋 Test Registration Form</h2>
        <form id="testForm" method="POST" action="{{ route('register.parent.post') }}">
            @csrf
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="Test" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="User" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="test{{ rand(1000,9999) }}@example.com" required>
            </div>
            <div class="form-group">
                <label for="birth_date">Birth Date:</label>
                <input type="date" id="birth_date" name="birth_date" value="1990-01-01" required>
            </div>
            <div class="form-group">
                <label for="sex">Gender:</label>
                <select id="sex" name="sex" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="Test Address 123" required>
            </div>
            <div class="form-group">
                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" value="0912-345-6789" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="TestPass123!" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password:</label>
                <input type="password" id="password_confirmation" name="password_confirmation" value="TestPass123!" required>
            </div>
            
            <button type="submit">🚀 Submit Test Registration</button>
            <button type="button" onclick="validateForm()">✅ Validate Form</button>
        </form>

        <h2>📝 Debug Log</h2>
        <pre id="debugLog">Debug information will appear here...</pre>

        <a href="{{ route('register.parent') }}" class="back-link">← Back to Registration</a>
    </div>

    <script>
        // Debug logging function
        function logDebug(message) {
            const log = document.getElementById('debugLog');
            const timestamp = new Date().toLocaleTimeString();
            log.textContent += `[${timestamp}] ${message}\n`;
            log.scrollTop = log.scrollHeight;
            console.log(message);
        }

        // Test CSRF token refresh
        async function testCSRFRefresh() {
            logDebug('Testing CSRF token refresh...');
            updateStatus('Testing CSRF token refresh...', 'info');
            
            try {
                const response = await fetch('/csrf-token', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    logDebug(`✅ CSRF token refreshed successfully: ${data.csrf_token.substring(0, 20)}...`);
                    updateStatus('✅ CSRF token refresh successful!', 'success');
                    
                    // Update the form token
                    const csrfInput = document.querySelector('input[name="_token"]');
                    if (csrfInput) {
                        csrfInput.value = data.csrf_token;
                        logDebug('Form CSRF token updated');
                    }
                } else {
                    throw new Error(`HTTP ${response.status}`);
                }
            } catch (error) {
                logDebug(`❌ CSRF token refresh failed: ${error.message}`);
                updateStatus('❌ CSRF token refresh failed', 'error');
            }
        }

        // Test form submission (without actually submitting)
        function testFormSubmission() {
            logDebug('Testing form submission validation...');
            updateStatus('Validating form submission...', 'info');
            
            const form = document.getElementById('testForm');
            const formData = new FormData(form);
            
            // Check required fields
            const requiredFields = ['first_name', 'last_name', 'email', 'password'];
            const missingFields = [];
            
            requiredFields.forEach(field => {
                const value = formData.get(field);
                if (!value || !value.trim()) {
                    missingFields.push(field);
                }
            });
            
            // Check CSRF token
            const csrfToken = formData.get('_token');
            
            if (missingFields.length > 0) {
                logDebug(`❌ Missing required fields: ${missingFields.join(', ')}`);
                updateStatus('❌ Form validation failed - missing fields', 'error');
            } else if (!csrfToken) {
                logDebug('❌ CSRF token missing');
                updateStatus('❌ CSRF token missing', 'error');
            } else {
                logDebug(`✅ Form validation passed. CSRF token: ${csrfToken.substring(0, 20)}...`);
                updateStatus('✅ Form validation passed!', 'success');
            }
        }

        // Check session status
        async function checkSessionStatus() {
            logDebug('Checking session status...');
            updateStatus('Checking session status...', 'info');
            
            try {
                const response = await fetch('/csrf-token');
                if (response.ok) {
                    logDebug('✅ Session is active');
                    updateStatus('✅ Session is active', 'success');
                } else {
                    logDebug('❌ Session may have expired');
                    updateStatus('❌ Session issues detected', 'error');
                }
            } catch (error) {
                logDebug(`❌ Session check failed: ${error.message}`);
                updateStatus('❌ Session check failed', 'error');
            }
        }

        // Validate form before submission
        function validateForm() {
            logDebug('Running form validation...');
            testFormSubmission();
        }

        // Update status display
        function updateStatus(message, type) {
            const statusDiv = document.getElementById('status');
            statusDiv.className = `status ${type}`;
            statusDiv.innerHTML = `<strong>Status:</strong> ${message}`;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            logDebug('CSRF Test Page loaded successfully');
            logDebug(`Initial CSRF token: {{ csrf_token() }}`);
            
            // Add form submission handler for debugging
            document.getElementById('testForm').addEventListener('submit', function(e) {
                logDebug('🚀 Form submission started');
                logDebug(`Form action: ${this.action}`);
                logDebug(`Form method: ${this.method}`);
                
                const csrfToken = document.querySelector('input[name="_token"]');
                if (csrfToken) {
                    logDebug(`CSRF token: ${csrfToken.value.substring(0, 20)}...`);
                } else {
                    logDebug('❌ No CSRF token found!');
                }
                
                updateStatus('Form submitted! Check browser network tab for response...', 'info');
            });
        });
    </script>
</body>
</html>
