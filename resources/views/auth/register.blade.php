@extends('layouts.auth')
@section('title', 'Customer Registration')
@section('card-title', 'Create Your Account')
@section('card-subtitle', 'Join TAJ Limited for orders & live tracking')

@section('card-icon')
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
    <circle cx="8.5" cy="7" r="4"/>
    <line x1="20" y1="8" x2="20" y2="14"/>
    <line x1="23" y1="11" x2="17" y2="11"/>
</svg>
@endsection

@section('content')
<form method="POST" action="{{ route('register.store') }}" id="registerForm">
    @csrf
    <div class="taj-field-group">
        {{-- Full Name --}}
        <div style="margin-bottom: 12px;">
            <label for="regName" style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Full Name</label>
            <div class="taj-input-box">
                <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <input type="text" name="name" id="regName" value="{{ old('name') }}" required autofocus placeholder="Your full name" class="taj-input">
            </div>
        </div>

        {{-- Email Address --}}
        <div style="margin-bottom: 12px;">
            <label for="regEmail" style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Email Address</label>
            <div class="taj-input-box">
                <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <input type="email" name="email" id="regEmail" value="{{ old('email') }}" required placeholder="name@example.com" class="taj-input">
            </div>
        </div>

        {{-- Phone Number --}}
        <div style="margin-bottom: 12px;">
            <label for="regPhone" style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Phone Number</label>
            <div class="taj-input-box">
                <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <input type="tel" name="phone" id="regPhone" value="{{ old('phone') }}" required placeholder="+880 1XXXXXXXXX" class="taj-input">
            </div>
        </div>

        {{-- Password --}}
        <div style="margin-bottom: 12px;">
            <label for="regPassword" style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Password</label>
            <div class="taj-input-box">
                <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" name="password" id="regPassword" required minlength="8" placeholder="At least 8 characters" class="taj-input">
            </div>
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="regPasswordConfirm" style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Confirm Password</label>
            <div class="taj-input-box">
                <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" name="password_confirmation" id="regPasswordConfirm" required minlength="8" placeholder="Confirm password" class="taj-input">
            </div>
        </div>
    </div>

    <button type="submit" class="taj-btn-signin" style="margin-top: 20px;">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
        </svg>
        <span>Create Account</span>
    </button>

    <div class="taj-create-row" style="margin-top: 18px;">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </div>
</form>
@endsection
