@extends('layouts.auth')
@section('title', 'Reset Password')
@section('card-title', 'Create New Password')
@section('card-subtitle', 'Minimum 8 characters with numbers and letters')

@section('card-icon')
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 2l-2 2m-1.5 1.5L12 11l4 4 5.5-5.5a2.121 2.121 0 0 0 0-3L19.5 4.5"/>
    <path d="M9 13l-4 4v4h4l4-4"/>
</svg>
@endsection

@section('content')
<form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
    @csrf
    <div class="taj-field-group">
        {{-- New Password --}}
        <div style="margin-bottom: 14px;">
            <label for="newPassword" style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">
                New Password
            </label>
            <div class="taj-input-box">
                <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" 
                       name="password" 
                       id="newPassword" 
                       required 
                       minlength="8" 
                       placeholder="At least 8 characters" 
                       class="taj-input">
            </div>
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="confirmPassword" style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">
                Confirm New Password
            </label>
            <div class="taj-input-box">
                <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" 
                       name="password_confirmation" 
                       id="confirmPassword" 
                       required 
                       minlength="8" 
                       placeholder="Confirm new password" 
                       class="taj-input">
            </div>
        </div>
    </div>

    <button type="submit" class="taj-btn-signin" id="resetPasswordSubmitBtn" style="margin-top: 20px;">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        <span>Reset Password & Sign In</span>
    </button>

    <div class="taj-create-row" style="margin-top: 18px;">
        Remembered your existing password? <a href="{{ route('login') }}">Sign in</a>
    </div>
</form>
@endsection
