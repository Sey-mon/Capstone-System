@extends('layouts.dashboard')

@section('title', 'Reports & Analytics')

@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'View detailed reports and analytics for your nutrition system.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-reports.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/report-content.css') }}">
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

    <!-- Charts Section -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Assessment Trends</h3>
            <div class="chart-period-buttons">
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
            <div id="assessment-chart">
                @if(isset($reports['assessment_trends']) && count($reports['assessment_trends']) > 0)
                    <canvas id="trendsChart"></canvas>
                @else
                    <div class="chart-placeholder">
                        <div class="chart-placeholder-content">
                            <i class="fas fa-chart-area chart-placeholder-icon"></i>
                            <p class="chart-placeholder-title">Charts Coming Soon</p>
                            <p class="chart-placeholder-subtitle">Interactive charts and graphs will be available here.</p>
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
    <script src="{{ asset('js/admin/modal-bootstrap.js') }}"></script>
    <script src="{{ asset('js/admin/report-content.js') }}"></script>
    <script src="{{ asset('js/admin/admin-reports-enhanced.js') }}"></script>
@endpush
