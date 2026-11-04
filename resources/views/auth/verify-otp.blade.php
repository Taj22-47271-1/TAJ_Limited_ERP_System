@extends('layouts.auth')
@section('title','Verify OTP')
@section('content')
<span class="eyebrow dark">VERIFY IDENTITY</span>
<h2>Enter your OTP</h2>
<p class="muted">A code was sent to <strong>{{ $email }}</strong>.</p>

@if(session('demo_otp'))
<div style="background: #fff8e1; border: 1px solid #f0d98f; padding: 12px; border-radius: 8px; margin: 15px 0;">
    <span style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #854d0e; font-weight: 800; display: block;">TEST / DEMO OTP CODE</span>
    <strong style="font-size: 24px; letter-spacing: 6px; color: #b45309; display: block; margin: 4px 0;">{{ session('demo_otp') }}</strong>
    <p style="margin: 0; font-size: 10px; color: #78350f; line-height: 1.4;">(SMTP email delivery is not configured on server. You can use this code to verify.)</p>
</div>
@endif

<form method="POST" action="{{ route('password.otp.verify') }}" class="stack-form">
    @csrf
    <label>
        6-digit OTP
        <input class="otp-input" inputmode="numeric" maxlength="6" name="otp" value="{{ session('demo_otp') }}" required autofocus placeholder="000000">
    </label>
    <button class="btn primary wide">Verify OTP</button>
</form>
<p class="auth-switch"><a href="{{ route('password.request') }}">Request a new OTP</a></p>
@endsection
