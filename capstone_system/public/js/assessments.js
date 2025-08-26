// Assessment Filters - Real-time Search and Pagination
class AssessmentManager {
    constructor() {
        this.currentPage = 1;
        this.currentSort = 'assessment_date';
        this.currentSortOrder = 'desc';
        this.searchTimeout = null;
        this.isLoading = false;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        // Get the route URLs from global variables set by the Blade template
        this.assessmentsUrl = window.assessmentsRoutes.assessments;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateResultsInfo();
    }
    
    bindEvents() {
        // Real-time search with debouncing
        document.getElementById('searchInput').addEventListener('input', () => {
            this.handleSearch();
        });
        
        // Filter changes
        document.getElementById('statusFilter').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        document.getElementById('diagnosisFilter').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        document.getElementById('dateFrom').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        document.getElementById('dateTo').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        document.getElementById('perPage').addEventListener('change', () => {
            this.resetPageAndLoad();
        });
        
        // Clear filters
        document.getElementById('clearFilters').addEventListener('click', () => {
            this.clearAllFilters();
        });
        
        // Export
        document.getElementById('exportBtn').addEventListener('click', () => {
            this.exportAssessments();
        });
        
        // Pagination clicks (delegate)
        document.addEventListener('click', (e) => {
            if (e.target.matches('.page-link[data-page]')) {
                e.preventDefault();
                const page = parseInt(e.target.getAttribute('data-page'));
                this.currentPage = page;
                this.loadAssessments();
            }
        });
        
        // Sorting clicks (delegate)
        document.addEventListener('click', (e) => {
            const sortLink = e.target.closest('.sort-link[data-sort]');
            if (sortLink) {
                e.preventDefault();
                this.handleSort(sortLink.getAttribute('data-sort'));
            }
        });
    }
    
    handleSearch() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.resetPageAndLoad();
        }, 300); // 300ms debounce
    }
    
    resetPageAndLoad() {
        this.currentPage = 1;
        this.loadAssessments();
    }
    
    handleSort(sortField) {
        if (this.currentSort === sortField) {
            this.currentSortOrder = this.currentSortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            this.currentSort = sortField;
            this.currentSortOrder = 'asc';
        }
        this.updateSortIcons();
        this.loadAssessments();
    }
    
    async loadAssessments() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading(true);
        
        try {
            const params = this.buildQueryParams();
            
            const response = await fetch(this.assessmentsUrl + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('assessmentsContainer').innerHTML = data.html;
                this.updateResultsInfo(data.pagination);
                this.updateURL();
            } else {
                this.showError('Failed to load assessments');
            }
        } catch (error) {
            console.error('Error loading assessments:', error);
            this.showError('An error occurred while loading assessments');
        } finally {
            this.isLoading = false;
            this.showLoading(false);
        }
    }
    
    buildQueryParams() {
        const params = new URLSearchParams();
        params.append('search', document.getElementById('searchInput').value);
        params.append('status', document.getElementById('statusFilter').value);
        params.append('diagnosis', document.getElementById('diagnosisFilter').value);
        params.append('date_from', document.getElementById('dateFrom').value);
        params.append('date_to', document.getElementById('dateTo').value);
        params.append('per_page', document.getElementById('perPage').value);
        params.append('page', this.currentPage);
        params.append('sort_by', this.currentSort);
        params.append('sort_order', this.currentSortOrder);
        return params;
    }
    
    clearAllFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('diagnosisFilter').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('perPage').value = '15';
        
        this.currentPage = 1;
        this.currentSort = 'assessment_date';
        this.currentSortOrder = 'desc';
        
        this.updateSortIcons();
        this.loadAssessments();
    }
    
    showLoading(show) {
        const loadingIndicator = document.getElementById('loadingIndicator');
        const assessmentsContainer = document.getElementById('assessmentsContainer');
        
        if (show) {
            loadingIndicator.style.display = 'flex';
            assessmentsContainer.style.opacity = '0.5';
        } else {
            loadingIndicator.style.display = 'none';
            assessmentsContainer.style.opacity = '1';
        }
    }
    
    updateResultsInfo(pagination = null) {
        const resultsInfo = document.getElementById('resultsInfo');
        if (pagination && resultsInfo) {
            const from = pagination.from || 0;
            const to = pagination.to || 0;
            const total = pagination.total || 0;
            resultsInfo.textContent = `${from}-${to} of ${total} assessments`;
        }
    }
    
    updateSortIcons() {
        document.querySelectorAll('.sort-icon').forEach(icon => {
            icon.className = 'fas fa-sort sort-icon';
        });
        
        const currentSortIcon = document.querySelector(`[data-sort="${this.currentSort}"] .sort-icon`);
        if (currentSortIcon) {
            currentSortIcon.className = `fas fa-sort-${this.currentSortOrder === 'asc' ? 'up' : 'down'} sort-icon`;
        }
    }
    
    updateURL() {
        const params = new URLSearchParams();
        
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const diagnosis = document.getElementById('diagnosisFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const perPage = document.getElementById('perPage').value;
        
        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (diagnosis) params.set('diagnosis', diagnosis);
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);
        if (perPage !== '15') params.set('per_page', perPage);
        if (this.currentPage > 1) params.set('page', this.currentPage);
        if (this.currentSort !== 'assessment_date') params.set('sort_by', this.currentSort);
        if (this.currentSortOrder !== 'desc') params.set('sort_order', this.currentSortOrder);
        
        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newURL);
    }
    
    exportAssessments() {
        const params = new URLSearchParams();
        params.set('export', 'csv');
        params.set('search', document.getElementById('searchInput').value);
        params.set('status', document.getElementById('statusFilter').value);
        params.set('diagnosis', document.getElementById('diagnosisFilter').value);
        params.set('date_from', document.getElementById('dateFrom').value);
        params.set('date_to', document.getElementById('dateTo').value);
        
        window.location.href = this.assessmentsUrl + '?' + params.toString();
    }
    
    showError(message) {
        const existingError = document.getElementById('errorMessage');
        if (existingError) existingError.remove();
        
        const errorDiv = document.createElement('div');
        errorDiv.id = 'errorMessage';
        errorDiv.className = 'alert alert-danger';
        errorDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        document.querySelector('.assessment-filters-container').appendChild(errorDiv);
        
        setTimeout(() => {
            if (errorDiv.parentElement) errorDiv.remove();
        }, 5000);
    }
}

// Additional utility functions
function viewAssessment(assessmentId) {
    const modal = new bootstrap.Modal(document.getElementById('assessmentDetailsModal'));
    modal.show();
    loadAssessmentDetails(assessmentId);
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

function printAssessment(assessmentId) {
    const printUrl = window.assessmentsRoutes.assessmentPdf.replace(':assessmentId', assessmentId);
    window.open(printUrl, '_blank');
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
                printBtn.onclick = () => printAssessment(assessmentId);
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
                        <span class="diagnosis-badge ${getDiagnosisBadgeClass(assessment.diagnosis)}">${assessment.diagnosis}</span>
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

function getDiagnosisBadgeClass(treatment) {
    if (treatment.toLowerCase().includes('normal')) return 'success';
    if (treatment.toLowerCase().includes('severe')) return 'danger';
    if (treatment.toLowerCase().includes('moderate')) return 'warning';
    return 'info';
}

function getRecoveryStatusClass(status) {
    if (!status) return 'secondary';
    const statusLower = status.toLowerCase();
    if (statusLower.includes('recovered')) return 'success';
    if (statusLower.includes('improving')) return 'info';
    if (statusLower.includes('stable')) return 'warning';
    if (statusLower.includes('critical')) return 'danger';
    return 'secondary';
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    window.assessmentManager = new AssessmentManager();
});

// Make functions globally available
window.viewAssessment = viewAssessment;
window.assessSpecificPatient = assessSpecificPatient;
window.printAssessment = printAssessment;
window.showAssessmentForm = showAssessmentForm;
window.loadAssessmentForm = loadAssessmentForm;
window.setupAssessmentFormSubmission = setupAssessmentFormSubmission;
window.showSuccessMessage = showSuccessMessage;
window.showErrorMessage = showErrorMessage;

// Debug logging
console.log('Assessment page functions loaded:', {
    viewAssessment: typeof window.viewAssessment,
    assessSpecificPatient: typeof window.assessSpecificPatient,
    printAssessment: typeof window.printAssessment,
    showAssessmentForm: typeof window.showAssessmentForm
});