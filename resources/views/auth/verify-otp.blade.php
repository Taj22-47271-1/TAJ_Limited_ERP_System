@extends('layouts.auth')
@section('title','Verify OTP')
@section('content')
<span class="eyebrow dark">VERIFY IDENTITY</span><h2>Enter your OTP</h2><p class="muted">A code was sent to <strong>{{ $email }}</strong>.</p>
<form method="POST" action="{{ route('password.otp.verify') }}" class="stack-form">@csrf<label>6-digit OTP<input class="otp-input" inputmode="numeric" maxlength="6" name="otp" required autofocus placeholder="000000"></label><button class="btn primary wide">Verify OTP</button></form><p class="auth-switch"><a href="{{ route('password.request') }}">Request a new OTP</a></p>
@endsection
