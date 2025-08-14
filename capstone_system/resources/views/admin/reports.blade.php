@extends('layouts.dashboard')

@section('title', 'Reports & Analytics')

@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'View detailed reports and analytics for your nutrition system.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-reports.css') }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Report Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Monthly Assessments</div>
                <div class="stat-icon primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-value">{{ $reports['monthly_assessments'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-calendar"></i>
                <span>This month</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Low Stock Items</div>
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $reports['low_stock_items'] }}</div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i>
                <span>Needs attention</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Active Users</div>
                <div class="stat-icon success">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div class="stat-value">{{ $reports['active_users'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>Last 30 days</span>
            </div>
        </div>
    </div>

    <!-- Report Actions -->
    <div class="reports-grid">
        <!-- Quick Reports -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Quick Reports</h3>
            </div>
            <div class="card-content">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <button class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i>
                        User Activity Report
                    </button>
                    <button class="btn btn-secondary">
                        <i class="fas fa-file-excel"></i>
                        Inventory Report
                    </button>
                    <button class="btn btn-success">
                        <i class="fas fa-chart-line"></i>
                        Assessment Trends
                    </button>
                    <button class="btn btn-warning">
                        <i class="fas fa-exclamation-circle"></i>
                        Low Stock Alert
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Reports</h3>
            </div>
            <div class="card-content">
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Monthly Assessment Report</div>
                            <div class="activity-time">Generated 2 hours ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Inventory Stock Report</div>
                            <div class="activity-time">Generated yesterday</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">User Activity Analysis</div>
                            <div class="activity-time">Generated 3 days ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Analytics Overview</h3>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-secondary">
                    <i class="fas fa-calendar-week"></i>
                    Weekly
                </button>
                <button class="btn btn-primary">
                    <i class="fas fa-calendar-alt"></i>
                    Monthly
                </button>
                <button class="btn btn-secondary">
                    <i class="fas fa-calendar"></i>
                    Yearly
                </button>
            </div>
        </div>
        <div class="card-content">
            <div style="height: 400px; display: flex; align-items: center; justify-content: center; background: var(--bg-secondary); border-radius: 0.5rem; border: 2px dashed var(--border-medium);">
                <div style="text-align: center; color: var(--text-secondary);">
                    <i class="fas fa-chart-area" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p style="font-size: 1.125rem; margin-bottom: 0.5rem;">Charts Coming Soon</p>
                    <p style="font-size: 0.875rem;">Interactive charts and graphs will be available here.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- System Status -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">System Health</h3>
            </div>
            <div class="card-content">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-secondary); border-radius: 0.5rem;">
                        <span style="font-weight: 500;">Database Status</span>
                        <span style="padding: 0.25rem 0.75rem; background: linear-gradient(135deg, var(--success-color), #16a34a); color: white; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">
                            <i class="fas fa-check-circle"></i> Online
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-secondary); border-radius: 0.5rem;">
                        <span style="font-weight: 500;">Storage Usage</span>
                        <span style="padding: 0.25rem 0.75rem; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">
                            <i class="fas fa-hdd"></i> 45%
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-secondary); border-radius: 0.5rem;">
                        <span style="font-weight: 500;">Last Backup</span>
                        <span style="padding: 0.25rem 0.75rem; background: linear-gradient(135deg, var(--success-color), #16a34a); color: white; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">
                            <i class="fas fa-clock"></i> 2 hours ago
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Admin Actions</h3>
            </div>
            <div class="card-content">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <button class="btn btn-primary">
                        <i class="fas fa-download"></i>
                        Backup Data
                    </button>
                    <button class="btn btn-secondary">
                        <i class="fas fa-sync"></i>
                        Sync Data
                    </button>
                    <button class="btn btn-warning">
                        <i class="fas fa-broom"></i>
                        Clear Cache
                    </button>
                    <button class="btn btn-success">
                        <i class="fas fa-upload"></i>
                        Import Data
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-reports.js') }}"></script>
@endpush
