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

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="width: 800px; max-width: 95vw; border-radius: 1rem; box-shadow: 0 8px 32px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background: linear-gradient(90deg, #38b6ff 0%, #6dd5ed 100%); border-top-left-radius: 1rem; border-top-right-radius: 1rem; color: #fff;">
                    <h3 class="modal-title" id="reportModalLabel" style="font-weight: 600; letter-spacing: 1px;">Inventory Report</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
                </div>
                <div class="modal-body" style="background: #f8f9fa; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                    <div id="reportModalContent">
                        <!-- Report content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Close</button>
                    <button type="button" class="btn btn-primary" id="downloadReportBtn" style="border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(56,182,255,0.15);">
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
