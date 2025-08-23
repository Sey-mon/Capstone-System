<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
    <style>
        body { background: #fff; color: #222; font-family: Arial, sans-serif; }
        .container { max-width: 480px; margin: 2rem auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #e5e7eb; padding: 2rem; }
        h2 { color: #22c55e; margin-bottom: 1rem; }
        .btn { display: inline-block; background: #22c55e; color: #fff; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; font-weight: bold; margin-top: 1.5rem; }
        p { color: #444; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>Hello {{ $user->name ?? $user->email }},</p>
        <p>We received a request to reset your password. Click the button below to set a new password:</p>
        <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
        <p>If you did not request a password reset, you can ignore this email.</p>
        <p style="color:#22c55e; margin-top:2rem;">Nutrition System</p>
    </div>
</body>
</html>
