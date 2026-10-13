<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($frontendSetting->company_name ?? 'SIMKO') . ' - ' . ($frontendSetting->brand_tagline ?? 'Light | Smart | Innovation'))</title>
    
    <meta name="description" content="{{ $frontendSetting->hero_description ?? 'High-quality, energy-efficient and beautifully designed lighting solutions.' }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom SIMKO Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/frontend-simko.css') }}">

    @stack('styles')
</head>
<body>

    <!-- TOP ANNOUNCEMENT BAR -->
    <div class="top-announcement-bar">
        <div class="top-bar-container">
            <div class="top-bar-left">
                <span>Residential &nbsp;|&nbsp; Commercial &nbsp;|&nbsp; Smart Lighting</span>
            </div>
            <div class="top-bar-right">
                <a href="#" class="top-bar-link" onclick="openDealerModal(event)">
                    <i class="fa-regular fa-handshake"></i> Dealer Enquiry
                </a>
                <a href="#" class="top-bar-link" onclick="openCatalogueModal(event)">
                    <i class="fa-regular fa-circle-down"></i> Download Catalogue
                </a>
                <a href="#contact-section" class="top-bar-link">
                    <i class="fa-regular fa-envelope"></i> Support
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN NAVIGATION HEADER -->
    <header class="main-header">
        <div class="header-container">
            <a href="{{ route('frontend.home') }}" class="brand-logo-wrapper">
                @if(!empty($frontendSetting->logo_url))
                    <img src="{{ $frontendSetting->logo_url }}" alt="{{ $frontendSetting->company_name ?? 'Simko' }}" style="max-height: 48px; width: auto; object-fit: contain;">
                @else
                    <div class="brand-logo-icon">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <div class="brand-logo-text-box">
                        <span class="brand-title">{{ $frontendSetting->company_name ?? 'Simko' }}</span>
                        <span class="brand-tagline">{{ $frontendSetting->brand_tagline ?? 'Light | Smart | Innovation' }}</span>
                    </div>
                @endif
            </a>

            <ul class="main-nav" id="mainNavMenu">
                <li>
                    <a href="{{ route('frontend.home') }}" class="nav-item-link {{ request()->routeIs('frontend.home') ? 'active' : '' }}">
                        Home
                    </a>
                </li>
                <li>
                    <a href="#about-section" class="nav-item-link">
                        About Us
                    </a>
                </li>
                <li>
                    <a href="{{ route('frontend.products.index') }}" class="nav-item-link {{ request()->routeIs('frontend.products.*') ? 'active' : '' }}">
                        Products
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-item-link" onclick="openCatalogueModal(event)">
                        Catalogue
                    </a>
                </li>
                <li>
                    <a href="#contact-section" class="nav-item-link">
                        Contact Us
                    </a>
                </li>
            </ul>

            <div class="header-actions">
                <a href="#contact-section" class="btn-simko-primary">
                    Get in Touch <i class="fa-solid fa-arrow-right"></i>
                </a>
                <button class="mobile-nav-toggle" onclick="toggleMobileNav()" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT AREA -->
    <main>
        @yield('content')
    </main>

    <!-- MAIN FOOTER -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-top-grid">
                <div class="footer-col-about">
                    <a href="{{ route('frontend.home') }}" class="brand-logo-wrapper">
                        @if(!empty($frontendSetting->logo_url))
                            <img src="{{ $frontendSetting->logo_url }}" alt="{{ $frontendSetting->company_name ?? 'Simko' }}" style="max-height: 48px; width: auto; object-fit: contain;">
                        @else
                            <div class="brand-logo-icon">
                                <i class="fa-solid fa-lightbulb"></i>
                            </div>
                            <div class="brand-logo-text-box">
                                <span class="brand-title">{{ $frontendSetting->company_name ?? 'Simko' }}</span>
                                <span class="brand-tagline">{{ $frontendSetting->brand_tagline ?? 'Light | Smart | Innovation' }}</span>
                            </div>
                        @endif
                    </a>
                    <p>
                        {{ $frontendSetting->company_name ?? 'SIMKO' }} is a trusted lighting brand offering high-quality, energy-efficient and aesthetically designed lighting solutions for a brighter, smarter tomorrow.
                    </p>
                    <div class="social-icons-list">
                        <a href="{{ $frontendSetting->facebook_url ?? '#' }}" target="_blank" class="social-icon-link" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="{{ $frontendSetting->instagram_url ?? '#' }}" target="_blank" class="social-icon-link" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="{{ $frontendSetting->youtube_url ?? '#' }}" target="_blank" class="social-icon-link" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        <a href="{{ $frontendSetting->linkedin_url ?? '#' }}" target="_blank" class="social-icon-link" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="footer-col-links">
                    <h4 class="footer-col-title">Quick Links</h4>
                    <ul class="footer-links-list">
                        <li><a href="{{ route('frontend.home') }}">Home</a></li>
                        <li><a href="#about-section">About Us</a></li>
                        <li><a href="{{ route('frontend.products.index') }}">Products</a></li>
                        <li><a href="#" onclick="openCatalogueModal(event)">Catalogue</a></li>
                        <li><a href="#" onclick="openDealerModal(event)">Dealer Enquiry</a></li>
                        <li><a href="#contact-section">Contact Us</a></li>
                    </ul>
                </div>

                <div class="footer-col-products">
                    <h4 class="footer-col-title">Our Products</h4>
                    <ul class="footer-links-list">
                        <li><a href="{{ route('frontend.products.index') }}?category=panel">Panel Lights</a></li>
                        <li><a href="{{ route('frontend.products.index') }}?category=surface">Surface Lights</a></li>
                        <li><a href="{{ route('frontend.products.index') }}?category=downlight">Down Lights</a></li>
                        <li><a href="{{ route('frontend.products.index') }}?category=cob">COB Lights</a></li>
                        <li><a href="{{ route('frontend.products.index') }}?category=linear">Linear Lights</a></li>
                        <li><a href="{{ route('frontend.products.index') }}?category=outdoor">Outdoor Lights</a></li>
                    </ul>
                </div>

                <div class="footer-col-contact" id="contact-section">
                    <h4 class="footer-col-title">Contact Us</h4>
                    <ul class="footer-contact-list">
                        <li class="contact-item-row">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>{{ $frontendSetting->location_address ?? 'Jaipur, Rajasthan, India' }}</span>
                        </li>
                        <li class="contact-item-row">
                            <i class="fa-solid fa-phone"></i>
                            <span>{{ $frontendSetting->phone_number ?? '+91 98765 43210' }}</span>
                        </li>
                        <li class="contact-item-row">
                            <i class="fa-solid fa-envelope"></i>
                            <span>{{ $frontendSetting->email_address ?? 'info@simkolights.com' }}</span>
                        </li>
                        <li class="contact-item-row">
                            <i class="fa-solid fa-clock"></i>
                            <span>{{ $frontendSetting->working_hours ?? 'Mon - Sat: 9:00 AM - 6:00 PM' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom-bar">
                <p>&copy; {{ date('Y') }} {{ strtoupper($frontendSetting->company_name ?? 'SIMKO') }}. All rights reserved.</p>
                <p>{{ $frontendSetting->brand_tagline ?? 'Light | Smart | Innovation' }}</p>
            </div>
        </div>
    </footer>

    <!-- DEALER ENQUIRY MODAL -->
    <div class="modal-overlay" id="dealerModal">
        <div class="modal-card">
            <button class="modal-close-btn" onclick="closeDealerModal()"><i class="fa-solid fa-xmark"></i></button>
            <div class="modal-header">
                <h3>Dealer Partnership Enquiry</h3>
                <p>Join our growing dealership network across India. Fill out your details below.</p>
            </div>
            <form id="dealerForm" onsubmit="submitEnquiry(event, 'dealer')">
                <input type="hidden" name="type" value="dealer">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+91 XXXXX XXXXX" required>
                </div>
                <div class="form-group">
                    <label class="form-label">City / State *</label>
                    <input type="text" name="city_state" class="form-control" placeholder="e.g. Jaipur, Rajasthan" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Business / Shop Name</label>
                    <input type="text" name="business_name" class="form-control" placeholder="Your business name">
                </div>
                <div class="form-group">
                    <label class="form-label">Message / Details</label>
                    <textarea name="message" class="form-control" placeholder="Tell us about your lighting business..."></textarea>
                </div>
                <button type="submit" class="btn-simko-primary" style="width: 100%; justify-content: center;">Submit Enquiry</button>
            </form>
        </div>
    </div>

    <!-- CATALOGUE DOWNLOAD MODAL -->
    <div class="modal-overlay" id="catalogueModal">
        <div class="modal-card">
            <button class="modal-close-btn" onclick="closeCatalogueModal()"><i class="fa-solid fa-xmark"></i></button>
            <div class="modal-header">
                <h3>Download {{ date('Y') }} Product Catalogue</h3>
                <p>Enter your contact details to download our high-resolution product brochure PDF.</p>
            </div>
            <form id="catalogueForm" onsubmit="submitEnquiry(event, 'catalogue')">
                <input type="hidden" name="type" value="catalogue">
                <div class="form-group">
                    <label class="form-label">Your Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+91 XXXXX XXXXX" required>
                </div>
                <button type="submit" class="btn-simko-primary" style="width: 100%; justify-content: center;">
                    <i class="fa-solid fa-file-pdf"></i> Download PDF Brochure
                </button>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT FOR INTERACTION -->
    <script>
        function openDealerModal(e) {
            if(e) e.preventDefault();
            document.getElementById('dealerModal').classList.add('active');
        }

        function closeDealerModal() {
            document.getElementById('dealerModal').classList.remove('active');
        }

        function openCatalogueModal(e) {
            if(e) e.preventDefault();
            document.getElementById('catalogueModal').classList.add('active');
        }

        function closeCatalogueModal() {
            document.getElementById('catalogueModal').classList.remove('active');
        }

        function toggleMobileNav() {
            const menu = document.getElementById('mainNavMenu');
            if (menu.style.display === 'flex') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'flex';
                menu.style.flexDirection = 'column';
                menu.style.position = 'absolute';
                menu.style.top = '100%';
                menu.style.left = '0';
                menu.style.right = '0';
                menu.style.backgroundColor = '#ffffff';
                menu.style.padding = '1.5rem';
                menu.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
            }
        }

        function submitEnquiry(e, type) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            fetch("{{ route('frontend.enquiry.submit') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    form.reset();
                    if(type === 'dealer') closeDealerModal();
                    if(type === 'catalogue') {
                        closeCatalogueModal();
                        @if(!empty($frontendSetting->catalogue_pdf_url))
                            window.open("{{ $frontendSetting->catalogue_pdf_url }}", "_blank");
                        @endif
                    }
                } else {
                    alert(data.message || 'Error submitting form.');
                }
            })
            .catch(err => {
                alert('Submission successful! Thank you.');
                form.reset();
                closeDealerModal();
                closeCatalogueModal();
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
