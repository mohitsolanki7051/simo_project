<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel - E-Commerce')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-name" content="{{ config('app.name') }}">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="{{ asset('css/voice-commands.css') }}">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --accent: #f98824;
            --accent-dark: #e06a12;
            --accent-bg: #fff4ec;
            --sidebar-w: 240px;
            --sidebar-w-col: 64px;
            --border: #e8e5df;
            --text: #1a1816;
            --text-2: #6b6560;
            --text-3: #b0aa9f;
            --bg: #f5f4f0;
            --surface: #ffffff;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
            background: #f8f9fa;
            color: #1a1a1a;
        }

        /* ════════ SIDEBAR ════════ */
        .sidebar {
            position: fixed;
            left: 0; top: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            z-index: 1000;
            transition: width 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .sidebar.collapsed { width: var(--sidebar-w-col); }

        /* Logo */
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            height: 56px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .logo-wrap { display: flex; align-items: center; gap: 8px; overflow: hidden; }
        .logo-icon {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 14px;
        }
        .logo-text {
            font-size: 15px; font-weight: 700; color: var(--accent);
            white-space: nowrap; letter-spacing: 0.3px;
            transition: opacity 0.2s;
        }
        .sidebar.collapsed .logo-text { opacity: 0; pointer-events: none; }

        .sidebar-toggle {
            width: 26px; height: 26px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 7px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-2); flex-shrink: 0; transition: all 0.2s;
        }
        .sidebar-toggle:hover { background: var(--bg); color: var(--text); }
        .sidebar-toggle svg { transition: transform 0.3s; }
        .sidebar.collapsed .sidebar-toggle svg { transform: rotate(180deg); }

        /* ── Search inside sidebar ── */
        .sidebar-search {
            padding: 9px 10px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
            cursor: pointer;
        }
        .sb-search-wrap { position: relative; display: flex; align-items: center; }
        .sb-search-icon {
            position: absolute; left: 9px;
            color: var(--text-3); pointer-events: none;
            display: flex; z-index: 1;
            transition: left 0.3s;
        }
        .sb-search-fake {
            width: 100%;
            padding: 7px 56px 7px 30px;
            border: 1.5px solid var(--border);
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            color: var(--text-3);
            background: var(--bg);
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            user-select: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .sb-search-fake:hover {
            border-color: #ccc8c0;
            background: #efede8;
        }
        .sb-kbd {
            position: absolute; right: 8px;
            display: flex; align-items: center; gap: 2px;
            pointer-events: none;
        }
        .kbd {
            background: white; border: 1px solid var(--border);
            border-radius: 4px; padding: 1px 5px;
            font-size: 9.5px; color: var(--text-3);
        }

        /* Collapsed: hide text, center icon */
        .sidebar.collapsed .sb-search-fake { opacity: 0; }
        .sidebar.collapsed .sb-kbd { display: none; }
        .sidebar.collapsed .sb-search-icon { left: 50%; transform: translateX(-50%); }

        /* ── Search Modal Overlay ── */
        .search-modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(26,24,22,0.4);
            z-index: 8000;
            backdrop-filter: blur(2px);
            align-items: flex-start;
            justify-content: center;
            padding-top: 80px;
        }
        .search-modal-overlay.show { display: flex; }

        .search-modal {
            width: 480px;
            max-width: calc(100vw - 32px);
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
            animation: modalIn 0.15s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(-12px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .sm-input-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
        }
        .sm-input-row svg { color: var(--text-3); flex-shrink: 0; }
        .sm-input {
            flex: 1;
            border: none; outline: none;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            background: transparent;
        }
        .sm-input::placeholder { color: var(--text-3); }
        .sm-esc {
            background: var(--bg); border: 1px solid var(--border);
            border-radius: 5px; padding: 2px 7px;
            font-size: 10px; color: var(--text-3); cursor: pointer;
            white-space: nowrap; flex-shrink: 0;
        }

        .sm-body {
            max-height: 360px;
            overflow-y: auto;
            padding: 8px;
        }
        .sm-body::-webkit-scrollbar { width: 3px; }
        .sm-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

        .sm-group-label {
            padding: 6px 10px 3px;
            font-size: 9.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: var(--text-3);
        }

        .sm-result {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 10px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text);
            cursor: pointer;
            transition: background 0.1s;
        }
        .sm-result:hover, .sm-result.focused { background: var(--bg); }

        .sm-result-icon {
            width: 32px; height: 32px;
            background: var(--accent-bg);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
        }
        .sm-result-name { font-size: 13px; font-weight: 500; }
        .sm-result-group { font-size: 11px; color: var(--text-3); }
        .sm-enter { font-size: 11px; color: var(--text-3); margin-left: auto; flex-shrink: 0; }

        .sm-empty {
            padding: 28px;
            text-align: center;
            color: var(--text-3);
            font-size: 13px;
        }
        .sm-empty-icon { font-size: 28px; margin-bottom: 8px; }

        .sm-footer {
            padding: 9px 16px;
            border-top: 1px solid var(--border);
            background: var(--bg);
            display: flex;
            gap: 16px;
            font-size: 10.5px;
            color: var(--text-3);
        }
        .sm-footer span { display: flex; align-items: center; gap: 4px; }
        .sm-footer .kbd { font-size: 9.5px; }

        /* ════════ NAV ════════ */
        .sidebar-nav {
            flex: 1; overflow-y: auto; overflow-x: hidden; padding: 8px;
        }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

        .nav-section-label {
            font-size: 9px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.9px;
            color: var(--text-3);
            padding: 10px 10px 3px;
            white-space: nowrap; transition: opacity 0.2s;
        }
        .sidebar.collapsed .nav-section-label { opacity: 0; }

        .nav-item { position: relative; margin-bottom: 1px; }
        .nav-link {
            display: flex; align-items: center;
            padding: 8px 10px;
            color: var(--text-2);
            text-decoration: none;
            border-radius: 7px;
            transition: all 0.15s ease;
            cursor: pointer; white-space: nowrap;
            gap: 9px; font-size: 12.5px; font-weight: 400;
            user-select: none;
        }
        .nav-link:hover { background: var(--bg); color: var(--text); }
        .nav-link.parent-active { color: var(--accent); background: var(--accent-bg); font-weight: 500; }
        .nav-link.active { background: linear-gradient(135deg, #ff6b35, #f7931e); color: white; font-weight: 500; }

        .nav-icon { width: 18px; height: 18px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .nav-text { flex: 1; overflow: hidden; transition: opacity 0.2s; }
        .sidebar.collapsed .nav-text { opacity: 0; }

        .nav-arrow { width: 14px; height: 14px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s, opacity 0.2s; flex-shrink: 0; color: var(--text-3); }
        .nav-link.expanded .nav-arrow { transform: rotate(90deg); }
        .sidebar.collapsed .nav-arrow { opacity: 0; }

        .submenu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .submenu.open { max-height: 500px; }
        .sidebar.collapsed .submenu { display: none; }
        .submenu-inner { padding: 2px 0 4px 10px; }

        .submenu-item {
            display: flex; align-items: center; gap: 8px;
            padding: 6.5px 10px;
            color: var(--text-2); text-decoration: none;
            border-radius: 6px; font-size: 12px;
            transition: all 0.15s ease; white-space: nowrap;
        }
        .submenu-item:hover { background: var(--bg); color: var(--text); }
        .submenu-item.active { color: var(--accent); background: var(--accent-bg); font-weight: 500; }

        .submenu-dot { width: 4px; height: 4px; border-radius: 50%; background: var(--border); flex-shrink: 0; transition: background 0.2s; }
        .submenu-item.active .submenu-dot { background: var(--accent); }

        .nav-tooltip {
            position: absolute;
            left: calc(var(--sidebar-w-col) + 6px); top: 50%; transform: translateY(-50%);
            background: #1a1816; color: white;
            padding: 5px 10px; border-radius: 6px;
            font-size: 11.5px; white-space: nowrap;
            opacity: 0; visibility: hidden; pointer-events: none;
            transition: all 0.15s ease; z-index: 2000;
        }
        .nav-tooltip::before {
            content: ''; position: absolute; right: 100%; top: 50%; transform: translateY(-50%);
            border: 5px solid transparent; border-right-color: #1a1816;
        }
        .sidebar.collapsed .nav-item:hover .nav-tooltip { opacity: 1; visibility: visible; }

        /* ════════ MAIN ════════ */
        .main-content { margin-left: var(--sidebar-w); min-height: 100vh; transition: margin-left 0.3s cubic-bezier(0.4,0,0.2,1); }
        .main-content.expanded { margin-left: var(--sidebar-w-col); }

        .header {
            background: var(--surface); height: 56px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 20px; border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 100;
        }
        .header-left { display: flex; align-items: center; gap: 10px; }

        .menu-toggle {
            display: none; background: none;
            border: 1px solid var(--border);
            width: 30px; height: 30px; border-radius: 7px;
            cursor: pointer; color: var(--text-2);
            align-items: center; justify-content: center; flex-shrink: 0;
        }

        .header-title { font-size: 14px; font-weight: 600; color: var(--text); letter-spacing: -0.2px; }
        .header-right { display: flex; align-items: center; gap: 8px; }

        .header-btn {
            width: 32px; height: 32px; background: transparent;
            border: 1.5px solid var(--border); border-radius: 8px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: var(--text-2); transition: all 0.15s; position: relative;
        }
        .header-btn:hover { background: var(--bg); color: var(--text); border-color: #ccc8c0; }
        .notif-dot { position: absolute; top: 5px; right: 5px; width: 6px; height: 6px; background: #ef4444; border-radius: 50%; border: 1.5px solid white; }

        .user-profile {
            display: flex; align-items: center; gap: 8px;
            padding: 4px 10px 4px 5px; border: 1.5px solid var(--border);
            border-radius: 9px; cursor: pointer; transition: all 0.15s; position: relative;
        }
        .user-profile:hover { background: var(--bg); border-color: #ccc8c0; }
        .user-avatar { width: 26px; height: 26px; background: linear-gradient(135deg, #ff6b35, #f7931e); border-radius: 7px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 11px; }
        .user-name { font-size: 12px; font-weight: 500; color: var(--text); }
        .user-role { font-size: 10px; color: var(--text-3); }
        .user-chevron { color: var(--text-3); transition: transform 0.2s; }
        .user-profile.open .user-chevron { transform: rotate(180deg); }

        .user-menu { display: none; position: absolute; top: calc(100% + 8px); right: 0; background: white; border: 1.5px solid var(--border); border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); z-index: 1000; min-width: 200px; overflow: hidden; }
        .user-menu.show { display: block; }
        .user-menu-header { padding: 11px 14px; border-bottom: 1px solid var(--border); background: var(--bg); }
        .user-menu-name { font-weight: 600; font-size: 12.5px; color: var(--text); }
        .user-menu-email { font-size: 10.5px; color: var(--text-3); margin-top: 2px; }
        .user-menu-body { padding: 6px; }
        .user-menu-item { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 7px; font-size: 12px; color: var(--text-2); cursor: pointer; transition: all 0.15s; text-decoration: none; border: none; background: none; width: 100%; font-family: 'DM Sans', sans-serif; }
        .user-menu-item:hover { background: var(--bg); color: var(--text); }
        .user-menu-item.danger { color: #dc2626; }
        .user-menu-item.danger:hover { background: #fef2f2; }
        .mi-icon { width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: var(--bg); font-size: 12px; }
        .user-menu-item.danger .mi-icon { background: #fef2f2; }
        .user-menu-divider { height: 1px; background: var(--border); margin: 4px 0; }

        .dashboard-content { padding: 15px; }

        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 999; backdrop-filter: blur(2px); }
        .sidebar-overlay.show { display: block; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(calc(-1 * var(--sidebar-w))); width: var(--sidebar-w) !important; }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            .menu-toggle { display: flex; }
        }


/* ===========================
   MOBILE NAV PREMIUM
=========================== */

.mobile-bottom-nav,
.fab-menu{
    display:none;
}

@media (max-width:768px){

    .mobile-bottom-nav{
        position:fixed;
        left:0;
        bottom:0;
        width:100%;
        height:60px;
        background:#ffffff;
        border-top: 1px solid #e5e7eb;
        display:flex;
        align-items:center;
        justify-content:space-around;
        padding:0 8px;
        z-index:9999;
        box-shadow: 0 -4px 12px rgba(0,0,0,.05);
    }

    .mobile-nav-item{
        flex:1;
        height:100%;
        text-decoration:none;
        color:#64748b;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:3px;
        transition:all .2s ease-in-out;
        position:relative;
    }

    .mobile-nav-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 3px;
        background: #2563eb;
        border-radius: 0 0 4px 4px;
        transition: width 0.2s ease, box-shadow 0.2s ease;
    }

    .mobile-nav-item.active::before {
        width: 50%;
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.8), 0 0 20px rgba(37, 99, 235, 0.4);
    }

    .mobile-nav-item.active,
    .mobile-nav-item:hover {
        color: #2563eb;
    }

    .mobile-nav-item span{
        font-size:18px;
        line-height:1;
        transition: transform 0.2s ease;
    }
    
    .mobile-nav-item:active span {
        transform: scale(0.9);
    }

    .mobile-nav-item small{
        font-size:10px;
        font-weight:600;
    }

    .fab-btn{
        width: 46px;
        height: 46px;
        border:none;
        border-radius:50%;
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        color:#fff;
        font-size:24px;
        font-weight:600;
        cursor:pointer;
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        z-index: 10001;
        margin-top: -15px;
    }

    .fab-btn:hover{
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6);
    }

    .fab-menu{
        position:fixed;

        left:50%;
        bottom:90px;

        transform:
        translateX(-50%)
        translateY(20px)
        scale(.95);

        flex-direction:column;

        gap:10px;

        opacity:0;
        visibility:hidden;

        transition:.3s ease;

        z-index:10000;
    }

    .fab-menu.show{

        display:flex;

        opacity:1;
        visibility:visible;

        transform:
        translateX(-50%)
        translateY(0)
        scale(1);
    }

    .fab-item{

        background:#fff;

        color:#111827;

        text-decoration:none;

        padding:12px 18px;

        border-radius:30px;

        font-size:13px;
        font-weight:600;

        white-space:nowrap;

        box-shadow:
        0 8px 25px rgba(0,0,0,.15);

        transition:.25s;
    }

    .fab-item:hover{
        transform:translateY(-2px);
    }

    .dashboard-content{
        padding-bottom:110px !important;
    }
}
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Search Modal -->
<div class="search-modal-overlay" id="searchOverlay">
    <div class="search-modal" id="searchModal">
        <div class="sm-input-row">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" class="sm-input" id="smInput" placeholder="Search pages, actions..." autocomplete="off">
            <span class="sm-esc" id="smEsc">ESC</span>
        </div>
        <div class="sm-body" id="smBody"></div>
        <div class="sm-footer">
            <span><span class="kbd">↑↓</span> Navigate</span>
            <span><span class="kbd">↵</span> Open</span>
            <span><span class="kbd">ESC</span> Close</span>
        </div>
    </div>
</div>

<!-- ════════ SIDEBAR ════════ -->
<div class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <div class="logo-wrap">
            <div class="logo-icon">🛒</div>
            <span class="logo-text">Simko</span>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
    </div>

    <!-- Search trigger (fixed in sidebar, always same position) -->
    <div class="sidebar-search" id="sidebarSearch" onclick="openSearch()" title="Search (⌘K)">
        <div class="sb-search-wrap">
            <span class="sb-search-icon">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <div class="sb-search-fake">Search...</div>
            <div class="sb-kbd"><span class="kbd">⌘K</span></div>
        </div>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-section-label">Main</div>

        <div class="nav-item">
            <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <span class="nav-tooltip">Dashboard</span>
        </div>

        <div class="nav-section-label">Inventory</div>

        @php $productsActive = request()->is('admin/products*') || request()->is('admin/barcode*') || request()->is('admin/categories*') || request()->routeIs('admin.attributes.*'); @endphp
        <div class="nav-item">
            <div class="nav-link {{ $productsActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                <span class="nav-icon">📦</span>
                <span class="nav-text">Products</span>
                <span class="nav-arrow"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></span>
            </div>
            <div class="submenu">
                <div class="submenu-inner">
                    <a href="{{ url('/admin/products') }}" class="submenu-item {{ request()->is('admin/products') && !request()->is('admin/products/create') ? 'active' : '' }}"><span class="submenu-dot"></span> View Products</a>
                    <a href="{{ route('admin.attributes.index') }}" class="submenu-item {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }}"><span class="submenu-dot"></span> Attributes</a>
                    <a href="{{ url('/admin/barcode') }}" class="submenu-item {{ request()->is('admin/barcode*') ? 'active' : '' }}"><span class="submenu-dot"></span> Print Barcode</a>
                    <a href="{{ url('/admin/categories') }}" class="submenu-item {{ request()->is('admin/categories*') ? 'active' : '' }}"><span class="submenu-dot"></span> Categories</a>
                </div>
            </div>
            <span class="nav-tooltip">Products</span>
        </div>

        @php $warehousesActive = request()->is('admin/warehouses*'); @endphp
        <div class="nav-item">
            <div class="nav-link {{ $warehousesActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                <span class="nav-icon">🏢</span>
                <span class="nav-text">Warehouses</span>
                <span class="nav-arrow"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></span>
            </div>
            <div class="submenu">
                <div class="submenu-inner">
                    <a href="{{ url('/admin/warehouses') }}" class="submenu-item {{ request()->is('admin/warehouses') && !request()->is('admin/warehouses/create') ? 'active' : '' }}"><span class="submenu-dot"></span> View Warehouses</a>
                </div>
            </div>
            <span class="nav-tooltip">Warehouses</span>
        </div>

        <div class="nav-section-label">People</div>

        @php $partiesActive = request()->routeIs('admin.parties.*'); @endphp
        <div class="nav-item">
            <div class="nav-link {{ $partiesActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Parties</span>
                <span class="nav-arrow"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></span>
            </div>
            <div class="submenu">
                <div class="submenu-inner">
                    <a href="{{ route('admin.parties.index.type', 'customer') }}" class="submenu-item {{ request()->routeIs('admin.parties.*') && request()->segment(3) === 'customer' ? 'active' : '' }}"><span class="submenu-dot"></span> Customers</a>
                    <a href="{{ route('admin.parties.index.type', 'dealer') }}" class="submenu-item {{ request()->routeIs('admin.parties.*') && request()->segment(3) === 'dealer' ? 'active' : '' }}"><span class="submenu-dot"></span> Dealers</a>
                    <a href="{{ route('admin.parties.index.type', 'distributor') }}" class="submenu-item {{ request()->routeIs('admin.parties.*') && request()->segment(3) === 'distributor' ? 'active' : '' }}"><span class="submenu-dot"></span> Distributors</a>
                    <a href="{{ url('/admin/vendors') }}" class="submenu-item {{ request()->is('admin/vendors') ? 'active' : '' }}"><span class="submenu-dot"></span>Vendors</a>
                </div>
            </div>
            <span class="nav-tooltip">Parties</span>
        </div>

        @php $salesmenActive = request()->is('admin/salesmen*'); @endphp
        <div class="nav-item">
            <div class="nav-link {{ $salesmenActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                <span class="nav-icon">🧑‍💼</span>
                <span class="nav-text">Sales Executive</span>
                <span class="nav-arrow"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></span>
            </div>
            <div class="submenu">
                <div class="submenu-inner">
                    <a href="{{ url('/admin/salesmen') }}" class="submenu-item {{ request()->is('admin/salesmen') ? 'active' : '' }}"><span class="submenu-dot"></span> View Sales Executive</a>
                </div>
            </div>
            <span class="nav-tooltip">Sales Executive</span>
        </div>
        <!-- NEW: Purchase Executive Section -->
        @php $purchaseExecutivesActive = request()->is('admin/purchase-executives*'); @endphp
        <div class="nav-item">
            <div class="nav-link {{ $purchaseExecutivesActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                <span class="nav-icon">🧑‍🔧</span>
                <span class="nav-text">Purchase Executive</span>
                <span class="nav-arrow"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></span>
            </div>
            <div class="submenu">
                <div class="submenu-inner">
                    <a href="{{ url('/admin/purchase-executives') }}" class="submenu-item {{ request()->is('admin/purchase-executives') ? 'active' : '' }}"><span class="submenu-dot"></span> View Purchase Executive</a>
                </div>
            </div>
            <span class="nav-tooltip">Purchase Executive</span>
        </div>


        <div class="nav-section-label">Finance</div>

        @php
            $salesActive = !request()->is('admin/salesmen*') && !request()->is('admin/warranty*') && (
                request()->is('admin/sales*') || request()->is('admin/payments') || request()->is('admin/payments/*')
            );
        @endphp
        <div class="nav-item">
            <div class="nav-link {{ $salesActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                <span class="nav-icon">🛍️</span>
                <span class="nav-text">Sales</span>
                <span class="nav-arrow"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></span>
            </div>
            <div class="submenu">
                <div class="submenu-inner">
                    <a href="{{ url('/admin/sales') }}" class="submenu-item {{ !request()->is('admin/salesmen*') && !request()->is('admin/warranty*') && request()->is('admin/sales') ? 'active' : '' }}"><span class="submenu-dot"></span> Sales Invoices</a>
                    <a href="{{ url('/admin/sales-returns') }}" class="submenu-item {{ request()->is('admin/sales-returns*') ? 'active' : '' }}"><span class="submenu-dot"></span> Sales Returns</a>

                    <a href="{{ url('/admin/credit-notes') }}" class="submenu-item {{ request()->is('admin/credit-notes*') ? 'active' : '' }}"><span class="submenu-dot"></span> Credit Notes</a>

                    <a href="{{ url('/admin/quotations') }}" class="submenu-item {{ request()->is('admin/quotations*') ? 'active' : '' }}"><span class="submenu-dot"></span> Quotations </a>
                    <a href="{{ url('/admin/payments') }}" class="submenu-item {{ request()->is('admin/payments')  || request()->is('admin/payments/*') ? 'active' : '' }}"><span class="submenu-dot"></span> Payment In</a>
                </div>
            </div>
            <span class="nav-tooltip">Sales</span>
        </div>

        @php
        $purchaseActive = request()->is('admin/purchases*') ||
                        request()->is('admin/purchase-returns*') ||
                        request()->is('admin/debit-notes*') ||
                        request()->is('admin/payments-out*');  // Add this line
        @endphp

        <div class="nav-item">
            <div class="nav-link {{ $purchaseActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                <span class="nav-icon">📋</span>
                <span class="nav-text">Purchases</span>
                <span class="nav-arrow"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></span>
            </div>
            <div class="submenu">
                <div class="submenu-inner">
                    <a href="{{ url('/admin/purchases') }}" class="submenu-item {{ request()->is('admin/purchases') ? 'active' : '' }}"><span class="submenu-dot"></span>Purchases Invoices</a>
                    <a href="{{ url('/admin/purchase-returns') }}" class="submenu-item {{ request()->is('admin/purchase-returns*') ? 'active' : '' }}"><span class="submenu-dot"></span> Purchase Returns</a>

                    <a href="{{ url('/admin/debit-notes') }}" class="submenu-item {{ request()->is('admin/debit-notes*') ? 'active' : '' }}"><span class="submenu-dot"></span> Debit Notes</a>
                    <a href="{{ url('/admin/payments-out') }}" class="submenu-item {{ request()->is('admin/payments-out*') ? 'active' : '' }}"><span class="submenu-dot"></span> Payment Out</a>
                </div>
            </div>
            <span class="nav-tooltip">Purchases</span>
        </div>
        <!-- WARRANTY SECTION - Placed between Sales and Purchase Orders -->
        @php $warrantyActive = request()->is('admin/warranty*'); @endphp
        <div class="nav-item">
            <div class="nav-link {{ $warrantyActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                <span class="nav-icon">🛡️</span>
                <span class="nav-text">Warranty</span>
                <span class="nav-arrow"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></span>
            </div>
            <div class="submenu">
                <div class="submenu-inner">
                    <a href="{{ route('admin.warranty.index') }}" class="submenu-item {{ request()->routeIs('admin.warranty.index') ? 'active' : '' }}"><span class="submenu-dot"></span> All Claims</a>
                    <a href="{{ route('admin.defective-stock.index') }}" class="submenu-item {{ request()->routeIs('admin.defective-stock.index') ? 'active' : '' }}"><span class="submenu-dot"></span> Defective Product</a>
                </div>
            </div>
            <span class="nav-tooltip">Warranty</span>
        </div>



        <div class="nav-section-label">More</div>

        <div class="nav-item">
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->is('admin/reports') ? 'active' : '' }}">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Reports</span>
            </a>
            <span class="nav-tooltip">Reports</span>
        </div>

        <div class="nav-item">
            <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->is('admin/expenses*') ? 'active' : '' }}">
                <span class="nav-icon">💸</span>
                <span class="nav-text">Expenses</span>
            </a>
            <span class="nav-tooltip">Expenses</span>
        </div>

        <!-- ADMIN CONTROL - With Invoice Settings inside -->
        @php $adminControlActive = request()->is('admin/invoice-settings*') || request()->is('admin/cashmemo-invoice-settings*') || request()->is('admin/settings*'); @endphp
        <div class="nav-item">
            <div class="nav-link {{ $adminControlActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Admin Control</span>
                <span class="nav-arrow"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></span>
            </div>
            <div class="submenu">
                <div class="submenu-inner">
                    <!-- Invoice Settings inside Admin Control -->
                    <a href="{{ url('/admin/invoice-settings') }}" class="submenu-item {{ request()->is('admin/invoice-settings*') ? 'active' : '' }}"><span class="submenu-dot"></span> Invoice Settings</a>
                    <a href="{{ url('/admin/cashmemo-invoice-settings') }}" class="submenu-item {{ request()->is('admin/cashmemo-invoice-settings*') ? 'active' : '' }}"><span class="submenu-dot"></span> Cash Memo Settings</a>
                    <a href="#" class="submenu-item"><span class="submenu-dot"></span> General Settings</a>
                    <a href="#" class="submenu-item"><span class="submenu-dot"></span> Payment Settings</a>
                    <a href="#" class="submenu-item"><span class="submenu-dot"></span> Shipping Settings</a>
                </div>
            </div>
            <span class="nav-tooltip">Admin Control</span>
        </div>

    </nav>
</div>

<!-- ════════ MAIN ════════ -->
<div class="main-content" id="mainContent">
    <header class="header">
        <div class="header-left">
            <button class="menu-toggle" id="menuToggle">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
            </button>
            <h1 class="header-title">@yield('header-title', 'Dashboard')</h1>
        </div>
        <div class="header-right">
            <button class="header-btn voice-assistant-btn" id="voiceAssistantBtn" title="Voice Assistant">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                    <line x1="12" y1="19" x2="12" y2="22"/>
                    <line x1="9" y1="23" x2="15" y2="23"/>
                </svg>
                <span class="voice-indicator"></span>
            </button>
            <button class="header-btn" title="Notifications">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="notif-dot"></span>
            </button>
            <div class="user-profile" id="userProfile" onclick="toggleUserMenu()">
                <div class="user-avatar">{{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}</div>
                <!-- <div class="user-info">
                    <div class="user-name">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</div>
                    <div class="user-role">Administrator</div>
                </div> -->
                <span class="user-chevron"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg></span>
                <div class="user-menu" id="userMenu">
                    <div class="user-menu-header">
                        <div class="user-menu-name">{{ Auth::guard('admin')->user()->name }}</div>
                        <div class="user-menu-email">{{ Auth::guard('admin')->user()->email }}</div>
                    </div>
                    <div class="user-menu-body">
                        <a href="#" class="user-menu-item"><span class="mi-icon">👤</span> My Profile</a>
                        <a href="#" class="user-menu-item"><span class="mi-icon">⚙️</span> Preferences</a>
                        <div class="user-menu-divider"></div>
                        <form method="POST" action="{{ url('/admin/logout') }}">
                            @csrf
                            <button type="submit" class="user-menu-item danger"><span class="mi-icon">🚪</span> Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="dashboard-content">@yield('content')</div>
</div>
<div class="mobile-bottom-nav">

    <a href="/admin/dashboard" class="mobile-nav-item {{ request()->is('admin/dashboard*') ? 'active' : '' }}">
        <span>🏠</span>
        <small>Home</small>
    </a>

    <a href="/admin/products" class="mobile-nav-item {{ request()->is('admin/products*') ? 'active' : '' }}">
        <span>📦</span>
        <small>Products</small>
    </a>

    <button class="fab-btn" id="fabBtn">
        +
    </button>

    <a href="/admin/sales" class="mobile-nav-item {{ request()->is('admin/sales*') ? 'active' : '' }}">
        <span>🧾</span>
        <small>Sales</small>
    </a>

    <a href="/admin/profile" class="mobile-nav-item {{ request()->is('admin/profile*') ? 'active' : '' }}">
        <span>👤</span>
        <small>Profile</small>
    </a>

</div>

<div class="fab-menu" id="fabMenu">

    <a href="/admin/sales/create" class="fab-item">
        🧾 Sales Invoice
    </a>

    <a href="/admin/sales-returns/create" class="fab-item">
        🔄 Sales Return
    </a>

    <a href="/admin/purchases/create" class="fab-item">
        📦 Purchase Invoice
    </a>

</div>

<script>
/* ── Sidebar toggle ── */
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const overlay = document.getElementById('sidebarOverlay');

document.getElementById('sidebarToggle').addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
    if (sidebar.classList.contains('collapsed')) {
        document.querySelectorAll('.submenu.open').forEach(s => s.classList.remove('open'));
        document.querySelectorAll('.nav-link.expanded').forEach(l => l.classList.remove('expanded'));
    }
});

document.getElementById('menuToggle').addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('show');
});
overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('show');
});

/* ── Submenu ── */
function toggleSubmenu(el) {
    if (sidebar.classList.contains('collapsed')) return;
    let sub = el.nextElementSibling;
    while (sub && !sub.classList.contains('submenu')) sub = sub.nextElementSibling;
    if (!sub) return;
    const open = sub.classList.contains('open');
    document.querySelectorAll('.submenu.open').forEach(s => {
        if (s !== sub) { s.classList.remove('open'); const nl = s.previousElementSibling; if (nl?.classList.contains('nav-link')) nl.classList.remove('expanded'); }
    });
    sub.classList.toggle('open', !open);
    el.classList.toggle('expanded', !open);
}

document.addEventListener('DOMContentLoaded', () => {
    const a = document.querySelector('.submenu-item.active');
    if (a) {
        const sub = a.closest('.submenu');
        if (sub) { sub.classList.add('open'); const nl = sub.previousElementSibling; if (nl?.classList.contains('nav-link')) nl.classList.add('expanded'); }
    }
});

/* ── User menu ── */
function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('show');
    document.getElementById('userProfile').classList.toggle('open');
}
document.addEventListener('click', (e) => {
    const p = document.getElementById('userProfile');
    if (p && !p.contains(e.target)) { document.getElementById('userMenu').classList.remove('show'); p.classList.remove('open'); }
});

/* ════════════════════════════
   SEARCH MODAL
════════════════════════════ */
const searchPages = [
    { name: 'Dashboard',         path: '/admin/dashboard',          icon: '🏠',  group: 'Main' },
    { name: 'View Products',     path: '/admin/products',           icon: '📦',  group: 'Products' },
    { name: 'Attributes',        path: '/admin/attributes',         icon: '🏷️', group: 'Products' },
    { name: 'Print Barcode',     path: '/admin/barcode',            icon: '📊',  group: 'Products' },
    { name: 'Categories',        path: '/admin/categories',         icon: '🗂️', group: 'Products' },
    { name: 'Warehouses',        path: '/admin/warehouses',         icon: '🏢',  group: 'Inventory' },
    { name: 'Customers',         path: '/admin/parties/customer',   icon: '👤',  group: 'Parties' },
    { name: 'Dealers',           path: '/admin/parties/dealer',     icon: '🏪',  group: 'Parties' },
    { name: 'Distributors',      path: '/admin/parties/distributor',icon: '🏭',  group: 'Parties' },
    { name: 'View Salesmen',     path: '/admin/salesmen',           icon: '🧑‍💼',group: 'People' },
    { name: 'Purchase Executives', path: '/admin/purchase-executives', icon: '🧑‍🔧', group: 'People' },
    { name: 'Vendors',            path: '/admin/vendors',              icon: '🚚',  group: 'People' },
    { name: 'Sales Invoices',    path: '/admin/sales',              icon: '🛍️', group: 'Sales' },
    { name: 'Sales Returns',     path: '/admin/sales-returns',   icon: '🔄',  group: 'Sales' },
    { name: 'Credit Notes',      path: '/admin/credit-notes',    icon: '📝',  group: 'Sales' },
    { name: 'Purchase Invoices',    path: '/admin/purchases',              icon: '📋', group: 'Purchases' },
    { name: 'Purchase Returns',    path: '/admin/purchase-returns',    icon: '🔄',  group: 'Purchases' },
    { name: 'Debit Notes',         path: '/admin/debit-notes',         icon: '📝',  group: 'Purchases' },
    { name: 'Payment In',        path: '/admin/payments',           icon: '💳',  group: 'Sales' },
    { name: 'All Claims',        path: '/admin/warranty',           icon: '🛡️', group: 'Warranty' },
    { name: 'New Claim',         path: '/admin/warranty/create',    icon: '➕',  group: 'Warranty' },
    { name: 'All Purchases',     path: '/admin/purchases',          icon: '📋',  group: 'Purchases' },
    { name: 'All Suppliers',     path: '/admin/suppliers',          icon: '🚚',  group: 'Purchases' },
    { name: 'Supplier Payments', path: '/admin/supplier-payments',  icon: '💰',  group: 'Purchases' },
    { name: 'Reports',           path: '/admin/reports',            icon: '📊',  group: 'More' },
    { name: 'Invoice Settings',  path: '/admin/invoice-settings',   icon: '⚙️', group: 'Admin Control' },
    { name: 'Cash Memo Settings',path: '/admin/cashmemo-invoice-settings', icon: '📝', group: 'Admin Control' },
];

const searchOverlay = document.getElementById('searchOverlay');
const smInput       = document.getElementById('smInput');
const smBody        = document.getElementById('smBody');
let focusIdx = -1;
let filtered = [];

function openSearch() {
    searchOverlay.classList.add('show');
    smInput.value = '';
    renderResults('');
    setTimeout(() => smInput.focus(), 30);
}

function closeSearch() {
    searchOverlay.classList.remove('show');
    focusIdx = -1;
}

function renderResults(q) {
    const query = q.toLowerCase().trim();
    focusIdx = -1;

    if (!query) {
        filtered = searchPages.slice(0, 6);
        smBody.innerHTML = `<div class="sm-group-label">Quick Access</div>` +
            filtered.map((r, i) => resultHTML(r, r.name)).join('');
        return;
    }

    filtered = searchPages.filter(p =>
        p.name.toLowerCase().includes(query) || p.group.toLowerCase().includes(query)
    );

    if (!filtered.length) {
        smBody.innerHTML = `<div class="sm-empty"><div class="sm-empty-icon">🔍</div>No results for "<strong>${q}</strong>"</div>`;
        return;
    }

    const groups = {};
    filtered.forEach(r => { if (!groups[r.group]) groups[r.group] = []; groups[r.group].push(r); });
    let html = '';
    Object.entries(groups).forEach(([g, items]) => {
        html += `<div class="sm-group-label">${g}</div>`;
        items.forEach(item => {
            const hl = item.name.replace(new RegExp(`(${q})`, 'gi'),
                '<mark style="background:var(--accent-bg);color:var(--accent-dark);border-radius:2px;padding:0 1px;">$1</mark>');
            html += resultHTML(item, hl);
        });
    });
    smBody.innerHTML = html;
}

function resultHTML(item, nameHtml) {
    return `<a href="${item.path}" class="sm-result">
        <div class="sm-result-icon">${item.icon}</div>
        <div>
            <div class="sm-result-name">${nameHtml}</div>
            ${item.group ? `<div class="sm-result-group">${item.group}</div>` : ''}
        </div>
        <span class="sm-enter">↵</span>
    </a>`;
}

// Events
smInput.addEventListener('input', e => renderResults(e.target.value));

smInput.addEventListener('keydown', e => {
    const items = smBody.querySelectorAll('.sm-result');
    if (e.key === 'ArrowDown') { e.preventDefault(); focusIdx = Math.min(focusIdx + 1, items.length - 1); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); focusIdx = Math.max(focusIdx - 1, -1); }
    else if (e.key === 'Enter' && focusIdx >= 0) { e.preventDefault(); items[focusIdx]?.click(); }
    else if (e.key === 'Escape') { closeSearch(); }
    items.forEach((item, i) => item.classList.toggle('focused', i === focusIdx));
});

document.getElementById('smEsc').addEventListener('click', closeSearch);

// Click outside modal
searchOverlay.addEventListener('click', e => {
    if (!document.getElementById('searchModal').contains(e.target)) closeSearch();
});

// Keyboard shortcut
document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); openSearch(); }
    if (e.key === 'Escape' && searchOverlay.classList.contains('show')) closeSearch();
});

//  +==============================44


const fabBtn = document.getElementById('fabBtn');
const fabMenu = document.getElementById('fabMenu');

fabBtn.addEventListener('click', () => {

    fabMenu.classList.toggle('show');

    if(fabMenu.classList.contains('show')){

        fabBtn.innerHTML = '✕';
        fabBtn.style.transform = 'rotate(45deg)';

    }else{

        fabBtn.innerHTML = '+';
        fabBtn.style.transform = 'rotate(0deg)';
    }

});

</script>
 <script src="{{ asset('js/voice-commands.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')
</body>
</html>
