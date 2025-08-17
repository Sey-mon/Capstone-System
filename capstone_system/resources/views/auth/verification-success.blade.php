@extends('layouts.auth')

@section('title', 'Email Verified Successfully')

@section('content')
<div class="text-center">
    <div class="success-animation">
        <div class="checkmark">
            <div class="checkmark-circle"></div>
            <div class="checkmark-stem"></div>
            <div class="checkmark-kick"></div>
        </div>
    </div>

    <h2 class="auth-title" style="color: #2e7d32;">🎉 Email Verified Successfully!</h2>
    <p class="auth-subtitle">
        Your email address has been verified. You can now access all features of your account.
    </p>

    <div class="success-info">
        <div class="verified-email-box">
            <h5>✅ Verified Email</h5>
            <p class="email-display">{{ Auth::user()->email }}</p>
            <p class="text-muted small">Verified on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <div class="next-steps">
            <h5>🚀 What's Next?</h5>
            <ul class="next-steps-list">
                <li>Complete your profile information</li>
                <li>Explore the dashboard features</li>
                <li>Start using the monitoring system</li>
            </ul>
        </div>
    </div>

    <div class="action-buttons">
        <a href="{{ Auth::user()->role->role_name === 'Admin' ? route('admin.dashboard') : (Auth::user()->role->role_name === 'Nutritionist' ? route('nutritionist.dashboard') : route('parent.dashboard')) }}" 
           class="btn btn-success btn-lg" style="text-decoration: none;">
            📊 Go to Dashboard
        </a>
        <a href="{{ route('login') }}" class="btn btn-outline-success btn-lg" style="text-decoration: none;">
            🔐 Login Page
        </a>
    </div>
</div>

<style>
.success-animation {
    margin: 20px 0 30px 0;
}

.checkmark {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    margin: 0 auto;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    box-shadow: 0 4px 20px rgba(76, 175, 80, 0.3);
    animation: checkmark-bounce 0.6s ease-in-out;
}

@keyframes checkmark-bounce {
    0% { transform: scale(0); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.checkmark-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    position: absolute;
    top: 0;
    left: 0;
    border: 3px solid #4CAF50;
    background: linear-gradient(135deg, #4CAF50, #45a049);
}

.checkmark-stem {
    position: absolute;
    width: 20px;
    height: 3px;
    background-color: white;
    left: 28px;
    top: 38px;
    transform: rotate(45deg);
    animation: checkmark-stem 0.3s ease-in-out 0.3s both;
}

.checkmark-kick {
    position: absolute;
    width: 12px;
    height: 3px;
    background-color: white;
    left: 23px;
    top: 42px;
    transform: rotate(-45deg);
    animation: checkmark-kick 0.3s ease-in-out 0.4s both;
}

@keyframes checkmark-stem {
    0% { width: 0; }
    100% { width: 20px; }
}

@keyframes checkmark-kick {
    0% { width: 0; }
    100% { width: 12px; }
}

.success-info {
    margin: 30px 0;
}

.verified-email-box {
    background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
    border: 2px solid #4CAF50;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.1);
}

.verified-email-box h5 {
    color: #2e7d32;
    margin-bottom: 15px;
    font-weight: 600;
}

.email-display {
    font-size: 1.2em;
    font-weight: bold;
    color: #2e7d32;
    margin-bottom: 10px;
}

.next-steps {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 25px;
    text-align: left;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.next-steps h5 {
    color: #2e7d32;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 600;
}

.next-steps-list {
    list-style: none;
    padding-left: 0;
}

.next-steps-list li {
    padding: 12px 0;
    border-bottom: 1px solid #e8f5e9;
    color: #424242;
    font-weight: 500;
}

.next-steps-list li:before {
    content: "✓ ";
    color: #4CAF50;
    font-weight: bold;
    margin-right: 10px;
    font-size: 1.1em;
}

.next-steps-list li:last-child {
    border-bottom: none;
}

.action-buttons {
    margin-top: 30px;
}

.btn-success {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
    margin: 0 10px;
}

.btn-success:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
    color: white;
    text-decoration: none;
}

.btn-outline-success {
    background-color: transparent;
    color: #4CAF50;
    border: 2px solid #4CAF50;
    padding: 15px 30px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin: 0 10px;
}

.btn-outline-success:hover {
    background-color: #4CAF50;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
    text-decoration: none;
}

.btn-lg {
    font-size: 18px;
    padding: 15px 30px;
}

/* Override auth layout for white/green theme */
.auth-container {
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 50%, #ffffff 100%);
}

.auth-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(76, 175, 80, 0.1);
    box-shadow: 0 10px 40px rgba(76, 175, 80, 0.1);
}

@media (max-width: 576px) {
    .action-buttons .btn {
        display: block;
        margin: 10px 0;
        width: 100%;
    }
    
    .checkmark {
        width: 60px;
        height: 60px;
    }
    
    .checkmark-circle {
        width: 60px;
        height: 60px;
    }
    
    .checkmark-stem {
        width: 15px;
        left: 21px;
        top: 28px;
    }
    
    .checkmark-kick {
        width: 9px;
        left: 17px;
        top: 32px;
    }
}
</style>
@endsection
