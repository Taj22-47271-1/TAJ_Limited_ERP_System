@extends('layouts.app')
@section('title','Company Settings')
@section('content')
<div class="page-head"><div><span class="eyebrow dark">ADMINISTRATION</span><h1>Company Settings</h1><p>These details dynamically update the ERP header, footer and currency display.</p></div></div>
<div class="card narrow"><form method="POST" action="{{ route('admin.settings.update') }}" class="stack-form">@csrf @method('PUT')<label>Company name<input name="company_name" value="{{ old('company_name',$settings->company_name) }}" required></label><label>Company email<input type="email" name="company_email" value="{{ old('company_email',$settings->company_email) }}"></label><label>Phone<input name="company_phone" value="{{ old('company_phone',$settings->company_phone) }}"></label><label>Address<textarea name="company_address" rows="4">{{ old('company_address',$settings->company_address) }}</textarea></label><label>Currency symbol<input name="currency_symbol" value="{{ old('currency_symbol',$settings->currency_symbol) }}" required></label><button class="btn primary">Save Settings</button></form></div>
@endsection
