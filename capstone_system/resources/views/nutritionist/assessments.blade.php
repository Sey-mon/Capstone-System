@extends('layouts.dashboard')

@section('title', 'Assessments')

@section('page-title', 'Patient Assessments')
@section('page-subtitle', 'View and manage latest malnutrition assessments for each patient')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <!-- Enhanced Single Line Filter Bar -->
    <div class="assessment-filters-container">
        <div class="filters-row">
            <input type="text" 
                   id="searchInput" 
                   class="filter-control search-input" 
                   placeholder="Search patients, diagnosis..."
                   value="{{ request('search') }}">
            
            <select id="statusFilter" class="filter-control">
                <option value="">All Status</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
            
            <select id="diagnosisFilter" class="filter-control">
                <option value="">All Diagnoses</option>
                <option value="Normal">Normal</option>
                <option value="Moderate">Moderate</option>
                <option value="Severe">Severe</option>
                <option value="Stunted">Stunted</option>
                <option value="Wasted">Wasted</option>
            </select>
            
            <input type="date" 
                   id="dateFrom" 
                   class="filter-control date-input"
                   value="{{ request('date_from') }}">
            
            <input type="date" 
                   id="dateTo" 
                   class="filter-control date-input"
                   value="{{ request('date_to') }}">
            
            <select id="perPage" class="filter-control small-select">
                <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                <option value="15" {{ request('per_page') == '15' || !request('per_page') ? 'selected' : '' }}>15</option>
                <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
            </select>
            
            <div class="action-buttons">
                <button type="button" id="clearFilters" class="btn-action btn-clear" title="Clear Filters">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" id="exportBtn" class="btn-action btn-export" title="Export">
                    <i class="fas fa-download"></i>
                </button>
                <a href="{{ route('nutritionist.patients') }}" class="btn-action btn-primary" title="New Assessment">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading assessments...</span>
        </div>
    </div>

    <!-- Assessments Table Container -->
    <div class="assessments-card">
        <div class="card-header">
            <h3>Assessment History</h3>
            <div class="header-info">
                <span class="badge bg-info text-white me-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Showing latest assessment per patient
                </span>
                <span id="resultsInfo" class="results-count"></span>
            </div>
        </div>
        <div id="assessmentsContainer" class="card-content">
            @include('nutritionist.partials.assessments-table', ['assessments' => $assessments])
        </div>
    </div>

    <!-- Quick Assessment Button -->
    <div class="mb-4">
        <button type="button" class="btn btn-primary btn-lg" onclick="openQuickAssessmentModal()">
            <i class="fas fa-bolt"></i>
            Quick Assessment Tool
        </button>
    </div>

    <!-- Quick Assessment Modal -->
    <div class="modal fade" id="quickAssessmentModal" tabindex="-1" aria-labelledby="quickAssessmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="quickAssessmentModalLabel">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Assessment Tool
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeQuickAssessmentModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">Perform a quick malnutrition assessment without saving to patient records</p>
                    
                    <form id="quickAssessmentForm" class="quick-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="quick_age" class="form-label">Age (months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quick_age" name="age_months" min="0" max="60" required>
                                <div class="form-text">Age in months (0-60)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="quick_gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="quick_gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="quick_weight" class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" class="form-control" id="quick_weight" name="weight_kg" min="1" max="50" required>
                                <div class="form-text">Weight in kilograms</div>
                            </div>
                            <div class="col-md-6">
                                <label for="quick_height" class="form-label">Height (cm) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" class="form-control" id="quick_height" name="height_cm" min="30" max="150" required>
                                <div class="form-text">Height in centimeters</div>
                            </div>
                        </div>
                    </form>

                    <!-- Assessment Result -->
                    <div id="quickAssessmentResult" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Assessment Result
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="quickResultContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeQuickAssessmentModal()">
                        <i class="fas fa-times me-1"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-success" onclick="performQuickAssessment()">
                        <i class="fas fa-bolt me-1"></i>
                        Perform Assessment
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
// Set up route URLs for external JS file
window.assessmentsRoutes = {
    assessments: '{{ route("nutritionist.assessments") }}',
    quickAssessment: '{{ route("nutritionist.assessment.quick") }}'
};
</script>
<script src="{{ asset('js/assessments.js') }}"></script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/assessments.css') }}">
@endpush
