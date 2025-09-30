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
    <link rel="stylesheet" href="{{ asset('css/admin/admin-dashboard.css') }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Users</div>
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_users'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+12% from last month</span>
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
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+8% from last month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Assessments</div>
                <div class="stat-icon warning">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_assessments'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+15% from last month</span>
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
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i>
                <span>-3% from last month</span>
            </div>
        </div>
    </div>

    <!-- Dashboard Grid Layout -->
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
                <!-- Removed Barangays, Patients, and Assessments buttons -->
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
@endsection

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

    <!-- Admin Dashboard Configuration -->
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
    </script>

    <!-- Admin Dashboard JS -->
    <script src="{{ asset('js/admin/admin-dashboard.js') }}"></script>
@endpush
