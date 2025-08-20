@extends('layouts.dashboard')

@section('title', 'Assessment Results')

@section('page-title', 'Assessment Results')
@section('page-subtitle', 'Complete assessment results for {{ $patient->first_name }} {{ $patient->last_name }}')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <!-- Back Buttons -->
    <div class="mb-3">
        <a href="{{ route('nutritionist.patients') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Patients
        </a>
        <a href="{{ route('nutritionist.patients.assess', $patient->patient_id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-redo"></i> New Assessment
        </a>
    </div>

    <!-- Assessment Overview -->
    <div class="assessment-overview">
        <div class="overview-header">
            <h3>Assessment Summary</h3>
            <div class="assessment-date">
                <i class="fas fa-calendar"></i>
                Completed: {{ $assessment->completed_at->format('M d, Y \a\t h:i A') }}
            </div>
        </div>

        <div class="overview-cards">
            <div class="overview-card diagnosis">
                <div class="card-icon {{ getDiagnosisClass($result['assessment']['primary_diagnosis']) }}">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="card-content">
                    <h4>Primary Diagnosis</h4>
                    <p>{{ $result['assessment']['primary_diagnosis'] }}</p>
                </div>
            </div>

            <div class="overview-card risk">
                <div class="card-icon {{ getRiskClass($result['assessment']['risk_level']) }}">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="card-content">
                    <h4>Risk Level</h4>
                    <p>{{ $result['assessment']['risk_level'] }}</p>
                </div>
            </div>

            <div class="overview-card confidence">
                <div class="card-icon info">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="card-content">
                    <h4>Confidence Level</h4>
                    <p>{{ round($result['assessment']['confidence'] * 100) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient & Assessment Data -->
    <div class="content-grid">
        <!-- Child Data -->
        <div class="data-card">
            <h4>Child Measurements</h4>
            <div class="data-list">
                <div class="data-item">
                    <span class="data-label">Age:</span>
                    <span class="data-value">{{ $childData['age_months'] }} months</span>
                </div>
                <div class="data-item">
                    <span class="data-label">Gender:</span>
                    <span class="data-value">{{ ucfirst($childData['gender']) }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">Weight:</span>
                    <span class="data-value">{{ $childData['weight_kg'] }} kg</span>
                </div>
                <div class="data-item">
                    <span class="data-label">Height:</span>
                    <span class="data-value">{{ $childData['height_cm'] }} cm</span>
                </div>
                @if($childData['muac_cm'])
                <div class="data-item">
                    <span class="data-label">MUAC:</span>
                    <span class="data-value">{{ $childData['muac_cm'] }} cm</span>
                </div>
                @endif
                <div class="data-item">
                    <span class="data-label">Edema:</span>
                    <span class="data-value">{{ $childData['has_edema'] ? 'Present' : 'Absent' }}</span>
                </div>
            </div>
        </div>

        <!-- WHO Assessment -->
        @if(isset($result['assessment']['who_assessment']))
        <div class="data-card">
            <h4>WHO Z-Scores</h4>
            <div class="z-scores-grid">
                @foreach($result['assessment']['who_assessment']['z_scores'] as $indicator => $score)
                <div class="z-score-item {{ getZScoreClass($score) }}">
                    <div class="z-score-label">{{ strtoupper(str_replace('_', ' ', $indicator)) }}</div>
                    <div class="z-score-value">{{ round($score, 2) }}</div>
                    <div class="z-score-status">{{ getZScoreStatus($score) }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Risk Factors -->
        @if(isset($result['assessment']['risk_factors']) && count($result['assessment']['risk_factors']) > 0)
        <div class="data-card">
            <h4>Risk Factors</h4>
            <div class="risk-factors-list">
                @foreach($result['assessment']['risk_factors'] as $factor)
                <div class="risk-factor-item">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $factor }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Treatment Plan -->
    @if(isset($result['treatment_plan']))
    <div class="treatment-plan-section">
        <h3>Treatment Plan</h3>
        
        <div class="treatment-tabs">
            <ul class="nav nav-tabs" id="treatmentTabs" role="tablist">
                @if(isset($result['treatment_plan']['immediate_actions']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="immediate-tab" data-bs-toggle="tab" data-bs-target="#immediate" type="button" role="tab">
                        Immediate Actions
                    </button>
                </li>
                @endif
                @if(isset($result['treatment_plan']['nutrition_plan']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="nutrition-tab" data-bs-toggle="tab" data-bs-target="#nutrition" type="button" role="tab">
                        Nutrition Plan
                    </button>
                </li>
                @endif
                @if(isset($result['treatment_plan']['medical_interventions']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="medical-tab" data-bs-toggle="tab" data-bs-target="#medical" type="button" role="tab">
                        Medical Interventions
                    </button>
                </li>
                @endif
                @if(isset($result['treatment_plan']['monitoring_schedule']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="monitoring-tab" data-bs-toggle="tab" data-bs-target="#monitoring" type="button" role="tab">
                        Monitoring
                    </button>
                </li>
                @endif
            </ul>
            
            <div class="tab-content" id="treatmentTabContent">
                @if(isset($result['treatment_plan']['immediate_actions']))
                <div class="tab-pane fade show active" id="immediate" role="tabpanel">
                    <div class="treatment-content">
                        @if(is_array($result['treatment_plan']['immediate_actions']))
                            <ul class="treatment-list">
                                @foreach($result['treatment_plan']['immediate_actions'] as $action)
                                <li>{{ is_array($action) ? json_encode($action) : $action }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ $result['treatment_plan']['immediate_actions'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(isset($result['treatment_plan']['nutrition_plan']))
                <div class="tab-pane fade" id="nutrition" role="tabpanel">
                    <div class="treatment-content">
                        @if(is_array($result['treatment_plan']['nutrition_plan']))
                            @foreach($result['treatment_plan']['nutrition_plan'] as $key => $value)
                            <div class="nutrition-item">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                            </div>
                            @endforeach
                        @else
                            <p>{{ $result['treatment_plan']['nutrition_plan'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(isset($result['treatment_plan']['medical_interventions']))
                <div class="tab-pane fade" id="medical" role="tabpanel">
                    <div class="treatment-content">
                        @if(is_array($result['treatment_plan']['medical_interventions']))
                            @foreach($result['treatment_plan']['medical_interventions'] as $key => $value)
                            <div class="medical-item">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                            </div>
                            @endforeach
                        @else
                            <p>{{ $result['treatment_plan']['medical_interventions'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(isset($result['treatment_plan']['monitoring_schedule']))
                <div class="tab-pane fade" id="monitoring" role="tabpanel">
                    <div class="treatment-content">
                        @if(is_array($result['treatment_plan']['monitoring_schedule']))
                            @foreach($result['treatment_plan']['monitoring_schedule'] as $key => $value)
                            <div class="monitoring-item">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                            </div>
                            @endforeach
                        @else
                            <p>{{ $result['treatment_plan']['monitoring_schedule'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Assessment Notes -->
    @if($assessment->notes)
    <div class="notes-section">
        <h4>Clinical Notes</h4>
        <div class="notes-content">
            {{ $assessment->notes }}
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="action-buttons">
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="fas fa-print"></i> Print Results
        </button>
        <button onclick="downloadPDF()" class="btn btn-outline-secondary">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button>
    </div>
@endsection

@section('scripts')
<script>
function downloadPDF() {
    // This would need a PDF generation endpoint
    alert('PDF download feature would be implemented here');
}
</script>

<style>
.assessment-overview {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.overview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.overview-header h3 {
    margin: 0;
    color: #1f2937;
}

.assessment-date {
    color: #6b7280;
    font-size: 0.9rem;
}

.assessment-date i {
    margin-right: 0.5rem;
}

.overview-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.overview-card {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #ddd;
}

.overview-card.diagnosis { border-left-color: #3b82f6; }
.overview-card.risk { border-left-color: #f59e0b; }
.overview-card.confidence { border-left-color: #22c55e; }

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.card-icon.success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.card-icon.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.card-icon.danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.card-icon.info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }

.card-content h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: #374151;
}

.card-content p {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.data-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.data-card h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 1.1rem;
    font-weight: 600;
}

.data-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.data-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.data-item:last-child {
    border-bottom: none;
}

.data-label {
    color: #6b7280;
    font-weight: 500;
}

.data-value {
    color: #1f2937;
    font-weight: 600;
}

.z-scores-grid {
    display: grid;
    gap: 1rem;
}

.z-score-item {
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    text-align: center;
}

.z-score-item.normal { background: rgba(34, 197, 94, 0.05); border-color: #22c55e; }
.z-score-item.mild { background: rgba(245, 158, 11, 0.05); border-color: #f59e0b; }
.z-score-item.moderate { background: rgba(239, 68, 68, 0.05); border-color: #ef4444; }
.z-score-item.severe { background: rgba(127, 29, 29, 0.05); border-color: #7f1d1d; }

.z-score-label {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.z-score-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.z-score-status {
    font-size: 0.8rem;
    font-weight: 500;
}

.z-score-item.normal .z-score-status { color: #22c55e; }
.z-score-item.mild .z-score-status { color: #f59e0b; }
.z-score-item.moderate .z-score-status { color: #ef4444; }
.z-score-item.severe .z-score-status { color: #7f1d1d; }

.risk-factors-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.risk-factor-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: rgba(239, 68, 68, 0.05);
    border-radius: 6px;
    border: 1px solid rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.risk-factor-item i {
    margin-right: 0.75rem;
    color: #ef4444;
}

.treatment-plan-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.treatment-plan-section h3 {
    margin: 0 0 1.5rem 0;
    color: #1f2937;
}

.treatment-tabs .nav-tabs {
    border-bottom: 1px solid #dee2e6;
}

.treatment-tabs .nav-link {
    border: none;
    padding: 0.75rem 1.5rem;
    color: #6b7280;
    border-bottom: 3px solid transparent;
}

.treatment-tabs .nav-link.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
    background: none;
}

.treatment-content {
    padding: 1.5rem 0;
}

.treatment-list {
    margin: 0;
    padding-left: 1.2rem;
}

.treatment-list li {
    margin-bottom: 0.75rem;
    color: #374151;
    line-height: 1.6;
}

.nutrition-item, .medical-item, .monitoring-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.nutrition-item:last-child, .medical-item:last-child, .monitoring-item:last-child {
    border-bottom: none;
}

.nutrition-item strong, .medical-item strong, .monitoring-item strong {
    color: #1f2937;
    margin-right: 0.5rem;
}

.nutrition-item span, .medical-item span, .monitoring-item span {
    color: #374151;
}

.notes-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.notes-section h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
}

.notes-content {
    color: #374151;
    line-height: 1.6;
    white-space: pre-wrap;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
}

@media print {
    .action-buttons,
    .btn,
    .nav-tabs {
        display: none !important;
    }
    
    .treatment-tabs .tab-content .tab-pane {
        display: block !important;
        opacity: 1 !important;
    }
}
</style>

@php
function getDiagnosisClass($diagnosis) {
    if (strpos(strtolower($diagnosis), 'normal') !== false) return 'success';
    if (strpos(strtolower($diagnosis), 'severe') !== false) return 'danger';
    if (strpos(strtolower($diagnosis), 'moderate') !== false) return 'warning';
    return 'info';
}

function getRiskClass($risk) {
    if (strtolower($risk) === 'low') return 'success';
    if (strtolower($risk) === 'high') return 'danger';
    if (strtolower($risk) === 'medium') return 'warning';
    return 'info';
}

function getZScoreClass($score) {
    if ($score >= -2) return 'normal';
    if ($score >= -3) return 'mild';
    if ($score >= -4) return 'moderate';
    return 'severe';
}

function getZScoreStatus($score) {
    if ($score >= -2) return 'Normal';
    if ($score >= -3) return 'Mild';
    if ($score >= -4) return 'Moderate';
    return 'Severe';
}
@endphp
@endsection
