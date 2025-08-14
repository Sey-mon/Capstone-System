@extends('layouts.dashboard')

@section('title', 'Parent Dashboard')

@section('page-title', 'Parent Dashboard')
@section('page-subtitle', 'Welcome back, ' . Auth::user()->first_name . '! Track your children\'s nutrition progress.')

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

    <!-- Children and Assessments Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- My Children -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">My Children</h3>
                <a href="{{ route('parent.children') }}" class="btn btn-secondary">
                    <i class="fas fa-child"></i>
                    View All
                </a>
            </div>
            <div class="card-content">
                @forelse($stats['my_children_list'] as $child)
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <strong>{{ $child->first_name }} {{ $child->last_name }}</strong>
                                </div>
                                <div class="activity-time">
                                    Age: {{ $child->age }} years | 
                                    Nutritionist: {{ $child->nutritionist->first_name ?? 'Not assigned' }} {{ $child->nutritionist->last_name ?? '' }} | 
                                    Assessments: {{ $child->assessments->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-child text-gray-400 text-2xl mb-2"></i>
                        <p class="text-gray-500">No children registered yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Assessments -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Assessments</h3>
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
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <strong>{{ $assessment->patient->first_name }} {{ $assessment->patient->last_name }}</strong>
                                </div>
                                <div class="activity-time">
                                    By: {{ $assessment->nutritionist->first_name }} {{ $assessment->nutritionist->last_name }} | 
                                    {{ $assessment->created_at->diffForHumans() }}
                                </div>
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
    <script src="{{ asset('js/parent-dashboard.js') }}"></script>
@endpush
