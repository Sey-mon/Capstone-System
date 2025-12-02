@extends('layouts.dashboard')

@section('title', 'Assessments')

@section('page-title', 'Patient Assessments')
@section('page-subtitle', 'View and manage latest malnutrition assessments for each patient')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@push('head')
    <!-- Preload critical resources for better LCP -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preload" href="{{ asset('img/shares-logo.png') }}" as="image">
    <link rel="preload" href="{{ asset('css/nutritionist-assessments.css') }}" as="style">
@endpush

@section('content')
    <!-- Modern Filter Bar -->
    <div class="modern-filters-container" role="search" aria-label="Patient filters">
        <div class="filter-header">
            <h5 class="filter-title">
                <i class="fas fa-filter me-2" aria-hidden="true"></i>
                Filter Patients
                @php
                    $activeFilters = 0;
                    if(request('search')) $activeFilters++;
                    if(request('status')) $activeFilters++;
                    if(request('diagnosis')) $activeFilters++;
                    if(request('date_from')) $activeFilters++;
                    if(request('date_to')) $activeFilters++;
                @endphp
                @if($activeFilters > 0)
                    <span class="badge bg-primary ms-2">{{ $activeFilters }} active</span>
                @endif
            </h5>
            <button class="btn btn-outline-secondary btn-sm" id="clearFilters" aria-label="Clear all filters">
                <i class="fas fa-times me-1" aria-hidden="true"></i>
                Clear All
            </button>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label" for="searchInput">
                    <i class="fas fa-search me-1" aria-hidden="true"></i>
                    Search
                </label>
                <input type="text" 
                       id="searchInput" 
                       class="modern-filter-input" 
                       placeholder="Search patients, diagnosis..."
                       value="{{ request('search') }}"
                       aria-label="Search patients by name or diagnosis"
                       autocomplete="off">
            </div>
            
            <div class="filter-group">
                <label class="filter-label" for="statusFilter">
                    <i class="fas fa-clipboard-check me-1" aria-hidden="true"></i>
                    Status
                </label>
                <select id="statusFilter" class="modern-filter-select" aria-label="Filter by assessment status">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="no_assessment" {{ request('status') == 'no_assessment' ? 'selected' : '' }}>No Assessment</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label" for="diagnosisFilter">
                    <i class="fas fa-stethoscope me-1" aria-hidden="true"></i>
                    Diagnosis
                </label>
                <select id="diagnosisFilter" class="modern-filter-select" aria-label="Filter by diagnosis type">
                    <option value="">All Diagnoses</option>
                    <option value="Normal">Normal</option>
                    <option value="Moderate">Moderate</option>
                    <option value="Severe">Severe</option>
                    <option value="Stunted">Stunted</option>
                    <option value="Wasted">Wasted</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label" for="dateFrom">
                    <i class="fas fa-calendar-alt me-1" aria-hidden="true"></i>
                    Date From
                </label>
                <input type="date" 
                       id="dateFrom" 
                       class="modern-filter-input"
                       value="{{ request('date_from') }}"
                       aria-label="Filter assessments from date">
            </div>
            
            <div class="filter-group">
                <label class="filter-label" for="dateTo">
                    <i class="fas fa-calendar-alt me-1" aria-hidden="true"></i>
                    Date To
                </label>
                <input type="date" 
                       id="dateTo" 
                       class="modern-filter-input"
                       value="{{ request('date_to') }}"
                       aria-label="Filter assessments to date">
            </div>
            
            <div class="filter-group">
                <label class="filter-label" for="perPage">
                    <i class="fas fa-list me-1" aria-hidden="true"></i>
                    Per Page
                </label>
                <select id="perPage" class="modern-filter-select" aria-label="Items per page">
                    <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page') == '15' || !request('per_page') ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Modern Loading Indicator -->
    <div id="loadingIndicator" class="loading-overlay" style="display: none;" role="status" aria-live="polite">
        <div class="loading-spinner">
            <div class="modern-spinner" aria-hidden="true">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <p class="loading-text">Loading patient assessments...</p>
            <span class="visually-hidden">Loading content, please wait</span>
        </div>
    </div>

    <!-- Modern Assessments Container -->
    <div class="modern-assessments-card">
        <div class="modern-card-header">
            <div class="header-left">
                <h2 class="modern-title">
                    <i class="fas fa-chart-line me-3"></i>
                    Patient Assessments
                </h2>
                <p class="modern-subtitle">Manage and track your assigned patients' nutritional assessments</p>
            </div>
            <div class="header-right">
                <div class="info-badges">
                    <span class="modern-badge info">
                        <i class="fas fa-users me-1"></i>
                        All Assigned Patients
                    </span>
                    <span class="modern-badge primary" id="resultsInfo">
                        <i class="fas fa-chart-bar me-1"></i>
                        Loading...
                    </span>
                </div>
                <button class="btn btn-primary btn-lg" onclick="openPatientSelectionModal()" aria-label="Create new patient assessment">
                    <i class="fas fa-plus me-2" aria-hidden="true"></i>
                    New Assessment
                </button>
            </div>
        </div>
        
        <div id="assessmentsContainer" class="modern-card-content">
            @include('nutritionist.partials.assessments-table', ['patients' => $patients, 'assessments' => $patients])
        </div>
    </div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/nutritionist-assessments.css') }}?v={{ time() }}">
@endpush

@section('scripts')
<script>
// Set up route URLs for external JS file
window.assessmentsRoutes = {
    assessments: '{{ route("nutritionist.assessments") }}',
    patientsForAssessment: '{{ route("nutritionist.patients") }}',
    assessPatient: '{{ route("nutritionist.patients.assess", ":patientId") }}',
    assessmentDetails: '{{ route("nutritionist.assessment.details", ":assessmentId") }}',
    assessmentPdf: '{{ route("nutritionist.assessment.pdf", ":assessmentId") }}'
};

// Pass barangays and parents data for add patient form
window.barangaysData = {!! json_encode(\App\Models\Barangay::all(['barangay_id', 'barangay_name'])) !!};
window.parentsData = {!! json_encode(\App\Models\User::where('role_id', 4)->get(['user_id', 'first_name', 'last_name'])) !!};
</script>
<script src="{{ asset('js/nutritionist-assessments.js') }}" defer></script>

<script>
// Pagination keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.target.tagName.toLowerCase() !== 'input' && e.target.tagName.toLowerCase() !== 'textarea') {
        @if(isset($patients))
        const currentPage = {{ $patients->currentPage() ?? 1 }};
        const lastPage = {{ $patients->lastPage() ?? 1 }};
        
        // Left arrow - previous page
        if (e.key === 'ArrowLeft' && currentPage > 1) {
            e.preventDefault();
            goToPage(currentPage - 1);
        }
        
        // Right arrow - next page
        if (e.key === 'ArrowRight' && currentPage < lastPage) {
            e.preventDefault();
            goToPage(currentPage + 1);
        }
        
        // Home - first page
        if (e.key === 'Home' && currentPage > 1) {
            e.preventDefault();
            goToPage(1);
        }
        
        // End - last page
        if (e.key === 'End' && currentPage < lastPage) {
            e.preventDefault();
            goToPage(lastPage);
        }
        @endif
    }
});
</script>
@endsection
