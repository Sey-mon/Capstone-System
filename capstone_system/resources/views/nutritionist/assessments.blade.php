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
                <button type="button" class="btn-action btn-primary" title="Assess Patient" onclick="openPatientSelectionModal()">
                    <i class="fas fa-stethoscope"></i>
                </button>
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



// Patient search functionality
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>


<style>
/* Patient Selection Modal Styles */
.patient-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.patient-item:hover {
    background-color: #f8f9fa;
}

.patient-list {
    max-height: 400px;
    overflow-y: auto;
}

/* Assessment Details Modal Styles */
.assessment-details .card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

.assessment-details .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
}

.assessment-details .card-header h6 {
    color: #495057;
    font-weight: 600;
}

.measurement-item {
    text-align: center;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.measurement-item h5 {
    margin-bottom: 0.25rem;
    font-size: 1.5rem;
    font-weight: 700;
}

.diagnosis-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 1rem;
}

.diagnosis-badge.success {
    background-color: #d4edda;
    color: #155724;
}

.diagnosis-badge.warning {
    background-color: #fff3cd;
    color: #856404;
}

.diagnosis-badge.danger {
    background-color: #f8d7da;
    color: #721c24;
}

.diagnosis-badge.info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.diagnosis-badge.secondary {
    background-color: #e2e3e5;
    color: #383d41;
}

.treatment-plan {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 1rem;
    font-size: 0.9rem;
    white-space: pre-wrap;
    max-height: 200px;
    overflow-y: auto;
}

.custom-modal-size .modal-content {
  max-width: 800px; /* Adjust this value as needed */
  margin: auto; /* Center the content within the dialog */
}
.modal-dialog.custom-modal-wide {
  max-width: 800px; /* Adjust this value as needed */
  margin: auto; /* Center the content within the dialog */
}

</style>

