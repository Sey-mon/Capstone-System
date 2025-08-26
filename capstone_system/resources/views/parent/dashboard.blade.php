@extends('layouts.dashboard')

@section('title', 'Parent Dashboard')

@section('page-title')
    <div class="page-header-modern">
        <div class="page-title-section">
            <i class="fas fa-home page-icon"></i>
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
<style>
/* Compact Page Header */
.page-header-modern {
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
    border-radius: var(--radius-md);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    color: var(--surface-white);
    box-shadow: var(--shadow-md);
    position: relative;
    overflow: hidden;
}

.page-header-modern::before {
    content: '';
    position: absolute;
    top: -25%;
    right: -5%;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    backdrop-filter: blur(10px);
}

.page-title-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
    z-index: 1;
}

.page-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.page-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--surface-white);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.page-subtitle {
    margin: 0.25rem 0 0 0;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 400;
}

/* Modern Minimalist Green & White Theme */
:root {
    --primary-green: #10b981;
    --primary-green-light: #34d399;
    --primary-green-dark: #059669;
    --secondary-green: #d1fae5;
    --accent-green: #6ee7b7;
    --surface-white: #ffffff;
    --surface-light: #f8fafc;
    --surface-gray: #f1f5f9;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --text-muted: #94a3b8;
    --border-light: #e2e8f0;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --radius-sm: 6px;
    --radius-md: 12px;
    --radius-lg: 16px;
}

/* Stats Grid Enhancement */
.stats-grid {
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, var(--surface-white) 0%, var(--surface-light) 100%);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: 2rem;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-green), var(--primary-green-light));
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-green-light);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--surface-white);
    font-size: 1.25rem;
}

.stat-icon.primary {
    background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark));
}

.stat-icon.success {
    background: linear-gradient(135deg, var(--accent-green), var(--primary-green));
}

.stat-icon.warning {
    background: linear-gradient(135deg, var(--primary-green-light), var(--primary-green));
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 1rem 0 0.5rem 0;
    line-height: 1;
}

.stat-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-change {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--primary-green-dark);
}

/* Content Cards */
.content-card {
    background: var(--surface-white);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: all 0.3s ease;
}

.content-card:hover {
    box-shadow: var(--shadow-md);
}

.card-header {
    padding: 2rem 2rem 1rem 2rem;
    border-bottom: 1px solid var(--border-light);
    background: linear-gradient(135deg, var(--surface-white) 0%, var(--surface-light) 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-title::before {
    content: '';
    width: 4px;
    height: 24px;
    background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
    border-radius: 2px;
}

.card-content {
    padding: 2rem;
}

/* Modern Button Styles */
.btn {
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark));
    color: var(--surface-white);
    box-shadow: var(--shadow-sm);
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--primary-green-dark), var(--primary-green));
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    color: var(--surface-white);
}

.btn-secondary {
    background: var(--surface-white);
    color: var(--primary-green);
    border: 2px solid var(--primary-green);
}

.btn-secondary:hover {
    background: var(--primary-green);
    color: var(--surface-white);
    transform: translateY(-1px);
}

/* Growth Tracking Styles */
.growth-item {
    background: var(--surface-white);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
}

.growth-item:hover {
    border-color: var(--primary-green-light);
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
}

.growth-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
}

.child-info {
    margin-bottom: 1rem;
}

.child-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.child-header h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.nutrition-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 2px solid transparent;
}

.nutrition-status.normal {
    background: var(--secondary-green);
    color: var(--primary-green-dark);
    border-color: var(--primary-green-light);
}

.nutrition-status.at-risk {
    background: #fef3c7;
    color: #d97706;
    border-color: #fbbf24;
}

.nutrition-status.moderate-malnutrition {
    background: #fed7aa;
    color: #ea580c;
    border-color: #fb923c;
}

.nutrition-status.severe-malnutrition {
    background: #fecaca;
    color: #dc2626;
    border-color: #f87171;
}

.nutrition-status.assessment-needed,
.nutrition-status.no-assessment {
    background: var(--surface-gray);
    color: var(--text-muted);
    border-color: var(--border-light);
}

.child-details {
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.growth-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: var(--surface-light);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-light);
}

.metric {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.metric-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.metric-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
}

.metric-change {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.metric-change.positive {
    background: var(--secondary-green);
    color: var(--primary-green-dark);
}

.metric-change.negative {
    background: #fecaca;
    color: #dc2626;
}

.growth-trend {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    border: 2px solid transparent;
}

.growth-trend.improving {
    background: var(--secondary-green);
    color: var(--primary-green-dark);
    border-color: var(--primary-green-light);
}

.growth-trend.declining {
    background: #fecaca;
    color: #dc2626;
    border-color: #f87171;
}

.growth-trend.stable {
    background: var(--surface-gray);
    color: var(--text-secondary);
    border-color: var(--border-light);
}

.last-assessment {
    color: var(--text-muted);
    font-size: 0.8rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    background: var(--surface-light);
    border-radius: var(--radius-sm);
    border-left: 3px solid var(--primary-green-light);
}

.no-assessment {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    color: var(--text-muted);
    font-style: normal;
    padding: 2rem;
    text-align: center;
    background: var(--surface-light);
    border-radius: var(--radius-md);
    border: 2px dashed var(--border-light);
}

.no-assessment i {
    font-size: 2rem;
    color: var(--primary-green-light);
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--surface-light);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-light);
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: var(--surface-white);
    border-color: var(--primary-green-light);
    box-shadow: var(--shadow-sm);
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
    color: var(--surface-white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.activity-time {
    font-size: 0.875rem;
    color: var(--text-secondary);
    font-weight: 500;
}

/* Empty State Styles */
.text-center {
    text-align: center;
}

.py-4 {
    padding: 2rem 0;
}

.text-gray-400 {
    color: var(--text-muted);
}

.text-2xl {
    font-size: 1.5rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.text-gray-500 {
    color: var(--text-secondary);
}

.mt-2 {
    margin-top: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .growth-metrics {
        grid-template-columns: 1fr;
    }
    
    .card-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .child-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

/* Dashboard Grid Layout */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 0;
}

/* Text Color Utilities */
.text-primary-green {
    color: var(--primary-green);
}

/* Empty State Enhancements */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 3rem 2rem;
    text-align: center;
    background: linear-gradient(135deg, var(--surface-light) 0%, var(--surface-white) 100%);
    border-radius: var(--radius-md);
    border: 2px dashed var(--border-light);
}

.empty-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--primary-green-light), var(--primary-green));
    color: var(--surface-white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.empty-state h4 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
}

.empty-state p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 1rem;
    line-height: 1.5;
    max-width: 300px;
}

/* Activity Badge */
.activity-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background: var(--secondary-green);
    color: var(--primary-green-dark);
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-left: 0.5rem;
}

.activity-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.activity-time {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.activity-time:last-child {
    margin-bottom: 0;
}
</style>
@endpush

@push('scripts')
    <script src="{{ asset('js/parent/parent-dashboard.js') }}"></script>
@endpush
