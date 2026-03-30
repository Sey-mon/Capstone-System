<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
    <style>
        body { background: #fff; color: #222; font-family: Arial, sans-serif; }
        .container { max-width: 480px; margin: 2rem auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #e5e7eb; padding: 2rem; }
        h2 { color: #22c55e; margin-bottom: 1rem; }
        .code-box { background: #f0fdf4; border: 2px solid #22c55e; border-radius: 8px; padding: 1.5rem; text-align: center; margin: 1.5rem 0; }
        .code { font-size: 2rem; font-weight: bold; color: #22c55e; letter-spacing: 0.5rem; font-family: 'Courier New', monospace; }
        .expires { font-size: 0.875rem; color: #666; margin-top: 0.5rem; }
        p { color: #444; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>Hello {{ $user->name ?? $user->email }},</p>
        <p>We received a request to reset your password. Use the verification code below to set a new password:</p>
        
        <div class="code-box">
            <div class="code">{{ $code }}</div>
            <div class="expires">This code expires in 15 minutes</div>
        </div>
        
        <p>Enter this code on the password reset page to continue.</p>
        <p>If you did not request a password reset, you can ignore this email.</p>
        <p style="color:#22c55e; margin-top:2rem;">Nutrition System</p>
    </div>
</body>
</html>
