@extends('frontend.layouts.app')

@section('title', 'Products Catalog - SIMKO Lighting Solutions')

@push('styles')
<style>
    .catalog-header {
        text-align: center;
        margin: 2rem 0 3rem 0;
    }
    .catalog-header h1 {
        font-family: var(--font-heading);
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--simko-dark);
        margin-bottom: 0.5rem;
    }
    .catalog-header p {
        color: var(--simko-text-muted);
        font-size: 1.1rem;
    }

    .catalog-filter-bar {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 2.5rem;
    }

    .filter-btn {
        background-color: var(--simko-gray-bg);
        border: 1px solid var(--simko-border);
        color: var(--simko-text-main);
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .filter-btn.active, .filter-btn:hover {
        background-color: var(--simko-orange);
        color: #ffffff;
        border-color: var(--simko-orange);
    }

    .products-grid-catalog {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 4rem;
    }

    .catalog-product-card {
        background: #ffffff;
        border: 1px solid var(--simko-border);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .catalog-product-card:hover {
        transform: translateY(-50px);
        transform: translateY(-6px);
        box-shadow: var(--simko-shadow-hover);
        border-color: var(--simko-orange);
    }

    .catalog-product-img-box {
        height: 220px;
        background-color: var(--simko-card-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .catalog-product-img-box img {
        max-width: 100%;
        max-height: 180px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .catalog-product-card:hover .catalog-product-img-box img {
        transform: scale(1.06);
    }

    .catalog-product-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .catalog-product-category {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--simko-orange);
        letter-spacing: 0.8px;
        margin-bottom: 0.35rem;
    }

    .catalog-product-title {
        font-family: var(--font-heading);
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--simko-dark);
        margin-bottom: 0.5rem;
        text-decoration: none;
    }

    .catalog-product-desc {
        color: var(--simko-text-muted);
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1.5rem;
        flex: 1;
    }

    .catalog-product-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1rem;
        border-top: 1px solid var(--simko-border);
    }

    .series-badge {
        background-color: #fff7ed;
        color: var(--simko-orange);
        padding: 0.3rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="section-wrapper">
    <div class="catalog-header">
        <span class="section-tag">Lighting Range</span>
        <h1>SIMKO Product Catalog</h1>
        <p>Discover our full lineup of energy-efficient LED fixtures, architectural spotlights, and smart control systems.</p>
    </div>

    <div class="catalog-filter-bar">
        <a href="{{ route('frontend.products.index') }}" class="filter-btn {{ !request('category') ? 'active' : '' }}">All Products</a>
        <a href="{{ route('frontend.products.index') }}?category=panel" class="filter-btn {{ request('category') == 'panel' ? 'active' : '' }}">Panel Lights</a>
        <a href="{{ route('frontend.products.index') }}?category=surface" class="filter-btn {{ request('category') == 'surface' ? 'active' : '' }}">Surface Lights</a>
        <a href="{{ route('frontend.products.index') }}?category=downlight" class="filter-btn {{ request('category') == 'downlight' ? 'active' : '' }}">Down Lights</a>
        <a href="{{ route('frontend.products.index') }}?category=cob" class="filter-btn {{ request('category') == 'cob' ? 'active' : '' }}">COB Lights</a>
        <a href="{{ route('frontend.products.index') }}?category=linear" class="filter-btn {{ request('category') == 'linear' ? 'active' : '' }}">Linear Lights</a>
        <a href="{{ route('frontend.products.index') }}?category=outdoor" class="filter-btn {{ request('category') == 'outdoor' ? 'active' : '' }}">Outdoor Lights</a>
    </div>

    <div class="products-grid-catalog">
        <!-- 1. Panel Light -->
        <div class="catalog-product-card">
            <div class="catalog-product-img-box">
                <img src="{{ asset('images/frontend/products/panel.jpg') }}" alt="Panel Light">
            </div>
            <div class="catalog-product-body">
                <span class="catalog-product-category">Panel Series</span>
                <a href="#" class="catalog-product-title">Slim LED Square Panel Light</a>
                <p class="catalog-product-desc">Ultra-slim architectural recessed ceiling panel with edge-lit technology and uniform light dispersion.</p>
                <div class="catalog-product-footer">
                    <span class="series-badge">Zola / Adjust Series</span>
                    <button onclick="openDealerModal(event)" class="btn-simko-outline" style="padding: 0.4rem 0.9rem; font-size: 0.8rem;">Enquire Now</button>
                </div>
            </div>
        </div>

        <!-- 2. Surface Light -->
        <div class="catalog-product-card">
            <div class="catalog-product-img-box">
                <img src="{{ asset('images/frontend/products/surface.jpg') }}" alt="Surface Light">
            </div>
            <div class="catalog-product-body">
                <span class="catalog-product-category">Surface Mounted</span>
                <a href="#" class="catalog-product-title">Cylindrical Ceiling Spot</a>
                <p class="catalog-product-desc">Modern surface mounted spotlight fixture engineered for accent wall and gallery lighting.</p>
                <div class="catalog-product-footer">
                    <span class="series-badge">Zeno / Orbit Series</span>
                    <button onclick="openDealerModal(event)" class="btn-simko-outline" style="padding: 0.4rem 0.9rem; font-size: 0.8rem;">Enquire Now</button>
                </div>
            </div>
        </div>

        <!-- 3. Down Light -->
        <div class="catalog-product-card">
            <div class="catalog-product-img-box">
                <img src="{{ asset('images/frontend/products/downlight.jpg') }}" alt="Down Light">
            </div>
            <div class="catalog-product-body">
                <span class="catalog-product-category">Downlight Series</span>
                <a href="#" class="catalog-product-title">Deep Anti-Glare COB Downlight</a>
                <p class="catalog-product-desc">High-CRI architectural downlight with anti-glare reflector and die-cast aluminum heat sink.</p>
                <div class="catalog-product-footer">
                    <span class="series-badge">Classic / Deep Series</span>
                    <button onclick="openDealerModal(event)" class="btn-simko-outline" style="padding: 0.4rem 0.9rem; font-size: 0.8rem;">Enquire Now</button>
                </div>
            </div>
        </div>

        <!-- 4. COB Light -->
        <div class="catalog-product-card">
            <div class="catalog-product-img-box">
                <img src="{{ asset('images/frontend/products/cob.jpg') }}" alt="COB Light">
            </div>
            <div class="catalog-product-body">
                <span class="catalog-product-category">COB Spotlight</span>
                <a href="#" class="catalog-product-title">Adjustable Track COB Light</a>
                <p class="catalog-product-desc">Premium high lumen output COB spotlight for showrooms, retail stores, and display windows.</p>
                <div class="catalog-product-footer">
                    <span class="series-badge">Sine / Zent Series</span>
                    <button onclick="openDealerModal(event)" class="btn-simko-outline" style="padding: 0.4rem 0.9rem; font-size: 0.8rem;">Enquire Now</button>
                </div>
            </div>
        </div>

        <!-- 5. Linear Light -->
        <div class="catalog-product-card">
            <div class="catalog-product-img-box">
                <img src="{{ asset('images/frontend/products/linear.jpg') }}" alt="Linear Light">
            </div>
            <div class="catalog-product-body">
                <span class="catalog-product-category">Linear Architecture</span>
                <a href="#" class="catalog-product-title">Suspended Magnetic Linear Light</a>
                <p class="catalog-product-desc">Seamless continuous line light system for executive offices, conference rooms, and commercial lobbies.</p>
                <div class="catalog-product-footer">
                    <span class="series-badge">Lino / Eltra Series</span>
                    <button onclick="openDealerModal(event)" class="btn-simko-outline" style="padding: 0.4rem 0.9rem; font-size: 0.8rem;">Enquire Now</button>
                </div>
            </div>
        </div>

        <!-- 6. Outdoor Light -->
        <div class="catalog-product-card">
            <div class="catalog-product-img-box">
                <img src="{{ asset('images/frontend/products/outdoor.jpg') }}" alt="Outdoor Light">
            </div>
            <div class="catalog-product-body">
                <span class="catalog-product-category">Outdoor Lighting</span>
                <a href="#" class="catalog-product-title">Heavy-Duty Outdoor LED Floodlight</a>
                <p class="catalog-product-desc">IP66 weatherproof rated outdoor floodlight for architectural building facades, parks, and stadiums.</p>
                <div class="catalog-product-footer">
                    <span class="series-badge">Flood / 4Way / Street</span>
                    <button onclick="openDealerModal(event)" class="btn-simko-outline" style="padding: 0.4rem 0.9rem; font-size: 0.8rem;">Enquire Now</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
