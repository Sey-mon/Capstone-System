@extends('layouts.auth')
@section('content')
<div class="auth-container">
    <h2 style="color:#22c55e;">Forgot Password</h2>
    <p style="color:#6b7280;">Enter your email address and we'll send you a link to reset your password.</p>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="Enter your email" required autofocus>
            @error('email')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit" class="btn" style="background:#22c55e;color:#fff;">Send Reset Link</button>
    </form>
    <div class="extra-links" style="margin-top:1rem;">
        <a href="{{ route('login') }}" style="color:#22c55e;">← Back to Login</a>
    </div>
</div>
@endsection
