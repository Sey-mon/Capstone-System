@extends('layouts.dashboard')

@section('title', 'Nutritionist Dashboard')

@section('page-title', 'Nutritionist Dashboard')
@section('page-subtitle', 'Welcome back, ' . Auth::user()->first_name . '! Manage your patients and assessments.')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/dashboard.css') }}">
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
                <div class="stat-title">Pending Assessments</div>
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['pending_assessments'] }}</div>
            <div class="stat-change negative">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Needs attention</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Completed Assessments</div>
                <div class="stat-icon primary">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['completed_assessments'] }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>This month</span>
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
                                    Age: {{ $patient->age }} | Parent: {{ $patient->parent->first_name ?? 'N/A' }}
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
    <script src="{{ asset('js/nutritionist/nutritionist-dashboard.js') }}"></script>
@endpush
