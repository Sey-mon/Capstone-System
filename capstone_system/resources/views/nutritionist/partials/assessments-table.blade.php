@if($assessments->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <a href="#" class="sort-link" data-sort="patient_id">
                            Patient
                            <i class="fas fa-sort sort-icon"></i>
                        </a>
                    </th>
                    <th>
                        <a href="#" class="sort-link" data-sort="assessment_date">
                            Assessment Date
                            <i class="fas fa-sort sort-icon"></i>
                        </a>
                    </th>
                    <th>
                        <a href="#" class="sort-link" data-sort="treatment">
                            Diagnosis
                            <i class="fas fa-sort sort-icon"></i>
                        </a>
                    </th>
                    <th>Weight (kg)</th>
                    <th>Height (cm)</th>
                    <th>
                        <a href="#" class="sort-link" data-sort="completed_at">
                            Status
                            <i class="fas fa-sort sort-icon"></i>
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assessments as $assessment)
                    <tr>
                        <td>
                            <div class="patient-info">
                                <strong>{{ $assessment->patient->first_name }} {{ $assessment->patient->last_name }}</strong>
                                <small class="d-block text-muted">{{ $assessment->patient->age_months }} months old</small>
                            </div>
                        </td>
                        <td>{{ $assessment->assessment_date->format('M d, Y') }}</td>
                        <td>
                            @php
                                $diagnosisDisplay = 'Not specified';
                                if (isset($assessment) && $assessment->treatment) {
                                    $treatmentData = json_decode($assessment->treatment, true);
                                    if ($treatmentData && isset($treatmentData['patient_info']['diagnosis'])) {
                                        $diagnosisDisplay = $treatmentData['patient_info']['diagnosis'];
                                    }
                                }
                            @endphp
                            <span class="diagnosis-badge {{ getDiagnosisBadgeClass($diagnosisDisplay) }}">
                                {{ $diagnosisDisplay }}
                            </span>
                        </td>
                        <td>{{ $assessment->weight_kg }} kg</td>
                        <td>{{ $assessment->height_cm }} cm</td>
                        <td>
                            @if($assessment->completed_at)
                                <span class="status-badge completed">
                                    <i class="fas fa-check-circle"></i>
                                    Completed
                                </span>
                            @else
                                <span class="status-badge pending">
                                    <i class="fas fa-clock"></i>
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if($assessment->completed_at)
                                    <button class="btn btn-sm btn-info" onclick="viewAssessment({{ $assessment->assessment_id }})" title="View Results">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-success" onclick="assessSpecificPatient({{ $assessment->patient_id }})" title="New Assessment">
                                    <i class="fas fa-redo"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick="printAssessmentDetails({{ $assessment->assessment_id }})" title="Print">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination Info -->
    <div class="pagination-info">
        <span class="text-muted">
            Showing {{ $assessments->firstItem() ?? 0 }} to {{ $assessments->lastItem() ?? 0 }} 
            of {{ $assessments->total() }} results
        </span>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        @if ($assessments->hasPages())
            <nav aria-label="Assessment pagination">
                <ul class="pagination justify-content-center">
                    {{-- Previous Page Link --}}
                    @if ($assessments->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">‹</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="{{ $assessments->currentPage() - 1 }}">‹</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @php
                        $start = max($assessments->currentPage() - 2, 1);
                        $end = min($assessments->currentPage() + 2, $assessments->lastPage());
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
                        @if ($page == $assessments->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="{{ $page }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endfor
                    
                    @if($end < $assessments->lastPage())
                        @if($end < $assessments->lastPage() - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="{{ $assessments->lastPage() }}">{{ $assessments->lastPage() }}</a>
                        </li>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($assessments->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="{{ $assessments->currentPage() + 1 }}">›</a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">›</span>
                        </li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <h4>No Assessments Found</h4>
        <p>{{ request('search') ? 'No assessments match your search criteria.' : 'You haven\'t performed any assessments yet.' }}</p>
        <a href="{{ route('nutritionist.patients') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Start Your First Assessment
        </a>
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
