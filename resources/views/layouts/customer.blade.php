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
@stack('head')
</head>
<body class="customer-body vc-body fl-customer-body">
<header class="vc-header">
    <div class="vc-header-inner">
        <a href="{{ route('dashboard') }}" class="vc-brand" aria-label="TAJ Limited Online Store">
            <span class="vc-brand-mark">T</span>
            <span class="vc-brand-copy"><strong>{{ $companySettings->company_name ?? 'TAJ Limited' }}</strong><small>Online Store</small></span>
        </a>

        <form class="vc-search" method="GET" action="{{ route('shop.index') }}" role="search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 21-4.35-4.35m2.35-5.15A7.5 7.5 0 1 1 4 11.5a7.5 7.5 0 0 1 15 0Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            <input name="q" value="{{ request('q') }}" placeholder="Search products..." autocomplete="off">
        </form>

        <nav class="vc-nav" aria-label="Customer navigation">
            <a class="{{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">Home</a>
            <a class="{{ request()->routeIs('shop.*')?'active':'' }}" href="{{ route('shop.index') }}">Shop</a>
            <a class="{{ request()->routeIs('customer.orders*')?'active':'' }}" href="{{ route('customer.orders') }}">Orders</a>
            <a class="{{ request()->routeIs('customer.profile')?'active':'' }}" href="{{ route('customer.profile') }}">Profile</a>
        </nav>

        <div class="vc-actions">
            <a class="vc-cart" href="{{ route('cart.index') }}" id="headerCartBtn" aria-label="Shopping cart">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 8h11l1 11h-13l1-11Zm3 0V6a2.5 2.5 0 0 1 5 0v2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                @php $cartCount = collect(session('cart', []))->sum(fn($line) => is_array($line) ? (float)($line['quantity'] ?? 0) : (float)$line); @endphp
                <span class="cart-badge-count">{{ number_format($cartCount, 0) }}</span>
            </a>
            <details class="vc-account">
                <summary><span>{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span></summary>
                <div class="vc-account-menu">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->email }}</small>
                    <a href="{{ route('customer.profile') }}">My profile</a>
                    <a href="{{ route('password.change') }}">Change password</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form>
                </div>
            </details>
        </div>
    </div>
</header>

@if(session('status'))<div class="toast success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="toast error">{{ $errors->first() }}</div>@endif

<main class="vc-main">@yield('content')</main>

<!-- Floating Bottom Cart Bar (as seen in video frame_38 and frame_77) -->
<div class="vc-floating-cart-bar" id="vcFloatingCartBar">
    <div class="vc-floating-cart-inner" id="vcFloatingCartTrigger">
        <div class="vc-floating-thumbs" id="vcFloatingThumbs"></div>
        <div class="vc-floating-info">
            <strong>View cart</strong>
            <span id="vcFloatingCountText">0 items</span>
        </div>
        <div class="vc-floating-right">
            <strong id="vcFloatingTotalText">{{ $companySettings->currency_symbol }}0.00</strong>
            <span class="vc-floating-arrow">›</span>
        </div>
    </div>
</div>

<!-- Slide-Up Mini Cart Drawer (as seen in video frame_115) -->
<div class="vc-mini-cart-overlay" id="vcMiniCartOverlay"></div>
<div class="vc-mini-cart-drawer" id="vcMiniCartDrawer">
    <div class="vc-mini-cart-head">
        <div>
            <span style="font-size: 20px;">🛒</span>
            <h3>Your Cart <span id="vcDrawerCountBadge">(0)</span></h3>
        </div>
        <button type="button" id="vcMiniCartClose" class="vc-drawer-close-btn" aria-label="Close cart">×</button>
    </div>
    <div class="vc-mini-cart-items" id="vcMiniCartItems">
        <!-- Rendered dynamically -->
    </div>
    <div class="vc-mini-cart-footer">
        <div class="vc-free-shipping-pill">
            <span>🚚</span> Standard Delivery · Reliable Tracking
        </div>
        <div class="vc-drawer-subtotal-row">
            <span>Subtotal</span>
            <strong id="vcDrawerSubtotal">{{ $companySettings->currency_symbol }}0.00</strong>
        </div>
        <a href="{{ route('checkout') }}" class="vc-drawer-checkout-btn">
            <span>Proceed to Checkout</span>
            <strong id="vcDrawerCheckoutTotal">{{ $companySettings->currency_symbol }}0.00 ›</strong>
        </a>
    </div>
</div>

<footer class="vc-footer">
    <div class="vc-footer-inner">
        <div class="vc-footer-brand">
            <a href="{{ route('dashboard') }}" class="vc-brand"><span class="vc-brand-mark">T</span><span class="vc-brand-copy"><strong>{{ $companySettings->company_name ?? 'TAJ Limited' }}</strong><small>Inventory · POS · Online Store</small></span></a>
            <p>Shop products using the same live inventory managed by TAJ Limited ERP.</p>
        </div>
        <div><strong>Shop</strong><a href="{{ route('shop.index') }}">All products</a><a href="{{ route('cart.index') }}">Cart</a><a href="{{ route('customer.orders') }}">Track orders</a></div>
        <div><strong>Account</strong><a href="{{ route('customer.profile') }}">Profile</a><a href="{{ route('password.change') }}">Password</a><span>Secure checkout</span></div>
        <div><strong>Contact</strong><span>{{ $companySettings->company_email ?? 'info@tajlimited.com' }}</span><span>{{ $companySettings->company_phone ?? '+880 1XXXXXXXXX' }}</span><span>{{ $companySettings->company_address ?? 'Dhaka, Bangladesh' }}</span></div>
    </div>
    <div class="vc-footer-bottom"><span>© {{ date('Y') }} {{ $companySettings->company_name ?? 'TAJ Limited' }}.</span><span>Built for fast, simple commerce.</span></div>
</footer>
<script src="{{ asset('assets/app.js') }}"></script>
@stack('scripts')
</body>
</html>
