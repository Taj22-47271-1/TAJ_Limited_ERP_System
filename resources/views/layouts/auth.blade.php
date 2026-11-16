<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ $companySettings->company_name ?? 'TAJ Limited' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/taj-login.css') }}?v={{ file_exists(public_path('assets/taj-login.css')) ? filemtime(public_path('assets/taj-login.css')) : '1.0' }}">
    @stack('head')
</head>
<body>

<div class="taj-login-viewport">
    {{-- =========================================================================
         LEFT SECTION: AUTH PANEL (Approx 36% Desktop)
         ========================================================================= --}}
    <section class="taj-login-panel">
        {{-- Background Curved Wave Shapes --}}
        <div class="taj-left-bg-art">
            {{-- Top-Right Decorative Blue Wave --}}
            <svg class="taj-left-wave-top" viewBox="0 0 220 220" fill="none" preserveAspectRatio="none">
                <path d="M70 0C120 40 170 80 220 120V0H70Z" fill="url(#tajTopWaveGradAuth)" opacity="0.65"/>
                <path d="M120 0C160 50 190 100 220 170V0H120Z" fill="url(#tajTopWaveGrad2Auth)" opacity="0.4"/>
                <defs>
                    <linearGradient id="tajTopWaveGradAuth" x1="70" y1="0" x2="220" y2="120" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#38bdf8"/>
                        <stop offset="1" stop-color="#1d4ed8"/>
                    </linearGradient>
                    <linearGradient id="tajTopWaveGrad2Auth" x1="120" y1="0" x2="220" y2="170" gradientUnits="userSpaceOnUse">
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
                        Your Business. Your Store. <span class="taj-swoosh-wrap">One Platform.<svg class="taj-tagline-swoosh" viewBox="0 0 135 10" fill="none"><path d="M2 3C40 9 95 9 133 3" stroke="#f59e0b" stroke-width="3" stroke-linecap="round"/></svg></span>
                    </div>
                </div>

                {{-- 3. Floating Auth Card --}}
                <div class="taj-login-card">
                    <div class="taj-card-head">
                        <div class="taj-card-icon">
                            @hasSection('card-icon')
                                @yield('card-icon')
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            @endif
                        </div>
                        <div class="taj-card-titles">
                            <h2>@yield('card-title', 'Sign in to Your Account')</h2>
                            <p>@yield('card-subtitle', 'Admin, Cashier or Customer')</p>
                        </div>
                    </div>

                    {{-- Status / Error Alerts --}}
                    @if(session('status'))
                        <div class="taj-alert taj-alert-success">{{ session('status') }}</div>
                    @endif
                    @if(isset($errors) && $errors->any())
                        <div class="taj-alert taj-alert-error">{{ $errors->first() }}</div>
                    @endif

                    {{-- Page Content / Form --}}
                    @yield('content')
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
    @php
        $loginOffersConfig = method_exists($companySettings, 'getLoginOffers')
            ? $companySettings->getLoginOffers()
            : \App\Models\Setting::defaultLoginOffers();
        $offerSlides = $loginOffersConfig['slides'] ?? \App\Models\Setting::defaultLoginOffers()['slides'];
        $slideInterval = max(2, (int)($loginOffersConfig['interval_seconds'] ?? 4)) * 1000;
        $sliderEnabled = $loginOffersConfig['enabled'] ?? true;
        $slidesCount = count($offerSlides);
    @endphp

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

        {{-- Carousel Slides Stage (Dynamic from Login Offers & Promo Slider Settings) --}}
        <div class="taj-carousel-stage">
            <div class="taj-carousel-track" id="carouselTrack">
                @foreach($offerSlides as $idx => $slide)
                    @php
                        $rawTitle = trim($slide['title'] ?? ($slide['product_name'] ?? 'Exclusive Offer'));
                        $words = preg_split('/\s+/', $rawTitle);
                        if (count($words) > 1) {
                            $lastWord = array_pop($words);
                            $titlePrefix = implode(' ', $words);
                        } else {
                            $titlePrefix = $rawTitle;
                            $lastWord = '';
                        }

                        $rawImg = $slide['image'] ?? '';
                        if (empty($rawImg)) {
                            $imgUrl = asset('assets/images/behance/product_loewe_solo.png');
                        } elseif (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) {
                            $imgUrl = $rawImg;
                        } elseif (str_starts_with($rawImg, 'storage/')) {
                            $imgUrl = asset($rawImg);
                        } elseif (str_starts_with($rawImg, 'assets/')) {
                            $imgUrl = asset($rawImg);
                        } else {
                            $imgUrl = asset('storage/' . ltrim($rawImg, '/'));
                        }

                        $targetUrl = !empty($slide['product_id'])
                            ? route('shop.show', $slide['product_id'])
                            : route('shop.index');
                    @endphp

                    <article class="taj-carousel-slide {{ $loop->first ? 'active' : '' }}" data-index="{{ $idx }}">
                        <div class="taj-slide-info">
                            @if(!empty($slide['badge']))
                                <span class="taj-slide-kicker">{{ $slide['badge'] }}</span>
                            @endif

                            <h2 class="taj-slide-title">
                                {{ $titlePrefix }}
                                @if($lastWord)
                                    <span class="taj-highlight-gold">{{ $lastWord }}</span>
                                @endif
                            </h2>

                            @if(!empty($slide['subtitle']))
                                <p class="taj-slide-subtitle">{{ $slide['subtitle'] }}</p>
                            @endif

                            @if(!empty($slide['discount']))
                                <div class="taj-slide-badge-row">
                                    <span class="taj-slide-discount-tag">{{ $slide['discount'] }}</span>
                                </div>
                            @endif

                            <a href="{{ $targetUrl }}" class="taj-btn-shopnow">
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
                                <img src="{{ $imgUrl }}" 
                                     alt="{{ $slide['title'] ?? 'Promotional Product' }}" 
                                     class="taj-stage-product" 
                                     loading="lazy">
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($slidesCount > 1)
                {{-- Previous & Next Navigation Arrows --}}
                <button type="button" class="taj-carousel-arrow taj-carousel-prev" id="carouselPrevBtn" aria-label="Previous Slide">‹</button>
                <button type="button" class="taj-carousel-arrow taj-carousel-next" id="carouselNextBtn" aria-label="Next Slide">›</button>
            @endif
        </div>

        {{-- Bottom Controls & Feature Bar --}}
        <div>
            @if($slidesCount > 1)
                {{-- Carousel Pagination Dots --}}
                <div class="taj-promo-dots" id="carouselDots" role="tablist">
                    @foreach($offerSlides as $idx => $slide)
                        <span class="taj-dot {{ $loop->first ? 'active' : '' }}" data-slide="{{ $idx }}" role="tab" aria-label="Slide {{ $idx + 1 }}"></span>
                    @endforeach
                </div>
            @endif

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
            <path d="M0 160C180 80 420 140 900 60V160H0Z" fill="#040e2d"/>
            <path d="M0 160C220 100 500 130 900 85V160H0Z" fill="#1d4ed8" opacity="0.6"/>
            <path d="M0 160C260 120 540 145 900 110V160H0Z" fill="#38bdf8" opacity="0.3"/>
            <path d="M460 160C620 145 760 120 900 95V105C760 130 620 152 460 160Z" fill="url(#tajGoldWaveGradAuth)"/>
            <defs>
                <linearGradient id="tajGoldWaveGradAuth" x1="460" y1="140" x2="900" y2="100" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#f59e0b" stop-opacity="0.3"/>
                    <stop offset="0.5" stop-color="#fbbf24"/>
                    <stop offset="1" stop-color="#f59e0b"/>
                </linearGradient>
            </defs>
        </svg>
    </section>
</div>

{{-- Global Luxury Footer (Shared with Customer Storefront) --}}
<footer class="vc-footer taj-login-footer-section">
    <div class="vc-footer-inner">
        <div class="vc-footer-brand-col">
            <div class="vc-brand">
                <span class="vc-brand-mark">T</span>
                <strong>{{ $companySettings->company_name ?? 'TAJ Limited' }}</strong>
            </div>
            <p>Shop products using the same live inventory managed by TAJ Limited ERP.</p>
        </div>

        <div>
            <h4>Categories</h4>
            <a href="{{ route('shop.index') }}">All products</a>
            <a href="{{ route('shop.index', ['filter' => 'sale']) }}">Special Offers</a>
        </div>

        <div>
            <h4>Company</h4>
            <a href="{{ route('shop.index') }}">Shop online</a>
            <a href="{{ route('login') }}">Staff portal</a>
            <a href="{{ route('register') }}">Customer Register</a>
        </div>

        <div>
            <h4>Assistance</h4>
            <small>{{ $companySettings->company_phone ?? '+880 1XXXXXXXXX' }}</small>
            <small>{{ $companySettings->company_email ?? 'info@tajlimited.com' }}</small>
            <small>Dhaka, Bangladesh</small>
        </div>
    </div>

    <div class="vc-copyright">
        © {{ date('Y') }} {{ $companySettings->company_name ?? 'TAJ Limited' }}. Live inventory powered by TAJ ERP.
    </div>
</footer>

{{-- Interactive Carousel & Accessibility Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.taj-carousel-slide');
    const dots = document.querySelectorAll('.taj-dot');
    const prevBtn = document.getElementById('carouselPrevBtn');
    const nextBtn = document.getElementById('carouselNextBtn');
    const carouselPane = document.getElementById('promoCarouselPane');

    const totalSlides = slides.length;
    let currentIdx = 0;
    let autoplayTimer = null;
    const intervalMs = {{ $slideInterval }};
    const isAutoplayEnabled = {{ $sliderEnabled ? 'true' : 'false' }};

    function showSlide(index) {
        if (totalSlides <= 1) return;
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
        if (!isAutoplayEnabled || totalSlides <= 1) return;
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

    if (carouselPane) {
        carouselPane.addEventListener('mouseenter', stopAutoplay);
        carouselPane.addEventListener('mouseleave', startAutoplay);
    }

    let touchStartX = 0;
    let touchEndX = 0;
    if (carouselPane) {
        carouselPane.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        carouselPane.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            if (touchEndX < touchStartX - 40) {
                nextSlide();
                startAutoplay();
            } else if (touchEndX > touchStartX + 40) {
                prevSlide();
                startAutoplay();
            }
        }, { passive: true });
    }

    startAutoplay();
});
</script>

@stack('scripts')
</body>
</html>
