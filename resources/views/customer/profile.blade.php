@extends('layouts.customer')
@section('title','My Profile')
@section('content')
<div class="page-head customer-page-head"><div><span class="eyebrow dark">CUSTOMER PROFILE</span><h1>My Profile</h1><p>Keep your contact and delivery information current.</p></div></div>
<div class="card narrow"><form method="POST" action="{{ route('customer.profile.update') }}" class="stack-form">@csrf @method('PUT')
<label>Full name<input name="name" value="{{ old('name',$customer->name) }}" required></label><label>Email<input value="{{ auth()->user()->email }}" disabled><small>Email is the login identity for this account.</small></label><label>Phone number<input name="phone" value="{{ old('phone',$customer->phone) }}" required></label><label>Default delivery address<textarea name="address" rows="4">{{ old('address',$customer->address) }}</textarea></label><button class="btn primary">Save Profile</button></form></div>
@endsection
