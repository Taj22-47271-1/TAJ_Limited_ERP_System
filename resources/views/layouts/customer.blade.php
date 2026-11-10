<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title','Customer') | {{ $companySettings->company_name ?? 'TAJ Limited' }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('assets/app.css') }}">

<link rel="stylesheet" href="{{ asset('assets/storefront.css') }}">

<link rel="stylesheet" href="{{ asset('assets/flatlogic-one.css') }}">
<link rel="stylesheet" href="{{ asset('assets/behance-luxury.css') }}">

@stack('head')

</head>

<body class="customer-body vc-body fl-customer-body behance-luxury-body">

<div class="taj-announcement-strip">
    <span>✨ FREE DELIVERY ON ORDERS OVER ৳3,000 · USE PROMO CODE <strong>FESTIVE10</strong> FOR 10% OFF</span>
    <span>•</span>
    <span>CASH ON DELIVERY NATIONWIDE</span>
    @guest
        <a href="{{ route('login') }}">Sign In Now →</a>
    @endguest
</div>
<header class="vc-header">

    <div class="vc-header-inner">

        <button type="button" class="vc-mobile-menu-btn" id="vcMobileMenuBtn" aria-label="Open navigation menu">☰</button>

        <a href="{{ auth()->check() ? route('dashboard') : route('shop.index') }}" class="lux-brand" aria-label="taj.">
            taj<span class="brand-dot">.</span>
        </a>



        <form class="vc-search" method="GET" action="{{ route('shop.index') }}" role="search">

            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 21-4.35-4.35m2.35-5.15A7.5 7.5 0 1 1 4 11.5a7.5 7.5 0 0 1 15 0Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>

            <input name="q" value="{{ request('q') }}" placeholder="Search products..." autocomplete="off">

        </form>



        <nav class="vc-nav" aria-label="Customer navigation">

            @if(auth()->check() && auth()->user()->role === 'customer')

                <a class="{{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">Home</a>

            @endif

            <a class="{{ request()->routeIs('shop.*')?'active':'' }}" href="{{ route('shop.index') }}">Shop</a>

            @if(auth()->check())

                @if(auth()->user()->role === 'customer')

                    <a class="{{ request()->routeIs('customer.orders*')?'active':'' }}" href="{{ route('customer.orders') }}">Orders</a>

                    <a class="{{ request()->routeIs('customer.profile')?'active':'' }}" href="{{ route('customer.profile') }}">Profile</a>

                @elseif(auth()->user()->role === 'admin' || auth()->user()->role === 'cashier')

                    <a href="{{ route('dashboard') }}" style="color: #2563eb; font-weight: 700;">Admin ERP ↗</a>

                @endif

            @else

                <a href="{{ route('login') }}">Sign In</a>

                <a href="{{ route('register') }}">Register</a>

            @endif

        </nav>



        <div class="vc-actions">

            <a class="vc-wishlist-icon" href="{{ route('shop.index') }}" title="My Wishlist" style="position: relative; width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; color: #475569; text-decoration: none;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span class="wishlist-counter" style="position: absolute; top: 0; right: 0; background: #e11d48; color: #fff; font-size: 10px; font-weight: 800; min-width: 17px; height: 17px; border-radius: 999px; display: none; align-items: center; justify-content: center; padding: 0 4px;">0</span>
            </a>
            <a class="vc-cart" href="{{ route('cart.index') }}" id="headerCartBtn" aria-label="Shopping cart">

                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 8h11l1 11h-13l1-11Zm3 0V6a2.5 2.5 0 0 1 5 0v2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>

                @php $cartCount = collect(session('cart', []))->sum(fn($line) => is_array($line) ? (float)($line['quantity'] ?? 0) : (float)$line); @endphp

                <span class="cart-badge-count">{{ number_format($cartCount, 0) }}</span>

            </a>

            @if(auth()->check())

                <details class="vc-account">

                    <summary><span>{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span></summary>

                    <div class="vc-account-menu">

                        <strong>{{ auth()->user()->name }}</strong>

                        <small>{{ auth()->user()->email }}</small>

                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'cashier')

                            <a href="{{ route('dashboard') }}" style="color: #2563eb; font-weight: bold;">← Admin ERP Dashboard</a>

                        @else

                            <a href="{{ route('customer.profile') }}">My profile</a>

                            <a href="{{ route('customer.orders') }}">My orders</a>

                        @endif

                        <a href="{{ route('password.change') }}">Change password</a>

                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form>

                    </div>

                </details>

            @else

                <a href="{{ route('login') }}" class="btn primary" style="height: 38px; line-height: 36px; padding: 0 16px; font-size: 13px; text-decoration: none;">Sign In</a>

            @endif

        </div>

    </div>

</header>

{{-- Mobile Navigation Drawer & Backdrop --}}
<div class="vc-mobile-drawer-overlay" id="vcDrawerOverlay"></div>
<aside class="vc-mobile-drawer" id="vcMobileDrawer" aria-label="Mobile Navigation">
    <div class="vc-mobile-drawer-header">
        <a href="{{ auth()->check() ? route('dashboard') : route('shop.index') }}" class="lux-brand" style="font-size: 24px;">
            taj<span class="brand-dot">.</span>
        </a>
        <button type="button" class="vc-mobile-drawer-close" id="vcDrawerClose" aria-label="Close menu">✕</button>
    </div>

    @if(auth()->check())
        <div class="vc-mobile-user-card">
            <span class="vc-mobile-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="vc-mobile-user-meta">
                <strong>{{ auth()->user()->name }}</strong>
                <small>{{ auth()->user()->email }}</small>
                <span class="vc-mobile-role-badge">{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>
    @else
        <div class="vc-mobile-guest-card">
            <p style="margin: 0 0 10px; font-size: 13px; color: #475569;">Welcome to <strong>{{ $companySettings->company_name ?? 'TAJ Limited' }}</strong></p>
            <div class="vc-mobile-guest-actions" style="display: flex; gap: 8px;">
                <a href="{{ route('login') }}" class="btn primary" style="background: #0f172a; color: #fff; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; text-align: center; flex: 1;">Sign In</a>
                <a href="{{ route('register') }}" class="btn ghost" style="border: 1px solid #cbd5e1; color: #0f172a; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; text-align: center; flex: 1;">Register</a>
            </div>
        </div>
    @endif

    <nav class="vc-mobile-nav">
        @if(auth()->check() && auth()->user()->role === 'customer')
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <span class="vc-mob-icon">🏠</span> <span>Home</span>
            </a>
        @endif

        <a class="{{ request()->routeIs('shop.*') ? 'active' : '' }}" href="{{ route('shop.index') }}">
            <span class="vc-mob-icon">🛍️</span> <span>Shop Collection</span>
        </a>

        <a class="{{ request()->routeIs('cart.*') ? 'active' : '' }}" href="{{ route('cart.index') }}">
            <span class="vc-mob-icon">🛒</span> <span>My Cart @if(($cartCount ?? 0) > 0)({{ number_format($cartCount, 0) }})@endif</span>
        </a>

        @if(auth()->check())
            @if(auth()->user()->role === 'customer')
                <a class="{{ request()->routeIs('customer.orders*') ? 'active' : '' }}" href="{{ route('customer.orders') }}">
                    <span class="vc-mob-icon">📦</span> <span>My Orders</span>
                </a>
                <a class="{{ request()->routeIs('customer.profile') ? 'active' : '' }}" href="{{ route('customer.profile') }}">
                    <span class="vc-mob-icon">👤</span> <span>My Profile</span>
                </a>
            @elseif(auth()->user()->role === 'admin' || auth()->user()->role === 'cashier')
                <a href="{{ route('dashboard') }}" style="color: #2563eb; font-weight: 700;">
                    <span class="vc-mob-icon">⚙️</span> <span>Admin ERP Dashboard ↗</span>
                </a>
            @endif

            <a class="{{ request()->routeIs('password.change') ? 'active' : '' }}" href="{{ route('password.change') }}">
                <span class="vc-mob-icon">🔒</span> <span>Change Password</span>
            </a>
        @endif
    </nav>

    @if(auth()->check())
        <div class="vc-mobile-drawer-footer">
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="vc-mobile-logout-button">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    @endif
</aside>



@if(session('status'))<div class="toast success">{{ session('status') }}</div>@endif

@if($errors->any())<div class="toast error">{{ $errors->first() }}</div>@endif



<main class="vc-main">@yield('content')</main>



<footer class="vc-footer">

    <div class="vc-footer-inner">

        <div>

            <div class="vc-brand"><span class="vc-brand-mark">T</span><strong>{{ $companySettings->company_name ?? 'TAJ Limited' }}</strong></div>

            <p>Shop products using the same live inventory managed by TAJ Limited ERP.</p>

        </div>

        <div><h4>Categories</h4><a href="{{ route('shop.index') }}">All products</a></div>

        <div><h4>Company</h4><a href="{{ route('shop.index') }}">Shop online</a><a href="{{ route('login') }}">Staff portal</a></div>

        <div><h4>Assistance</h4><small>{{ $companySettings->company_phone ?? '+880 1XXXXXXXXX' }}</small><small>{{ $companySettings->company_email ?? 'support@tajlimited.com' }}</small></div>

    </div>

    <div class="vc-copyright">© {{ date('Y') }} {{ $companySettings->company_name ?? 'TAJ Limited' }}. Live inventory powered by TAJ ERP.</div>

</footer>

<script src="{{ asset('assets/app.js') }}"></script>
<script>
(function() {
    var drawer = document.getElementById('vcMobileDrawer');
    var overlay = document.getElementById('vcDrawerOverlay');
    var openBtn = document.getElementById('vcMobileMenuBtn');
    var closeBtn = document.getElementById('vcDrawerClose');

    function openNav() {
        if (drawer) drawer.classList.add('open');
        if (overlay) overlay.classList.add('open');
        document.body.classList.add('vc-drawer-open');
    }

    function closeNav() {
        if (drawer) drawer.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
        document.body.classList.remove('vc-drawer-open');
    }

    if (openBtn) openBtn.addEventListener('click', openNav);
    if (closeBtn) closeBtn.addEventListener('click', closeNav);
    if (overlay) overlay.addEventListener('click', closeNav);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeNav();
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 990) closeNav();
    });
})();
</script>

@stack('scripts')

</body>

</html>

