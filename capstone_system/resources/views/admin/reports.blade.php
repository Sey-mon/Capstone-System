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

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Patients</div>
                <div class="stat-icon info">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $reports['total_patients'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-plus"></i>
                <span>Registered</span>
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
                    <button class="btn btn-primary" onclick="generateReport('user-activity')">
                        <i class="fas fa-file-pdf"></i>
                        User Activity Report
                    </button>
                    <button class="btn btn-secondary" onclick="generateReport('inventory')">
                        <i class="fas fa-file-excel"></i>
                        Inventory Report
                    </button>
                    <button class="btn btn-success" onclick="generateReport('assessment-trends')">
                        <i class="fas fa-chart-line"></i>
                        Assessment Trends
                    </button>
                    <button class="btn btn-warning" onclick="generateReport('low-stock')">
                        <i class="fas fa-exclamation-circle"></i>
                        Low Stock Alert
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Activities</h3>
            </div>
            <div class="card-content">
                <div class="activity-list">
                    @forelse($reports['recent_activities'] as $activity)
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-{{ $activity['type'] === 'assessment' ? 'clipboard-list' : 'box' }}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">{{ $activity['description'] }}</div>
                                <div class="activity-time">
                                    By {{ $activity['user'] }} • {{ $activity['time']->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>No recent activities found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Assessment Trends</h3>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-secondary" onclick="updateChartPeriod('weekly')">
                    <i class="fas fa-calendar-week"></i>
                    Weekly
                </button>
                <button class="btn btn-primary" onclick="updateChartPeriod('monthly')">
                    <i class="fas fa-calendar-alt"></i>
                    Monthly
                </button>
                <button class="btn btn-secondary" onclick="updateChartPeriod('yearly')">
                    <i class="fas fa-calendar"></i>
                    Yearly
                </button>
            </div>
        </div>
        <div class="card-content">
            <div id="assessment-chart" style="height: 300px;">
                @if(isset($reports['assessment_trends']) && count($reports['assessment_trends']) > 0)
                    <canvas id="trendsChart"></canvas>
                @else
                    <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: var(--bg-secondary); border-radius: 0.5rem; border: 2px dashed var(--border-medium);">
                        <div style="text-align: center; color: var(--text-secondary);">
                            <i class="fas fa-chart-area" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p style="font-size: 1.125rem; margin-bottom: 0.5rem;">Charts Coming Soon</p>
                            <p style="font-size: 0.875rem;">Interactive charts and graphs will be available here.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Inventory Overview -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Inventory by Category</h3>
        </div>
        <div class="card-content">
            @if(isset($reports['inventory_by_category']) && count($reports['inventory_by_category']) > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                    @foreach($reports['inventory_by_category'] as $category => $count)
                        <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 0.5rem; text-align: center;">
                            <div style="font-size: 2rem; font-weight: 600; color: var(--primary-color);">{{ $count }}</div>
                            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">{{ $category }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                    <i class="fas fa-boxes" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>No inventory data available</p>
                </div>
            @endif
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

    <!-- Report Modal -->
    <div id="reportModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 id="reportModalTitle">Report Results</h3>
                <span class="close" onclick="closeReportModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="reportModalContent">
                    <!-- Report content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeReportModal()">Close</button>
                <button class="btn btn-primary" onclick="downloadReport()">
                    <i class="fas fa-download"></i>
                    Download
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/admin/admin-reports-enhanced.js') }}"></script>
    
    <style>
        /* Modal Styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: var(--bg-primary);
            margin: 5% auto;
            padding: 0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 90%;
            max-width: 800px;
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: var(--text-primary);
        }
        
        .close {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-secondary);
            cursor: pointer;
            border: none;
            background: none;
        }
        
        .close:hover {
            color: var(--text-primary);
        }
        
        .modal-body {
            padding: 1.5rem;
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-light);
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        /* Report Content Styles */
        .report-summary {
            margin-bottom: 2rem;
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .report-section {
            margin-bottom: 2rem;
        }
        
        .report-section h4 {
            margin-bottom: 1rem;
            color: var(--text-primary);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }
        
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
        }
        
        .data-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--bg-secondary);
            border-radius: 0.25rem;
        }
        
        .data-value {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .data-list .data-item {
            display: grid;
            grid-template-columns: auto auto auto auto;
            gap: 1rem;
            align-items: center;
        }
        
        .data-date {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }
        
        .data-table th {
            background: var(--bg-secondary);
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-low {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-good {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-medium {
            background: #fde68a;
            color: #92400e;
        }
        
        .urgency-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .urgency-critical {
            background: #fecaca;
            color: #991b1b;
        }
        
        .urgency-medium {
            background: #fed7aa;
            color: #9a3412;
        }
        
        /* Alert Styles */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1001;
            min-width: 300px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fecaca;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        .alert .close {
            margin-left: auto;
            font-size: 1.25rem;
        }
    </style>
@endpush
