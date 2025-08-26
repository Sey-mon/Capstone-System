@extends('layouts.dashboard')

@section('title', 'Assessment Results')

@section('page-title', 'Assessment Results')
@section('page-subtitle', 'Complete assessment results for {{ $patient->first_name }} {{ $patient->last_name }}')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/nutritionist/assessment-results.css') }}">
@endpush

@section('content')
<div class="assessment-results-container">
    <!-- Header Section -->
    <div class="assessment-header">
        <div class="header-controls">
            <a href="{{ route('nutritionist.patients') }}" class="btn-header back-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Patients</span>
            </a>
            <a href="{{ route('nutritionist.patients.assess', $patient->patient_id) }}" class="btn-header new-btn">
                <i class="fas fa-plus"></i>
                <span>New Assessment</span>
            </a>
            <div class="status-badge">Assessment Complete</div>
        </div>
        
        <div class="patient-info-header">
            <h1 class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</h1>
            <div class="patient-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>{{ $patient->age_months }} months old</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-venus-mars"></i>
                    <span>{{ ucfirst($patient->gender) }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <span>{{ $assessment->completed_at->format('F d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Information Card -->
    <div class="patient-info-section">
        <h2 class="section-title">
            <i class="fas fa-user"></i>
            Patient Information
        </h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Full Name:</span>
                <span class="info-value">{{ $patient->first_name }} {{ $patient->last_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Age:</span>
                <span class="info-value">{{ $patient->age_months }} months</span>
            </div>
            <div class="info-item">
                <span class="info-label">Gender:</span>
                <span class="info-value">{{ ucfirst($patient->gender) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Weight:</span>
                <span class="info-value">{{ $assessment->weight_kg }} kg</span>
            </div>
            <div class="info-item">
                <span class="info-label">Height:</span>
                <span class="info-value">{{ $assessment->height_cm }} cm</span>
            </div>
            <div class="info-item">
                <span class="info-label">Assessment Date:</span>
                <span class="info-value">{{ $assessment->completed_at->format('F d, Y \a\t g:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Assessment Overview -->
    <div class="overview-section">
        <h2 class="section-title">
            <i class="fas fa-chart-line"></i>
            Assessment Overview
        </h2>
        <div class="overview-grid">
            <div class="overview-card diagnosis">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Primary Diagnosis</h3>
                        <p class="card-value">{{ $assessment->primary_diagnosis }}</p>
                    </div>
                </div>
            </div>
            
            @if($assessment->risk_level)
            <div class="overview-card risk">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Risk Level</h3>
                        <p class="card-value">{{ $assessment->risk_level }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($assessment->confidence)
            <div class="overview-card confidence">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Confidence Level</h3>
                        <p class="card-value">{{ round($assessment->confidence * 100) }}%</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- WHO Z-Scores Section -->
    @if($assessment->who_assessment && $assessment->who_assessment['z_scores'])
    <div class="z-scores-section">
        <h2 class="section-title">
            <i class="fas fa-chart-bar"></i>
            WHO Z-Scores Analysis
        </h2>
        <div class="z-scores-grid">
            @foreach($assessment->who_assessment['z_scores'] as $indicator => $value)
            <div class="z-score-card">
                <div class="z-score-label">{{ str_replace('_', ' ', strtoupper($indicator)) }}</div>
                <div class="z-score-value {{ getZScoreClass($value) }}">{{ number_format($value, 2) }}</div>
                <div class="z-score-interpretation">{{ getZScoreInterpretation($value) }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Risk Factors Section -->
    @if($assessment->risk_factors && count($assessment->risk_factors) > 0)
    <div class="risk-factors-section">
        <h2 class="section-title">
            <i class="fas fa-warning"></i>
            Risk Factors
        </h2>
        <div class="risk-factors-list">
            @foreach($assessment->risk_factors as $factor)
            <div class="risk-factor-item">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $factor }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Treatment Plan Section -->
    @if($assessment->treatment_plan)
    <div class="treatment-section">
        <h2 class="section-title">
            <i class="fas fa-clipboard-list"></i>
            Treatment Plan
        </h2>
        
        <div class="treatment-tabs">
            <nav class="nav nav-tabs" id="treatmentTabs">
                @if(isset($assessment->treatment_plan['nutrition_recommendations']))
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nutrition-tab">
                    <i class="fas fa-apple-alt"></i>
                    Nutrition
                </button>
                @endif
                @if(isset($assessment->treatment_plan['medical_interventions']))
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#medical-tab">
                    <i class="fas fa-pills"></i>
                    Medical
                </button>
                @endif
                @if(isset($assessment->treatment_plan['monitoring_schedule']))
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#monitoring-tab">
                    <i class="fas fa-calendar-check"></i>
                    Monitoring
                </button>
                @endif
            </nav>
            
            <div class="tab-content" id="treatmentTabContent">
                @if(isset($assessment->treatment_plan['nutrition_recommendations']))
                <div class="tab-pane fade show active" id="nutrition-tab">
                    <div class="treatment-content">
                        @if(is_array($assessment->treatment_plan['nutrition_recommendations']))
                            <ul class="treatment-list">
                                @foreach($assessment->treatment_plan['nutrition_recommendations'] as $recommendation)
                                <li>{{ $recommendation }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ $assessment->treatment_plan['nutrition_recommendations'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(isset($assessment->treatment_plan['medical_interventions']))
                <div class="tab-pane fade" id="medical-tab">
                    <div class="treatment-content">
                        @if(is_array($assessment->treatment_plan['medical_interventions']))
                            <ul class="treatment-list">
                                @foreach($assessment->treatment_plan['medical_interventions'] as $intervention)
                                <li>{{ $intervention }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ $assessment->treatment_plan['medical_interventions'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(isset($assessment->treatment_plan['monitoring_schedule']))
                <div class="tab-pane fade" id="monitoring-tab">
                    <div class="treatment-content">
                        @if(is_array($assessment->treatment_plan['monitoring_schedule']))
                            <ul class="treatment-list">
                                @foreach($assessment->treatment_plan['monitoring_schedule'] as $schedule)
                                <li>{{ $schedule }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ $assessment->treatment_plan['monitoring_schedule'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Clinical Notes Section -->
    @if($assessment->notes)
    <div class="notes-section">
        <h2 class="section-title">
            <i class="fas fa-sticky-note"></i>
            Clinical Notes & Observations
        </h2>
        <div class="notes-content">
            {{ $assessment->notes }}
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="action-section">
        <button class="btn-action secondary">
            <i class="fas fa-magic"></i>
            <span>Auto-Fill from Records</span>
        </button>
        <button onclick="downloadPDF()" class="btn-action success">
            <i class="fas fa-download"></i>
            <span>Download PDF Report</span>
        </button>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.downloadPDFRoute = "{{ route('nutritionist.assessment.pdf', $assessment->assessment_id) }}";

    </script>
    <script src="{{ asset('js/nutritionist/assessment-results.js') }}"></script>
@endpush

@php
function getZScoreClass($score) {
    if ($score >= -2) return 'normal';
    if ($score >= -3) return 'mild';
    if ($score >= -4) return 'moderate';
    return 'severe';
}

function getZScoreInterpretation($score) {
    if ($score >= -2) return 'Normal';
    if ($score >= -3) return 'Mild';
    if ($score >= -4) return 'Moderate';
    return 'Severe';
}

function getRiskClass($risk) {
    if (strtolower($risk) === 'low') return 'success';
    if (strtolower($risk) === 'high') return 'danger';
    if (strtolower($risk) === 'medium') return 'warning';
    return 'info';
}
@endphp
