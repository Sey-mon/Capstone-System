@extends('layouts.auth')
@section('content')
<div class="auth-container">
    <h2 style="color:#22c55e;">Contact Admin</h2>
    <p style="color:#6b7280;">If you need help, send a message to the system administrator.</p>
    <form method="POST" action="{{ route('contact.admin.send') }}">
        @csrf
        <div class="form-group">
            <label for="email">Your Email</label>
            <input type="email" name="email" id="email" placeholder="Enter your email" required>
            @error('email')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="message">Message</label>
            <textarea name="message" id="message" rows="4" placeholder="Type your message here..." required></textarea>
            @error('message')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit" class="btn" style="background:#22c55e;color:#fff;">Send Message</button>
    </form>
    <div class="extra-links" style="margin-top:1rem;">
        <a href="{{ route('login') }}" style="color:#22c55e;">← Back to Login</a>
    </div>
</div>
@endsection
