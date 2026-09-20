<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ $companySettings->company_name ?? 'TAJ Limited' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/flatlogic-one.css') }}">
</head>
<body class="auth-body fl-auth-body">
<div class="fl-auth-shell">
    <section class="fl-auth-form-pane">
        <a href="{{ route('login') }}" class="fl-auth-brand">
            <span class="fl-logo-mark">T</span>
            <span><strong>{{ strtoupper($companySettings->company_name ?? 'TAJ LIMITED') }}</strong><small>SMART BUSINESS ERP</small></span>
        </a>

        <div class="fl-auth-card">
            @if(session('status'))<div class="alert success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
            @yield('content')
        </div>

        <div class="fl-auth-meta">© {{ date('Y') }} {{ $companySettings->company_name ?? 'TAJ Limited' }} · Secure business access</div>
    </section>

    <section class="fl-auth-visual" aria-hidden="true">
        <div class="fl-wave fl-wave-one"></div>
        <div class="fl-wave fl-wave-two"></div>
        <span class="fl-float-cube fl-cube-one"></span>
        <span class="fl-float-cube fl-cube-two"></span>
        <span class="fl-float-cube fl-cube-three"></span>
        <span class="fl-paper-plane fl-plane-one">➤</span>
        <span class="fl-paper-plane fl-plane-two">➤</span>
        <div class="fl-gear fl-gear-one">⚙</div>
        <div class="fl-gear fl-gear-two">⚙</div>
        <div class="fl-laptop">
            <div class="fl-laptop-screen">
                <div class="fl-lock"><span class="fl-lock-loop"></span><span class="fl-lock-body">●</span></div>
                <div class="fl-screen-card fl-screen-card-one"></div>
                <div class="fl-screen-card fl-screen-card-two"></div>
            </div>
            <div class="fl-laptop-base"><span></span><span></span><span></span><span></span><span></span><span></span></div>
        </div>
        <div class="fl-auth-visual-copy">
            <span>TAJ LIMITED ERP</span>
            <h3>Inventory, POS, online orders and accounting in one workspace.</h3>
            <p>Role-based access for administrators, cashiers and customers.</p>
        </div>
    </section>
</div>

<footer class="corporate-footer auth-footer fl-auth-footer">
    <div class="footer-grid">
        <section>
            <h3>About {{ $companySettings->company_name ?? 'TAJ Limited' }}</h3>
            <p>Integrated inventory, point-of-sale, online ordering and accounting operations in one secure business platform.</p>
            <div class="social-row"><span>f</span><span>◎</span><span>in</span><span>▶</span></div>
        </section>
        <section><h3>Services</h3><span>Point of Sale</span><span>Inventory Management</span><span>Online Orders</span><span>Accounting</span><span>Business Reports</span></section>
        <section><h3>Company</h3><span>Home</span><span>Customers</span><span>Suppliers</span><span>Security</span><span>Settings</span></section>
        <section><h3>Contact Us</h3><strong>ADDRESS</strong><p>{{ $companySettings->company_address ?? 'Dhaka, Bangladesh' }}</p><strong>EMAIL</strong><p>{{ $companySettings->company_email ?? 'info@tajlimited.com' }}</p><strong>CONTACT</strong><p>{{ $companySettings->company_phone ?? '+880 1XXXXXXXXX' }}</p></section>
    </div>
    <div class="footer-bottom"><span>© {{ date('Y') }} {{ $companySettings->company_name ?? 'TAJ Limited' }}. All Rights Reserved.</span><span class="footer-logo"><span class="mini-mark">T</span>{{ strtoupper($companySettings->company_name ?? 'TAJ LIMITED') }}</span><span class="newsletter">ERP • INVENTORY • POS • ACCOUNTING</span></div>
</footer>
<script src="{{ asset('assets/app.js') }}"></script>
</body>
</html>
