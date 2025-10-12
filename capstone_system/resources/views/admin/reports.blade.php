@extends('layouts.dashboard')

@section('title', 'Reports & Analytics')

@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'View detailed reports and analytics for your nutrition system.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-reports.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/report-content.css') }}">
    <style>
        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .main-content {
            background: transparent;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 24px 24px;
        }
        .page-header h1 {
            color: white;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .page-header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
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

    <!-- Report Actions -->
    <div class="reports-grid">
        <!-- Quick Reports -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Quick Reports</h3>
            </div>
            <div class="card-content">
                <div class="quick-reports-container">
                    <button class="btn btn-primary" data-report-type="user-activity">
                        <i class="fas fa-file-pdf"></i>
                        User Activity Report
                    </button>
                    <button class="btn btn-secondary" data-report-type="inventory">
                        <i class="fas fa-file-excel"></i>
                        Inventory Report
                    </button>
                        <!-- Low Stock Alert button removed -->
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
                        <div class="empty-activities-placeholder">
                            <i class="fas fa-inbox empty-activities-icon"></i>
                            <p>No recent activities found</p>
                        </div>
                    @endforelse
                </div>
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
                                <div class="progress-bar" style="width: {{ $reports['patient_distribution']['normal']['percentage'] ?? 0 }}%; background: linear-gradient(90deg, #10b981, #059669)"></div>
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
                                <div class="legend-color" style="background: #10b981"></div>
                                <span>Normal Weight: {{ $reports['patient_distribution']['normal']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['normal']['count'] ?? 0 }})</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #f59e0b"></div>
                                <span>Underweight: {{ $reports['patient_distribution']['underweight']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['underweight']['count'] ?? 0 }})</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #ef4444"></div>
                                <span>Malnourished: {{ $reports['patient_distribution']['malnourished']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['malnourished']['count'] ?? 0 }})</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #991b1b"></div>
                                <span>Severe: {{ $reports['patient_distribution']['severe_malnourishment']['percentage'] ?? 0 }}% ({{ $reports['patient_distribution']['severe_malnourishment']['count'] ?? 0 }})</span>
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
                            <i class="fas fa-chart-line chart-placeholder-icon" style="font-size: 2.5rem; color: #4f46e5;"></i>
                            <p class="chart-placeholder-title">Monthly Progress</p>
                            <p class="chart-placeholder-subtitle">Assessment and recovery data will be displayed here.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content report-modal">
                <div class="modal-header report-modal-header">
                    <h3 class="modal-title report-modal-title" id="reportModalLabel">Inventory Report</h3>
                    <button type="button" class="btn-close report-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body report-modal-body">
                    <div id="reportModalContent">
                        <!-- Report content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer report-modal-footer">
                    <button type="button" class="btn btn-outline-secondary modal-close-btn" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary modal-download-btn" id="downloadReportBtn">
                        <i class="fas fa-download"></i>
                        Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pass monthly progress data to JavaScript
        @if(isset($reports['monthly_progress']))
        window.monthlyProgressData = {
            months: {!! json_encode($reports['monthly_progress']['months']) !!},
            assessments: {!! json_encode($reports['monthly_progress']['assessments']) !!},
            recovered: {!! json_encode($reports['monthly_progress']['recovered']) !!}
        };
        @endif
        
        // Pass patient distribution data to JavaScript
        @if(isset($reports['patient_distribution']))
        window.patientDistributionData = {
            normal: {!! json_encode($reports['patient_distribution']['normal']) !!},
            underweight: {!! json_encode($reports['patient_distribution']['underweight']) !!},
            malnourished: {!! json_encode($reports['patient_distribution']['malnourished']) !!},
            severe_malnourishment: {!! json_encode($reports['patient_distribution']['severe_malnourishment']) !!}
        };
        @endif
    </script>
    <script src="{{ asset('js/admin/modal-bootstrap.js') }}"></script>
    <script src="{{ asset('js/admin/report-content.js') }}"></script>
    <script src="{{ asset('js/admin/admin-reports-enhanced.js') }}"></script>
@endpush
