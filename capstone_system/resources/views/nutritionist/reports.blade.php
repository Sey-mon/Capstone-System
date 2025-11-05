@extends('layouts.dashboard')

@section('title', 'Reports & Monitoring')

@section('page-title', 'Reports & Monitoring')
@section('page-subtitle', 'Track and monitor children under your care for main office reporting')

@push('styles')
    <style>
        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .main-content {
            background: transparent;
        }
        .page-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .stat-title {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .stat-icon.primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        .stat-icon.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .stat-icon.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        .stat-icon.info {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        .stat-change {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        .stat-change.positive {
            color: #10b981;
        }
        .stat-change.negative {
            color: #ef4444;
        }
        .stat-change.neutral {
            color: #6b7280;
        }

        /* Report Sections */
        .report-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-title i {
            color: #10b981;
        }

        /* Charts */
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1rem;
        }

        /* Tables */
        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .report-table thead th {
            background: #f9fafb;
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        .report-table tbody td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            color: #1f2937;
        }
        .report-table tbody tr:hover {
            background: #f9fafb;
        }

        /* Buttons */
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
        }
        .btn-download:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
            color: white;
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-badge.normal {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge.at-risk {
            background: #fed7aa;
            color: #92400e;
        }
        .status-badge.malnourished {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Filter Form */
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background: #f9fafb;
            border-radius: 12px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .form-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
        .form-group input,
        .form-group select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        /* Alert Box */
        .alert-box {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-box.info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
        .alert-box.warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
    </style>
@endpush

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <!-- Alert Box -->
    <div class="alert-box info">
        <i class="fas fa-info-circle" style="font-size: 1.25rem;"></i>
        <div>
            <strong>Main Office Reporting:</strong> These reports are designed for submission to the main office for tracking and monitoring of children under your care.
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Patients</div>
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $reports['total_patients'] }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-user"></i>
                <span>{{ $reports['male_patients'] }} Male, {{ $reports['female_patients'] }} Female</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Assessments</div>
                <div class="stat-icon success">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>
            <div class="stat-value">{{ $reports['total_assessments'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-check"></i>
                <span>{{ $reports['completed_assessments'] }} Completed</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">This Month</div>
                <div class="stat-icon info">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <div class="stat-value">{{ $reports['assessments_this_month'] }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-calendar"></i>
                <span>Assessments</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">4Ps Beneficiaries</div>
                <div class="stat-icon warning">
                    <i class="fas fa-hands-helping"></i>
                </div>
            </div>
            <div class="stat-value">{{ $reports['4ps_beneficiaries'] }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-percent"></i>
                <span>{{ $reports['total_patients'] > 0 ? round(($reports['4ps_beneficiaries'] / $reports['total_patients']) * 100, 1) : 0 }}% of patients</span>
            </div>
        </div>
    </div>

    <!-- Report Downloads Section -->
    <div class="report-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-file-download"></i>
                Generate Reports for Main Office
            </h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <!-- Children Monitoring Report -->
            <div style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">
                    <i class="fas fa-child" style="color: #10b981;"></i> Children Monitoring Report
                </h3>
                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">
                    Comprehensive report of all children under your care, including their nutritional status and progress.
                </p>
                <form action="{{ route('nutritionist.reports.children-monitoring.pdf') }}" method="GET" style="margin-bottom: 1rem;">
                    <div class="form-group">
                        <label>Date Range</label>
                        <input type="date" name="start_date" value="{{ now()->subMonths(3)->format('Y-m-d') }}" required>
                        <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}" required style="margin-top: 0.5rem;">
                    </div>
                    <button type="submit" class="btn-download" style="width: 100%; justify-content: center; margin-top: 1rem;">
                        <i class="fas fa-download"></i> Download PDF
                    </button>
                </form>
            </div>

            <!-- Assessment Summary Report -->
            <div style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">
                    <i class="fas fa-clipboard-list" style="color: #3b82f6;"></i> Assessment Summary Report
                </h3>
                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">
                    Summary of all assessments conducted within a specific period, with statistics and trends.
                </p>
                <form action="{{ route('nutritionist.reports.assessment-summary.pdf') }}" method="GET" style="margin-bottom: 1rem;">
                    <div class="form-group">
                        <label>Date Range</label>
                        <input type="date" name="start_date" value="{{ now()->subMonth()->format('Y-m-d') }}" required>
                        <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}" required style="margin-top: 0.5rem;">
                    </div>
                    <button type="submit" class="btn-download" style="width: 100%; justify-content: center; margin-top: 1rem;">
                        <i class="fas fa-download"></i> Download PDF
                    </button>
                </form>
            </div>

            <!-- Monthly Progress Report -->
            <div style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">
                    <i class="fas fa-chart-line" style="color: #8b5cf6;"></i> Monthly Progress Report
                </h3>
                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">
                    Track month-to-month progress of children, showing improvements and changes in nutritional status.
                </p>
                <form action="{{ route('nutritionist.reports.monthly-progress.pdf') }}" method="GET" style="margin-bottom: 1rem;">
                    <div class="form-group">
                        <label>Select Month</label>
                        <select name="month" required>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group" style="margin-top: 0.5rem;">
                        <label>Select Year</label>
                        <select name="year" required>
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="btn-download" style="width: 100%; justify-content: center; margin-top: 1rem;">
                        <i class="fas fa-download"></i> Download PDF
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Age Distribution and Nutritional Status (Side by Side) -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Age Distribution Chart -->
        <div class="report-section" style="margin-bottom: 0;">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-chart-pie"></i>
                    Age Distribution
                </h2>
            </div>
            <div class="chart-container">
                <canvas id="ageDistributionChart"></canvas>
            </div>
        </div>

        <!-- Nutritional Status Distribution -->
        @if(count($nutritionalStatus) > 0)
        <div class="report-section" style="margin-bottom: 0;">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-heartbeat"></i>
                    Nutritional Status Distribution
                </h2>
            </div>
            <div class="chart-container">
                <canvas id="nutritionalStatusChart"></canvas>
            </div>
        </div>
        @else
        <div class="report-section" style="margin-bottom: 0;">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-heartbeat"></i>
                    Nutritional Status Distribution
                </h2>
            </div>
            <div class="chart-container" style="display: flex; align-items: center; justify-content: center; color: #6b7280;">
                <p>No nutritional status data available yet.</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Monthly Assessment Trend -->
    <div class="report-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i>
                Assessment Trend (Last 6 Months)
            </h2>
        </div>
        <div class="chart-container">
            <canvas id="monthlyTrendChart"></canvas>
        </div>
    </div>

    <!-- Children Needing Attention -->
    @if(count($childrenNeedingAttention) > 0)
    <div class="report-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-exclamation-triangle"></i>
                Children Needing Attention
            </h2>
        </div>
        <div class="alert-box warning">
            <i class="fas fa-exclamation-circle" style="font-size: 1.25rem;"></i>
            <div>
                <strong>Priority Cases:</strong> These children require immediate attention and follow-up based on their latest assessment.
            </div>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Age</th>
                    <th>Barangay</th>
                    <th>Status</th>
                    <th>Last Assessment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($childrenNeedingAttention as $item)
                <tr>
                    <td>
                        <strong>{{ $item['patient']->first_name }} {{ $item['patient']->last_name }}</strong>
                    </td>
                    <td>{{ floor($item['patient']->age_months / 12) }} years, {{ $item['patient']->age_months % 12 }} months</td>
                    <td>{{ $item['patient']->barangay->name ?? 'N/A' }}</td>
                    <td>
                        <span class="status-badge {{ 
                            str_contains($item['assessment']->recovery_status, 'Severely') ? 'malnourished' : 
                            (str_contains($item['assessment']->recovery_status, 'Moderately') ? 'malnourished' : 'at-risk') 
                        }}">
                            {{ $item['assessment']->recovery_status }}
                        </span>
                    </td>
                    <td>{{ $item['assessment']->assessment_date ? $item['assessment']->assessment_date->format('M d, Y') : 'N/A' }}</td>
                    <td>
                        <a href="{{ route('nutritionist.patients') }}?patient_id={{ $item['patient']->patient_id }}" 
                           class="btn-download btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent Assessments -->
    <div class="report-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                Recent Assessments
            </h2>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Barangay</th>
                    <th>Weight (kg)</th>
                    <th>Height (cm)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAssessments as $assessment)
                <tr>
                    <td>{{ $assessment->assessment_date ? $assessment->assessment_date->format('M d, Y') : 'N/A' }}</td>
                    <td>
                        <strong>{{ $assessment->patient->first_name }} {{ $assessment->patient->last_name }}</strong>
                    </td>
                    <td>{{ $assessment->patient->barangay->name ?? 'N/A' }}</td>
                    <td>{{ number_format($assessment->weight_kg, 2) }}</td>
                    <td>{{ number_format($assessment->height_cm, 2) }}</td>
                    <td>
                        @if($assessment->recovery_status)
                        <span class="status-badge {{ 
                            str_contains($assessment->recovery_status, 'Normal') || str_contains($assessment->recovery_status, 'Recovered') ? 'normal' : 
                            (str_contains($assessment->recovery_status, 'Severely') || str_contains($assessment->recovery_status, 'Moderately') ? 'malnourished' : 'at-risk') 
                        }}">
                            {{ $assessment->recovery_status }}
                        </span>
                        @else
                        <span class="status-badge">Pending</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('nutritionist.assessment.details', $assessment->assessment_id) }}" 
                           class="btn-download btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #6b7280;">No recent assessments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Age Distribution Chart
    const ageCtx = document.getElementById('ageDistributionChart').getContext('2d');
    new Chart(ageCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($ageDistribution->toArray())) !!},
            datasets: [{
                data: {!! json_encode(array_values($ageDistribution->toArray())) !!},
                backgroundColor: [
                    '#10b981',
                    '#3b82f6',
                    '#f59e0b',
                    '#8b5cf6',
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });

    @if(count($nutritionalStatus) > 0)
    // Nutritional Status Chart
    const statusCtx = document.getElementById('nutritionalStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($nutritionalStatus)) !!},
            datasets: [{
                data: {!! json_encode(array_values($nutritionalStatus)) !!},
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#3b82f6',
                    '#8b5cf6',
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
    @endif

    // Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($monthlyTrend)) !!},
            datasets: [{
                label: 'Assessments',
                data: {!! json_encode(array_values($monthlyTrend)) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush
