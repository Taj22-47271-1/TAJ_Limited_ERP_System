@extends('layouts.auth')
@section('title','Customer Registration')
@section('content')
<span class="eyebrow dark">CUSTOMER REGISTRATION</span>
<h2>Create your TAJ account</h2>
<p class="muted">Public registration is for customers only. Cashier accounts are created by an administrator.</p>
<form method="POST" action="{{ route('register.store') }}" class="stack-form">@csrf
<label>Full name<input name="name" value="{{ old('name') }}" required autocomplete="name"></label>
<label>Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
<label>Phone number<input name="phone" value="{{ old('phone') }}" required autocomplete="tel"></label>
<label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
<label>Confirm password<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
<button class="btn primary wide">Create Customer Account</button>
</form>
<p class="auth-switch">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
@endsection
