<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title','Admin Dashboard') | {{ $companySettings->company_name ?? 'TAJ Limited' }}</title><link rel="stylesheet" href="{{ asset('assets/app.css') }}">@stack('head')</head>
<body class="role-shell-body admin-role-body"><div class="role-shell">
<aside class="role-sidebar admin-sidebar">
<a href="{{ route('dashboard') }}" class="role-sidebar-brand"><span class="brand-mark">T</span><span><strong>{{ strtoupper($companySettings->company_name ?? 'TAJ LIMITED') }}</strong><small>ADMIN CONTROL</small></span></a>
<div class="role-user-card"><div class="role-user-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div><div><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></div></div>
<nav class="role-sidebar-nav">
<a class="{{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}"><span>⌂</span>Dashboard</a>
<a class="{{ request()->routeIs('pos')?'active':'' }}" href="{{ route('pos') }}"><span>▣</span>In-house POS</a>
<a class="{{ request()->routeIs('orders.*')?'active':'' }}" href="{{ route('orders.index') }}"><span>◉</span>Online Orders</a>
<a class="{{ request()->routeIs('sales.*')?'active':'' }}" href="{{ route('sales.index') }}"><span>↗</span>Sales</a>
<a class="{{ request()->routeIs('products.*')?'active':'' }}" href="{{ route('products.index') }}"><span>◫</span>Products</a>
<a href="{{ route('inventory.movements') }}"><span>⇄</span>Stock Management</a>
<a href="{{ route('purchases.index') }}"><span>🛒</span>Purchases</a>
<a href="{{ route('customers.index') }}"><span>👥</span>Customers</a>
<a href="{{ route('suppliers.index') }}"><span>▦</span>Suppliers</a>
<a href="{{ route('journals.index') }}"><span>▤</span>Accounting</a>
<a href="{{ route('reports.profit-loss') }}"><span>◔</span>Reports / Profit</a>
<div class="role-nav-label">ADMINISTRATION</div>
<a href="{{ route('admin.users') }}"><span>♙</span>Users</a><a href="{{ route('admin.roles.index') }}"><span>◆</span>Roles & Permissions</a><a href="{{ route('admin.settings') }}"><span>⚙</span>Settings</a>
</nav>
<div class="role-sidebar-bottom"><a href="{{ route('password.change') }}">Change Password</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign Out</button></form></div>
</aside>
<div class="role-main"><header class="role-topbar"><div><span class="role-top-kicker">TAJ LIMITED ERP</span><strong>Administration Dashboard</strong></div><div class="role-top-actions"><a href="{{ route('products.create') }}" class="btn primary">+ Add Product</a><a href="{{ route('orders.index',['status'=>'pending']) }}" class="btn ghost">Pending Orders</a><span class="role-top-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span></div></header>
@if(session('status'))<div class="role-flash success">{{ session('status') }}</div>@endif @if($errors->any())<div class="role-flash error">{{ $errors->first() }}</div>@endif
<main class="role-content">@yield('content')</main></div></div><script src="{{ asset('assets/app.js') }}"></script>@stack('scripts')</body></html>
