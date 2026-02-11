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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
            background: #f8f9fa;
            color: #1a1a1a;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 12px;
            border-bottom: 1px solid #e5e7eb;
            position: relative;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: #f98824;
            white-space: nowrap;
            transition: opacity 0.3s ease;
            letter-spacing: 0.5px;
        }

        .sidebar.collapsed .logo-text {
            opacity: 0;
            display: none;
        }

        .sidebar-toggle {
            background: transparent;
            border: none;
            color: #6b7280;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.2s ease;
            position: absolute;
            right: 12px;
        }

        .sidebar-toggle:hover {
            background: #f3f4f6;
            color: #1a1a1a;
        }

        .sidebar.collapsed .sidebar-toggle {
            transform: rotate(180deg);
        }

        .sidebar-nav {
            padding: 12px 8px;
        }

        .nav-item {
            position: relative;
            margin-bottom: 2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 9px 10px;
            color: #6b7280;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            font-size: 13px;
        }

        .nav-link:hover {
            background: #f3f4f6;
            color: #1a1a1a;
        }

        .nav-link.active {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            font-weight: 500;
        }

        .nav-icon {
            width: 18px;
            margin-right: 10px;
            font-size: 16px;
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
            font-size: 13px;
        }

        .sidebar.collapsed .nav-text {
            opacity: 0;
            display: none;
        }

        .nav-arrow {
            margin-left: auto;
            transition: transform 0.2s ease;
            font-size: 10px;
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
            padding-left: 10px;
        }

        .submenu.open {
            max-height: 400px;
        }

        .sidebar.collapsed .submenu {
            display: none;
        }

        .submenu-item {
            display: flex;
            align-items: center;
            padding: 7px 10px;
            color: #6b7280;
            text-decoration: none;
            border-radius: 5px;
            margin: 1px 0;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .submenu-item:hover {
            background: #f3f4f6;
            color: #1a1a1a;
            padding-left: 14px;
        }

        .submenu-item.active {
            background: #fef3f2;
            color: #ff6b35;
            font-weight: 500;
        }

        .submenu-icon {
            width: 4px;
            height: 4px;
            background: #9ca3af;
            border-radius: 50%;
            margin-right: 10px;
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
            left: 65px;
            top: 50%;
            transform: translateY(-50%);
            background: #1a1a1a;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            pointer-events: none;
            z-index: 1000;
        }

        .sidebar:not(.collapsed) .nav-tooltip {
            display: none;
        }

        /* Main Content */
        .main-content {
            margin-left: 240px;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        /* Header */
        .header {
            background: white;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 6px;
            height: 6px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 10px;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-profile:hover {
            background: #f3f4f6;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 13px;
        }

        .user-info {
            text-align: left;
        }

        .user-name {
            font-size: 13px;
            font-weight: 500;
            color: #1a1a1a;
        }

        .user-role {
            font-size: 11px;
            color: #6b7280;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 15px;
        }

        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #1a1a1a;
        }

        /* User Dropdown Menu */
        .user-menu {
            display: none;
            position: absolute;
            top: 60px;
            right: 24px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1000;
            min-width: 180px;
            border: 1px solid #e5e7eb;
        }

        .user-menu.show {
            display: block;
        }

        .user-menu-header {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .user-menu-name {
            font-weight: 500;
            color: #1a1a1a;
            font-size: 13px;
        }

        .user-menu-email {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .user-menu-actions {
            padding: 8px;
        }

        .logout-btn {
            width: 100%;
            padding: 8px;
            background: #fef2f2;
            color: #dc2626;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: #fee2e2;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-240px);
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
                padding: 0 16px;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
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
                    {{-- <a href="{{ url('/admin/products/create') }}" class="submenu-item {{ request()->is('admin/products/create') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Add Product
                    </a> --}}
                    <a href="{{ route('admin.attributes.index') }}" class="submenu-item {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Attributes
                    </a>
                    <a href="{{ url('/admin/barcode') }}" class="submenu-item {{ request()->is('admin/barcode*') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Print Barcode
                    </a>
                    <a href="{{ url('/admin/categories') }}" class="submenu-item {{ request()->is('admin/categories') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Categories
                    </a>
                </div>
            </div>

            <!-- Warehouses with Submenu -->
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
                    <a href="{{ url('admin/godown') }}" class="submenu-item {{ request()->is('admin/godown') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        View Godown
                    </a>
                    {{-- <a href="{{ url('/admin/warehouses/create') }}" class="submenu-item {{ request()->is('admin/warehouses/create') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Add Warehouse
                    </a> --}}
                </div>
            </div>
            <!-- Customers with Submenu -->
            <div class="nav-item">
                <div class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}" onclick="toggleSubmenu(this)">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Customers</span>
                    <span class="nav-arrow">▶</span>
                </div>
                <div class="submenu">
                    <a href="{{ url('/admin/customers') }}" class="submenu-item {{ request()->is('admin/customers') && !request()->is('admin/customers/create') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        View Customers
                    </a>
                </div>
            </div>

            <!-- Sales with Submenu -->
            <div class="nav-item">
                <div class="nav-link {{ request()->is('admin/sales*') ? 'active' : '' }}" onclick="toggleSubmenu(this)">
                    <span class="nav-icon">🛍️</span>
                    <span class="nav-text">Sales</span>
                    <span class="nav-arrow">▶</span>
                </div>
                <div class="submenu">
                     <a href="{{ url('/admin/sales') }}" class="submenu-item {{ request()->is('admin/sales') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        All Sales
                    </a>
                     <a href="{{ url('/admin/invoice-settings') }}" class="submenu-item {{ request()->is('admin/invoice-settings') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Invoice Settings
                    </a>
                    {{-- <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        Pending Orders
                    </a>
                    <a href="#" class="submenu-item">
                        <span class="submenu-icon"></span>
                        Completed Orders
                    </a> --}}
                </div>
            </div>

            <!-- Purchase Orders with Submenu -->
            <div class="nav-item">
                <div class="nav-link {{ request()->is('admin/purchase-orders*') ? 'active' : '' }}" onclick="toggleSubmenu(this)">
                    <span class="nav-icon">📋</span>
                    <span class="nav-text">Purchase Orders</span>
                    <span class="nav-arrow">▶</span>
                </div>
                <div class="submenu">
                     <a href="{{ url('/admin/purchases') }}" class="submenu-item {{ request()->is('admin/purchases') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        All Purchase Orders
                    </a>
                     <a href="{{ url('/admin/suppliers') }}" class="submenu-item {{ request()->is('admin/suppliers') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        All Suppliers
                    </a>
                     <a href="{{ url('/admin/supplier-payments') }}" class="submenu-item {{ request()->is('admin/supplier-payments') ? 'active' : '' }}">
                        <span class="submenu-icon"></span>
                        Supplier Payments
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>
