@extends('layouts.auth')
@section('title','Forgot Password')
@section('content')
<span class="eyebrow dark">ACCOUNT RECOVERY</span><h2>Forgot your password?</h2><p class="muted">Enter your registered email. We will send a 6-digit OTP that expires in 10 minutes.</p>
<form method="POST" action="{{ route('password.email') }}" class="stack-form">@csrf<label>Email address<input type="email" name="email" value="{{ old('email') }}" required autofocus></label><button class="btn primary wide">Send OTP</button></form><p class="auth-switch"><a href="{{ route('login') }}">Back to sign in</a></p>
@endsection
