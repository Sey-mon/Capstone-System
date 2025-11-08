@if($patients->count() > 0)
    <!-- Modern Card Grid -->
    <div class="patients-grid">
        @foreach($patients as $patient)
            @php
                $latestAssessment = $patient->assessments->first();
                $diagnosisDisplay = 'Not specified';
                
                if ($latestAssessment && $latestAssessment->treatment) {
                    $treatmentData = json_decode($latestAssessment->treatment, true);
                    if ($treatmentData && isset($treatmentData['patient_info']['diagnosis'])) {
                        $diagnosisDisplay = $treatmentData['patient_info']['diagnosis'];
                    }
                }
            @endphp
            
            <div class="patient-card">
                <!-- Patient Header -->
                <div class="patient-card-header">
                    <div class="patient-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="patient-basic-info">
                        <h4 class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</h4>
                        <p class="patient-details">
                            <span class="detail-item">
                                <i class="fas fa-birthday-cake me-1"></i>
                                {{ $patient->age_months }} months
                            </span>
                            <span class="detail-item">
                                <i class="fas fa-{{ $patient->sex == 'Male' ? 'mars' : 'venus' }} me-1"></i>
                                {{ $patient->sex }}
                            </span>
                        </p>
                    </div>
                    <div class="patient-status">
                        @if($latestAssessment)
                            @if($latestAssessment->completed_at)
                                <span class="modern-status-badge completed">
                                    <i class="fas fa-check-circle"></i>
                                    Completed
                                </span>
                            @else
                                <span class="modern-status-badge pending">
                                    <i class="fas fa-clock"></i>
                                    Pending
                                </span>
                            @endif
                        @else
                            <span class="modern-status-badge no-assessment">
                                <i class="fas fa-exclamation-triangle"></i>
                                No Assessment
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Assessment Info -->
                <div class="patient-card-body">
                    <div class="assessment-info">
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Last Assessment
                                </span>
                                <span class="info-value">
                                    @if($latestAssessment)
                                        {{ $latestAssessment->assessment_date->format('M d, Y') }}
                                    @else
                                        <span class="no-data">No assessment</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">
                                    <i class="fas fa-stethoscope me-1"></i>
                                    Diagnosis
                                </span>
                                <span class="info-value">
                                    <span class="modern-diagnosis-badge {{ getDiagnosisBadgeClass($diagnosisDisplay) }}">
                                        {{ $diagnosisDisplay }}
                                    </span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">
                                    <i class="fas fa-weight me-1"></i>
                                    Weight
                                </span>
                                <span class="info-value">
                                    @if($latestAssessment)
                                        <strong>{{ $latestAssessment->weight_kg }} kg</strong>
                                    @else
                                        <span class="no-data">-</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">
                                    <i class="fas fa-ruler-vertical me-1"></i>
                                    Height
                                </span>
                                <span class="info-value">
                                    @if($latestAssessment)
                                        <strong>{{ $latestAssessment->height_cm }} cm</strong>
                                    @else
                                        <span class="no-data">-</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="patient-card-actions">
                    @if($latestAssessment && $latestAssessment->completed_at)
                        <button class="action-btn view-btn" onclick="viewAssessment({{ $latestAssessment->assessment_id }})" title="View Assessment Details">
                            <i class="fas fa-eye me-1"></i>
                            View Details
                        </button>
                    @endif
                    
                    <button class="action-btn assess-btn" onclick="assessSpecificPatient({{ $patient->patient_id }})" title="{{ $latestAssessment ? 'New Assessment' : 'First Assessment' }}">
                        <i class="fas fa-{{ $latestAssessment ? 'redo' : 'plus' }} me-1"></i>
                        {{ $latestAssessment ? 'New Assessment' : 'First Assessment' }}
                    </button>
                    
                    @if($latestAssessment)
                        <button class="action-btn print-btn" onclick="printAssessmentDetails({{ $latestAssessment->assessment_id }})" title="Print Assessment">
                            <i class="fas fa-print me-1"></i>
                            Print
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    </div>

    <!-- Enhanced Pagination Container -->
    <div class="pagination-container">
        <div class="pagination-info-extended">
            <div class="total-results">
                <i class="fas fa-users me-1"></i>
                Total: {{ $patients->total() }} patients
            </div>
            <div class="showing-results">
                Showing {{ $patients->firstItem() ?? 0 }} to {{ $patients->lastItem() ?? 0 }}
            </div>
            <div class="page-size-selector">
                <label for="pageSizeSelect" class="me-2">Per page:</label>
                <select id="pageSizeSelect" onchange="changePageSize(this.value)">
                    <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page') == '15' || !request('per_page') ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </div>

        @if ($patients->hasPages())
            <div class="pagination-wrapper">
                <nav aria-label="Patient pagination" class="pagination-nav">
                    <ul class="pagination">
                        {{-- First Page --}}
                        @if ($patients->currentPage() > 3)
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="1" title="First page">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Previous Page --}}
                        @if ($patients->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-angle-left"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="{{ $patients->currentPage() - 1 }}" title="Previous page">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            $start = max($patients->currentPage() - 2, 1);
                            $end = min($patients->currentPage() + 2, $patients->lastPage());
                            
                            // Ensure we always show 5 pages when possible
                            if ($end - $start < 4) {
                                if ($start == 1) {
                                    $end = min($start + 4, $patients->lastPage());
                                } else {
                                    $start = max($end - 4, 1);
                                }
                            }
                        @endphp
                        
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="1">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif
                        
                        @for($page = $start; $page <= $end; $page++)
                            @if ($page == $patients->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="#" data-page="{{ $page }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endfor
                        
                        @if($end < $patients->lastPage())
                            @if($end < $patients->lastPage() - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="{{ $patients->lastPage() }}">{{ $patients->lastPage() }}</a>
                            </li>
                        @endif

                        {{-- Next Page --}}
                        @if ($patients->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="{{ $patients->currentPage() + 1 }}" title="Next page">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-angle-right"></i>
                                </span>
                            </li>
                        @endif

                        {{-- Last Page --}}
                        @if ($patients->currentPage() < $patients->lastPage() - 2)
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="{{ $patients->lastPage() }}" title="Last page">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif

        {{-- Quick Jump to Page --}}
        @if ($patients->lastPage() > 10)
            <div class="quick-jump mt-3">
                <div class="input-group" style="max-width: 200px; margin: 0 auto;">
                    <span class="input-group-text">Go to page:</span>
                    <input type="number" class="form-control" id="jumpToPage" min="1" max="{{ $patients->lastPage() }}" placeholder="Page #">
                    <button class="btn btn-outline-primary" onclick="jumpToPage()">Go</button>
                </div>
            </div>
        @endif
    </div>
@else
    <div class="modern-empty-state">
        <div class="empty-illustration">
            <div class="empty-icon-circle">
                <i class="fas fa-users"></i>
            </div>
            <div class="empty-dots">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
        <div class="empty-content">
            <h3 class="empty-title">No Patients Found</h3>
            <p class="empty-message">
                {{ request('search') ? 'No patients match your search criteria. Try adjusting your filters.' : 'You don\'t have any assigned patients yet. Contact your administrator to get patients assigned to you.' }}
            </p>
            <div class="empty-actions">
                <a href="{{ route('nutritionist.patients') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>
                    View All Patients
                </a>
                @if(request('search') || request()->hasAny(['status', 'diagnosis', 'date_from', 'date_to']))
                    <button class="btn btn-outline-secondary btn-lg ms-3" id="clearFiltersEmpty">
                        <i class="fas fa-times me-2"></i>
                        Clear Filters
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif

@php
function getDiagnosisBadgeClass($diagnosis) {
    if (strpos(strtolower($diagnosis), 'normal') !== false) return 'success';
    if (strpos(strtolower($diagnosis), 'severe') !== false) return 'danger';
    if (strpos(strtolower($diagnosis), 'moderate') !== false) return 'warning';
    return 'info';
}
@endphp
