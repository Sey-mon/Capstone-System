@extends('layouts.dashboard')

@section('title', 'Parent Dashboard')

@section('page-title')
    <div class="page-header-modern">
        <div class="page-title-section">
            <div class="header-content">
                <div class="breadcrumb">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                    <i class="fas fa-chevron-right"></i>
                    <span class="active">Dashboard</span>
                </div>
                <h1 class="page-title">Parent Dashboard</h1>
                <p class="page-subtitle">Welcome back, {{ Auth::user()->first_name }}! Monitor your children's health, track growth progress, and stay updated on nutritional screenings.</p>
            </div>
        </div>
        <div class="header-actions">
            <div class="quick-stat">
                <i class="fas fa-calendar-day"></i>
                <div class="quick-stat-info">
                    <span class="quick-stat-label">Today</span>
                    <span class="quick-stat-value">{{ now()->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('navigation')
    @include('partials.navigation')
@endsection

<!-- Meta tags for JavaScript -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="preview-child-url" content="{{ route('parent.preview-child') }}">
<meta name="link-child-url" content="{{ route('parent.link-child') }}">

@section('content')

    <!-- Quick Actions Bar -->
    <div class="quick-actions-bar">
        <a href="{{ route('parent.meal-plans') }}" class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="quick-action-content">
                <span class="quick-action-title">Generate Meal Plan</span>
                <span class="quick-action-desc">AI-powered nutrition planning</span>
            </div>
        </a>
        <a href="{{ route('parent.children') }}?openModal=link-child" class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-link"></i>
            </div>
            <div class="quick-action-content">
                <span class="quick-action-title">Link a Child</span>
                <span class="quick-action-desc">Add to your account</span>
            </div>
        </a>
        <a href="{{ route('parent.view-meal-plans') }}" class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-book-medical"></i>
            </div>
            <div class="quick-action-content">
                <span class="quick-action-title">View Meal Plans</span>
                <span class="quick-action-desc">Browse saved plans</span>
            </div>
        </a>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-child"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">My Children</div>
                <div class="stat-value">{{ $stats['my_children'] }}</div>
                <div class="stat-change positive">
                    <i class="fas fa-heart"></i>
                    <span>Registered children</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Total Screenings</div>
                <div class="stat-value">{{ $stats['total_assessments'] }}</div>
                <div class="stat-change positive">
                    <i class="fas fa-chart-line"></i>
                    <span>All time</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Recent Screenings</div>
                <div class="stat-value">{{ $stats['recent_assessments'] }}</div>
                <div class="stat-change positive">
                    <i class="fas fa-clock"></i>
                    <span>This month</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-user-nurse"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Under Care</div>
                <div class="stat-value">{{ $stats['children_with_growth']->filter(function($child) { return $child['child']->nutritionist !== null; })->count() }}</div>
                <div class="stat-change positive">
                    <i class="fas fa-check-circle"></i>
                    <span>With nutritionist</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth Chart Section -->
    @if($stats['children_with_growth']->count() > 0)
    <div class="chart-section">
        <div class="content-card chart-card">
            <div class="card-header">
                <div class="chart-header-left">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Growth History & Trends
                    </h3>
                    <p class="card-subtitle">Track weight and height progress over time</p>
                </div>
                <div class="chart-controls">
                    <div class="child-selector-wrapper">
                        <label for="childSelector">
                            <i class="fas fa-child"></i>
                            Select Child:
                        </label>
                        <select id="childSelector" class="child-select">
                            @foreach($stats['children_with_growth'] as $index => $childData)
                                @if($childData['assessment_history'] && count($childData['assessment_history']) > 0)
                                    <option value="{{ $index }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $childData['child']->first_name }} {{ $childData['child']->last_name }}
                                        ({{ count($childData['assessment_history']) }} screenings)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="chart-type-toggle">
                        <button class="toggle-btn active" data-view="combined" title="Show both metrics">
                            <i class="fas fa-layer-group"></i>
                            Combined
                        </button>
                        <button class="toggle-btn" data-view="weight" title="Show weight only">
                            <i class="fas fa-weight"></i>
                            Weight
                        </button>
                        <button class="toggle-btn" data-view="height" title="Show height only">
                            <i class="fas fa-ruler-vertical"></i>
                            Height
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-content chart-content">
                <div class="chart-wrapper">
                    <canvas id="growthChart"></canvas>
                </div>
                <div class="chart-footer">
                    <div class="chart-insights" id="chartInsights">
                        <div class="insight-item">
                            <i class="fas fa-info-circle"></i>
                            <span>Select a child to view their growth history and trends</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Children Growth and Assessments Section -->
    <div class="dashboard-grid">
        <!-- Children Growth Tracking -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-heartbeat"></i>
                    Children Health Status
                </h3>
                <a href="{{ route('parent.children') }}" class="btn btn-secondary">
                    <i class="fas fa-child"></i>
                    View All
                </a>
            </div>
            <div class="card-content scrollable-content">
                @forelse($stats['children_with_growth'] as $childData)
                    <div class="growth-item">
                        <div class="child-profile">
                            <div class="child-avatar-circle">
                                <i class="fas fa-child"></i>
                            </div>
                            <div class="child-info">
                                <div class="child-header">
                                    <h4>{{ $childData['child']->first_name }} {{ $childData['child']->last_name }}</h4>
                                    <span class="nutrition-status {{ strtolower(str_replace(' ', '-', $childData['nutrition_status'])) }}">
                                        @if($childData['nutrition_status'] == 'Normal')
                                            <i class="fas fa-check-circle"></i>
                                        @elseif(str_contains($childData['nutrition_status'], 'Severe'))
                                            <i class="fas fa-exclamation-triangle"></i>
                                        @elseif(str_contains($childData['nutrition_status'], 'Moderate'))
                                            <i class="fas fa-exclamation-circle"></i>
                                        @else
                                            <i class="fas fa-info-circle"></i>
                                        @endif
                                        {{ $childData['nutrition_status'] }}
                                    </span>
                                </div>
                                <div class="child-meta">
                                    @if($childData['child']->birthdate)
                                    <span class="meta-item">
                                        <i class="fas fa-calendar-day"></i>
                                        Born {{ \Carbon\Carbon::parse($childData['child']->birthdate)->format('M d, Y') }}
                                    </span>
                                    <span class="meta-divider">•</span>
                                    @endif
                                    <span class="meta-item">
                                        <i class="fas fa-birthday-cake"></i>
                                        {{ $childData['child']->age_months }} months old
                                    </span>
                                    <span class="meta-divider">•</span>
                                    <span class="meta-item">
                                        <i class="fas fa-clipboard-check"></i>
                                        {{ $childData['assessments_count'] }} screenings
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        @if($childData['latest_assessment'])
                            <div class="growth-metrics-row">
                                <div class="metric-card-small">
                                    <div class="metric-icon-small weight">
                                        <i class="fas fa-weight"></i>
                                    </div>
                                    <div class="metric-details">
                                        <span class="metric-label">Weight</span>
                                        <span class="metric-value">{{ $childData['child']->weight_kg }}<small>kg</small></span>
                                        @if($childData['weight_change'])
                                            <span class="metric-change {{ $childData['weight_change'] > 0 ? 'positive' : 'negative' }}">
                                                <i class="fas fa-{{ $childData['weight_change'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                                {{ $childData['weight_change'] > 0 ? '+' : '' }}{{ number_format($childData['weight_change'], 1) }}kg
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="metric-card-small">
                                    <div class="metric-icon-small height">
                                        <i class="fas fa-ruler-vertical"></i>
                                    </div>
                                    <div class="metric-details">
                                        <span class="metric-label">Height</span>
                                        <span class="metric-value">{{ $childData['child']->height_cm }}<small>cm</small></span>
                                        @if($childData['height_change'])
                                            <span class="metric-change {{ $childData['height_change'] > 0 ? 'positive' : 'negative' }}">
                                                <i class="fas fa-{{ $childData['height_change'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                                {{ $childData['height_change'] > 0 ? '+' : '' }}{{ number_format($childData['height_change'], 1) }}cm
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="growth-footer">
                                @if($childData['growth_trend'])
                                    <div class="growth-trend {{ $childData['growth_trend'] }}">
                                        <i class="fas fa-{{ $childData['growth_trend'] == 'improving' ? 'arrow-up' : ($childData['growth_trend'] == 'declining' ? 'arrow-down' : 'minus') }}"></i>
                                        <span>{{ ucfirst($childData['growth_trend']) }} Growth</span>
                                    </div>
                                @endif
                                <div class="last-assessment">
                                    <i class="fas fa-clock"></i>
                                    {{ $childData['latest_assessment']->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @else
                            <div class="no-assessment-compact">
                                <i class="fas fa-clipboard"></i>
                                <div>
                                    <span class="no-assessment-title">No screenings yet</span>
                                    <p>Schedule a nutritional screening</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-child"></i>
                        </div>
                        <h4>No children registered yet</h4>
                        <p>Start tracking your children's nutrition by linking them to your account.</p>
                        <button type="button" onclick="showAddChildModal()" class="btn btn-primary">
                            <i class="fas fa-link"></i>
                            Link Child
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Assessments -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Recent Activity
                </h3>
                <a href="{{ route('parent.assessments') }}" class="btn btn-secondary">
                    <i class="fas fa-clipboard-list"></i>
                    View All
                </a>
            </div>
            <div class="card-content scrollable-content">
                @forelse($stats['recent_assessments_list'] as $assessment)
                    <div class="activity-item-modern">
                        <div class="activity-timeline-dot"></div>
                        <div class="activity-icon">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <div class="activity-title">
                                    <strong>{{ $assessment->patient->first_name }} {{ $assessment->patient->last_name }}</strong>
                                    <span class="activity-badge">Screening Completed</span>
                                </div>
                                <span class="activity-date">{{ $assessment->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="activity-meta">
                                <span class="meta-item">
                                    <i class="fas fa-user-md"></i>
                                    @if($assessment->nutritionist)
                                        {{ $assessment->nutritionist->first_name }} {{ $assessment->nutritionist->last_name }}
                                    @else
                                        <em>Unassigned</em>
                                    @endif
                                </span>
                                <span class="meta-divider">•</span>
                                <span class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    {{ $assessment->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard"></i>
                        </div>
                        <h4>No screenings found</h4>
                        <p>Your children's nutritional screenings will appear here once they're completed.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/parent/parent-dashboard.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    window.childrenGrowthData = @json($stats['children_with_growth']->values());
</script>
<script src="{{ asset('js/parent/parent-dashboard.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/parent/parent-onboarding.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/parent/dashboard-link-child.js') }}?v={{ time() }}"></script>
@endpush

