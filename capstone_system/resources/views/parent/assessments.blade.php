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
            <h1 class="page-title">Child Assessment Overview</h1>
            <p class="page-description">Track your children's nutritional assessments and health progress</p>
        </div>
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-number">{{ count($children ?? []) }}</div>
                <div class="stat-label">Total Children</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $children ? $children->sum(function($child) { return $child->assessments->count(); }) : 0 }}</div>
                <div class="stat-label">Total Assessments</div>
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
                                    <span class="age">{{ $child->age_months ? ($child->age_months . ' months') : 'Age unknown' }}</span>
                                    <span class="gender">{{ $child->sex ?? 'Gender unknown' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-actions">
                            @if($child->assessments->count() > 0)
                                <button class="view-history-btn" data-bs-toggle="modal" data-bs-target="#assessmentModal{{ $child->id }}">
                                    <i class="fas fa-history"></i>
                                    View History
                                </button>
                            @endif
                        </div>
                    </div>

                    @php
                        $latestAssessment = $child->assessments->sortByDesc('created_at')->first();
                    @endphp

                    <div class="card-body">
                        @if($latestAssessment)
                            <div class="latest-assessment">
                                <div class="assessment-header">
                                    <h4>Latest Assessment</h4>
                                    <span class="assessment-date">{{ $latestAssessment->created_at->format('M d, Y') }}</span>
                                </div>
                                
                                <div class="assessment-details">
                                    @php
                                        $diagnosis = null;
                                        if (!empty($latestAssessment->treatment)) {
                                            $treatmentData = json_decode($latestAssessment->treatment, true);
                                            $diagnosis = $treatmentData['patient_info']['diagnosis'] ?? null;
                                        }
                                    @endphp
                                    
                                    <div class="diagnosis-section">
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

                                    <div class="metrics-grid">
                                        <div class="metric">
                                            <div class="metric-icon">
                                                <i class="fas fa-weight"></i>
                                            </div>
                                            <div class="metric-info">
                                                <span class="metric-label">Weight</span>
                                                <span class="metric-value">{{ $latestAssessment->weight ?? $child->weight_kg ?? 'N/A' }} kg</span>
                                            </div>
                                        </div>
                                        <div class="metric">
                                            <div class="metric-icon">
                                                <i class="fas fa-ruler-vertical"></i>
                                            </div>
                                            <div class="metric-info">
                                                <span class="metric-label">Height</span>
                                                <span class="metric-value">{{ $latestAssessment->height ?? $child->height_cm ?? 'N/A' }} cm</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="additional-info">
                                        <div class="info-item">
                                            <i class="fas fa-user-md"></i>
                                            <span class="info-label">Nutritionist:</span>
                                            <span class="info-value">{{ $latestAssessment->nutritionist->first_name ?? 'N/A' }} {{ $latestAssessment->nutritionist->last_name ?? '' }}</span>
                                        </div>
                                        @if($latestAssessment->remarks)
                                            <div class="info-item">
                                                <i class="fas fa-sticky-note"></i>
                                                <span class="info-label">Remarks:</span>
                                                <span class="info-value">{{ $latestAssessment->remarks }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="no-assessment">
                                <div class="no-assessment-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h4>No Assessments Yet</h4>
                                <p>This child hasn't received any nutritional assessments.</p>
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
                <p>You don't have any children registered for assessments yet.</p>
                <button class="empty-state-btn">
                    <i class="fas fa-plus"></i>
                    Register a Child
                </button>
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
