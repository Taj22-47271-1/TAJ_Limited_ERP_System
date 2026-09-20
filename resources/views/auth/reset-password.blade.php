@extends('layouts.auth')
@section('title','Reset Password')
@section('content')
<span class="eyebrow dark">NEW PASSWORD</span><h2>Reset your password</h2><p class="muted">Use at least 8 characters. A stronger password should include uppercase, lowercase, numbers and symbols.</p>
<form method="POST" action="{{ route('password.update') }}" class="stack-form">@csrf<label>New password<input type="password" name="password" required></label><label>Confirm new password<input type="password" name="password_confirmation" required></label><button class="btn primary wide">Reset Password</button></form>
@endsection
