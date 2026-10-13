@extends('frontend.layouts.app')

@section('title', 'SIMKO Lighting - Light | Smart | Innovation')

@section('content')

<!-- 1. HERO SLIDER BANNER SECTION -->
<section class="hero-wrapper">
    <div class="hero-card">
        <div class="hero-content">
            <span class="hero-subtitle-tag">{{ $frontendSetting->hero_subtitle ?? 'Illuminate a Brighter Tomorrow' }}</span>
            <h1 class="hero-headline">{{ $frontendSetting->hero_headline ?? 'Lighting for a Better Life' }}</h1>
            <p class="hero-description">
                {{ $frontendSetting->hero_description ?? 'High-quality, energy-efficient and beautifully designed lighting solutions for homes, offices and modern spaces.' }}
            </p>
            <div class="hero-cta-group">
                <a href="{{ route('frontend.products.index') }}" class="btn-simko-primary">
                    Explore Products <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="hero-badges">
                <div class="badge-item">
                    <i class="fa-solid fa-leaf"></i> Energy Efficient
                </div>
                <div class="badge-item">
                    <i class="fa-solid fa-shield-check"></i> Premium Quality
                </div>
                <div class="badge-item">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Innovative Designs
                </div>
            </div>
        </div>
        <div class="hero-visual-box" style="background-image: url('{{ asset('images/frontend/hero.jpg') }}');">
            <div class="hero-overlay-tag">
                <h3>Light<br>Smart<br>Innovation</h3>
                <span></span>
            </div>
            <div class="hero-dots-indicator">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
    </div>
</section>

<!-- 2. OUR PRODUCTS CATEGORY SECTION -->
<section class="section-wrapper">
    <div class="section-header-flex">
        <div>
            <span class="section-tag">Our Products</span>
            <h2 class="section-title">Explore Our Lighting Range</h2>
        </div>
        <a href="{{ route('frontend.products.index') }}" class="link-arrow">
            View All Products <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="product-grid">
        <a href="{{ route('frontend.products.index') }}?category=panel" class="product-card">
            <div class="product-img-box">
                <img src="{{ asset('images/frontend/products/panel.jpg') }}" alt="Panel Lights">
            </div>
            <h3 class="product-name">Panel Lights</h3>
            <span class="product-series">Zola / Adjust Series</span>
        </a>

        <a href="{{ route('frontend.products.index') }}?category=surface" class="product-card">
            <div class="product-img-box">
                <img src="{{ asset('images/frontend/products/surface.jpg') }}" alt="Surface Lights">
            </div>
            <h3 class="product-name">Surface Lights</h3>
            <span class="product-series">Zeno / Orbit Series</span>
        </a>

        <a href="{{ route('frontend.products.index') }}?category=downlight" class="product-card">
            <div class="product-img-box">
                <img src="{{ asset('images/frontend/products/downlight.jpg') }}" alt="Down Lights">
            </div>
            <h3 class="product-name">Down Lights</h3>
            <span class="product-series">Classic / Deep Series</span>
        </a>

        <a href="{{ route('frontend.products.index') }}?category=cob" class="product-card">
            <div class="product-img-box">
                <img src="{{ asset('images/frontend/products/cob.jpg') }}" alt="COB Lights">
            </div>
            <h3 class="product-name">COB Lights</h3>
            <span class="product-series">Sine / Zent Series</span>
        </a>

        <a href="{{ route('frontend.products.index') }}?category=linear" class="product-card">
            <div class="product-img-box">
                <img src="{{ asset('images/frontend/products/linear.jpg') }}" alt="Linear Lights">
            </div>
            <h3 class="product-name">Linear Lights</h3>
            <span class="product-series">Lino / Eltra Series</span>
        </a>

        <a href="{{ route('frontend.products.index') }}?category=outdoor" class="product-card">
            <div class="product-img-box">
                <img src="{{ asset('images/frontend/products/outdoor.jpg') }}" alt="Outdoor Lights">
            </div>
            <h3 class="product-name">Outdoor Lights</h3>
            <span class="product-series">Flood / 4Way / Street</span>
        </a>
    </div>
</section>

<!-- 3. WHY CHOOSE SIMKO SECTION -->
<section class="section-wrapper" id="about-section">
    <div class="why-choose-box">
        <div class="why-choose-header">
            <div>
                <span class="section-tag">Why Choose SIMKO</span>
                <h2 class="section-title">More Than Just Lighting</h2>
            </div>
            <div>
                <p class="why-choose-right-desc">
                    We combine innovation, design and reliability to deliver lighting solutions that make a difference in everyday life.
                </p>
                <div style="margin-top: 1rem;">
                    <a href="{{ route('frontend.products.index') }}" class="link-arrow">
                        Learn More <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="why-features-grid">
            <div class="feature-tile">
                <div class="feature-tile-icon">
                    <i class="fa-regular fa-gem"></i>
                </div>
                <div class="feature-tile-content">
                    <h4>Quality Assured</h4>
                    <p>Reliable & durable products built to last</p>
                </div>
            </div>

            <div class="feature-tile">
                <div class="feature-tile-icon">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <div class="feature-tile-content">
                    <h4>Energy Efficient</h4>
                    <p>Save energy, brighter future</p>
                </div>
            </div>

            <div class="feature-tile">
                <div class="feature-tile-icon">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div class="feature-tile-content">
                    <h4>Modern Designs</h4>
                    <p>Aesthetics for every space</p>
                </div>
            </div>

            <div class="feature-tile">
                <div class="feature-tile-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="feature-tile-content">
                    <h4>Wide Dealer Network</h4>
                    <p>Growing together across India</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4. SPLIT CALL-TO-ACTION BANNER -->
<section class="section-wrapper">
    <div class="split-banner-grid">
        <!-- LEFT DARK BANNER -->
        <div class="cta-dark-card" style="background-image: url('{{ asset('images/frontend/hero.jpg') }}');">
            <div class="cta-dark-overlay"></div>
            <div class="cta-dark-content">
                <h3>Smart Lighting for Modern Spaces</h3>
                <p>From homes to commercial projects, SIMKO brings light, style and innovation to every space.</p>
                <a href="{{ route('frontend.products.index') }}" class="btn-simko-primary">
                    Explore Products <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- RIGHT ORANGE CATALOGUE BANNER -->
        <div class="cta-orange-card">
            <div class="cta-orange-body">
                <div class="cta-orange-left">
                    <h3>Download Catalogue</h3>
                    <p>Explore our complete range of products designed for modern spaces.</p>
                    <div class="cta-orange-buttons">
                        <button class="btn-white-solid" onclick="openCatalogueModal(event)">
                            <i class="fa-solid fa-download"></i> Download PDF
                        </button>
                        <a href="{{ route('frontend.products.index') }}" class="btn-white-ghost">
                            View Products <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <img src="{{ asset('images/frontend/catalogue.jpg') }}" alt="Simko Catalogue" class="catalogue-mockup-img">
                </div>
            </div>
            <div class="cta-orange-footer-bar">
                <span>LED Lights</span>
                <span>|</span>
                <span>COB</span>
                <span>|</span>
                <span>Panels</span>
                <span>|</span>
                <span>Outdoor</span>
                <span>|</span>
                <span>Smart Lighting</span>
            </div>
        </div>
    </div>
</section>

<!-- 5. LIGHTING FOR EVERY SPACE (APPLICATIONS) -->
<section class="section-wrapper">
    <div class="section-header-flex">
        <div>
            <span class="section-tag">Lighting For Every Space</span>
            <h2 class="section-title">Designed for Multiple Applications</h2>
        </div>
        <div style="max-width: 480px; text-align: right;">
            <p style="color: var(--simko-text-muted); font-size: 0.95rem; margin-bottom: 0.5rem;">
                SIMKO lighting solutions are trusted in homes, offices, showrooms, hotels and large commercial projects.
            </p>
            <a href="#contact-section" class="link-arrow">
                Get in Touch <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="applications-grid">
        <a href="{{ route('frontend.products.index') }}?application=residential" class="application-card">
            <div class="app-img-wrapper">
                <img src="{{ asset('images/frontend/app-residential.jpg') }}" alt="Residential Applications">
            </div>
            <div class="app-info-box">
                <h4>Residential</h4>
                <p>Stylish & comfortable lighting</p>
            </div>
        </a>

        <a href="{{ route('frontend.products.index') }}?application=commercial" class="application-card">
            <div class="app-img-wrapper">
                <img src="{{ asset('images/frontend/app-commercial.jpg') }}" alt="Commercial Applications">
            </div>
            <div class="app-info-box">
                <h4>Commercial</h4>
                <p>Efficient & modern solutions</p>
            </div>
        </a>

        <a href="{{ route('frontend.products.index') }}?application=retail" class="application-card">
            <div class="app-img-wrapper">
                <img src="{{ asset('images/frontend/app-retail.jpg') }}" alt="Retail Applications">
            </div>
            <div class="app-info-box">
                <h4>Retail & Showrooms</h4>
                <p>Enhance your space with light</p>
            </div>
        </a>

        <a href="{{ route('frontend.products.index') }}?application=outdoor" class="application-card">
            <div class="app-img-wrapper">
                <img src="{{ asset('images/frontend/app-outdoor.jpg') }}" alt="Outdoor Applications">
            </div>
            <div class="app-info-box">
                <h4>Outdoor</h4>
                <p>Reliable performance outdoors</p>
            </div>
        </a>
    </div>
</section>

<!-- 6. STAY UPDATED NEWSLETTER BANNER -->
<section class="section-wrapper">
    <div class="newsletter-box">
        <div class="newsletter-left">
            <div class="newsletter-icon">
                <i class="fa-regular fa-paper-plane"></i>
            </div>
            <div class="newsletter-text">
                <h3>Stay Updated</h3>
                <p>Get latest updates, new products and offers directly in your inbox.</p>
            </div>
        </div>
        <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing to SIMKO updates!'); this.reset();">
            <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
            <button type="submit" class="btn-newsletter-submit">Subscribe</button>
        </form>
    </div>
</section>

@endsection
