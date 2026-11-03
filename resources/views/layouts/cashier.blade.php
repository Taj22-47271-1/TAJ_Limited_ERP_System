<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Cashier Dashboard') | {{ $companySettings->company_name ?? 'TAJ Limited' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/flatlogic-one.css') }}">
    @stack('head')
</head>
<body class="role-shell-body cashier-role-body">
<div class="role-shell">
    <aside class="role-sidebar cashier-sidebar">
        <a href="{{ route('dashboard') }}" class="role-sidebar-brand">
            <span class="brand-mark cashier-brand-mark">T</span>
            <span><strong>{{ strtoupper($companySettings->company_name ?? 'TAJ LIMITED') }}</strong><small>SALES COUNTER</small></span>
        </a>
        <div class="role-user-card cashier-user-card">
            <div class="role-user-avatar cashier-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
            <div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->roleDefinition?->name ?? 'Cashier' }}</small></div>
        </div>
        <nav class="role-sidebar-nav cashier-sidebar-nav">
            <a class="{{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}"><span>⌂</span>Dashboard</a>
            @if(auth()->user()->hasPermission('pos.use'))<a class="{{ request()->routeIs('pos')?'active':'' }}" href="{{ route('pos') }}"><span>▣</span>POS / In-house Sale</a>@endif
            @if(auth()->user()->hasPermission('orders.view'))<a class="{{ request()->routeIs('orders.*')?'active':'' }}" href="{{ route('orders.index') }}"><span>◉</span>Online Orders</a>@endif
            @if(auth()->user()->hasPermission('sales.view'))<a class="{{ request()->routeIs('sales.*')?'active':'' }}" href="{{ route('sales.index') }}"><span>↗</span>My Sales</a>@endif
            @if(auth()->user()->hasPermission('products.view'))<a class="{{ request()->routeIs('products.*')?'active':'' }}" href="{{ route('products.index') }}"><span>◫</span>Products</a>@endif
            @if(auth()->user()->hasPermission('customers.manage'))<a href="{{ route('customers.index') }}"><span>👥</span>Customers</a>@endif
        </nav>
        <div class="cashier-access-note">
            <strong>Cashier Access</strong>
            <span>POS, products, customers and order approval. No accounting, total profit, users or system settings.</span>
        </div>
        <div class="role-sidebar-bottom">
            <a href="{{ route('password.change') }}">Change Password</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Sign Out</button>
            </form>
        </div>
    </aside>
    <div class="role-main">
        <header class="role-topbar cashier-topbar">
            <div>
                <span class="role-top-kicker cashier-kicker">SALES OPERATIONS</span>
                <strong>{{ auth()->user()->roleDefinition?->name ?? 'Cashier' }} Workspace</strong>
            </div>
            <div class="role-top-actions">
                @if(auth()->user()->hasPermission('pos.use'))<a href="{{ route('pos') }}" class="btn cashier-green-btn">Open POS</a>@endif
                @if(auth()->user()->hasPermission('products.manage'))<a href="{{ route('products.create') }}" class="btn ghost">+ Product</a>@endif
                <span class="role-top-avatar cashier-top-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
            </div>
        </header>
        @if(session('status'))<div class="role-flash success">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="role-flash error">{{ $errors->first() }}</div>@endif
        <main class="role-content cashier-content">
            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('assets/app.js') }}"></script>
@stack('scripts')
</body>
</html>
