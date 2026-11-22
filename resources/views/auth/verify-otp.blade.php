@extends('layouts.auth')
@section('title', 'Verify OTP')
@section('card-title', 'Verify Your Identity')
@section('card-subtitle', 'Enter the 6-digit code sent to your email')

@section('card-icon')
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
    <polyline points="9 12 11 14 15 10"/>
</svg>
@endsection

@section('content')
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 20px; font-size: 13px; color: #475569;">
    A 6-digit verification code has been dispatched to: <strong style="color: #0f172a; word-break: break-all;">{{ $email }}</strong>
</div>

<form method="POST" action="{{ route('password.otp.verify') }}" id="verifyOtpForm">
    @csrf
    <div class="taj-field-group">
        <label for="otpInput" style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">
            6-Digit Verification Code
        </label>
        <div class="taj-input-box">
            <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="text" 
                   name="otp" 
                   id="otpInput" 
                   inputmode="numeric" 
                   pattern="[0-9]*" 
                   maxlength="6" 
                   required 
                   autofocus 
                   placeholder="000000" 
                   autocomplete="one-time-code"
                   style="letter-spacing: 6px; font-size: 20px; font-weight: 800; text-align: center;" 
                   class="taj-input">
        </div>
    </div>

    <button type="submit" class="taj-btn-signin" id="verifyOtpSubmitBtn" style="margin-top: 20px;">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        <span>Verify OTP & Proceed</span>
    </button>
</form>

<form method="POST" action="{{ route('password.email') }}" style="margin-top: 18px; text-align: center;">
    @csrf
    <input type="hidden" name="email" value="{{ $email }}">
    <button type="submit" style="background: none; border: none; color: #2563eb; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline; padding: 4px 8px;">
        Didn't receive code? Resend OTP
    </button>
</form>

<div class="taj-create-row" style="margin-top: 14px;">
    Entered wrong email? <a href="{{ route('password.request') }}">Change email</a>
</div>
@endsection
