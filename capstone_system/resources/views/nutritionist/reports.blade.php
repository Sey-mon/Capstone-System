@extends('layouts.dashboard')

@section('title', 'Reports & Monitoring')

@section('page-title', 'Reports & Monitoring')
@section('page-subtitle', 'Track and monitor children under your care for main office reporting')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .stat-icon.success {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            color: white;
        }
        .stat-icon.warning {
            background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%);
            color: white;
        }
        .stat-icon.info {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
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
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
        }
        .btn-secondary:hover {
            box-shadow: 0 4px 6px rgba(52, 211, 153, 0.3);
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
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .alert-box.warning {
            background: #ecfdf5;
            color: #047857;
            border-left: 4px solid #34d399;
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
            <div style="border: 2px solid #d1fae5; border-radius: 12px; padding: 1.5rem; background: #f0fdf4;">
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
            <div style="border: 2px solid #d1fae5; border-radius: 12px; padding: 1.5rem; background: #f0fdf4;">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">
                    <i class="fas fa-clipboard-list" style="color: #10b981;"></i> Assessment Summary Report
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
            <div style="border: 2px solid #d1fae5; border-radius: 12px; padding: 1.5rem; background: #f0fdf4;">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">
                    <i class="fas fa-chart-line" style="color: #10b981;"></i> Monthly Progress Report
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
                        <button onclick="viewAssessmentDetails({{ $assessment->assessment_id }})" 
                           class="btn-download btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.875rem; border: none;">
                            <i class="fas fa-eye"></i> View
                        </button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    // View Assessment Details with Sweet Alert
    function viewAssessmentDetails(assessmentId) {
        // Show loading
        Swal.fire({
            title: 'Loading...',
            html: 'Please wait while we fetch the assessment details',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch assessment details
        fetch(`/nutritionist/assessment/${assessmentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAssessmentModal(data.assessment);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to load assessment details',
                        confirmButtonColor: '#10b981'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while fetching the assessment details',
                    confirmButtonColor: '#10b981'
                });
            });
    }

    function showAssessmentModal(assessment) {
        // Helper function to format follow-up plan object
        function formatFollowUpObject(followUp) {
            let html = '';
            
            if (typeof followUp === 'string') {
                return followUp;
            }
            
            // Handle object format
            if (followUp.schedule) {
                html += `<p style="margin: 0 0 0.5rem 0;"><strong>Schedule:</strong> ${followUp.schedule}</p>`;
            }
            if (followUp.frequency) {
                html += `<p style="margin: 0 0 0.5rem 0;"><strong>Frequency:</strong> ${followUp.frequency}</p>`;
            }
            if (followUp.monitoring) {
                html += `<p style="margin: 0 0 0.5rem 0;"><strong>Monitoring:</strong> ${followUp.monitoring}</p>`;
            }
            if (followUp.assessments && Array.isArray(followUp.assessments)) {
                html += `<p style="margin: 0 0 0.25rem 0;"><strong>Assessments:</strong></p>`;
                html += '<ul style="margin: 0 0 0.5rem 0; padding-left: 1.5rem;">';
                followUp.assessments.forEach(item => {
                    html += `<li>${item}</li>`;
                });
                html += '</ul>';
            }
            if (followUp.target_outcomes && Array.isArray(followUp.target_outcomes)) {
                html += `<p style="margin: 0 0 0.25rem 0;"><strong>Target Outcomes:</strong></p>`;
                html += '<ul style="margin: 0; padding-left: 1.5rem;">';
                followUp.target_outcomes.forEach(item => {
                    html += `<li>${item}</li>`;
                });
                html += '</ul>';
            }
            
            // If still empty, try to convert to readable format
            if (!html) {
                html = Object.entries(followUp)
                    .map(([key, value]) => {
                        const formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        if (Array.isArray(value)) {
                            return `<p style="margin: 0 0 0.5rem 0;"><strong>${formattedKey}:</strong> ${value.join(', ')}</p>`;
                        }
                        return `<p style="margin: 0 0 0.5rem 0;"><strong>${formattedKey}:</strong> ${value}</p>`;
                    })
                    .join('');
            }
            
            return html || 'Follow-up plan details available';
        }

        // Parse treatment plan if available
        let treatmentPlanHtml = '';
        if (assessment.treatment_plan) {
            const plan = assessment.treatment_plan;
            
            // Immediate Actions
            if (plan.immediate_actions && plan.immediate_actions.length > 0) {
                treatmentPlanHtml += `
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: #10b981; font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-bolt" style="font-size: 0.875rem;"></i>
                            Immediate Actions
                        </h4>
                        <ul style="margin: 0; padding-left: 1.5rem; color: #374151;">
                            ${plan.immediate_actions.map(action => `<li style="margin-bottom: 0.5rem;">${action}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }

            // Dietary Recommendations
            if (plan.dietary_recommendations && plan.dietary_recommendations.length > 0) {
                treatmentPlanHtml += `
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: #10b981; font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-utensils" style="font-size: 0.875rem;"></i>
                            Dietary Recommendations
                        </h4>
                        <ul style="margin: 0; padding-left: 1.5rem; color: #374151;">
                            ${plan.dietary_recommendations.map(rec => `<li style="margin-bottom: 0.5rem;">${rec}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }

            // Supplementary Feeding
            if (plan.supplementary_feeding) {
                const feeding = plan.supplementary_feeding;
                treatmentPlanHtml += `
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: #10b981; font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-apple-alt" style="font-size: 0.875rem;"></i>
                            Supplementary Feeding Program
                        </h4>
                        <div style="background: #f0fdf4; padding: 1rem; border-radius: 8px; border-left: 4px solid #10b981;">
                            ${feeding.duration ? `<p style="margin: 0 0 0.5rem 0; color: #374151;"><strong>Duration:</strong> ${feeding.duration}</p>` : ''}
                            ${feeding.frequency ? `<p style="margin: 0 0 0.5rem 0; color: #374151;"><strong>Frequency:</strong> ${feeding.frequency}</p>` : ''}
                            ${feeding.supplementary_food ? `<p style="margin: 0; color: #374151;"><strong>Supplementary Food:</strong> ${feeding.supplementary_food}</p>` : ''}
                        </div>
                    </div>
                `;
            }

            // Medical Interventions
            if (plan.medical_interventions && plan.medical_interventions.length > 0) {
                treatmentPlanHtml += `
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: #10b981; font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-stethoscope" style="font-size: 0.875rem;"></i>
                            Medical Interventions
                        </h4>
                        <ul style="margin: 0; padding-left: 1.5rem; color: #374151;">
                            ${plan.medical_interventions.map(intervention => `<li style="margin-bottom: 0.5rem;">${intervention}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }

            // Follow-up Plan
            if (plan.follow_up_plan) {
                const followUpContent = typeof plan.follow_up_plan === 'object' 
                    ? formatFollowUpObject(plan.follow_up_plan)
                    : plan.follow_up_plan;
                    
                treatmentPlanHtml += `
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: #10b981; font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-calendar-check" style="font-size: 0.875rem;"></i>
                            Follow-up Plan
                        </h4>
                        <div style="margin: 0; padding: 0.75rem; background: #f9fafb; border-radius: 6px; color: #374151;">
                            ${followUpContent}
                        </div>
                    </div>
                `;
            }
        }

        const htmlContent = `
            <div style="text-align: left; max-height: 70vh; overflow-y: auto; padding: 0 0.5rem;">
                <!-- Patient Information -->
                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; color: white;">
                    <h3 style="margin: 0 0 1rem 0; font-size: 1.5rem; font-weight: 700; color: white;">
                        ${assessment.patient.name}
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; font-size: 0.875rem;">
                        <div>
                            <div style="opacity: 0.9; margin-bottom: 0.25rem;">Age</div>
                            <div style="font-weight: 600; font-size: 1rem;">${assessment.patient.age_months} months</div>
                        </div>
                        <div>
                            <div style="opacity: 0.9; margin-bottom: 0.25rem;">Sex</div>
                            <div style="font-weight: 600; font-size: 1rem; text-transform: capitalize;">${assessment.patient.sex}</div>
                        </div>
                        <div>
                            <div style="opacity: 0.9; margin-bottom: 0.25rem;">Barangay</div>
                            <div style="font-weight: 600; font-size: 1rem;">${assessment.patient.barangay}</div>
                        </div>
                    </div>
                </div>

                <!-- Assessment Details -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; border-left: 4px solid #10b981;">
                        <div style="color: #6b7280; font-size: 0.75rem; margin-bottom: 0.25rem;">Assessment Date</div>
                        <div style="color: #111827; font-weight: 600;">${assessment.assessment_date}</div>
                    </div>
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; border-left: 4px solid #10b981;">
                        <div style="color: #6b7280; font-size: 0.75rem; margin-bottom: 0.25rem;">Status</div>
                        <div style="color: #111827; font-weight: 600;">${assessment.status}</div>
                    </div>
                </div>

                <!-- Measurements -->
                <div style="background: white; border: 2px solid #d1fae5; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="color: #111827; font-size: 1.125rem; font-weight: 700; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-ruler-combined" style="color: #10b981;"></i>
                        Measurements
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 8px;">
                            <div style="color: #059669; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">WEIGHT</div>
                            <div style="color: #111827; font-size: 1.5rem; font-weight: 700;">${assessment.measurements.weight_kg}</div>
                            <div style="color: #6b7280; font-size: 0.75rem;">kg</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 8px;">
                            <div style="color: #059669; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">HEIGHT</div>
                            <div style="color: #111827; font-size: 1.5rem; font-weight: 700;">${assessment.measurements.height_cm}</div>
                            <div style="color: #6b7280; font-size: 0.75rem;">cm</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 8px;">
                            <div style="color: #059669; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">BMI</div>
                            <div style="color: #111827; font-size: 1.5rem; font-weight: 700;">${assessment.measurements.bmi || 'N/A'}</div>
                            <div style="color: #6b7280; font-size: 0.75rem;">${assessment.measurements.bmi ? 'kg/m²' : ''}</div>
                        </div>
                    </div>
                </div>

                <!-- Diagnosis -->
                <div style="background: white; border: 2px solid #d1fae5; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="color: #111827; font-size: 1.125rem; font-weight: 700; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-diagnoses" style="color: #10b981;"></i>
                        Diagnosis & Status
                    </h4>
                    <div style="background: #ecfdf5; padding: 1rem; border-radius: 8px; border-left: 4px solid #10b981;">
                        <div style="color: #059669; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">NUTRITIONAL STATUS</div>
                        <div style="color: #111827; font-size: 1rem; font-weight: 600;">${assessment.diagnosis}</div>
                        ${assessment.recovery_status ? `
                            <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #d1fae5;">
                                <div style="color: #059669; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">RECOVERY STATUS</div>
                                <div style="color: #374151; font-size: 0.875rem;">${assessment.recovery_status}</div>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <!-- Treatment Plan -->
                ${treatmentPlanHtml ? `
                    <div style="background: white; border: 2px solid #d1fae5; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="color: #111827; font-size: 1.125rem; font-weight: 700; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-clipboard-list" style="color: #10b981;"></i>
                            Treatment Plan
                        </h4>
                        ${treatmentPlanHtml}
                    </div>
                ` : ''}

                <!-- Notes -->
                ${assessment.notes ? `
                    <div style="background: white; border: 2px solid #d1fae5; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem;">
                        <h4 style="color: #111827; font-size: 1.125rem; font-weight: 700; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-sticky-note" style="color: #10b981;"></i>
                            Additional Notes
                        </h4>
                        <div style="background: #f8f9fa; padding: 1.25rem; border-radius: 8px; border-left: 4px solid #10b981;">
                            <p style="margin: 0; color: #374151; line-height: 1.6; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word;">${assessment.notes}</p>
                        </div>
                    </div>
                ` : ''}

                <!-- Assessed By -->
                <div style="background: #f9fafb; padding: 0.75rem 1rem; border-radius: 8px; text-align: center; color: #6b7280; font-size: 0.875rem;">
                    Assessed by <strong style="color: #111827;">${assessment.nutritionist}</strong>
                    ${assessment.completed_at ? ` on ${assessment.completed_at}` : ''}
                </div>
            </div>
        `;

        Swal.fire({
            title: '<span style="color: #111827; font-weight: 700;">Assessment Details</span>',
            html: htmlContent,
            width: '900px',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-download"></i> Download PDF',
            confirmButtonColor: '#10b981',
            showCancelButton: true,
            cancelButtonText: 'Close',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'assessment-modal',
                confirmButton: 'swal-confirm-btn',
                cancelButton: 'swal-cancel-btn'
            },
            didOpen: () => {
                // Add custom styles
                const style = document.createElement('style');
                style.innerHTML = `
                    .assessment-modal {
                        border-radius: 16px !important;
                        padding: 0 !important;
                    }
                    .assessment-modal .swal2-title {
                        padding: 1.5rem 1.5rem 1rem 1.5rem;
                        border-bottom: 2px solid #f3f4f6;
                        margin: 0;
                    }
                    .assessment-modal .swal2-html-container {
                        padding: 1.5rem;
                        margin: 0;
                    }
                    .assessment-modal .swal2-actions {
                        padding: 1rem 1.5rem 1.5rem 1.5rem;
                        margin: 0;
                        border-top: 2px solid #f3f4f6;
                    }
                    .swal-confirm-btn, .swal-cancel-btn {
                        padding: 0.625rem 1.5rem !important;
                        border-radius: 8px !important;
                        font-weight: 600 !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 0.5rem !important;
                    }
                    .swal-confirm-btn:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3) !important;
                    }
                `;
                document.head.appendChild(style);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Download PDF
                window.location.href = `/nutritionist/assessment/${assessment.assessment_id}/pdf`;
            }
        });
    }

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
                },
                tooltip: {
                    padding: 16,
                    bodySpacing: 8,
                    bodyFont: {
                        size: 16
                    },
                    titleFont: {
                        size: 18,
                        weight: 'bold'
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            label += value + ' (' + percentage + '%)';
                            return label;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 14
                    },
                    formatter: function(value, context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return percentage + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
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
                },
                tooltip: {
                    padding: 16,
                    bodySpacing: 8,
                    bodyFont: {
                        size: 16
                    },
                    titleFont: {
                        size: 18,
                        weight: 'bold'
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            label += value + ' (' + percentage + '%)';
                            return label;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 14
                    },
                    formatter: function(value, context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return percentage + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
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
                },
                tooltip: {
                    padding: 16,
                    bodySpacing: 8,
                    bodyFont: {
                        size: 16
                    },
                    titleFont: {
                        size: 18,
                        weight: 'bold'
                    }
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
