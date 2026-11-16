@extends('layouts.auth')
@section('title','Verify OTP')
@section('content')
<span class="eyebrow dark">VERIFY IDENTITY</span>
<h2>Enter your OTP</h2>
<p class="muted">A 6-digit verification code was sent to <strong>{{ $email }}</strong>.</p>

<form method="POST" action="{{ route('password.otp.verify') }}" class="stack-form">
    @csrf
    <label>
        6-digit OTP
        <input class="otp-input" inputmode="numeric" maxlength="6" name="otp" required autofocus placeholder="000000" autocomplete="one-time-code">
    </label>
    <button class="btn primary wide">Verify OTP</button>
</form>

<form method="POST" action="{{ route('password.email') }}" style="margin-top: 18px; text-align: center;">
    @csrf
    <input type="hidden" name="email" value="{{ $email }}">
    <button type="submit" style="background: none; border: none; color: #2563eb; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline; padding: 4px 8px;">
        Didn't receive code? Resend OTP
    </button>
</form>

<p class="auth-switch" style="margin-top: 10px; text-align: center;"><a href="{{ route('password.request') }}">Change email address</a></p>
@endsection
