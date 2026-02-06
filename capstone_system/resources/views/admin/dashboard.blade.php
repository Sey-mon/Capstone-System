@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Welcome back, ' . Auth::user()->first_name . '! Here\'s what\'s happening today.')

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>
    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin/admin-dashboard.css') }}?v={{ time() }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Statistics Grid (8 cards) -->
    <div class="stats-grid">
        <!-- Existing Cards with Dynamic Percentages -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Users</div>
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_users'] }}</div>
            <div class="stat-change {{ $stats['users_change'] >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $stats['users_change'] >= 0 ? 'up' : 'down' }}"></i>
                <span>{{ $stats['users_change'] > 0 ? '+' : '' }}{{ $stats['users_change'] }}% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Patients</div>
                <div class="stat-icon success">
                    <i class="fas fa-user-injured"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_patients'] }}</div>
            <div class="stat-change {{ $stats['patients_change'] >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $stats['patients_change'] >= 0 ? 'up' : 'down' }}"></i>
                <span>{{ $stats['patients_change'] > 0 ? '+' : '' }}{{ $stats['patients_change'] }}% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Screenings</div>
                <div class="stat-icon warning">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_screenings'] }}</div>
            <div class="stat-change {{ $stats['screenings_change'] >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $stats['screenings_change'] >= 0 ? 'up' : 'down' }}"></i>
                <span>{{ $stats['screenings_change'] > 0 ? '+' : '' }}{{ $stats['screenings_change'] }}% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Inventory Items</div>
                <div class="stat-icon info">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_inventory_items'] }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-minus"></i>
                <span>Total items in stock</span>
            </div>
        </div>

        <!-- New Stat Cards -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Low Stock Items</div>
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_low_stock'] }}</div>
            <div class="stat-breakdown">
                <span class="severity-critical">● {{ $stats['critical_count'] }} Critical</span>
                <span class="severity-warning">● {{ $stats['warning_count'] }} Warning</span>
                <span class="severity-low">● {{ $stats['low_count'] }} Low</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Pending Screenings</div>
                <div class="stat-icon warning">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['pending_screenings'] }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-check-circle"></i>
                <span>{{ $stats['completion_rate'] }}% completion rate</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Items Expiring Soon</div>
                <div class="stat-icon warning">
                    <i class="fas fa-calendar-times"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['expiring_count'] }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-clock"></i>
                <span>Within {{ $stats['expiring_days'] }} days</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Active Users</div>
                <div class="stat-icon success">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['active_users'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-users"></i>
                <span>{{ $stats['active_percentage'] }}% of total users</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="collapsible-header" data-section="charts">
            <h3 class="section-title">
                <i class="fas fa-chart-bar"></i>
                Data Analytics
            </h3>
            <i class="fas fa-chevron-down toggle-icon"></i>
        </div>
        <div class="section-content">
            <div class="charts-grid">
                <!-- Screening Trends Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h4 class="chart-title">Screening Trends</h4>
                        <div class="date-range-controls">
                            <div class="preset-buttons">
                                <button class="preset-btn" data-period="7">7D</button>
                                <button class="preset-btn" data-period="30">30D</button>
                                <button class="preset-btn active" data-period="180">6M</button>
                                <button class="preset-btn" data-period="365">12M</button>
                                <button class="preset-btn" data-period="custom">Custom</button>
                            </div>
                            <input type="text" id="dateRangePicker" class="date-picker" placeholder="Select date range" style="display: none;">
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="screeningTrendsChart"></canvas>
                        <div class="chart-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Loading chart data...</span>
                        </div>
                    </div>
                </div>

                <!-- Nutritional Status Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h4 class="chart-title">Nutritional Status Distribution</h4>
                    </div>
                    <div class="chart-container">
                        <canvas id="nutritionalStatusChart"></canvas>
                    </div>
                </div>

                <!-- Inventory by Category Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h4 class="chart-title">Inventory by Category</h4>
                    </div>
                    <div class="chart-container">
                        <canvas id="inventoryCategoryChart"></canvas>
                    </div>
                </div>

                <!-- Low Stock Alerts Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h4 class="chart-title">Low Stock Alerts</h4>
                    </div>
                    <div class="chart-container">
                        <canvas id="lowStockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Alerts Section -->
    <div class="alerts-section">
        <div class="collapsible-header" data-section="alerts">
            <h3 class="section-title">
                <i class="fas fa-exclamation-circle"></i>
                Critical Alerts
            </h3>
            <i class="fas fa-chevron-down toggle-icon"></i>
        </div>
        <div class="section-content">
            <div class="alerts-grid">
                <!-- Low Stock Items Alert -->
                <div class="alert-box alert-danger">
                    <div class="alert-header">
                        <i class="fas fa-box-open"></i>
                        <h4>Low Stock Items</h4>
                        <span class="alert-badge">{{ $stats['total_low_stock'] }}</span>
                    </div>
                    <div class="alert-content">
                        @if($stats['total_low_stock'] > 0)
                            <div class="alert-list">
                                @foreach($stats['critical_stock']->take(3) as $item)
                                    <div class="alert-item severity-critical">
                                        <span class="item-name">{{ $item->item_name }}</span>
                                        <span class="item-qty">{{ $item->quantity }} qty</span>
                                    </div>
                                @endforeach
                                @foreach($stats['warning_stock']->take(3) as $item)
                                    <div class="alert-item severity-warning">
                                        <span class="item-name">{{ $item->item_name }}</span>
                                        <span class="item-qty">{{ $item->quantity }} qty</span>
                                    </div>
                                @endforeach
                                @foreach($stats['low_stock']->take(4) as $item)
                                    <div class="alert-item severity-low">
                                        <span class="item-name">{{ $item->item_name }}</span>
                                        <span class="item-qty">{{ $item->quantity }} qty</span>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('admin.inventory') }}" class="alert-link">View All Inventory →</a>
                        @else
                            <div class="success-state">
                                <i class="fas fa-check-circle"></i>
                                <span>All Good! No low stock items</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Expired Items Alert -->
                <div class="alert-box alert-danger">
                    <div class="alert-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Expired Items</h4>
                        <span class="alert-badge">{{ $stats['expired_count'] }}</span>
                    </div>
                    <div class="alert-content">
                        @if($stats['expired_count'] > 0)
                            <div class="alert-list">
                                @foreach($stats['expired_items']->take(10) as $item)
                                    <div class="alert-item severity-critical">
                                        <span class="item-name">{{ $item->item_name }}</span>
                                        <span class="item-date" style="color: #ef4444;">{{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('admin.inventory') }}" class="alert-link">View Inventory →</a>
                        @else
                            <div class="success-state">
                                <i class="fas fa-check-circle"></i>
                                <span>All Good! No expired items</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Expiring Soon Alert -->
                <div class="alert-box alert-warning">
                    <div class="alert-header">
                        <i class="fas fa-calendar-times"></i>
                        <h4>Expiring Soon</h4>
                        <span class="alert-badge">{{ $stats['expiring_count'] }}</span>
                    </div>
                    <div class="alert-content">
                        @if($stats['expiring_count'] > 0)
                            <div class="alert-list">
                                @foreach($stats['expiring_items']->take(10) as $item)
                                    <div class="alert-item">
                                        <span class="item-name">{{ $item->item_name }}</span>
                                        <span class="item-date">{{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('admin.inventory') }}" class="alert-link">View Inventory →</a>
                        @else
                            <div class="success-state">
                                <i class="fas fa-check-circle"></i>
                                <span>All Good! No items expiring soon</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pending Approvals Alert -->
                <div class="alert-box alert-info">
                    <div class="alert-header">
                        <i class="fas fa-user-clock"></i>
                        <h4>Pending Approvals</h4>
                        <span class="alert-badge">{{ $stats['pending_nutritionist_applications'] }}</span>
                    </div>
                    <div class="alert-content">
                        @if($stats['pending_nutritionist_applications'] > 0)
                            <div class="alert-list">
                                <div class="alert-item">
                                    <span class="item-name">Nutritionist Applications</span>
                                    <span class="item-count">{{ $stats['pending_nutritionist_applications'] }} pending</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.users') }}" class="alert-link">View Users →</a>
                        @else
                            <div class="success-state">
                                <i class="fas fa-check-circle"></i>
                                <span>All Good! No pending approvals</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Grid Layout (Activity & Map) -->
    <div class="activity-map-section">
        <div class="collapsible-header" data-section="activity-map">
            <h3 class="section-title">
                <i class="fas fa-chart-line"></i>
                Recent Activity & Geographic Overview
            </h3>
            <i class="fas fa-chevron-down toggle-icon"></i>
        </div>
        <div class="section-content">
            <div class="dashboard-grid">
        <!-- Recent Activity (Left Side) -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Activity</h3>
                <a href="{{ route('admin.reports') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-line"></i>
                    View All Reports
                </a>
            </div>
            <div class="card-content">
                @forelse($stats['recent_audit_logs'] as $log)
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <strong>{{ $log->user->first_name ?? 'System' }}</strong> {{ $log->description }}
                                </div>
                                <div class="activity-time">{{ $log->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-gray-400 text-4xl mb-3"></i>
                        <p class="text-gray-500">No recent activity found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Geographic Overview Map (Right Side) -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Geographic Overview</h3>
                <div class="map-controls">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="barangaySearch" class="barangay-search" placeholder="Search barangay...">
                    </div>
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="sam" style="color: #ef4444;">SAM</button>
                        <button class="filter-btn" data-filter="mam" style="color: #f59e0b;">MAM</button>
                        <button class="filter-btn" data-filter="normal" style="color: #3b82f6;">Normal</button>
                        <button class="filter-btn" data-filter="unknown" style="color: #6b7280;">Unknown</button>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div id="admin-map"></div>
                <div class="map-legend mt-3">
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: #ef4444;"></span>
                        <span>SAM Patients</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: #f59e0b;"></span>
                        <span>MAM Patients</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: #3b82f6;"></span>
                        <span>Normal Patients</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    
    <!-- Flatpickr for date range picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

    <!-- Dashboard Data Configuration -->
    <div id="barangays-data" style="display: none;">{{ json_encode($barangays) }}</div>
    <script>
        // Configure admin dashboard settings
        const barangaysElement = document.getElementById('barangays-data');
        const barangaysData = barangaysElement ? JSON.parse(barangaysElement.textContent) : [];
        
        window.adminDashboard = {
            mapDataUrl: "{{ route('admin.dashboard.map-data') }}",
            assetPath: "{{ asset('img/markers/') }}/",
            barangays: barangaysData
        };
        
        // Dashboard chart data
        window.dashboardData = {
            screening_trends: @json($stats['screening_trends']),
            nutritional_status: @json($stats['nutritional_status']),
            inventory_by_category: @json($stats['inventory_by_category']),
            low_stock_threshold: {{ config('dashboard.low_stock_threshold') }},
            chart_data_url: "{{ route('admin.dashboard.chart-data', ['type' => '__TYPE__']) }}",
            colors: @json(config('dashboard.colors'))
        };
    </script>

    <!-- Admin Dashboard Charts JS -->
    <script src="{{ asset('js/admin/admin-dashboard-charts.js') }}?v={{ time() }}"></script>
    
    <!-- Admin Dashboard JS -->
    <script src="{{ asset('js/admin/admin-dashboard.js') }}?v={{ time() }}"></script>
    
    <!-- Modal backdrop cleanup for admin dashboard -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clean up any existing modal backdrops on page load
            if (typeof window.cleanupModalBackdrops === 'function') {
                window.cleanupModalBackdrops();
            }
            
            // Add keyboard shortcut (Ctrl+Alt+C) to cleanup modals
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.altKey && e.key === 'c') {
                    e.preventDefault();
                    if (typeof window.emergencyModalCleanup === 'function') {
                        window.emergencyModalCleanup();
                        // Show a brief notification
                        const notification = document.createElement('div');
                        notification.textContent = 'Modal backdrops cleaned up!';
                        notification.style.cssText = `
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            background: #28a745;
                            color: white;
                            padding: 10px 15px;
                            border-radius: 4px;
                            z-index: 10000;
                            font-size: 14px;
                        `;
                        document.body.appendChild(notification);
                        setTimeout(() => notification.remove(), 3000);
                    }
                }
            });
            
            console.log('🔧 Admin dashboard modal cleanup initialized');
            console.log('💡 Press Ctrl+Alt+C to cleanup modal backdrops');
        });
    </script>
@endpush
