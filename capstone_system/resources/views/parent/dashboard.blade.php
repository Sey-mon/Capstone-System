@extends('layouts.dashboard')

@section('title', 'Parent Dashboard')

@section('page-title')
    <div class="page-header-modern">
        <div class="page-title-section">
            <div>
                <h1 class="page-title">Parent Dashboard</h1>
                <p class="page-subtitle">Welcome back, {{ Auth::user()->first_name }}! Track your children's nutrition progress.</p>
            </div>
        </div>
    </div>
@endsection

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">My Children</div>
                <div class="stat-icon warning">
                    <i class="fas fa-child"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['my_children'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-heart"></i>
                <span>Registered children</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Assessments</div>
                <div class="stat-icon primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_assessments'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>All time</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Recent Assessments</div>
                <div class="stat-icon success">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['recent_assessments'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-clock"></i>
                <span>This month</span>
            </div>
        </div>
    </div>

    <!-- Children Growth and Assessments Section -->
    <div class="dashboard-grid">
        <!-- Children Growth Tracking -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Children Growth Tracking
                </h3>
                <a href="{{ route('parent.children') }}" class="btn btn-secondary">
                    <i class="fas fa-child"></i>
                    View All
                </a>
            </div>
            <div class="card-content">
                @forelse($stats['children_with_growth'] as $childData)
                    <div class="growth-item">
                        <div class="child-info">
                            <div class="child-header">
                                <h4>
                                    <i class="fas fa-user-circle text-primary-green"></i>
                                    {{ $childData['child']->first_name }} {{ $childData['child']->last_name }}
                                </h4>
                                <span class="nutrition-status {{ strtolower(str_replace(' ', '-', $childData['nutrition_status'])) }}">
                                    {{ $childData['nutrition_status'] }}
                                </span>
                            </div>
                            <div class="child-details">
                                <span><i class="fas fa-birthday-cake"></i> {{ $childData['child']->age_months }} months old</span>
                                <span><i class="fas fa-clipboard-list"></i> {{ $childData['assessments_count'] }} assessments</span>
                            </div>
                        </div>
                        
                        @if($childData['latest_assessment'])
                            <div class="growth-metrics">
                                <div class="metric">
                                    <span class="metric-label"><i class="fas fa-weight"></i> Weight</span>
                                    <span class="metric-value">{{ $childData['child']->weight_kg }}kg</span>
                                    @if($childData['weight_change'])
                                        <span class="metric-change {{ $childData['weight_change'] > 0 ? 'positive' : 'negative' }}">
                                            <i class="fas {{ $childData['weight_change'] > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            {{ $childData['weight_change'] > 0 ? '+' : '' }}{{ number_format($childData['weight_change'], 1) }}kg
                                        </span>
                                    @endif
                                </div>
                                <div class="metric">
                                    <span class="metric-label"><i class="fas fa-ruler-vertical"></i> Height</span>
                                    <span class="metric-value">{{ $childData['child']->height_cm }}cm</span>
                                    @if($childData['height_change'])
                                        <span class="metric-change {{ $childData['height_change'] > 0 ? 'positive' : 'negative' }}">
                                            <i class="fas {{ $childData['height_change'] > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            {{ $childData['height_change'] > 0 ? '+' : '' }}{{ number_format($childData['height_change'], 1) }}cm
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($childData['growth_trend'])
                                <div class="growth-trend {{ $childData['growth_trend'] }}">
                                    <i class="fas {{ $childData['growth_trend'] == 'improving' ? 'fa-arrow-up' : ($childData['growth_trend'] == 'declining' ? 'fa-arrow-down' : 'fa-minus') }}"></i>
                                    <span>{{ ucfirst($childData['growth_trend']) }} Growth</span>
                                </div>
                            @endif
                            
                            <div class="last-assessment">
                                <i class="fas fa-clock"></i>
                                Last assessment: {{ $childData['latest_assessment']->created_at->diffForHumans() }}
                            </div>
                        @else
                            <div class="no-assessment">
                                <i class="fas fa-info-circle"></i>
                                <span>No assessments yet</span>
                                <p>Register this child for their first nutritional assessment</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-child"></i>
                        </div>
                        <h4>No children registered yet</h4>
                        <p>Start tracking your children's nutrition by registering them in the system.</p>
                        <a href="{{ route('parent.bind-child') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Register a Child
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Assessments -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check"></i>
                    Recent Assessments
                </h3>
                <a href="{{ route('parent.assessments') }}" class="btn btn-secondary">
                    <i class="fas fa-clipboard-list"></i>
                    View All
                </a>
            </div>
            <div class="card-content">
                @forelse($stats['recent_assessments_list'] as $assessment)
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <strong>{{ $assessment->patient->first_name }} {{ $assessment->patient->last_name }}</strong>
                                    <span class="activity-badge">Assessment</span>
                                </div>
                                <div class="activity-time">
                                    <i class="fas fa-user-md"></i> By: {{ $assessment->nutritionist->first_name }} {{ $assessment->nutritionist->last_name }}
                                </div>
                                <div class="activity-time">
                                    <i class="fas fa-calendar"></i> {{ $assessment->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard"></i>
                        </div>
                        <h4>No assessments found</h4>
                        <p>Your children's nutritional assessments will appear here once they're completed.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/parent/parent-dashboard.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/parent/parent-dashboard.js') }}"></script>
@endpush
