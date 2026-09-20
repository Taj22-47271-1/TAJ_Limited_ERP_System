@extends(auth()->user()->role === 'customer' ? 'layouts.customer' : (auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.cashier'))
@section('title','Change Password')
@section('content')
<div class="page-head"><div><span class="eyebrow dark">SECURITY</span><h1>Change Password</h1><p>Update the password for {{ auth()->user()->email }}.</p></div></div>
<div class="card narrow"><form method="POST" action="{{ route('password.change.update') }}" class="stack-form">@csrf @method('PUT')<label>Current password<input type="password" name="current_password" required></label><label>New password<input type="password" name="password" required minlength="8"></label><label>Confirm new password<input type="password" name="password_confirmation" required minlength="8"></label><button class="btn primary">Update Password</button></form></div>
@endsection
