@extends('layouts.dashboard')

@section('title', 'Reports & Analytics')

@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'View detailed reports and analytics for your nutrition system.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-reports.css') }}?v={{ filemtime(public_path('css/admin/admin-reports.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin/modal.css') }}?v={{ filemtime(public_path('css/admin/modal.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin/report-content.css') }}?v={{ filemtime(public_path('css/admin/report-content.css')) }}">
    <style>
        .stats-grid {
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            overflow-x: hidden;
        }
        .stat-card {
            padding: 0.875rem;
            overflow: hidden;
        }
        .stat-value {
            font-size: 1.5rem;
        }
        .stat-title {
            font-size: 0.75rem;
        }
        .stat-icon {
            width: 2.25rem;
            height: 2.25rem;
            font-size: 1rem;
        }
        .stat-change {
            font-size: 0.75rem;
        }
        .content-card {
            padding: 0.875rem;
            margin-bottom: 1.25rem;
            overflow-x: hidden;
        }
        .card-header {
            margin-bottom: 0.875rem;
        }
        .card-title {
            font-size: 1rem;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .btn {
            padding: 0.5rem 0.875rem;
            font-size: 0.813rem;
        }
        .activity-item {
            padding: 0.625rem;
        }
        .activity-icon {
            width: 2rem;
            height: 2rem;
            font-size: 0.875rem;
        }
        .activity-title {
            font-size: 0.875rem;
        }
        .activity-time {
            font-size: 0.75rem;
        }
        .reports-grid {
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            overflow-x: hidden;
        }
        .progress-label {
            font-size: 0.813rem;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .monthly-chart canvas {
            max-height: 200px;
        }
        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            overflow-x: hidden;
        }
        .main-content {
            background: transparent;
            overflow-x: hidden;
            max-width: 100%;
        }
        * {
            box-sizing: border-box;
        }
        .page-header {
            background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%);
            color: white;
            padding: 1.25rem 0;
            margin-bottom: 1.5rem;
            border-radius: 0 0 16px 16px;
        }
        .page-header h1 {
            color: white;
            font-weight: 700;
            margin-bottom: 0.25rem;
            font-size: 1.5rem;
        }
        .page-header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-size: 0.875rem;
        }
        /* SweetAlert2 Custom Styles */
        .report-swal-popup {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .report-swal-title {
            color: #2e7d32;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .report-swal-content {
            max-height: 500px;
            overflow-y: auto;
            text-align: left;
        }
        .report-swal-content .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .report-swal-content .stat-item {
            text-align: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        .report-swal-content .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 0.4rem;
        }
        .report-swal-content .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        .report-swal-content .stat-meta {
            font-size: 0.7rem;
            color: #9ca3af;
            margin-top: 0.2rem;
        }
        .report-swal-content .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.75rem 0;
            font-size: 0.813rem;
        }
        .report-swal-content .data-table th,
        .report-swal-content .data-table td {
            padding: 0.625rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .report-swal-content .data-table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }
        .report-swal-content .status-badge {
            padding: 0.2rem 0.625rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        .report-swal-content .status-good {
            background: #d1fae5;
            color: #065f46;
        }
        .report-swal-content .status-medium {
            background: #fef3c7;
            color: #92400e;
        }
        .report-swal-content .status-low {
            background: #fee2e2;
            color: #991b1b;
        }
        .report-swal-content .report-section {
            margin-bottom: 1.25rem;
        }
        .report-swal-content .report-section h4 {
            color: #1f2937;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.625rem;
        }
        .report-swal-content .alert {
            padding: 0.875rem;
            border-radius: 6px;
            margin-bottom: 0.875rem;
            font-size: 0.875rem;
        }
        .report-swal-content .alert-info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }
        .report-swal-content .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
    </style>
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

    <!-- Essential Reports Section -->
    <div class="reports-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
        
        <!-- Critical Patient Reports -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-heartbeat" style="color: #dc2626; margin-right: 0.5rem;"></i>
                    Critical Patient Reports
                </h3>
            </div>
            <div class="card-content">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <button class="btn btn-danger" data-report-type="malnutrition-cases" style="justify-content: flex-start; text-align: left;">
                        <i class="fas fa-exclamation-circle"></i>
                        Malnutrition Cases by Severity
                    </button>
                    <button class="btn btn-primary" data-report-type="patient-progress" style="justify-content: flex-start; text-align: left;">
                        <i class="fas fa-chart-line"></i>
                        Patient Progress & Recovery Tracking
                    </button>
                    <button class="btn btn-info" data-report-type="individual-patient" style="justify-content: flex-start; text-align: left;">
                        <i class="fas fa-user-md"></i>
                        Individual Patient Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Inventory Management -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes" style="color: #f59e0b; margin-right: 0.5rem;"></i>
                    Inventory Management
                </h3>
            </div>
            <div class="card-content">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <button class="btn btn-warning" data-report-type="low-stock-alert" style="justify-content: flex-start; text-align: left;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Low Stock Alerts & Restocking
                    </button>
                    <button class="btn btn-secondary" data-report-type="inventory" style="justify-content: flex-start; text-align: left;">
                        <i class="fas fa-warehouse"></i>
                        Complete Inventory Status
                    </button>
                </div>
            </div>
        </div>

        <!-- Performance Analytics -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area" style="color: #2e7d32; margin-right: 0.5rem;"></i>
                    Performance Analytics
                </h3>
            </div>
            <div class="card-content">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <button class="btn btn-success" data-report-type="monthly-trends" style="justify-content: flex-start; text-align: left;">
                        <i class="fas fa-chart-line"></i>
                        Monthly Trends & Statistics
                    </button>
                    <button class="btn btn-info" data-report-type="user-activity" style="justify-content: flex-start; text-align: left;">
                        <i class="fas fa-users"></i>
                        User Activity & System Usage
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Activities Section -->
    <div class="content-card" style="margin-top: 1rem;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history" style="margin-right: 0.5rem;"></i>
                Recent System Activities
            </h3>
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
                    <div class="empty-activities-placeholder">
                        <i class="fas fa-inbox empty-activities-icon"></i>
                        <p>No recent activities found</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>



    <!-- Additional Analytics Section -->
    <div class="reports-grid">
        <!-- Patient Distribution -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Patient Distribution</h3>
                <div class="distribution-controls">
                    <small class="text-muted">Based on latest nutritional assessments</small>
                    <div class="view-toggle">
                        <button class="toggle-btn active" data-view="bars" id="barsViewBtn">
                            <i class="fas fa-chart-bar"></i>
                            Bars
                        </button>
                        <button class="toggle-btn" data-view="pie" id="pieViewBtn">
                            <i class="fas fa-chart-pie"></i>
                            Pie Chart
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-content">
                @if(isset($reports['patient_distribution']))
                    <!-- Progress Bars View -->
                    <div id="barsView" class="distribution-view active">
                        <div class="progress-container">
                            <div class="progress-label">
                                <span>Normal Weight</span>
                                <span>{{ $reports['patient_distribution']['normal']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['normal']['count'] ?? 0 }})</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ $reports['patient_distribution']['normal']['percentage'] ?? 0 }}%; background: linear-gradient(90deg, #2e7d32, #43a047)"></div>
                            </div>
                        </div>
                        <div class="progress-container">
                            <div class="progress-label">
                                <span>Underweight</span>
                                <span>{{ $reports['patient_distribution']['underweight']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['underweight']['count'] ?? 0 }})</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ $reports['patient_distribution']['underweight']['percentage'] ?? 0 }}%; background: linear-gradient(90deg, #f59e0b, #f97316)"></div>
                            </div>
                        </div>
                        <div class="progress-container">
                            <div class="progress-label">
                                <span>Malnourished</span>
                                <span>{{ $reports['patient_distribution']['malnourished']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['malnourished']['count'] ?? 0 }})</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ $reports['patient_distribution']['malnourished']['percentage'] ?? 0 }}%; background: linear-gradient(90deg, #ef4444, #dc2626)"></div>
                            </div>
                        </div>
                        <div class="progress-container">
                            <div class="progress-label">
                                <span>Severe Malnourishment</span>
                                <span>{{ $reports['patient_distribution']['severe_malnourishment']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['severe_malnourishment']['count'] ?? 0 }})</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ $reports['patient_distribution']['severe_malnourishment']['percentage'] ?? 0 }}%; background: linear-gradient(90deg, #991b1b, #7f1d1d)"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pie Chart View -->
                    <div id="pieView" class="distribution-view">
                        <div class="pie-chart-container">
                            <canvas id="patientDistributionChart"></canvas>
                        </div>
                        <div class="pie-legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background: #2e7d32"></div>
                                <span>Normal Weight: {{ $reports['patient_distribution']['normal']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['normal']['count'] ?? 0 }} patients)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #f59e0b"></div>
                                <span>Underweight: {{ $reports['patient_distribution']['underweight']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['underweight']['count'] ?? 0 }} patients)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #ef4444"></div>
                                <span>Malnourished: {{ $reports['patient_distribution']['malnourished']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['malnourished']['count'] ?? 0 }} patients)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #991b1b"></div>
                                <span>Severe: {{ $reports['patient_distribution']['severe_malnourishment']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['severe_malnourishment']['count'] ?? 0 }} patients)</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted">
                        <p>No patient data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Monthly Progress -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Monthly Progress</h3>
                <small class="text-muted">Last 6 months assessment and recovery trends</small>
            </div>
            <div class="card-content">
                @if(isset($reports['monthly_progress']))
                    <div class="monthly-stats">
                        <div class="stat-row">
                            <div class="stat-item">
                                <div class="stat-number">{{ $reports['monthly_progress']['total_assessments'] }}</div>
                                <div class="stat-label">Total Assessments</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">{{ $reports['monthly_progress']['total_recovered'] }}</div>
                                <div class="stat-label">Recovered Cases</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">
                                    @if($reports['monthly_progress']['total_assessments'] > 0)
                                        {{ round(($reports['monthly_progress']['total_recovered'] / $reports['monthly_progress']['total_assessments']) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </div>
                                <div class="stat-label">Recovery Rate</div>
                            </div>
                        </div>
                    </div>
                    <div class="monthly-chart" style="margin-top: 1.5rem;">
                        <canvas id="monthlyProgressChart" height="150"></canvas>
                    </div>
                @else
                    <div class="chart-placeholder" style="height: 200px;">
                        <div class="chart-placeholder-content">
                            <i class="fas fa-chart-line chart-placeholder-icon" style="font-size: 2.5rem; color: #2e7d32;"></i>
                            <p class="chart-placeholder-title">Monthly Progress</p>
                            <p class="chart-placeholder-subtitle">Assessment and recovery data will be displayed here.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script>
        // Pass monthly progress data to JavaScript
        @if(isset($reports['monthly_progress']))
        window.monthlyProgressData = {
            months: {!! json_encode($reports['monthly_progress']['months']) !!},
            assessments: {!! json_encode($reports['monthly_progress']['assessments']) !!},
            recovered: {!! json_encode($reports['monthly_progress']['recovered']) !!},
            total_assessments: {!! json_encode($reports['monthly_progress']['total_assessments']) !!},
            total_recovered: {!! json_encode($reports['monthly_progress']['total_recovered']) !!},
            patient_progress: {!! json_encode($reports['monthly_progress']['patient_progress'] ?? []) !!},
            barangay_progress: {!! json_encode($reports['monthly_progress']['barangay_progress'] ?? []) !!},
            barangays: {!! json_encode($reports['monthly_progress']['barangays'] ?? []) !!}
        };
        @endif
        
        // Pass patient distribution data to JavaScript
        @if(isset($reports['patient_distribution']))
        window.patientDistributionData = {
            normal: {!! json_encode($reports['patient_distribution']['normal']) !!},
            underweight: {!! json_encode($reports['patient_distribution']['underweight']) !!},
            malnourished: {!! json_encode($reports['patient_distribution']['malnourished']) !!},
            severe_malnourishment: {!! json_encode($reports['patient_distribution']['severe_malnourishment']) !!},
            barangay_breakdown: {!! json_encode($reports['patient_distribution']['barangay_breakdown'] ?? []) !!}
        };
        @endif
        
        // Pass stats data to JavaScript
        window.reportsStatsData = {
            monthly_assessments: {!! json_encode($reports['monthly_assessments']) !!},
            low_stock_items: {!! json_encode($reports['low_stock_items']) !!},
            low_stock_items_data: {!! json_encode($reports['low_stock_items_data']) !!},
            active_users: {!! json_encode($reports['active_users']) !!},
            total_patients: {!! json_encode($reports['total_patients']) !!}
        };
    </script>
    <script src="{{ asset('js/admin/modal-bootstrap.js') }}?v={{ filemtime(public_path('js/admin/modal-bootstrap.js')) }}"></script>
    <script src="{{ asset('js/admin/report-content.js') }}?v={{ filemtime(public_path('js/admin/report-content.js')) }}"></script>
    <script src="{{ asset('js/admin/admin-reports-enhanced.js') }}?v={{ filemtime(public_path('js/admin/admin-reports-enhanced.js')) }}"></script>
@endpush
