<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel - E-Commerce')</title>
     <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #2d3748;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
            padding: 20px 0;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: 100px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 25px;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo-icon img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 18px;
            font-weight: 700;
            color: white;
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .logo-text {
            opacity: 0;
            display: none;
        }

        .sidebar-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .sidebar.collapsed .sidebar-toggle {
            transform: rotate(180deg);
        }

        .sidebar-nav {
            padding: 0 15px;
        }

        .nav-item {
            position: relative;
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 14px 15px;
            color: #cbd5e0;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link.active {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
        }

        .nav-icon {
            width: 22px;
            margin-right: 14px;
            font-size: 18px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar.collapsed .nav-icon {
            margin-right: 0;
        }

        .nav-text {
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .nav-text {
            opacity: 0;
            display: none;
        }

        .nav-arrow {
            margin-left: auto;
            transition: transform 0.3s ease;
            font-size: 12px;
        }

        .sidebar.collapsed .nav-arrow {
            display: none;
        }

        .nav-link.expanded .nav-arrow {
            transform: rotate(90deg);
        }

        /* Submenu Styles */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding-left: 15px;
        }

        .submenu.open {
            max-height: 500px;
        }

        .sidebar.collapsed .submenu {
            display: none;
        }

        .submenu-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #a0aec0;
            text-decoration: none;
            border-radius: 8px;
            margin: 3px 0;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .submenu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            padding-left: 20px;
        }

        .submenu-item.active {
            background: rgba(255, 107, 53, 0.2);
            color: #ff6b35;
            font-weight: 600;
        }

        .submenu-icon {
            width: 6px;
            height: 6px;
            background: #a0aec0;
            border-radius: 50%;
            margin-right: 12px;
        }

        .submenu-item.active .submenu-icon {
            background: #ff6b35;
        }

        /* Tooltip for collapsed sidebar */
        .nav-item:hover .nav-tooltip {
            opacity: 1;
            visibility: visible;
        }

        .nav-tooltip {
            position: absolute;
            left: 75px;
            top: 50%;
            transform: translateY(-50%);
            background: #1a202c;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 1000;
        }

        .sidebar:not(.collapsed) .nav-tooltip {
            display: none;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        /* Header */
        .header {
            background: white;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 35px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title {
            font-size: 26px;
            font-weight: 700;
            color: #2d3748;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-btn {
            position: relative;
            width: 42px;
            height: 42px;
            background: #f7fafc;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .notification-btn:hover {
            background: #edf2f7;
            transform: scale(1.05);
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #f56565;
            border-radius: 50%;
            border: 2px solid white;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 15px;
            background: #f7fafc;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background: #edf2f7;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .user-info {
            text-align: left;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
        }

        .user-role {
            font-size: 12px;
            color: #718096;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 35px;
        }

        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #2d3748;
        }

        /* User Dropdown Menu */
        .user-menu {
            display: none;
            position: absolute;
            top: 70px;
            right: 35px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            z-index: 1000;
            min-width: 200px;
        }

        .user-menu.show {
            display: block;
        }

        .user-menu-header {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .user-menu-name {
            font-weight: 600;
            color: #2d3748;
        }

        .user-menu-email {
            font-size: 12px;
            color: #718096;
            margin-top: 4px;
        }

        .user-menu-actions {
            padding: 10px;
        }

        .logout-btn {
            width: 100%;
            padding: 10px;
            background: #fed7d7;
            color: #9b2c2c;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #fc8181;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-260px);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .header {
                padding: 0 20px;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-icon">
                    <!-- Replace this with your company logo -->
                    <img src="{{ asset('storage/logos/company-logo.png') }}" alt="Logo" onerror="this.parentElement.innerHTML='🛒'">
                </div>
                <div class="logo-text">Simko</div>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">☰</button>
        </div>

        <nav class="sidebar-nav">
            <!-- Dashboard -->
            <div class="nav-item">
                <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">🏠</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <span class="nav-tooltip">Dashboard</span>
            </div>

            <!-- Products with Submenu -->
            <div class="nav-item">
                <div class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}" onclick="toggleSubmenu(this)">
                    <span class="nav-icon">📦</span>
                    <span class="nav-text">Products</span>
                    <span class="nav-arrow">▶</span>
                </div>
                <div class="submenu">
                    <a href="{{ url('/admin/products') }}" class="submenu-item {{ request()->is('admin/products') && !request()->is('admin/products/create') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        View Products
                    </a>
                    <a href="{{ url('/admin/products/create') }}" class="submenu-item {{ request()->is('admin/products/create') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Add Product
                    </a>
                    <a href="{{ url('/admin/barcode') }}" class="submenu-item {{ request()->is('admin/barcode*') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Print Barcode
                    </a>
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        Categories
                    </a>
                </div>
            </div>
            <div class="nav-item">
                <div class="nav-link {{ request()->is('admin/warehouses*') ? 'active' : '' }}" onclick="toggleSubmenu(this)">
                    <span class="nav-icon">🏢</span>
                    <span class="nav-text">Warehouses</span>
                    <span class="nav-arrow">▶</span>
                </div>
                <div class="submenu">
                    <a href="{{ url('/admin/warehouses') }}" class="submenu-item {{ request()->is('admin/warehouses') && !request()->is('admin/warehouses/create') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        View Warehouses
                    </a>
                    <a href="{{ url('/admin/warehouses/create') }}" class="submenu-item {{ request()->is('admin/warehouses/create') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Add Warehouse
                    </a>
                </div>
            </div>

            <!-- Orders with Submenu -->
            <div class="nav-item">
                <div class="nav-link" onclick="toggleSubmenu(this)">
                    <span class="nav-icon">🛍️</span>
                    <span class="nav-text">Orders</span>
                    <span class="nav-arrow">▶</span>
                </div>
                <div class="submenu">
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        All Orders
                    </a>
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        Pending Orders
                    </a>
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        Completed Orders
                    </a>
                </div>
            </div>

            <!-- Customers with Submenu -->
            <div class="nav-item">
                <div class="nav-link" onclick="toggleSubmenu(this)">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Customers</span>
                    <span class="nav-arrow">▶</span>
                </div>
                <div class="submenu">
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        All Customers
                    </a>
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        Add Customer
                    </a>
                </div>
            </div>

            <!-- Reports -->
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Reports</span>
                </a>
                <span class="nav-tooltip">Reports</span>
            </div>

            <!-- Settings with Submenu -->
            <div class="nav-item">
                <div class="nav-link" onclick="toggleSubmenu(this)">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-text">Settings</span>
                    <span class="nav-arrow">▶</span>
                </div>
                <div class="submenu">
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        General Settings
                    </a>
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        Payment Settings
                    </a>
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        Shipping Settings
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <header class="header">
            <button class="menu-toggle" id="menuToggle">☰</button>
            <h1 class="header-title">@yield('header-title', 'Dashboard')</h1>

            <div class="header-right">
                <button class="notification-btn">
                    🔔
                    <span class="notification-badge"></span>
                </button>

                <div class="user-profile" onclick="toggleUserMenu()">
                    <div class="user-avatar">{{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::guard('admin')->user()->name ?? 'Admin User' }}</div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <span>▼</span>
                </div>

                <!-- User Dropdown Menu -->
                <div class="user-menu" id="userMenu">
                    <div class="user-menu-header">
                        <div class="user-menu-name">{{ Auth::guard('admin')->user()->name }}</div>
                        <div class="user-menu-email">{{ Auth::guard('admin')->user()->email }}</div>
                    </div>
                    <div class="user-menu-actions">
                        <form method="POST" action="{{ url('/admin/logout') }}">
                            @csrf
                            <button type="submit" class="logout-btn">🚪 Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="dashboard-content">
            @yield('content')
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const menuToggle = document.getElementById('menuToggle');

        // Desktop sidebar collapse
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');

            // Close all submenus when collapsing
            if (sidebar.classList.contains('collapsed')) {
                document.querySelectorAll('.submenu').forEach(submenu => {
                    submenu.classList.remove('open');
                });
                document.querySelectorAll('.nav-link.expanded').forEach(link => {
                    link.classList.remove('expanded');
                });
            }
        });

        // Mobile menu toggle
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        });

        // Submenu toggle function
        function toggleSubmenu(element) {
            // Don't toggle if sidebar is collapsed
            if (sidebar.classList.contains('collapsed')) {
                return;
            }

            // Find the submenu - it's the next sibling after the tooltip
            let submenu = element.nextElementSibling;

            // Skip the tooltip if it exists
            while (submenu && !submenu.classList.contains('submenu')) {
                submenu = submenu.nextElementSibling;
            }

            if (submenu && submenu.classList.contains('submenu')) {
                // Close other open submenus
                document.querySelectorAll('.submenu.open').forEach(openSubmenu => {
                    if (openSubmenu !== submenu) {
                        openSubmenu.classList.remove('open');
                        const parentNavItem = openSubmenu.closest('.nav-item');
                        const navLink = parentNavItem.querySelector('.nav-link');
                        if (navLink) {
                            navLink.classList.remove('expanded');
                        }
                    }
                });

                // Toggle current submenu
                submenu.classList.toggle('open');
                element.classList.toggle('expanded');
            }
        }

        // Auto-open submenu if on a submenu page
        document.addEventListener('DOMContentLoaded', () => {
            const activeSubmenuItem = document.querySelector('.submenu-item.active');
            if (activeSubmenuItem) {
                const submenu = activeSubmenuItem.closest('.submenu');
                const navLink = submenu.previousElementSibling;
                submenu.classList.add('open');
                navLink.classList.add('expanded');
            }
        });

        // User menu toggle
        function toggleUserMenu() {
            const userMenu = document.getElementById('userMenu');
            userMenu.classList.toggle('show');
        }

        // Close user menu when clicking outside
        document.addEventListener('click', (e) => {
            const userMenu = document.getElementById('userMenu');
            const userProfile = document.querySelector('.user-profile');

            if (userProfile && !userProfile.contains(e.target) && userMenu && !userMenu.contains(e.target)) {
                userMenu.classList.remove('show');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
