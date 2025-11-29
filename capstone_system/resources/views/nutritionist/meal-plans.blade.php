@extends('layouts.dashboard')

@section('title', 'Meal Plans')

@section('page-title', 'Meal Plans')
@section('page-subtitle', 'Generate and manage AI-powered meal plans')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/meal-plans.css') }}">
@endpush

@section('content')
    <div class="meal-plans-container">
        <!-- Feeding Program Section -->
        <div class="feeding-program-section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Feeding Program Meal Plans</h2>
                    <p class="section-subtitle">Create standardized, budget-conscious meal plans for Filipino children</p>
                </div>
                <div class="header-actions">
                    <button type="button" class="btn-primary" id="open-feeding-program-btn">
                        <i class="fas fa-plus-circle"></i>
                        Create Program Plan
                    </button>
                    <button type="button" class="btn-secondary" id="test-api-btn">
                        <i class="fas fa-plug"></i>
                        Test API
                    </button>
                </div>
            </div>

            <div class="info-banner">
                <i class="fas fa-info-circle"></i>
                <div class="info-content">
                    <strong>Generic Feeding Program Meal Plans</strong>
                    <p>No patient-specific data required - perfect for community programs. Generate meal plans with no dish repetition across the entire program duration.</p>
                </div>
            </div>
        </div>

        <!-- Individual Patient Meal Plans Section -->
        <div class="patients-section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Individual Patient Meal Plans</h2>
                    <p class="section-subtitle">Search and manage patient meal plans</p>
                </div>
                <div class="header-actions">
                    <button type="button" class="btn-primary" id="view-all-patients-btn">
                        <i class="fas fa-users"></i>
                        View All Patients
                    </button>
                </div>
            </div>

            @if($patients->count() > 0)
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="patient-search" class="search-input" placeholder="Search patients by name, age, or location...">
                </div>
            </div>

            <div class="patients-list-container" id="patients-list-container" style="display: none;">
                <div class="patients-scrollable" id="patients-list">
                    @foreach($patients as $patient)
                        <div class="patient-item" data-patient-id="{{ $patient->patient_id }}" 
                             data-name="{{ strtolower($patient->first_name . ' ' . $patient->last_name) }}"
                             data-age="{{ $patient->age_months }}"
                             data-location="{{ strtolower($patient->barangay->barangay_name ?? '') }}">
                            <div class="patient-item-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="patient-item-info">
                                <h4 class="patient-item-name">{{ $patient->first_name }} {{ $patient->last_name }}</h4>
                                <div class="patient-item-meta">
                                    <span><i class="fas fa-calendar"></i> {{ $patient->age_months }} months</span>
                                    <span><i class="fas fa-{{ $patient->sex === 'Male' ? 'mars' : 'venus' }}"></i> {{ $patient->sex }}</span>
                                    <span><i class="fas fa-map-marker-alt"></i> {{ $patient->barangay->barangay_name ?? 'N/A' }}</span>
                                </div>
                                <div class="patient-item-stats">
                                    <span class="stat-badge">{{ $patient->weight_kg }} kg</span>
                                    <span class="stat-badge">{{ $patient->height_cm }} cm</span>
                                </div>
                            </div>
                            <div class="patient-item-actions">
                                <button type="button" class="item-action-btn analysis-btn generate-analysis-btn" 
                                        data-patient-id="{{ $patient->patient_id }}" title="Nutrition Analysis">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                <button type="button" class="item-action-btn meal-btn generate-meal-plan-btn" 
                                        data-patient-id="{{ $patient->patient_id }}" title="Generate Meal Plan">
                                    <i class="fas fa-utensils"></i>
                                </button>
                                <button type="button" class="item-action-btn history-btn view-meal-plans-btn" 
                                        data-patient-id="{{ $patient->patient_id }}" title="View History">
                                    <i class="fas fa-history"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <h3 class="empty-title">No Patients Assigned</h3>
                    <p class="empty-subtitle">You don't have any patients for individual meal planning yet.</p>
                </div>
            @endif
        </div>

        <!-- Results Section -->
        <div id="results-section" class="results-section" style="display: none;">
            <div class="section-header">
                <h2 class="section-title" id="results-title">Results</h2>
                <button type="button" class="btn-secondary" id="close-results-btn">
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
            <div class="results-content" id="results-content">
                <!-- Results will be loaded here dynamically -->
            </div>
        </div>
    </div>

    <!-- Hidden data for JavaScript -->
    <script id="patients-data" type="application/json">
        @json($patients)
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/nutritionist/meal-plans.js') }}"></script>
@endpush
