@extends('layouts.dashboard')

@section('title', 'Nutritionist Dashboard')

@section('page-title', 'Nutritionist Dashboard')
@section('page-subtitle', 'Welcome back, ' . Auth::user()->first_name . '! Manage your patients and assessments.')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/dashboard.css') }}?v={{ filemtime(public_path('css/nutritionist/dashboard.css')) }}">
@endpush

@section('content')
    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">My Patients</div>
                <div class="stat-icon success">
                    <i class="fas fa-user-injured"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['my_patients'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>Active patients</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Active Cases</div>
                <div class="stat-icon warning">
                    <i class="fas fa-heartbeat"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['active_cases'] }}</div>
            <div class="stat-change negative">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Ongoing treatment</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">This Month</div>
                <div class="stat-icon primary">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_assessments_this_month'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-chart-line"></i>
                <span>Screening done</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Completed</div>
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['completed_assessments'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-award"></i>
                <span>Total completed</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Assessment Trends Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Screening Trends</div>
                    <div class="chart-subtitle">Last 6 months activity</div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="assessmentTrendsChart"></canvas>
            </div>
        </div>

        <!-- Patient Gender Distribution -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Patient Distribution</div>
                    <div class="chart-subtitle">By gender</div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="genderChart"></canvas>
            </div>
        </div>

        <!-- Age Distribution -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Age Distribution</div>
                    <div class="chart-subtitle">Patient age groups in years</div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="ageChart"></canvas>
            </div>
        </div>

        <!-- Nutritional Status -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Nutritional Status</div>
                    <div class="chart-subtitle">BMI for age categories</div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="nutritionalStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="recent-activity-grid">
        <!-- Recent Patients -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Patients</h3>
                <a href="{{ route('nutritionist.patients') }}" class="btn btn-secondary">
                    <i class="fas fa-users"></i>
                    View All
                </a>
            </div>
            <div class="card-content">
                @forelse($stats['recent_patients'] as $patient)
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>
                                </div>
                                <div class="activity-time">
                                    Age: {{ floor($patient->age_months / 12) }} years | Parent: {{ $patient->parent->first_name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-user-plus text-gray-400 text-2xl mb-2"></i>
                        <p class="text-gray-500">No patients assigned yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Assessments -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Assessments</h3>
                <a href="{{ route('nutritionist.assessments') }}" class="btn btn-secondary">
                    <i class="fas fa-clipboard-list"></i>
                    View All
                </a>
            </div>
            <div class="card-content">
                @forelse($stats['recent_assessments'] as $assessment)
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <strong>{{ $assessment->patient->first_name }} {{ $assessment->patient->last_name }}</strong>
                                    @if($assessment->completed_at)
                                        <span class="badge badge-success">Completed</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </div>
                                <div class="activity-time">{{ $assessment->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard text-gray-400 text-2xl mb-2"></i>
                        <p class="text-gray-500">No assessments found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <script>
        // Pass server-side data to the external JS file
        window.assessmentTrendsData = {!! json_encode($monthlyAssessments) !!};
        window.genderData           = {!! json_encode($genderStats) !!};
        window.ageData              = {!! json_encode($ageGroups) !!};
        window.nutritionalData      = {!! json_encode($nutritionalStatus) !!};
    </script>

    <script src="{{ asset('js/nutritionist/nutritionist-dashboard.js') }}?v={{ filemtime(public_path('js/nutritionist/nutritionist-dashboard.js')) }}"></script>
@endpush
