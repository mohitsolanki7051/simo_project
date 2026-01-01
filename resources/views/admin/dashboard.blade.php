@extends('layouts.admin')

@section('title', 'Dashboard - Admin Panel')
@section('header-title', 'Dashboard')

@section('content')
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value">1,234</div>
                    <div class="stat-change up">12% from last month</div>
                </div>
                <div class="stat-icon blue">🛒</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Revenue</div>
                    <div class="stat-value">₹45,678</div>
                    <div class="stat-change up">8% from last month</div>
                </div>
                <div class="stat-icon green">💰</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Products</div>
                    <div class="stat-value">456</div>
                    <div class="stat-change up">23 new products</div>
                </div>
                <div class="stat-icon purple">📦</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Customers</div>
                    <div class="stat-value">8,902</div>
                    <div class="stat-change up">5% from last month</div>
                </div>
                <div class="stat-icon orange">👥</div>
            </div>
        </div>
    </div>

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h2 class="welcome-title">Welcome back, {{ Auth::guard('admin')->user()->name }}! 👋</h2>
        <p class="welcome-subtitle">Here's what's happening with your store today.</p>
    </div>

    <!-- Recent Activity -->
    <div class="activity-section">
        <h3 class="activity-title">Recent Activity</h3>

        <div class="activity-item">
            <div class="activity-icon green">✓</div>
            <div class="activity-content">
                <div class="activity-text">New order received</div>
                <div class="activity-time">Order #12345 - 2 minutes ago</div>
            </div>
            <div class="activity-amount">₹2,499</div>
        </div>

        <div class="activity-item">
            <div class="activity-icon blue">👤</div>
            <div class="activity-content">
                <div class="activity-text">New customer registered</div>
                <div class="activity-time">john.doe@example.com - 15 minutes ago</div>
            </div>
        </div>

        <div class="activity-item">
            <div class="activity-icon purple">📦</div>
            <div class="activity-content">
                <div class="activity-text">Product stock low</div>
                <div class="activity-time">iPhone 15 Pro - 1 hour ago</div>
            </div>
            <div class="activity-badge badge-warning">Low Stock</div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #ebf4ff 0%, #c3dafe 100%);
            color: #3182ce;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
            color: #38a169;
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #faf5ff 0%, #e9d8fd 100%);
            color: #805ad5;
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #fffaf0 0%, #feebc8 100%);
            color: #dd6b20;
        }

        .stat-label {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .stat-change {
            font-size: 13px;
            color: #38a169;
            font-weight: 500;
        }

        .stat-change.up::before {
            content: '↑ ';
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 35px;
            border-radius: 16px;
            color: white;
            margin-bottom: 35px;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }

        .welcome-title {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            font-size: 15px;
            opacity: 0.9;
        }

        /* Activity Section */
        .activity-section {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .activity-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #2d3748;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 18px;
        }

        .activity-icon.green {
            background: #c6f6d5;
            color: #22543d;
        }

        .activity-icon.blue {
            background: #bee3f8;
            color: #2c5282;
        }

        .activity-icon.purple {
            background: #e9d8fd;
            color: #553c9a;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 12px;
            color: #a0aec0;
        }

        .activity-amount {
            font-size: 15px;
            font-weight: 700;
            color: #2d3748;
        }

        .activity-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-warning {
            background: #feebc8;
            color: #7c2d12;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .welcome-banner {
                padding: 25px;
            }

            .activity-section {
                padding: 20px;
            }
        }
    </style>
    @endpush
@endsection
