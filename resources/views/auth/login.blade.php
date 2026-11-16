<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In | {{ $companySettings->company_name ?? 'TAJ Limited' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/taj-login.css') }}?v={{ file_exists(public_path('assets/taj-login.css')) ? filemtime(public_path('assets/taj-login.css')) : '1.0' }}">
</head>
<body>

<div class="taj-login-viewport">
    {{-- =========================================================================
         LEFT SECTION: LOGIN PANEL (Approx 36% Desktop)
         ========================================================================= --}}
    <section class="taj-login-panel">
        {{-- Background Curved Wave Shapes --}}
        <div class="taj-left-bg-art">
            {{-- Top-Right Decorative Blue Wave --}}
            <svg class="taj-left-wave-top" viewBox="0 0 220 220" fill="none" preserveAspectRatio="none">
                <path d="M70 0C120 40 170 80 220 120V0H70Z" fill="url(#tajTopWaveGrad)" opacity="0.65"/>
                <path d="M120 0C160 50 190 100 220 170V0H120Z" fill="url(#tajTopWaveGrad2)" opacity="0.4"/>
                <defs>
                    <linearGradient id="tajTopWaveGrad" x1="70" y1="0" x2="220" y2="120" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#38bdf8"/>
                        <stop offset="1" stop-color="#1d4ed8"/>
                    </linearGradient>
                    <linearGradient id="tajTopWaveGrad2" x1="120" y1="0" x2="220" y2="170" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#60a5fa"/>
                        <stop offset="1" stop-color="#2563eb"/>
                    </linearGradient>
                </defs>
            </svg>

            {{-- Bottom Flowing Wave Strip --}}
            <svg class="taj-left-wave-bottom" viewBox="0 0 480 120" fill="none" preserveAspectRatio="none">
                <path d="M0 60C140 20 300 80 480 30V120H0V60Z" fill="#06153d"/>
                <path d="M0 40C120 15 280 65 480 15V45C300 80 140 20 0 60V40Z" fill="#1d4ed8" opacity="0.85"/>
                <path d="M0 25C100 5 240 45 480 5V18C280 65 120 15 0 40V25Z" fill="#38bdf8" opacity="0.45"/>
            </svg>
        </div>

        <div class="taj-login-content">
            <div>
                {{-- 1. Brand Header --}}
                <a href="{{ route('login') }}" class="taj-brand-header">
                    <div class="taj-brand-icon">
                        <span>T</span>
                    </div>
                    <div class="taj-brand-text">
                        <span class="taj-brand-title">{{ strtoupper($companySettings->company_name ?? 'TAJ LIMITED') }}</span>
                        <span class="taj-brand-sub">ERP • E-Commerce</span>
                    </div>
                </a>

                {{-- 2. Welcome Headings --}}
                <div class="taj-welcome-block">
                    <div class="taj-welcome-lead">Welcome to</div>
                    <div class="taj-welcome-title">{{ $companySettings->company_name ?? 'TAJ Limited' }}</div>
                    <div class="taj-welcome-tagline">
                        Your Business. Your Store. One Platform.
                        {{-- Curved Yellow Swoosh Underneath --}}
                        <svg class="taj-tagline-swoosh" viewBox="0 0 135 10" fill="none">
                            <path d="M2 3C40 9 95 9 133 3" stroke="#f59e0b" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                {{-- 3. Floating Login Card --}}
                <div class="taj-login-card">
                    <div class="taj-card-head">
                        <div class="taj-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="taj-card-titles">
                            <h2>Sign in to Your Account</h2>
                            <p>Admin, Cashier or Customer</p>
                        </div>
                    </div>

                    {{-- Status / Error Alerts --}}
                    @if(session('status'))
                        <div class="taj-alert taj-alert-success">{{ session('status') }}</div>
                    @endif
                    @if(isset($errors) && $errors->any())
                        <div class="taj-alert taj-alert-error">{{ $errors->first() }}</div>
                    @endif

                    {{-- Form --}}
                    <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                        @csrf
                        <div class="taj-field-group">
                            {{-- Email Field --}}
                            <div class="taj-input-box">
                                <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                <input type="email" 
                                       name="email" 
                                       id="loginEmail" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autofocus 
                                       placeholder="Email address" 
                                       class="taj-input">
                            </div>

                            {{-- Password Field with Visibility Toggle --}}
                            <div class="taj-input-box">
                                <svg class="taj-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                <input type="password" 
                                       name="password" 
                                       id="loginPassword" 
                                       required 
                                       placeholder="Password" 
                                       class="taj-input">
                                <button type="button" 
                                        class="taj-pass-toggle" 
                                        id="togglePassBtn" 
                                        aria-label="Toggle password visibility">
                                    {{-- Eye Icon --}}
                                    <svg id="eyeIconShow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    {{-- Eye Off Icon (Hidden initially) --}}
                                    <svg id="eyeIconHide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Remember Me & Forgot Password --}}
                        <div class="taj-form-row">
                            <label class="taj-remember-label">
                                <input type="checkbox" name="remember" value="1" id="rememberMe" {{ old('remember', true) ? 'checked' : '' }}>
                                <span>Remember me</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="taj-forgot-link">Forgot password?</a>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="taj-btn-signin" id="signInSubmitBtn">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                            <span>Sign In</span>
                        </button>

                        {{-- Create Account Link --}}
                        <div class="taj-create-row">
                            New account? <a href="{{ route('register') }}">Create account</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 4. Left Footer (Copyright & Security Shield) --}}
            <div class="taj-login-footer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <polyline points="9 12 11 14 15 10"/>
                </svg>
                <span>© {{ date('Y') }} {{ $companySettings->company_name ?? 'TAJ Limited' }}. All rights reserved.</span>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         RIGHT SECTION: PROMOTIONAL CAROUSEL (~64% Desktop)
         ========================================================================= --}}
    <section class="taj-promo-pane" id="promoCarouselPane" aria-label="Promotional Showcase">
        {{-- Top-Left Subtle Dot Matrix Grid --}}
        <div class="taj-dot-matrix"></div>

        {{-- Top-Right Header Badge --}}
        <div class="taj-promo-topbar">
            <a href="{{ route('shop.index') }}" class="taj-shop-smart-badge" title="Visit Online Shop">
                <div class="taj-shop-smart-content">
                    <svg class="taj-shop-smart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <span class="taj-shop-smart-text">Shop Smart | Manage Better</span>
                </div>
                <div class="taj-shop-smart-line"></div>
            </a>
        </div>

        {{-- Carousel Slides Stage --}}
        <div class="taj-carousel-stage">
            <div class="taj-carousel-track" id="carouselTrack">
                {{-- SLIDE 1: LOEWE SOLO VULCAN (Exact Reference Design Match) --}}
                <article class="taj-carousel-slide active" data-index="0">
                    <div class="taj-slide-info">
                        <span class="taj-slide-kicker">Premium Quality</span>
                        <h2 class="taj-slide-title">LoEwe <span class="taj-highlight-gold">Solo</span></h2>
                        <p class="taj-slide-subtitle">Vulcan Eau de Parfum</p>
                        <a href="{{ route('shop.index') }}" class="taj-btn-shopnow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            <span>Shop Now →</span>
                        </a>
                    </div>
                    <div class="taj-slide-visual">
                        <div class="taj-stage-wrap">
                            {{-- Exact Loewe Solo Photographic Stage from Reference --}}
                            <img src="{{ asset('assets/images/behance/login_ref_stage_perfect.png') }}" 
                                 alt="LoEwe Solo Vulcan Eau de Parfum" 
                                 class="taj-stage-composite-img">
                        </div>
                    </div>
                </article>

                {{-- SLIDE 2: MONTALE SOLEIL DE CAPRI --}}
                <article class="taj-carousel-slide" data-index="1">
                    <div class="taj-slide-info">
                        <span class="taj-slide-kicker">Signature Fragrance</span>
                        <h2 class="taj-slide-title">Montale <span class="taj-highlight-gold">Soleil</span></h2>
                        <p class="taj-slide-subtitle">Soleil de Capri Eau de Parfum</p>
                        <div class="taj-slide-badge-row">
                            <span class="taj-slide-discount-tag">-15% OFF</span>
                        </div>
                        <a href="{{ route('shop.index') }}" class="taj-btn-shopnow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            <span>Shop Now →</span>
                        </a>
                    </div>
                    <div class="taj-slide-visual">
                        <div class="taj-stage-wrap">
                            <div class="taj-stage-halo"></div>
                            <div class="taj-stage-pedestal"></div>
                            <div class="taj-stage-smoke"></div>
                            <img src="{{ asset('assets/images/behance/product_montale_soleil.png') }}" 
                                 alt="Montale Soleil de Capri" 
                                 class="taj-stage-product" 
                                 loading="lazy">
                        </div>
                    </div>
                </article>

                {{-- SLIDE 3: COSMIC INTENSE EAU DE PARFUM --}}
                <article class="taj-carousel-slide" data-index="2">
                    <div class="taj-slide-info">
                        <span class="taj-slide-kicker">Artisanal Essence</span>
                        <h2 class="taj-slide-title">Cosmic <span class="taj-highlight-gold">Intense</span></h2>
                        <p class="taj-slide-subtitle">Intense Eau de Parfum Natural Spray</p>
                        <div class="taj-slide-badge-row">
                            <span class="taj-slide-discount-tag">-20% OFF</span>
                        </div>
                        <a href="{{ route('shop.index') }}" class="taj-btn-shopnow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            <span>Shop Now →</span>
                        </a>
                    </div>
                    <div class="taj-slide-visual">
                        <div class="taj-stage-wrap">
                            <div class="taj-stage-halo"></div>
                            <div class="taj-stage-pedestal"></div>
                            <div class="taj-stage-smoke"></div>
                            <img src="{{ asset('assets/images/behance/perfume_hero_bottle.png') }}" 
                                 alt="Cosmic Intense Eau de Parfum" 
                                 class="taj-stage-product" 
                                 loading="lazy">
                        </div>
                    </div>
                </article>

                {{-- SLIDE 4: DESIGNER PURE MULBERRY SILK WEAVE --}}
                <article class="taj-carousel-slide" data-index="3">
                    <div class="taj-slide-info">
                        <span class="taj-slide-kicker">Festive Atelier</span>
                        <h2 class="taj-slide-title">Mulberry <span class="taj-highlight-gold">Silk</span></h2>
                        <p class="taj-slide-subtitle">Handcrafted Golden Zari Designer Weave</p>
                        <div class="taj-slide-badge-row">
                            <span class="taj-slide-discount-tag">-15% OFF</span>
                        </div>
                        <a href="{{ route('shop.index') }}" class="taj-btn-shopnow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            <span>Shop Now →</span>
                        </a>
                    </div>
                    <div class="taj-slide-visual">
                        <div class="taj-stage-wrap">
                            <div class="taj-stage-halo"></div>
                            <div class="taj-stage-pedestal"></div>
                            <div class="taj-stage-smoke"></div>
                            <img src="{{ asset('assets/images/products/designer-saree-gold.png') }}" 
                                 alt="Pure Mulberry Silk" 
                                 class="taj-stage-product" 
                                 loading="lazy" 
                                 style="max-height: 250px;">
                        </div>
                    </div>
                </article>
            </div>

            {{-- Previous & Next Navigation Arrows --}}
            <button type="button" class="taj-carousel-arrow taj-carousel-prev" id="carouselPrevBtn" aria-label="Previous Slide">‹</button>
            <button type="button" class="taj-carousel-arrow taj-carousel-next" id="carouselNextBtn" aria-label="Next Slide">›</button>
        </div>

        {{-- Bottom Controls & Feature Bar --}}
        <div>
            {{-- Carousel Pagination Dots --}}
            <div class="taj-promo-dots" id="carouselDots" role="tablist">
                <span class="taj-dot active" data-slide="0" role="tab" aria-label="Slide 1"></span>
                <span class="taj-dot" data-slide="1" role="tab" aria-label="Slide 2"></span>
                <span class="taj-dot" data-slide="2" role="tab" aria-label="Slide 3"></span>
                <span class="taj-dot" data-slide="3" role="tab" aria-label="Slide 4"></span>
            </div>

            {{-- Bottom Feature Bar --}}
            <div class="taj-feature-bar">
                <div class="taj-feature-item">
                    {{-- Shield with Checkmark Icon --}}
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <polyline points="9 12 11 14 15 10"/>
                    </svg>
                    <span>Secure & Reliable</span>
                </div>
                <div class="taj-feature-divider"></div>

                <div class="taj-feature-item">
                    {{-- Lightning Bolt Icon --}}
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                    </svg>
                    <span>Fast Performance</span>
                </div>
                <div class="taj-feature-divider"></div>

                <div class="taj-feature-item">
                    {{-- 24/7 Headset Support Icon --}}
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                        <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                    </svg>
                    <span>24/7 Support</span>
                </div>
            </div>
        </div>

        {{-- Bottom Flowing Curves with Radiant Golden Yellow Ribbon --}}
        <svg class="taj-promo-bottom-waves" viewBox="0 0 900 160" fill="none" preserveAspectRatio="none">
            {{-- Deep Royal Wave --}}
            <path d="M0 160C180 80 420 140 900 60V160H0Z" fill="#040e2d"/>
            {{-- Vibrant Blue Wave --}}
            <path d="M0 160C220 100 500 130 900 85V160H0Z" fill="#1d4ed8" opacity="0.6"/>
            {{-- Cyan Accent Wave --}}
            <path d="M0 160C260 120 540 145 900 110V160H0Z" fill="#38bdf8" opacity="0.3"/>
            {{-- Golden Yellow Swoosh Ribbon --}}
            <path d="M460 160C620 145 760 120 900 95V105C760 130 620 152 460 160Z" fill="url(#tajGoldWaveGrad)"/>
            <defs>
                <linearGradient id="tajGoldWaveGrad" x1="460" y1="140" x2="900" y2="100" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#f59e0b" stop-opacity="0.3"/>
                    <stop offset="0.5" stop-color="#fbbf24"/>
                    <stop offset="1" stop-color="#f59e0b"/>
                </linearGradient>
            </defs>
        </svg>
    </section>
</div>

{{-- Interactive Functionality Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Password Visibility Toggle
    const toggleBtn = document.getElementById('togglePassBtn');
    const passInput = document.getElementById('loginPassword');
    const eyeShow = document.getElementById('eyeIconShow');
    const eyeHide = document.getElementById('eyeIconHide');

    if (toggleBtn && passInput) {
        toggleBtn.addEventListener('click', function () {
            const isPass = passInput.type === 'password';
            passInput.type = isPass ? 'text' : 'password';
            if (eyeShow && eyeHide) {
                eyeShow.style.display = isPass ? 'none' : 'block';
                eyeHide.style.display = isPass ? 'block' : 'none';
            }
        });
    }

    // 2. Promotional Carousel Engine
    const slides = document.querySelectorAll('.taj-carousel-slide');
    const dots = document.querySelectorAll('.taj-dot');
    const prevBtn = document.getElementById('carouselPrevBtn');
    const nextBtn = document.getElementById('carouselNextBtn');
    const carouselPane = document.getElementById('promoCarouselPane');

    const totalSlides = slides.length;
    let currentIdx = 0;
    let autoplayTimer = null;
    const intervalMs = 4500;

    function showSlide(index) {
        if (index < 0) index = totalSlides - 1;
        if (index >= totalSlides) index = 0;
        currentIdx = index;

        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === currentIdx);
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentIdx);
        });
    }

    function nextSlide() {
        showSlide(currentIdx + 1);
    }

    function prevSlide() {
        showSlide(currentIdx - 1);
    }

    function startAutoplay() {
        if (autoplayTimer) clearInterval(autoplayTimer);
        autoplayTimer = setInterval(nextSlide, intervalMs);
    }

    function stopAutoplay() {
        if (autoplayTimer) {
            clearInterval(autoplayTimer);
            autoplayTimer = null;
        }
    }

    if (nextBtn) nextBtn.addEventListener('click', function() {
        nextSlide();
        startAutoplay();
    });

    if (prevBtn) prevBtn.addEventListener('click', function() {
        prevSlide();
        startAutoplay();
    });

    dots.forEach((dot, i) => {
        dot.addEventListener('click', function() {
            showSlide(i);
            startAutoplay();
        });
    });

    // Pause on Hover
    if (carouselPane) {
        carouselPane.addEventListener('mouseenter', stopAutoplay);
        carouselPane.addEventListener('mouseleave', startAutoplay);
    }

    // Touch Swipe Navigation for Mobile / Tablet
    let touchStartX = 0;
    let touchEndX = 0;

    if (carouselPane) {
        carouselPane.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        carouselPane.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
    }

    function handleSwipe() {
        const threshold = 40;
        if (touchEndX < touchStartX - threshold) {
            nextSlide();
            startAutoplay();
        } else if (touchEndX > touchStartX + threshold) {
            prevSlide();
            startAutoplay();
        }
    }

    // Keyboard Arrow Navigation
    document.addEventListener('keydown', function(e) {
        if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) return;
        if (e.key === 'ArrowRight') { nextSlide(); startAutoplay(); }
        if (e.key === 'ArrowLeft') { prevSlide(); startAutoplay(); }
    });

    // Start carousel autoplay
    startAutoplay();
});
</script>

</body>
</html>
