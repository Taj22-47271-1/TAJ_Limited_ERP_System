<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ $companySettings->company_name ?? 'TAJ Limited' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/flatlogic-one.css') }}">
    @stack('head')
</head>
@php
    $user = auth()->user();
    $roleName = $user->roleDefinition?->name ?? ucwords(str_replace('_',' ',$user->role));
    $isCashier = $user->role === 'cashier';
@endphp
<body class="fl-body {{ $isCashier ? 'fl-cashier-theme' : 'fl-admin-theme' }}">
<div class="fl-app-shell">
    <aside class="fl-sidebar drawer" id="drawer">
        <div class="fl-sidebar-brand">
            <a href="{{ route('dashboard') }}" class="fl-brand-link">
                <span class="fl-logo-mark">T</span>
                <span class="fl-brand-text"><strong>{{ strtoupper($companySettings->company_name ?? 'TAJ LIMITED') }}</strong><small>ERP SYSTEM</small></span>
            </a>
            <button type="button" id="drawerClose" class="fl-sidebar-close" aria-label="Close navigation">×</button>
        </div>

        <div class="fl-sidebar-scroll">
            <div class="fl-nav-section">
                <span class="fl-nav-title">APP</span>
                <a class="fl-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="fl-nav-icon">▦</span><span>Dashboard</span></a>
                @if($user->hasPermission('pos.use'))<a class="fl-nav-item {{ request()->routeIs('pos') ? 'active' : '' }}" href="{{ route('pos') }}"><span class="fl-nav-icon">▣</span><span>In-house POS</span></a>@endif
                @if($user->hasPermission('orders.view'))
                    <a class="fl-nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                        <span class="fl-nav-icon">◎</span>
                        <span>Online Orders</span>
                        @if(($pendingOrdersCount ?? 0) > 0)
                            <span class="fl-sidebar-badge">{{ $pendingOrdersCount }}</span>
                        @else
                            <span class="fl-nav-dot"></span>
                        @endif
                    </a>
                @endif
                @if($user->hasPermission('sales.view'))<a class="fl-nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}"><span class="fl-nav-icon">↗</span><span>{{ $isCashier ? 'My Sales' : 'Sales' }}</span></a>@endif
            </div>

            <div class="fl-nav-section">
                <span class="fl-nav-title">BUSINESS</span>
                @if($user->hasPermission('products.view'))<a class="fl-nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><span class="fl-nav-icon">◇</span><span>Products</span></a>@endif
                @if($user->hasPermission('products.manage'))<a class="fl-nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><span class="fl-nav-icon">⊞</span><span>Categories</span></a>@endif
                @if($user->hasPermission('inventory.view'))<a class="fl-nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.movements') }}"><span class="fl-nav-icon">⇄</span><span>{{ $user->hasPermission('inventory.manage') ? 'Stock Management' : 'Stock Ledger' }}</span></a>@endif
                @if($user->hasPermission('purchases.manage'))<a class="fl-nav-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}"><span class="fl-nav-icon">↓</span><span>Purchases</span></a>@endif
                @if($user->hasPermission('customers.manage'))<a class="fl-nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}"><span class="fl-nav-icon">♙</span><span>Customers</span></a>@endif
                @if($user->hasPermission('suppliers.manage'))<a class="fl-nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}"><span class="fl-nav-icon">▤</span><span>Suppliers</span></a>@endif
            </div>

            @if($user->hasPermission('accounting.view') || $user->hasPermission('accounting.manage') || $user->hasPermission('reports.view'))
            <div class="fl-nav-section">
                <span class="fl-nav-title">FINANCE</span>
                @if($user->hasPermission('accounting.manage'))<a class="fl-nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}"><span class="fl-nav-icon">−</span><span>Expenses</span></a>@endif
                @if($user->hasPermission('accounting.view'))<a class="fl-nav-item {{ request()->routeIs('journals.*') ? 'active' : '' }}" href="{{ route('journals.index') }}"><span class="fl-nav-icon">≡</span><span>Accounting</span></a>@endif
                @if($user->hasPermission('reports.view'))<a class="fl-nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.profit-loss') }}"><span class="fl-nav-icon">◔</span><span>Reports / Profit</span></a>@endif
            </div>
            @endif

            @if($user->hasPermission('showcase.manage') || $user->hasPermission('users.manage') || $user->hasPermission('roles.manage') || $user->hasPermission('settings.manage'))
            <div class="fl-nav-section">
                <span class="fl-nav-title">ADMINISTRATION</span>
                @if($user->hasPermission('showcase.manage'))<a class="fl-nav-item {{ request()->routeIs('admin.showcase*') ? 'active' : '' }}" href="{{ route('admin.showcase') }}"><span class="fl-nav-icon">✨</span><span>3D Showcase</span></a>@endif
                @if($user->hasPermission('showcase.manage') || $user->hasPermission('settings.manage'))<a class="fl-nav-item {{ request()->routeIs('admin.login-offers*') ? 'active' : '' }}" href="{{ route('admin.login-offers') }}"><span class="fl-nav-icon">🎉</span><span>Login Offers</span></a>@endif
                @if($user->hasPermission('users.manage'))<a class="fl-nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}"><span class="fl-nav-icon">♟</span><span>Users</span></a>@endif
                @if($user->hasPermission('roles.manage'))<a class="fl-nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}"><span class="fl-nav-icon">◆</span><span>Roles & Permissions</span></a>@endif
                @if($user->hasPermission('settings.manage'))<a class="fl-nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><span class="fl-nav-icon">⚙</span><span>Settings</span></a>@endif
            </div>
            @endif
        </div>

        <div class="fl-sidebar-account">
            <span class="fl-user-avatar">{{ strtoupper(substr($user->name,0,1)) }}</span>
            <div><strong>{{ $user->name }}</strong><small>{{ $roleName }}</small></div>
            <a href="{{ route('password.change') }}" class="fl-account-cog" title="Change password">⚙</a>
        </div>
    </aside>

    <div class="drawer-overlay fl-drawer-overlay" id="drawerOverlay"></div>

    <section class="fl-workspace">
        <header class="fl-topbar">
            <div class="fl-topbar-left">
                <button class="fl-mobile-menu" type="button" id="drawerToggle" aria-label="Open navigation">☰</button>
                <div class="fl-breadcrumb"><span>Main</span><b>›</b><strong>@yield('title', 'Dashboard')</strong></div>
            </div>
            <div class="fl-topbar-right">
                @if($user->hasPermission('products.view'))
                <form class="fl-global-search" action="{{ route('products.index') }}" method="GET">
                    <span>⌕</span><input name="q" value="{{ request()->routeIs('products.*') ? request('q') : '' }}" placeholder="Search products...">
                </form>
                @endif
                @if($user->hasPermission('orders.view'))
                <a href="{{ route('orders.index',['status'=>'pending']) }}" class="fl-orders-btn" title="Pending Online Orders: {{ $pendingOrdersCount ?? 0 }}" aria-label="Pending online orders">
                    <span class="fl-orders-icon">📦</span>
                    @if(($pendingOrdersCount ?? 0) > 0)
                        <span class="fl-order-counter-badge">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>
                @endif
                <a href="{{ route('password.change') }}" class="fl-top-user">
                    <span class="fl-top-avatar">{{ strtoupper(substr($user->name,0,1)) }}</span>
                    <span><strong>{{ $user->name }}</strong><small>{{ $roleName }}</small></span>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="fl-logout-button" type="submit" title="Sign out">↪</button></form>
            </div>
        </header>

        @if(session('status'))<div class="toast success fl-toast">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="toast error fl-toast">{{ $errors->first() }}</div>@endif

        <main class="page-shell fl-content">
            @yield('content')
        </main>

        <footer class="fl-app-footer">
            <span>© {{ date('Y') }} {{ $companySettings->company_name ?? 'TAJ Limited' }}</span>
            <span>Inventory · POS · E-commerce · Accounting</span>
        </footer>
    </section>
</div>
<script src="{{ asset('assets/app.js') }}"></script>
<script>
// Instant link prefetching on hover to make ERP & Storefront navigation zero-lag
document.addEventListener('mouseover', function(e) {
    const a = e.target.closest('a');
    if (!a || !a.href || a.origin !== location.origin || a.target === '_blank' || a.dataset.prefetched) return;
    a.dataset.prefetched = 'true';
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = a.href;
    document.head.appendChild(link);
}, { passive: true });
</script>
@stack('scripts')
</body>
</html>
