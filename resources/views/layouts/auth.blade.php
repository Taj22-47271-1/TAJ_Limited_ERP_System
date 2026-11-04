<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ $companySettings->company_name ?? 'TAJ Limited' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/flatlogic-one.css') }}">
</head>
<body class="auth-body fl-auth-body">
@php
    $loginOffers = $companySettings->getLoginOffers();
    $offerSlides = $loginOffers['slides'] ?? \App\Models\Setting::defaultLoginOffers()['slides'];
    $slideInterval = max(2, (int)($loginOffers['interval_seconds'] ?? 4)) * 1000;
@endphp

<div class="fl-auth-shell">
    <section class="fl-auth-form-pane">
        <a href="{{ route('login') }}" class="fl-auth-brand">
            <span class="fl-logo-mark">T</span>
            <span><strong>{{ strtoupper($companySettings->company_name ?? 'TAJ LIMITED') }}</strong><small>SMART BUSINESS ERP</small></span>
        </a>

        @if(($loginOffers['enabled'] ?? true) && !empty($offerSlides))
            <!-- Mobile Offer Ticker -->
            <div class="fl-mobile-offer-bar" onclick="focusLoginForm()">
                <span class="fl-mob-tag">{{ $offerSlides[0]['discount'] ?? 'FESTIVE OFFER' }}</span>
                <span class="fl-mob-text">{{ $offerSlides[0]['badge'] ?? 'Special Offer' }}: {{ $offerSlides[0]['title'] }}</span>
                <span class="fl-mob-arrow">→</span>
            </div>
        @endif

        <div class="fl-auth-card">
            @if(session('status'))<div class="alert success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
            @yield('content')
        </div>

        <div class="fl-auth-meta">© {{ date('Y') }} {{ $companySettings->company_name ?? 'TAJ Limited' }} · Secure business access</div>
    </section>

    @if(($loginOffers['enabled'] ?? true) && !empty($offerSlides))
        <!-- Dynamic Promotional Offer Slider -->
        <section class="fl-auth-promo-visual" id="authPromoSlider" aria-label="Promotional Offers">
            <div class="fl-promo-slider-track" id="sliderTrack">
                @foreach($offerSlides as $idx => $slide)
                    @php
                        $slideImg = !empty($slide['image'])
                            ? (str_starts_with($slide['image'], 'http') || str_starts_with($slide['image'], 'storage/') || str_starts_with($slide['image'], 'assets/') ? asset($slide['image']) : asset('storage/' . $slide['image']))
                            : asset('assets/images/offers/eid-offer.svg');
                    @endphp
                    <article class="fl-promo-slide {{ $idx === 0 ? 'active' : '' }}" data-slide="{{ $idx }}" onclick="focusLoginForm()">
                        <div class="fl-promo-slide-bg" style="background-image: url('{{ $slideImg }}');"></div>
                        <div class="fl-promo-slide-overlay"></div>
                        <div class="fl-promo-content">
                            <div class="fl-promo-badge-row">
                                <span class="fl-promo-badge">{{ $slide['badge'] ?? 'FESTIVE OFFER' }}</span>
                                <span class="fl-promo-discount">{{ $slide['discount'] ?? '10% DISCOUNT' }}</span>
                            </div>
                            <h3>{{ $slide['title'] }}</h3>
                            @if(!empty($slide['subtitle']))
                                <p>{{ $slide['subtitle'] }}</p>
                            @endif
                            <button type="button" class="fl-promo-action-btn" onclick="event.stopPropagation(); focusLoginForm();">
                                <span>⚡ Claim Offer · Sign In Now</span>
                                <span>→</span>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Slide Navigation Controls -->
            <div class="fl-promo-controls" onclick="event.stopPropagation();">
                <div class="fl-promo-dots" id="promoDots">
                    @foreach($offerSlides as $idx => $slide)
                        <span class="fl-promo-dot {{ $idx === 0 ? 'active' : '' }}" onclick="goToSlide({{ $idx }})" title="Slide {{ $idx + 1 }}"></span>
                    @endforeach
                </div>
                <div class="fl-promo-nav-btns">
                    <button type="button" class="fl-promo-nav-btn" onclick="prevSlide()" aria-label="Previous Slide">‹</button>
                    <button type="button" class="fl-promo-nav-btn" onclick="nextSlide()" aria-label="Next Slide">›</button>
                </div>
            </div>
        </section>
    @else
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
    @endif
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
<script>
// Promo Carousel Logic with Infinite Auto-Loop and Hover Pause
(function() {
    const slides = document.querySelectorAll('.fl-promo-slide');
    const dots = document.querySelectorAll('.fl-promo-dot');
    const totalSlides = slides.length;
    if (totalSlides <= 1) return;

    let currentIndex = 0;
    let timer = null;
    const intervalTime = {{ $slideInterval ?? 4000 }};
    const sliderEl = document.getElementById('authPromoSlider');

    window.goToSlide = function(index) {
        if (index < 0) index = totalSlides - 1;
        if (index >= totalSlides) index = 0;
        currentIndex = index;

        slides.forEach((s, i) => {
            s.classList.toggle('active', i === currentIndex);
        });
        dots.forEach((d, i) => {
            d.classList.toggle('active', i === currentIndex);
        });
    };

    window.nextSlide = function() {
        goToSlide(currentIndex + 1);
    };

    window.prevSlide = function() {
        goToSlide(currentIndex - 1);
    };

    function startAutoplay() {
        if (timer) clearInterval(timer);
        timer = setInterval(nextSlide, intervalTime);
    }

    function stopAutoplay() {
        if (timer) clearInterval(timer);
    }

    if (sliderEl) {
        sliderEl.addEventListener('mouseenter', stopAutoplay);
        sliderEl.addEventListener('mouseleave', startAutoplay);
    }

    startAutoplay();
})();

// Smooth focus to login form when clicking offer
window.focusLoginForm = function() {
    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput) {
        emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        emailInput.focus();
        emailInput.style.transition = 'box-shadow 0.3s, border-color 0.3s';
        emailInput.style.borderColor = '#f59e0b';
        emailInput.style.boxShadow = '0 0 0 4px rgba(245, 158, 11, 0.25)';
        setTimeout(() => {
            emailInput.style.borderColor = '';
            emailInput.style.boxShadow = '';
        }, 1500);
    }
};
</script>
</body>
</html>
