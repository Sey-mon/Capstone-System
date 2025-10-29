@extends('layouts.dashboard')

@section('title', 'My Child Assessments')
@section('page-title', 'My Child Assessments')
@section('page-subtitle', 'View all child assessments for your children.')

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/parent/parent-assessments.css') }}">

<div class="assessments-container">
    <!-- Header Section -->
    <div class="assessments-header">
        <div class="header-content">
            <div class="breadcrumb">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
                <i class="fas fa-chevron-right"></i>
                <span class="active">Assessments</span>
            </div>
            <h1 class="page-title">Child Assessment Overview</h1>
            <p class="page-description">Monitor nutritional assessments, track health progress, and view detailed assessment history for your children</p>
        </div>
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-child"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">{{ count($children ?? []) }}</div>
                    <div class="stat-label">Total Children</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">{{ $children ? $children->sum(function($child) { return $child->assessments->count(); }) : 0 }}</div>
                    <div class="stat-label">Total Assessments</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">{{ $children ? $children->filter(function($child) { return $child->assessments->isNotEmpty(); })->count() : 0 }}</div>
                    <div class="stat-label">Active Cases</div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($children) && count($children) > 0)
        <div class="assessments-grid">
            @foreach($children as $child)
                <div class="assessment-card">
                    <div class="card-header">
                        <div class="child-info">
                            <div class="child-avatar">
                                <i class="fas fa-child"></i>
                            </div>
                            <div class="child-details">
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
                                            {{ $child->assessments->count() }} {{ $child->assessments->count() === 1 ? 'Assessment' : 'Assessments' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-actions">
                            @if($child->assessments->count() > 0)
                                <button class="view-history-btn" data-bs-toggle="modal" data-bs-target="#assessmentModal{{ $child->id }}">
                                    <i class="fas fa-history"></i>
                                    Full History
                                </button>
                            @else
                                <span class="no-data-badge">
                                    <i class="fas fa-info-circle"></i>
                                    No Assessments
                                </span>
                            @endif
                        </div>
                    </div>

                    @php
                        $latestAssessment = $child->assessments->sortByDesc('created_at')->first();
                    @endphp

                    <div class="card-body">
                        @if($latestAssessment)
                            <div class="latest-assessment">
                                <div class="assessment-header-inline">
                                    <div class="assessment-title-section">
                                        <h4><i class="fas fa-file-medical-alt"></i> Latest Assessment</h4>
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

                                    @if($latestAssessment->remarks)
                                        <div class="remarks-section">
                                            <div class="remarks-header">
                                                <i class="fas fa-comment-medical"></i>
                                                <span>Professional Remarks</span>
                                            </div>
                                            <p class="remarks-content">{{ $latestAssessment->remarks }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="no-assessment">
                                <div class="no-assessment-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h4>No Assessments Available</h4>
                                <p>This child has not received any nutritional assessments yet. Please contact your nutritionist to schedule an assessment.</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if($child->assessments->count() > 0)
                <div class="modal fade assessment-modal" id="assessmentModal{{ $child->id }}" tabindex="-1" aria-labelledby="assessmentModalLabel{{ $child->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="modal-title-section">
                                    <h5 class="modal-title" id="assessmentModalLabel{{ $child->id }}">
                                        <i class="fas fa-chart-line"></i>
                                        Assessment History - {{ $child->first_name }} {{ $child->last_name }}
                                    </h5>
                                    <p class="modal-subtitle">Complete assessment timeline and progress tracking</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="assessment-timeline">
                                    @foreach($child->assessments->sortByDesc('created_at') as $index => $assessment)
                                        <div class="timeline-item {{ $index === 0 ? 'latest' : '' }}">
                                            <div class="timeline-marker">
                                                <div class="timeline-dot"></div>
                                                @if($index < $child->assessments->count() - 1)
                                                    <div class="timeline-line"></div>
                                                @endif
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <h6 class="timeline-date">{{ $assessment->created_at->format('F d, Y') }}</h6>
                                                    @if($index === 0)
                                                        <span class="latest-badge">Latest</span>
                                                    @endif
                                                </div>
                                                <div class="timeline-body">
                                                    @php
                                                        $diagnosis = null;
                                                        if (!empty($assessment->treatment)) {
                                                            $treatmentData = json_decode($assessment->treatment, true);
                                                            $diagnosis = $treatmentData['patient_info']['diagnosis'] ?? null;
                                                        }
                                                    @endphp
                                                    
                                                    <div class="timeline-diagnosis">
                                                        @if($diagnosis == 'Severe Acute Malnutrition (SAM)')
                                                            <div class="diagnosis-badge critical">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                Severe Acute Malnutrition
                                                            </div>
                                                        @elseif($diagnosis == 'Moderate Acute Malnutrition (MAM)')
                                                            <div class="diagnosis-badge warning">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                                Moderate Acute Malnutrition
                                                            </div>
                                                        @elseif($diagnosis == 'Normal')
                                                            <div class="diagnosis-badge normal">
                                                                <i class="fas fa-check-circle"></i>
                                                                Normal Nutritional Status
                                                            </div>
                                                        @else
                                                            <div class="diagnosis-badge unknown">
                                                                <i class="fas fa-question-circle"></i>
                                                                {{ $diagnosis ?? 'Status Unknown' }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="timeline-metrics">
                                                        <div class="metric-pair">
                                                            <div class="metric-item">
                                                                <i class="fas fa-weight"></i>
                                                                <span class="metric-label">Weight:</span>
                                                                <span class="metric-value">{{ $assessment->weight ?? 'N/A' }} kg</span>
                                                            </div>
                                                            <div class="metric-item">
                                                                <i class="fas fa-ruler-vertical"></i>
                                                                <span class="metric-label">Height:</span>
                                                                <span class="metric-value">{{ $assessment->height ?? 'N/A' }} cm</span>
                                                            </div>
                                                        </div>
                                                        <div class="metric-item">
                                                            <i class="fas fa-user-md"></i>
                                                            <span class="metric-label">Nutritionist:</span>
                                                            <span class="metric-value">{{ $assessment->nutritionist->first_name ?? 'N/A' }} {{ $assessment->nutritionist->last_name ?? '' }}</span>
                                                        </div>
                                                        @if($assessment->remarks)
                                                            <div class="metric-item">
                                                                <i class="fas fa-sticky-note"></i>
                                                                <span class="metric-label">Remarks:</span>
                                                                <span class="metric-value">{{ $assessment->remarks }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-content">
                <div class="empty-state-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>No Children Registered</h3>
                <p>You don't have any children registered in the system yet. Once children are added to your account, their nutritional assessments and health information will appear here.</p>
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

<script>
function toggleOldAssessments(childId) {
    var el = document.getElementById('old-assessments-' + childId);
    if (el) {
        el.classList.toggle('d-none');
    }
}
</script>
@endsection
