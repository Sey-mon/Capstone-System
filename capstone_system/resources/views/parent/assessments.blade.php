@extends('layouts.dashboard')

@section('title', 'Screening History')
@section('page-title', 'Screening History')
@section('page-subtitle', 'View all screening records for your children.')

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/parent/parent-assessments.css') }}?v={{ time() }}">
<script src="{{ asset('js/parent/parent-assessments.js') }}?v={{ time() }}" defer></script>

<div class="desktop-page-wrapper">
    <!-- Desktop Header Section -->
    <div class="desktop-header-section">
        <div class="header-left">
            <div class="page-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="page-info">
                <h1 class="page-main-title">My Children's Health Records</h1>
                <p class="page-description">Comprehensive monitoring and tracking of your children's nutrition and health journey</p>
            </div>
        </div>
        <div class="header-right">
            <div class="header-stats-cards">
                @php
                    $totalChildren = isset($children) ? count($children) : 0;
                    $childrenWithAssessments = 0;
                    $totalAssessments = 0;
                    $latestAssessmentDate = null;
                    
                    if (isset($children) && $totalChildren > 0) {
                        foreach ($children as $child) {
                            $assessmentCount = $child->assessments->count();
                            if ($assessmentCount > 0) {
                                $childrenWithAssessments++;
                                $totalAssessments += $assessmentCount;
                                
                                // Get the latest assessment date
                                $childLatestAssessment = $child->assessments->sortByDesc('created_at')->first();
                                if ($childLatestAssessment && (!$latestAssessmentDate || $childLatestAssessment->created_at > $latestAssessmentDate)) {
                                    $latestAssessmentDate = $childLatestAssessment->created_at;
                                }
                            }
                        }
                    }
                @endphp
                
                <div class="header-stat-item">
                    <div class="header-stat-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <div class="header-stat-content">
                        <div class="header-stat-value">{{ $totalChildren }}</div>
                        <div class="header-stat-label">Registered Children</div>
                    </div>
                </div>
                <div class="header-stat-item">
                    <div class="header-stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="header-stat-content">
                        <div class="header-stat-value">{{ $childrenWithAssessments }}</div>
                        <div class="header-stat-label">With Screenings</div>
                    </div>
                </div>
                <div class="header-stat-item">
                    <div class="header-stat-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="header-stat-content">
                        <div class="header-stat-value">{{ $totalAssessments }}</div>
                        <div class="header-stat-label">Total Screenings</div>
                    </div>
                </div>
                @if($latestAssessmentDate)
                <div class="header-stat-item">
                    <div class="header-stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="header-stat-content">
                        <div class="header-stat-value" style="font-size: 1rem;">{{ $latestAssessmentDate->diffForHumans() }}</div>
                        <div class="header-stat-label">Last Screening</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="desktop-content-area">

    @if(isset($children) && count($children) > 0)
        <div class="assessments-grid">
            @foreach($children as $child)
                <div class="child-card">
                    <div class="child-card-header">
                        <div class="child-profile-section">
                            <div class="child-avatar">
                                <i class="fas fa-child"></i>
                            </div>
                            <div class="child-info">
                                <h3 class="child-name">{{ $child->first_name }} {{ $child->last_name }}</h3>
                                <div class="child-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-birthday-cake"></i>
                                        {{ $child->age_months ? ($child->age_months . ' months') : 'Age unknown' }}
                                    </span>
                                    <span class="meta-divider">•</span>
                                    <span class="meta-item">
                                        <i class="fas fa-{{ $child->sex === 'Male' ? 'mars' : 'venus' }}"></i>
                                        {{ $child->sex ?? 'Gender unknown' }}
                                    </span>
                                    @if($child->assessments->count() > 0)
                                        <span class="meta-divider">•</span>
                                        <span class="meta-item">
                                            <i class="fas fa-chart-line"></i>
                                            {{ $child->assessments->count() }} {{ $child->assessments->count() === 1 ? 'Screening' : 'Screenings' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="child-actions">
                            @if($child->assessments->count() > 0)
                                <button class="view-history-btn" onclick="showAssessmentHistory({{ $child->patient_id }}, '{{ addslashes($child->first_name . ' ' . $child->last_name) }}')">
                                    <i class="fas fa-history"></i>
                                    Full History
                                </button>
                            @else
                                <span class="no-data-badge">
                                    <i class="fas fa-info-circle"></i>
                                    No Screenings
                                </span>
                            @endif
                        </div>
                    </div>

                    @php
                        $latestAssessment = $child->assessments->sortByDesc('created_at')->first();
                    @endphp

                    <div class="child-card-body">
                        @if($latestAssessment)
                            <div class="latest-assessment">
                                <div class="assessment-header-inline">
                                    <div class="assessment-title-section">
                                        <h4><i class="fas fa-file-medical-alt"></i> Latest Screening</h4>
                                        <span class="assessment-date">
                                            <i class="fas fa-calendar"></i>
                                            {{ $latestAssessment->created_at->format('F d, Y') }}
                                        </span>
                                    </div>
                                    
                                    @php
                                        $diagnosis = null;
                                        if (!empty($latestAssessment->treatment)) {
                                            $treatmentData = json_decode($latestAssessment->treatment, true);
                                            $diagnosis = $treatmentData['patient_info']['diagnosis'] ?? null;
                                        }
                                    @endphp
                                    
                                    <div class="diagnosis-section-inline">
                                        @if($diagnosis == 'Severe Acute Malnutrition (SAM)')
                                            <div class="diagnosis-badge critical">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <span>Severe Acute Malnutrition</span>
                                            </div>
                                        @elseif($diagnosis == 'Moderate Acute Malnutrition (MAM)')
                                            <div class="diagnosis-badge warning">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>Moderate Acute Malnutrition</span>
                                            </div>
                                        @elseif($diagnosis == 'Normal')
                                            <div class="diagnosis-badge normal">
                                                <i class="fas fa-check-circle"></i>
                                                <span>Normal Status</span>
                                            </div>
                                        @else
                                            <div class="diagnosis-badge unknown">
                                                <i class="fas fa-question-circle"></i>
                                                <span>{{ $diagnosis ?? 'Status Unknown' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="assessment-details">
                                    <div class="metrics-row">
                                        <div class="metric-card">
                                            <div class="metric-icon-wrapper weight">
                                                <i class="fas fa-weight"></i>
                                            </div>
                                            <div class="metric-content">
                                                <span class="metric-label">Weight</span>
                                                <span class="metric-value">{{ $latestAssessment->weight ?? $child->weight_kg ?? 'N/A' }} <small>kg</small></span>
                                            </div>
                                        </div>
                                        
                                        <div class="metric-card">
                                            <div class="metric-icon-wrapper height">
                                                <i class="fas fa-ruler-vertical"></i>
                                            </div>
                                            <div class="metric-content">
                                                <span class="metric-label">Height</span>
                                                <span class="metric-value">{{ $latestAssessment->height ?? $child->height_cm ?? 'N/A' }} <small>cm</small></span>
                                            </div>
                                        </div>
                                        
                                        <div class="metric-card">
                                            <div class="metric-icon-wrapper nutritionist">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                            <div class="metric-content">
                                                <span class="metric-label">Assessed By</span>
                                                <span class="metric-value">{{ $latestAssessment->nutritionist->first_name ?? 'N/A' }} {{ $latestAssessment->nutritionist->last_name ?? '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="no-assessment">
                                <div class="no-assessment-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h4>No Screenings Available</h4>
                                <p>This child has not received any nutritional screenings yet. Please contact your nutritionist to schedule a screening.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-content">
                <div class="empty-state-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>No Children Registered</h3>
                <p>You don't have any children registered in the system yet. Once children are added to your account, their nutritional screenings and health information will appear here.</p>
                <div class="empty-state-actions">
                    <button class="empty-state-btn primary">
                        <i class="fas fa-user-plus"></i>
                        Contact Administrator
                    </button>
                    <button class="empty-state-btn secondary">
                        <i class="fas fa-question-circle"></i>
                        Learn More
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
</div>

<!-- Hidden data for assessments -->
<script id="assessments-data" type="application/json">
@if(isset($children) && count($children) > 0)
@php
$assessmentsDataArray = [];
foreach($children as $child) {
    $assessmentsList = [];
    foreach($child->assessments->sortByDesc('created_at') as $assessment) {
        $diagnosis = null;
        if (!empty($assessment->treatment)) {
            try {
                $treatmentData = json_decode($assessment->treatment, true);
                $diagnosis = $treatmentData['patient_info']['diagnosis'] ?? null;
            } catch (\Exception $e) {
                $diagnosis = null;
            }
        }
        
        $nutritionistName = 'N/A';
        if (isset($assessment->nutritionist)) {
            $firstName = $assessment->nutritionist->first_name ?? '';
            $lastName = $assessment->nutritionist->last_name ?? '';
            $nutritionistName = trim($firstName . ' ' . $lastName) ?: 'N/A';
        }
        
        $assessmentsList[] = [
            'id' => $assessment->assessment_id,
            'date' => $assessment->created_at->format('F d, Y'),
            'timestamp' => $assessment->created_at->timestamp,
            'weight' => $assessment->weight_kg ?? 'N/A',
            'height' => $assessment->height_cm ?? 'N/A',
            'weight_for_age' => $assessment->weight_for_age ?? 'Not assessed',
            'height_for_age' => $assessment->height_for_age ?? 'Not assessed',
            'bmi_for_age' => $assessment->bmi_for_age ?? 'Not assessed',
            'diagnosis' => $diagnosis,
            'nutritionist' => $nutritionistName,
            'remarks' => $assessment->notes
        ];
    }
    
    $assessmentsDataArray[$child->patient_id] = [
        'id' => $child->patient_id,
        'name' => trim(($child->first_name ?? '') . ' ' . ($child->last_name ?? '')),
        'assessments' => $assessmentsList
    ];
}
echo json_encode($assessmentsDataArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
@endphp
@else
{}
@endif
</script>
@endsection
