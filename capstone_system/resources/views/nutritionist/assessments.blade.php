@extends('layouts.dashboard')

@section('title', 'Assessments')

@section('page-title', 'Patient Assessments')
@section('page-subtitle', 'View and manage latest malnutrition assessments for each patient')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <!-- Modern Filter Bar -->
    <div class="modern-filters-container">
        <div class="filter-header">
            <h5 class="filter-title">
                <i class="fas fa-filter me-2"></i>
                Filter Patients
            </h5>
            <button class="btn btn-outline-secondary btn-sm" id="clearFilters">
                <i class="fas fa-times me-1"></i>
                Clear All
            </button>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-search me-1"></i>
                    Search
                </label>
                <input type="text" 
                       id="searchInput" 
                       class="modern-filter-input" 
                       placeholder="Search patients, diagnosis..."
                       value="{{ request('search') }}">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-clipboard-check me-1"></i>
                    Status
                </label>
                <select id="statusFilter" class="modern-filter-select">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-stethoscope me-1"></i>
                    Diagnosis
                </label>
                <select id="diagnosisFilter" class="modern-filter-select">
                    <option value="">All Diagnoses</option>
                    <option value="Normal">Normal</option>
                    <option value="Moderate">Moderate</option>
                    <option value="Severe">Severe</option>
                    <option value="Stunted">Stunted</option>
                    <option value="Wasted">Wasted</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Date From
                </label>
                <input type="date" 
                       id="dateFrom" 
                       class="modern-filter-input"
                       value="{{ request('date_from') }}">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Date To
                </label>
                <input type="date" 
                       id="dateTo" 
                       class="modern-filter-input"
                       value="{{ request('date_to') }}">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-list me-1"></i>
                    Per Page
                </label>
                <select id="perPage" class="modern-filter-select">
                    <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page') == '15' || !request('per_page') ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Modern Loading Indicator -->
    <div id="loadingIndicator" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <div class="modern-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <p class="loading-text">Loading patient assessments...</p>
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
                <button class="btn btn-primary btn-lg" onclick="openPatientSelectionModal()">
                    <i class="fas fa-plus me-2"></i>
                    New Assessment
                </button>
            </div>
        </div>
        
        <div id="assessmentsContainer" class="modern-card-content">
            @include('nutritionist.partials.assessments-table', ['patients' => $patients, 'assessments' => $patients])
        </div>
    </div>

    <!-- Assessment Details Modal -->
    <div class="modal fade" id="assessmentDetailsModal" tabindex="-1" aria-labelledby="assessmentDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog custom-modal-size">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assessmentDetailsModalLabel">
                        <i class="fas fa-chart-line me-2"></i>
                        Assessment Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="assessmentDetailsContent">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading assessment details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="printAssessmentBtn" style="display: none;">
                        <i class="fas fa-print me-1"></i>
                        Print Assessment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Selection Modal -->
    <div class="modal fade" id="patientSelectionModal" tabindex="-1" aria-labelledby="patientSelectionModalLabel" aria-hidden="true">
        <div class="modal-dialog custom-modal-size">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patientSelectionModalLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        Select Patient to Assess
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" id="patientSearchInput" class="form-control" placeholder="Search patients by name...">
                    </div>
                    <div id="patientListContainer" class="patient-list">
                        <!-- Patients will be loaded here via AJAX -->
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading patients...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Form Modal -->
    <div class="modal fade" id="assessmentFormModal" tabindex="-1" aria-labelledby="assessmentFormModalLabel" aria-hidden="true">
    <div class="modal-dialog custom-modal-size">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assessmentFormModalLabel">
                        <i class="fas fa-stethoscope me-2"></i>
                        New Assessment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="assessmentFormContent">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading assessment form...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitAssessmentBtn" style="display: none;">
                        <i class="fas fa-save me-1"></i>
                        Submit Assessment
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
    patientsForAssessment: '{{ route("nutritionist.patients") }}',
    assessPatient: '{{ route("nutritionist.patients.assess", ":patientId") }}',
    assessmentDetails: '{{ route("nutritionist.assessment.details", ":assessmentId") }}',
    assessmentPdf: '{{ route("nutritionist.assessment.pdf", ":assessmentId") }}'
};

// Patient selection modal functions
function openPatientSelectionModal() {
    const modal = new bootstrap.Modal(document.getElementById('patientSelectionModal'));
    modal.show();
    loadPatientsForSelection();
}

function loadPatientsForSelection() {
    const container = document.getElementById('patientListContainer');
    
    fetch(window.assessmentsRoutes.patientsForAssessment + '?ajax=1')
        .then(response => response.json())
        .then(data => {
            if (data.patients && data.patients.length > 0) {
                container.innerHTML = data.patients.map(patient => `
                    <div class="patient-item border-bottom p-3" onclick="selectPatientForAssessment(${patient.patient_id})">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${patient.first_name} ${patient.last_name}</h6>
                                <small class="text-muted">
                                    ${patient.age_months} months • ${patient.sex} • ${patient.barangay?.barangay_name || 'Unknown Barangay'}
                                </small>
                            </div>
                            <button class="btn btn-success btn-sm">
                                <i class="fas fa-stethoscope me-1"></i>
                                Assess
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center p-4">
                        <i class="fas fa-user-slash text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted">No patients available for assessment</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading patients:', error);
            container.innerHTML = `
                <div class="text-center p-4 text-danger">
                    <i class="fas fa-exclamation-triangle mb-2" style="font-size: 2rem;"></i>
                    <p>Error loading patients. Please try again.</p>
                </div>
            `;
        });
}

function selectPatientForAssessment(patientId) {
    // Close the patient selection modal
    const patientModal = bootstrap.Modal.getInstance(document.getElementById('patientSelectionModal'));
    if (patientModal) {
        patientModal.hide();
    }
    
    // Open the assessment form modal
    showAssessmentForm(patientId);
}

function assessSpecificPatient(patientId) {
    showAssessmentForm(patientId);
}

function showAssessmentForm(patientId) {
    const modal = new bootstrap.Modal(document.getElementById('assessmentFormModal'));
    modal.show();
    loadAssessmentForm(patientId);
}

function loadAssessmentForm(patientId) {
    const content = document.getElementById('assessmentFormContent');
    const submitBtn = document.getElementById('submitAssessmentBtn');
    
    // Show loading state
    content.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading assessment form...</p>
        </div>
    `;
    submitBtn.style.display = 'none';
    
    const assessUrl = window.assessmentsRoutes.assessPatient.replace(':patientId', patientId);
    
    fetch(assessUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
            submitBtn.style.display = 'inline-block';
            
            // Set up form submission
            setupAssessmentFormSubmission(patientId);
        })
        .catch(error => {
            console.error('Error loading assessment form:', error);
            content.innerHTML = `
                <div class="text-center p-4 text-danger">
                    <i class="fas fa-exclamation-triangle mb-2" style="font-size: 2rem;"></i>
                    <p>Error loading assessment form. Please try again.</p>
                </div>
            `;
        });
}

function setupAssessmentFormSubmission(patientId) {
    const submitBtn = document.getElementById('submitAssessmentBtn');
    const form = document.querySelector('#assessmentFormContent form');
    
    if (form && submitBtn) {
        submitBtn.onclick = function() {
            // Trigger form submission
            const formData = new FormData(form);
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal and refresh assessments
                    const modal = bootstrap.Modal.getInstance(document.getElementById('assessmentFormModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Refresh the assessments list
                    if (window.assessmentManager) {
                        window.assessmentManager.loadAssessments();
                    } else {
                        location.reload();
                    }
                    
                    // Show success message
                    showSuccessMessage('Assessment submitted successfully!');
                } else {
                    showErrorMessage(data.message || 'Failed to submit assessment');
                }
            })
            .catch(error => {
                console.error('Error submitting assessment:', error);
                showErrorMessage('An error occurred while submitting the assessment');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Submit Assessment';
            });
        };
    }
}

function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.assessment-filters-container').prepend(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentElement) alertDiv.remove();
    }, 5000);
}

function showErrorMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.assessment-filters-container').prepend(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentElement) alertDiv.remove();
    }, 5000);
}

// Assessment details modal functions
function viewAssessment(assessmentId) {
    const modal = new bootstrap.Modal(document.getElementById('assessmentDetailsModal'));
    modal.show();
    loadAssessmentDetails(assessmentId);
}

function loadAssessmentDetails(assessmentId) {
    const content = document.getElementById('assessmentDetailsContent');
    const printBtn = document.getElementById('printAssessmentBtn');
    
    // Show loading state
    content.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading assessment details...</p>
        </div>
    `;
    printBtn.style.display = 'none';
    
    const url = window.assessmentsRoutes.assessmentDetails.replace(':assessmentId', assessmentId);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAssessmentDetails(data.assessment);
                // Show print button and set up click handler
                printBtn.style.display = 'inline-block';
                printBtn.onclick = () => printAssessmentDetails(assessmentId);
            } else {
                content.innerHTML = `
                    <div class="text-center p-4 text-danger">
                        <i class="fas fa-exclamation-triangle mb-2" style="font-size: 2rem;"></i>
                        <p>${data.message || 'Failed to load assessment details'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading assessment details:', error);
            content.innerHTML = `
                <div class="text-center p-4 text-danger">
                    <i class="fas fa-exclamation-triangle mb-2" style="font-size: 2rem;"></i>
                    <p>Error loading assessment details. Please try again.</p>
                </div>
            `;
        });
}

function displayAssessmentDetails(assessment) {
    const content = document.getElementById('assessmentDetailsContent');
    
    // Parse treatment plan if it exists
    let treatmentPlan = null;
    if (assessment.treatment_plan) {
        // If treatment_plan is a string, try to parse it
        if (typeof assessment.treatment_plan === 'string') {
            try {
                treatmentPlan = JSON.parse(assessment.treatment_plan);
            } catch (e) {
                console.error('Failed to parse treatment_plan string:', e);
                treatmentPlan = null;
            }
        } else if (typeof assessment.treatment_plan === 'object') {
            treatmentPlan = assessment.treatment_plan;
        }
    }
    
    // If no treatment_plan, try using the treatment field
    if (!treatmentPlan && assessment.treatment) {
        if (typeof assessment.treatment === 'string') {
            try {
                treatmentPlan = JSON.parse(assessment.treatment);
            } catch (e) {
                console.error('Failed to parse treatment string:', e);
                treatmentPlan = null;
            }
        } else if (typeof assessment.treatment === 'object') {
            treatmentPlan = assessment.treatment;
        }
    }

    content.innerHTML = `
        <div class="assessment-details">
            <!-- Patient Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Patient Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> ${assessment.patient.name}</p>
                            <p><strong>Age:</strong> ${assessment.patient.age_months} months</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Sex:</strong> ${assessment.patient.sex}</p>
                            <p><strong>Barangay:</strong> ${assessment.patient.barangay}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assessment Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Assessment Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Assessment Date:</strong> ${assessment.assessment_date}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge ${assessment.status === 'Completed' ? 'bg-success' : 'bg-warning'}">${assessment.status}</span>
                            </p>
                            ${assessment.completed_at ? `<p><strong>Completed At:</strong> ${assessment.completed_at}</p>` : ''}
                        </div>
                        <div class="col-md-6">
                            <p><strong>Nutritionist:</strong> ${assessment.nutritionist}</p>
                            <p><strong>Recovery Status:</strong> 
                                <span class="badge ${getRecoveryStatusClass(assessment.recovery_status)}">${assessment.recovery_status || 'Not specified'}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Measurements -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-ruler me-2"></i>
                        Measurements
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="measurement-item">
                                <h5 class="text-primary">${assessment.measurements.weight_kg || 'N/A'}</h5>
                                <small class="text-muted">Weight (kg)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="measurement-item">
                                <h5 class="text-primary">${assessment.measurements.height_cm || 'N/A'}</h5>
                                <small class="text-muted">Height (cm)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="measurement-item">
                                <h5 class="text-primary">${assessment.measurements.bmi || 'N/A'}</h5>
                                <small class="text-muted">BMI</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Diagnosis and Treatment -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-stethoscope me-2"></i>
                        Diagnosis and Treatment
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Diagnosis:</strong><br>
                        <span class="diagnosis-badge ${getDiagnosisClass(assessment.diagnosis)}">${assessment.diagnosis}</span>
                    </div>
                    
                    ${treatmentPlan && treatmentPlan.patient_info && treatmentPlan.patient_info.confidence_level ? `
                    <div class="mb-3">
                        <strong>Confidence Level:</strong> ${(treatmentPlan.patient_info.confidence_level * 100).toFixed(1)}%
                    </div>
                    ` : ''}
                    
                    <div class="mb-3">
                        <strong>Treatment Plan:</strong><br>
                        ${treatmentPlan ? `
                            <div class="mt-2">
                                <div class="alert alert-info">
                                    <small><i class="fas fa-info-circle me-1"></i>Comprehensive treatment plan available - see detailed sections below.</small>
                                </div>
                            </div>
                        ` : `
                            <div class="mt-2">
                                <div class="alert alert-warning">
                                    <small><i class="fas fa-exclamation-triangle me-1"></i>No detailed treatment plan available for this assessment.</small>
                                    ${assessment.treatment ? '<br><small>Raw treatment data: ' + (typeof assessment.treatment === 'string' ? assessment.treatment.substring(0, 100) + '...' : 'Object detected') + '</small>' : ''}
                                    ${assessment.treatment_plan ? '<br><small>Raw treatment_plan data: ' + (typeof assessment.treatment_plan === 'string' ? assessment.treatment_plan.substring(0, 100) + '...' : 'Object detected') + '</small>' : ''}
                                </div>
                            </div>
                        `}
                    </div>
                </div>
            </div>

            ${treatmentPlan ? `
            <!-- Immediate Actions -->
            ${treatmentPlan.immediate_actions ? `
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                        Immediate Actions Required
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        ${treatmentPlan.immediate_actions.map(action => `
                            <li class="list-group-item d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                ${action}
                            </li>
                        `).join('')}
                    </ul>
                </div>
            </div>
            ` : ''}

            <!-- Nutrition Plan -->
            ${treatmentPlan.nutrition_plan ? `
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-apple-alt me-2"></i>
                        Nutrition Plan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Age Group:</strong> ${treatmentPlan.nutrition_plan.age_group || 'Not specified'}</p>
                            <p><strong>Current Weight:</strong> ${treatmentPlan.nutrition_plan.current_weight || 'Not specified'}</p>
                            <p><strong>Target Weight:</strong> ${treatmentPlan.nutrition_plan.target_weight || 'Not specified'}</p>
                            <p><strong>Phase:</strong> ${treatmentPlan.nutrition_plan.phase || 'Not specified'}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>RUTF Sachets Daily:</strong> ${treatmentPlan.nutrition_plan.rutf_sachets_daily || 'Not specified'}</p>
                            <p><strong>RUTF Calories Daily:</strong> ${treatmentPlan.nutrition_plan.rutf_calories_daily || 'Not specified'}</p>
                            <p><strong>Feeding Frequency:</strong> ${treatmentPlan.nutrition_plan.feeding_frequency || 'Not specified'}</p>
                            <p><strong>Feeding Schedule:</strong> ${treatmentPlan.nutrition_plan.feeding_schedule || 'Not specified'}</p>
                        </div>
                    </div>
                    ${treatmentPlan.nutrition_plan.special_instructions ? `
                    <div class="mt-3">
                        <h6>Special Instructions:</h6>
                        <ul>
                            ${treatmentPlan.nutrition_plan.special_instructions.map(instruction => `<li>${instruction}</li>`).join('')}
                        </ul>
                    </div>
                    ` : ''}
                    ${treatmentPlan.nutrition_plan.breastfeeding ? `
                    <div class="alert alert-info mt-3">
                        <strong>Breastfeeding:</strong> ${treatmentPlan.nutrition_plan.breastfeeding}
                    </div>
                    ` : ''}
                </div>
            </div>
            ` : ''}

            <!-- Medical Interventions -->
            ${treatmentPlan && treatmentPlan.medical_interventions ? `
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-pills me-2"></i>
                        Medical Interventions
                    </h6>
                </div>
                <div class="card-body">
                    ${treatmentPlan.medical_interventions.routine_medications ? `
                    <div class="mb-3">
                        <h6>Routine Medications:</h6>
                        <ul>
                            ${treatmentPlan.medical_interventions.routine_medications.map(med => `<li>${med}</li>`).join('')}
                        </ul>
                    </div>
                    ` : ''}
                    ${treatmentPlan.medical_interventions.therapeutic_medications ? `
                    <div class="mb-3">
                        <h6>Therapeutic Medications:</h6>
                        <ul>
                            ${treatmentPlan.medical_interventions.therapeutic_medications.map(med => `<li>${med}</li>`).join('')}
                        </ul>
                    </div>
                    ` : ''}
                    ${treatmentPlan.medical_interventions.medical_monitoring ? `
                    <div class="mb-3">
                        <h6>Medical Monitoring:</h6>
                        <ul>
                            ${treatmentPlan.medical_interventions.medical_monitoring.map(monitor => `<li>${monitor}</li>`).join('')}
                        </ul>
                    </div>
                    ` : ''}
                </div>
            </div>
            ` : ''}

            <!-- Monitoring Schedule -->
            ${treatmentPlan && treatmentPlan.monitoring_schedule ? `
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Monitoring Schedule
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        ${treatmentPlan.monitoring_schedule.phase_1_stabilization ? `
                        <div class="col-md-6">
                            <h6>Phase 1 - Stabilization</h6>
                            <p><strong>Duration:</strong> ${treatmentPlan.monitoring_schedule.phase_1_stabilization.duration}</p>
                            <p><strong>Frequency:</strong> ${treatmentPlan.monitoring_schedule.phase_1_stabilization.frequency}</p>
                            ${treatmentPlan.monitoring_schedule.phase_1_stabilization.assessments ? `
                            <p><strong>Assessments:</strong></p>
                            <ul>
                                ${treatmentPlan.monitoring_schedule.phase_1_stabilization.assessments.map(assess => `<li>${assess}</li>`).join('')}
                            </ul>
                            ` : ''}
                        </div>
                        ` : ''}
                        ${treatmentPlan.monitoring_schedule.phase_2_rehabilitation ? `
                        <div class="col-md-6">
                            <h6>Phase 2 - Rehabilitation</h6>
                            <p><strong>Duration:</strong> ${treatmentPlan.monitoring_schedule.phase_2_rehabilitation.duration}</p>
                            <p><strong>Frequency:</strong> ${treatmentPlan.monitoring_schedule.phase_2_rehabilitation.frequency}</p>
                            ${treatmentPlan.monitoring_schedule.phase_2_rehabilitation.assessments ? `
                            <p><strong>Assessments:</strong></p>
                            <ul>
                                ${treatmentPlan.monitoring_schedule.phase_2_rehabilitation.assessments.map(assess => `<li>${assess}</li>`).join('')}
                            </ul>
                            ` : ''}
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Follow-up Plan -->
            ${treatmentPlan && treatmentPlan.follow_up_plan ? `
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Follow-up Plan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            ${treatmentPlan.follow_up_plan.next_assessment ? `<p><strong>Next Assessment:</strong> ${treatmentPlan.follow_up_plan.next_assessment}</p>` : ''}
                            ${treatmentPlan.follow_up_plan.key_milestone_1 ? `<p><strong>Key Milestone 1:</strong> ${treatmentPlan.follow_up_plan.key_milestone_1}</p>` : ''}
                        </div>
                        <div class="col-md-6">
                            ${treatmentPlan.follow_up_plan.key_milestone_2 ? `<p><strong>Key Milestone 2:</strong> ${treatmentPlan.follow_up_plan.key_milestone_2}</p>` : ''}
                            ${treatmentPlan.follow_up_plan.discharge_evaluation ? `<p><strong>Discharge Evaluation:</strong> ${treatmentPlan.follow_up_plan.discharge_evaluation}</p>` : ''}
                            ${treatmentPlan.follow_up_plan.post_discharge_followup ? `<p><strong>Post-Discharge Follow-up:</strong> ${treatmentPlan.follow_up_plan.post_discharge_followup}</p>` : ''}
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Family Education -->
            ${treatmentPlan && treatmentPlan.family_education ? `
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Family Education
                    </h6>
                </div>
                <div class="card-body">
                    <ul>
                        ${treatmentPlan.family_education.map(education => `<li>${education}</li>`).join('')}
                    </ul>
                </div>
            </div>
            ` : ''}

            <!-- Success Criteria -->
            ${treatmentPlan && treatmentPlan.success_criteria ? `
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        Success Criteria
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        ${treatmentPlan.success_criteria.short_term ? `
                        <div class="col-md-4">
                            <h6>Short Term</h6>
                            <ul>
                                ${treatmentPlan.success_criteria.short_term.map(criteria => `<li>${criteria}</li>`).join('')}
                            </ul>
                        </div>
                        ` : ''}
                        ${treatmentPlan.success_criteria.medium_term ? `
                        <div class="col-md-4">
                            <h6>Medium Term</h6>
                            <ul>
                                ${treatmentPlan.success_criteria.medium_term.map(criteria => `<li>${criteria}</li>`).join('')}
                            </ul>
                        </div>
                        ` : ''}
                        ${treatmentPlan.success_criteria.long_term ? `
                        <div class="col-md-4">
                            <h6>Long Term</h6>
                            <ul>
                                ${treatmentPlan.success_criteria.long_term.map(criteria => `<li>${criteria}</li>`).join('')}
                            </ul>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Discharge Criteria -->
            ${treatmentPlan && treatmentPlan.discharge_criteria ? `
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Discharge Criteria
                    </h6>
                </div>
                <div class="card-body">
                    <ul>
                        ${treatmentPlan.discharge_criteria.map(criteria => `<li>${criteria}</li>`).join('')}
                    </ul>
                </div>
            </div>
            ` : ''}

            <!-- Emergency Signs -->
            ${treatmentPlan && treatmentPlan.emergency_signs ? `
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Emergency Warning Signs
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <strong>Seek immediate medical attention if any of these signs appear:</strong>
                    </div>
                    <ul>
                        ${treatmentPlan.emergency_signs.map(sign => `<li class="text-danger"><strong>${sign}</strong></li>`).join('')}
                    </ul>
                </div>
            </div>
            ` : ''}
            ` : ''}

            <!-- Notes -->
            ${assessment.notes ? `
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-sticky-note me-2"></i>
                        Notes
                    </h6>
                </div>
                <div class="card-body">
                    <p>${assessment.notes}</p>
                </div>
            </div>
            ` : ''}
        </div>
    `;
}

function getRecoveryStatusClass(status) {
    switch(status) {
        case 'Recovered': return 'bg-success';
        case 'Dropped Out': return 'bg-danger';
        case 'Ongoing': return 'bg-info';
        default: return 'bg-secondary';
    }
}

function getDiagnosisClass(diagnosis) {
    if (diagnosis.toLowerCase().includes('normal')) return 'success';
    if (diagnosis.toLowerCase().includes('severe')) return 'danger';
    if (diagnosis.toLowerCase().includes('moderate')) return 'warning';
    if (diagnosis.toLowerCase().includes('stunted')) return 'info';
    if (diagnosis.toLowerCase().includes('wasted')) return 'warning';
    return 'secondary';
}

function printAssessmentDetails(assessmentId) {
    const printUrl = window.assessmentsRoutes.assessmentPdf.replace(':assessmentId', assessmentId);
    window.open(printUrl, '_blank');
}



// Modern UI Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Patient search functionality in modal
    const searchInput = document.getElementById('patientSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const patientItems = document.querySelectorAll('.patient-item');
            
            patientItems.forEach(item => {
                const patientName = item.querySelector('h6').textContent.toLowerCase();
                if (patientName.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Clear filters functionality
    const clearFiltersBtn = document.getElementById('clearFilters');
    const clearFiltersEmptyBtn = document.getElementById('clearFiltersEmpty');
    
    function clearAllFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('diagnosisFilter').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('perPage').value = '15';
        
        // Trigger filter update
        if (window.assessmentManager) {
            window.assessmentManager.loadAssessments();
        } else {
            // Fallback: reload page without query parameters
            window.location.href = window.location.pathname;
        }
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
    
    if (clearFiltersEmptyBtn) {
        clearFiltersEmptyBtn.addEventListener('click', clearAllFilters);
    }

    // Sort buttons functionality
    const sortButtons = document.querySelectorAll('.sort-btn');
    sortButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            sortButtons.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get sort parameters
            const sortBy = this.dataset.sort;
            const currentOrder = this.dataset.order || 'asc';
            const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            this.dataset.order = newOrder;
            
            // Update sort icon direction
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-sort-up') || icon.classList.contains('fa-sort-down')) {
                icon.classList.toggle('fa-sort-up');
                icon.classList.toggle('fa-sort-down');
            }
            
            // Trigger sort if assessment manager exists
            if (window.assessmentManager) {
                window.assessmentManager.loadAssessments({
                    sort_by: sortBy,
                    sort_order: newOrder
                });
            }
        });
    });

    // Add hover effects to patient cards
    const patientCards = document.querySelectorAll('.patient-card');
    patientCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Animate filter inputs on focus
    const filterInputs = document.querySelectorAll('.modern-filter-input, .modern-filter-select');
    filterInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Update results info with animation
    function updateResultsInfo() {
        const resultsInfo = document.getElementById('resultsInfo');
        if (resultsInfo) {
            const cards = document.querySelectorAll('.patient-card');
            const count = cards.length;
            resultsInfo.innerHTML = `<i class="fas fa-chart-bar me-1"></i>${count} Patient${count !== 1 ? 's' : ''}`;
            
            // Add pulse animation
            resultsInfo.style.animation = 'pulse 0.5s ease-in-out';
            setTimeout(() => {
                resultsInfo.style.animation = '';
            }, 500);
        }
    }

    // Call update results info on load
    updateResultsInfo();

    // Enhanced pagination functionality
    const paginationLinks = document.querySelectorAll('.page-link');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            if (page) {
                goToPage(page);
            }
        });
    });
});

// Enhanced pagination functions
function changePageSize(size) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', size);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}

function goToPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    
    // Show loading
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.style.display = 'flex';
    }
    
    // Smooth scroll to top
    document.querySelector('.modern-assessments-card').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
    
    // Small delay for smooth scroll, then navigate
    setTimeout(() => {
        window.location.href = url.toString();
    }, 300);
}

function jumpToPage() {
    const input = document.getElementById('jumpToPage');
    const page = parseInt(input.value);
    const maxPage = parseInt(input.max);
    
    if (page && page >= 1 && page <= maxPage) {
        goToPage(page);
    } else {
        input.classList.add('is-invalid');
        setTimeout(() => {
            input.classList.remove('is-invalid');
        }, 2000);
    }
}


</script>

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

<style>
/* Clean White, Grey & Light Green Color Scheme */
:root {
    --primary-color: #10b981;
    --primary-light: #6ee7b7;
    --primary-dark: #059669;
    --success-color: #10b981;
    --success-light: #a7f3d0;
    --warning-color: #f59e0b;
    --warning-light: #fcd34d;
    --danger-color: #ef4444;
    --danger-light: #fca5a5;
    --info-color: #06b6d4;
    --info-light: #67e8f9;
    --card-bg: #ffffff;
    --card-bg-light: #f8fafc;
    --card-bg-hover: #f1f5f9;
    --light-green-bg: #ecfdf5;
    --light-green-border: #d1fae5;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --text-muted: #9ca3af;
    --border-color: #e5e7eb;
    --border-light: #f3f4f6;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

/* Modern Filter Container */
.modern-filters-container {
    background: linear-gradient(135deg, var(--light-green-bg) 0%, #ffffff 100%);
    border: 2px solid var(--light-green-border);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1), 0 2px 4px -1px rgba(16, 185, 129, 0.06);
    color: var(--text-primary);
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.filter-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary);
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
}

.modern-filter-input,
.modern-filter-select {
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    background: white;
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.modern-filter-input::placeholder {
    color: var(--text-muted);
}

.modern-filter-input:focus,
.modern-filter-select:focus {
    outline: none;
    border-color: var(--primary-color);
    background: white;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1);
}

.modern-filter-select option {
    background: white;
    color: var(--text-primary);
}

/* Modern Assessments Card */
.modern-assessments-card {
    background: white;
    border-radius: 20px;
    box-shadow: var(--shadow-xl);
    overflow: hidden;
}

.modern-card-header {
    background: linear-gradient(135deg, white 0%, var(--light-green-bg) 100%);
    padding: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid var(--light-green-border);
}

.header-left .modern-title {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
}

.header-left .modern-subtitle {
    color: var(--text-secondary);
    margin: 0;
    font-size: 1rem;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.info-badges {
    display: flex;
    gap: 0.75rem;
}

.modern-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    box-shadow: var(--shadow-sm);
}

.modern-badge.info {
    background: var(--info-light);
    color: #0891b2;
    border: 2px solid var(--info-color);
}

.modern-badge.primary {
    background: var(--success-light);
    color: var(--success-color);
    border: 2px solid var(--primary-color);
}

.modern-card-content {
    padding: 2rem;
}

/* Sort Controls */
.sort-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: var(--gray-50);
    border-radius: 12px;
    border: 2px solid var(--border-color);
}

.sort-label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.sort-buttons {
    display: flex;
    gap: 0.5rem;
}

.sort-btn {
    padding: 0.5rem 1rem;
    border: 2px solid var(--border-color);
    background: white;
    border-radius: 8px;
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.sort-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: var(--light-green-bg);
    transform: translateY(-1px);
}

.sort-btn.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
}

/* Modern Patient Cards Grid */
.patients-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
}

.patient-card {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
}

.patient-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15), 0 4px 6px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-color);
    background: var(--card-bg-hover);
}

.patient-card-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, var(--light-green-bg) 0%, white 100%);
    border-bottom: 1px solid var(--light-green-border);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.patient-avatar {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
}

.patient-basic-info {
    flex: 1;
}

.patient-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 0.25rem 0;
}

.patient-details {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
    display: flex;
    gap: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
}

.patient-status {
    flex-shrink: 0;
}

.modern-status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.modern-status-badge.completed {
    background: var(--success-light);
    color: var(--success-color);
    border: 2px solid var(--success-color);
}

.modern-status-badge.pending {
    background: var(--warning-light);
    color: #b45309;
    border: 2px solid var(--warning-color);
}

.modern-status-badge.no-assessment {
    background: var(--danger-light);
    color: var(--danger-color);
    border: 2px solid var(--danger-color);
}

.patient-card-body {
    padding: 1.5rem;
    background: white;
}

.assessment-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-value {
    font-size: 0.875rem;
    color: var(--text-primary);
    font-weight: 500;
}

.no-data {
    color: var(--text-muted);
    font-style: italic;
}

.modern-diagnosis-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.modern-diagnosis-badge.success {
    background: var(--success-light);
    color: var(--success-color);
    border: 1px solid var(--success-color);
}

.modern-diagnosis-badge.warning {
    background: var(--warning-light);
    color: #b45309;
    border: 1px solid var(--warning-color);
}

.modern-diagnosis-badge.danger {
    background: var(--danger-light);
    color: var(--danger-color);
    border: 1px solid var(--danger-color);
}

.modern-diagnosis-badge.info {
    background: var(--info-light);
    color: #0891b2;
    border: 1px solid var(--info-color);
}

.modern-diagnosis-badge.secondary {
    background: var(--gray-200);
    color: var(--gray-700);
    border: 1px solid var(--gray-400);
}

.patient-card-actions {
    padding: 1rem 1.5rem 1.5rem;
    background: var(--gray-50);
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    border-top: 1px solid var(--border-light);
}

.action-btn {
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    flex: 1;
    justify-content: center;
    min-width: 120px;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.view-btn {
    background: linear-gradient(135deg, var(--info-color), var(--info-light));
    color: white;
}

.assess-btn {
    background: linear-gradient(135deg, var(--success-color), var(--success-light));
    color: white;
}

.print-btn {
    background: linear-gradient(135deg, var(--gray-600), var(--gray-700));
    color: white;
}

/* Modern Empty State */
.modern-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
}

.empty-illustration {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 2rem;
}

.empty-icon-circle {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, var(--light-green-bg), var(--success-light));
    border: 3px solid var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
    animation: float 3s ease-in-out infinite;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.empty-dots {
    display: flex;
    gap: 0.5rem;
}

.dot {
    width: 8px;
    height: 8px;
    background: var(--gray-400);
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

.dot:nth-child(2) {
    animation-delay: 0.2s;
}

.dot:nth-child(3) {
    animation-delay: 0.4s;
}

.empty-content .empty-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.empty-content .empty-message {
    font-size: 1rem;
    color: var(--text-secondary);
    margin-bottom: 2rem;
    max-width: 500px;
    line-height: 1.6;
}

.empty-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes pulse {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
}

/* Modern Pagination Styles */
.pagination-container {
    background: linear-gradient(135deg, white 0%, var(--light-green-bg) 100%);
    border-radius: 0 0 20px 20px;
    border-top: 2px solid var(--light-green-border);
    padding: 1.5rem 2rem;
}

.pagination-info {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.pagination-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.page-size-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.page-size-selector select {
    padding: 0.5rem;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    background: white;
    color: var(--text-primary);
    font-size: 0.875rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.page-item {
    list-style: none;
}

.page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: 2px solid var(--border-color);
    color: var(--text-secondary);
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    background: white;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.page-link:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: var(--light-green-bg);
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
}

.page-item.active .page-link {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.4);
}

.page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.pagination-nav {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.pagination-info-extended {
    display: flex;
    align-items: center;
    gap: 2rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.total-results {
    font-weight: 600;
    color: var(--text-primary);
}

/* Quick Jump Styles */
.quick-jump {
    text-align: center;
    margin-top: 1rem;
}

.quick-jump .input-group {
    box-shadow: var(--shadow-sm);
    border-radius: 8px;
    overflow: hidden;
}

.quick-jump .input-group-text {
    background: var(--gray-100);
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
}

.quick-jump .form-control {
    border: none;
    text-align: center;
    font-weight: 500;
}

.quick-jump .form-control.is-invalid {
    animation: shake 0.5s ease-in-out;
}

.quick-jump .btn {
    border: none;
    font-weight: 500;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Loading states for pagination */
.pagination-loading {
    opacity: 0.6;
    pointer-events: none;
}

.pagination-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid var(--primary-color);
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Better mobile pagination */
@media (max-width: 768px) {
    .pagination-info-extended {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .pagination-controls {
        flex-direction: column;
        gap: 1rem;
    }
    
    .pagination {
        gap: 0.25rem;
    }
    
    .page-link {
        width: 35px;
        height: 35px;
        font-size: 0.875rem;
    }
    
    .quick-jump .input-group {
        max-width: 250px !important;
    }
}

/* Accessibility improvements */
.page-link:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
    outline: none;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Modal Styles */
.custom-modal-size .modal-content {
    max-width: 900px;
    margin: auto;
    border-radius: 16px;
    box-shadow: var(--shadow-xl);
}

.modal-header {
    background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
    border-bottom: 1px solid var(--gray-200);
    border-radius: 16px 16px 0 0;
}

.modal-title {
    font-weight: 700;
    color: var(--gray-800);
}

/* Patient Selection Modal */
.patient-item {
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 12px;
    margin-bottom: 0.5rem;
}

.patient-item:hover {
    background: var(--gray-50);
    transform: translateX(4px);
}

.patient-list {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 8px;
}

.patient-list::-webkit-scrollbar {
    width: 6px;
}

.patient-list::-webkit-scrollbar-track {
    background: var(--gray-100);
    border-radius: 3px;
}

.patient-list::-webkit-scrollbar-thumb {
    background: var(--gray-400);
    border-radius: 3px;
}

/* Assessment Details Modal */
.assessment-details .card {
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1rem;
}

.assessment-details .card-header {
    background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
    border-bottom: 1px solid var(--gray-200);
    padding: 1rem 1.25rem;
    border-radius: 12px 12px 0 0;
}

.assessment-details .card-header h6 {
    color: var(--gray-700);
    font-weight: 600;
    margin: 0;
}

.measurement-item {
    text-align: center;
    padding: 1.5rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
}

.measurement-item h5 {
    margin-bottom: 0.5rem;
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--primary-color);
}

/* Loading Styles */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    text-align: center;
    color: var(--primary-color);
}

.modern-spinner {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto 2rem;
}

.spinner-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 4px solid transparent;
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1.2s linear infinite;
}

.spinner-ring:nth-child(2) {
    width: 60px;
    height: 60px;
    top: 10px;
    left: 10px;
    border-top-color: var(--success-color);
    animation-duration: 1s;
}

.spinner-ring:nth-child(3) {
    width: 40px;
    height: 40px;
    top: 20px;
    left: 20px;
    border-top-color: var(--warning-color);
    animation-duration: 0.8s;
}

.loading-text {
    color: var(--gray-700);
    font-size: 1.1rem;
    font-weight: 500;
    margin: 0;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Page Load Animation */
.modern-assessments-card {
    animation: slideInUp 0.6s ease-out;
}

.modern-filters-container {
    animation: slideInDown 0.6s ease-out;
}

.patient-card {
    animation: fadeInUp 0.4s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
}

.patient-card:nth-child(1) { animation-delay: 0.1s; }
.patient-card:nth-child(2) { animation-delay: 0.2s; }
.patient-card:nth-child(3) { animation-delay: 0.3s; }
.patient-card:nth-child(4) { animation-delay: 0.4s; }
.patient-card:nth-child(5) { animation-delay: 0.5s; }
.patient-card:nth-child(6) { animation-delay: 0.6s; }

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Dark mode support (optional) */
@media (prefers-color-scheme: dark) {
    :root {
        --gray-50: #1f2937;
        --gray-100: #111827;
        --gray-200: #374151;
        --gray-300: #4b5563;
        --gray-800: #f9fafb;
        --gray-900: #f3f4f6;
    }
}

/* Print styles */
@media print {
    .modern-filters-container,
    .patient-card-actions,
    .modern-card-header .header-right {
        display: none !important;
    }
    
    .patient-card {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ccc;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .modern-card-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .header-right {
        justify-content: space-between;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .patients-grid {
        grid-template-columns: 1fr;
    }
    
    .info-row {
        grid-template-columns: 1fr;
    }
    
    .patient-card-actions {
        flex-direction: column;
    }
    
    .action-btn {
        min-width: auto;
    }
}

@media (max-width: 480px) {
    .modern-filters-container {
        padding: 1rem;
    }
    
    .modern-card-content {
        padding: 1rem;
    }
    
    .patient-card-header {
        padding: 1rem;
    }
    
    .patient-card-body {
        padding: 1rem;
    }
}

</style>

