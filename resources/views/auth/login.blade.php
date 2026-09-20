@extends('layouts.auth')
@section('title','Sign In')

@section('content')
<span class="eyebrow dark">WELCOME BACK</span>
<h2>Sign in to TAJ ERP</h2>
<p class="muted">Admin, Cashier or Customer can sign in with their account.</p>

<form method="POST" action="{{ route('login.store') }}" class="stack-form">
@csrf
<label>Email address
<input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@company.com">
</label>

<label>Password
<div class="password-wrap">
<input id="loginPassword" type="password" name="password" required placeholder="••••••••">
<button type="button" class="show-pass" data-target="loginPassword">Show</button>
</div>
</label>

<div class="form-row between">
<label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
<a href="{{ route('password.request') }}">Forgot password?</a>
</div>

<button class="btn primary wide" type="submit">Sign In</button>
</form>

<p class="auth-switch">New account? <a href="{{ route('register') }}">Create account</a></p>

<script>
document.querySelector('.show-pass').onclick=function(){
const i=document.getElementById(this.dataset.target);
i.type=i.type==='password'?'text':'password';
this.textContent=i.type==='password'?'Show':'Hide'
}
</script>
@endsection
