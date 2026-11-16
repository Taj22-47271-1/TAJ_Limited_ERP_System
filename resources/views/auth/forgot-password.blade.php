@extends('layouts.auth')
@section('title', 'Forgot Password')
@section('card-title', 'Forgot your password?')
@section('card-subtitle', 'Enter your email to receive a 6-digit OTP')

@section('card-icon')
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    <circle cx="12" cy="16" r="1.5"/>
</svg>
@endsection

@section('content')
<form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
    @csrf
    <div class="taj-field-group">
        <label for="forgotEmail" style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">
            Registered Email Address
        </label>
        <div class="taj-input-box">
            <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            <input type="email" 
                   name="email" 
                   id="forgotEmail" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus 
                   placeholder="e.g. name@example.com" 
                   class="taj-input">
        </div>
    </div>

    <div style="font-size: 12px; color: #64748b; line-height: 1.5; margin: 12px 0 20px;">
        💡 A 6-digit verification code will be sent to this email address. The code remains valid for 10 minutes.
    </div>

    <button type="submit" class="taj-btn-signin" id="sendOtpSubmitBtn">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"/>
            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
        <span>Send Verification OTP</span>
    </button>

    <div class="taj-create-row" style="margin-top: 22px;">
        Remembered your password? <a href="{{ route('login') }}">Back to sign in</a>
    </div>
</form>
@endsection
