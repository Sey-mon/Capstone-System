@extends('layouts.dashboard')

@section('title', 'Nutritionist Dashboard')

@section('page-title', 'Nutritionist Dashboard')
@section('page-subtitle', 'Welcome back, ' . Auth::user()->first_name . '! Manage your patients and assessments.')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/dashboard.css') }}">
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }
        .chart-subtitle {
            font-size: 14px;
            color: #718096;
            margin-top: 4px;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                <span>Assessments done</span>
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
                    <div class="chart-title">Assessment Trends</div>
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
    
    <script>
        // Chart.js Global Configuration
        Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
        Chart.defaults.color = '#4a5568';
        
        // Assessment Trends Chart (Line Chart)
        const assessmentTrendsData = {!! json_encode($monthlyAssessments) !!};
        const trendsCtx = document.getElementById('assessmentTrendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: assessmentTrendsData.map(d => {
                    const date = new Date(d.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Total Assessments',
                    data: assessmentTrendsData.map(d => d.count),
                    borderColor: '#4299e1',
                    backgroundColor: 'rgba(66, 153, 225, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Completed',
                    data: assessmentTrendsData.map(d => d.completed),
                    borderColor: '#48bb78',
                    backgroundColor: 'rgba(72, 187, 120, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Pending',
                    data: assessmentTrendsData.map(d => d.pending),
                    borderColor: '#ed8936',
                    backgroundColor: 'rgba(237, 137, 54, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
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

        // Gender Distribution Chart (Pie Chart)
        const genderData = {!! json_encode($genderStats) !!};
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: genderData.map(d => d.sex === 'M' ? 'Male' : 'Female'),
                datasets: [{
                    data: genderData.map(d => d.count),
                    backgroundColor: [
                        'rgba(66, 153, 225, 0.8)',
                        'rgba(237, 100, 166, 0.8)'
                    ],
                    borderColor: [
                        'rgba(66, 153, 225, 1)',
                        'rgba(237, 100, 166, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Age Distribution Chart (Bar Chart)
        const ageData = {!! json_encode($ageGroups) !!};
        const ageCtx = document.getElementById('ageChart').getContext('2d');
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: ageData.map(d => d.age_years + ' years'),
                datasets: [{
                    label: 'Number of Patients',
                    data: ageData.map(d => d.count),
                    backgroundColor: 'rgba(72, 187, 120, 0.7)',
                    borderColor: 'rgba(72, 187, 120, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
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

        // Nutritional Status Chart (Doughnut Chart)
        const nutritionalData = {!! json_encode($nutritionalStatus) !!};
        const nutritionalCtx = document.getElementById('nutritionalStatusChart').getContext('2d');
        
        const statusColors = {
            'Severely Wasted': '#e53e3e',
            'Wasted': '#ed8936',
            'Normal': '#48bb78',
            'Possible Risk of Overweight': '#ecc94b',
            'Overweight': '#ed8936',
            'Obese': '#e53e3e'
        };
        
        new Chart(nutritionalCtx, {
            type: 'doughnut',
            data: {
                labels: nutritionalData.map(d => d.bmi_for_age || 'Not Assessed'),
                datasets: [{
                    data: nutritionalData.map(d => d.count),
                    backgroundColor: nutritionalData.map(d => statusColors[d.bmi_for_age] || '#cbd5e0'),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 10,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    </script>
    
    <script src="{{ asset('js/nutritionist/nutritionist-dashboard.js') }}"></script>
@endpush
