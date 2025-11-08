// Set up route URLs - will be initialized from blade template
window.assessmentsRoutes = window.assessmentsRoutes || {};

// Helper function to safely map arrays
function safeMap(data, callback, fallback = '') {
    if (Array.isArray(data)) {
        return data.map(callback).join('');
    }
    if (data) {
        return callback(data);
    }
    return fallback;
}

// Patient selection modal functions using SweetAlert2
function openPatientSelectionModal() {
    // Show loading state
    Swal.fire({
        title: '<i class="fas fa-user-plus me-2"></i> Select Patient to Assess',
        html: `
            <div class="patient-filters-wrapper">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <input type="text" 
                               id="swal-patientSearchInput" 
                               class="swal2-input modern-filter-input" 
                               placeholder="🔍 Search by name..." 
                               style="width: 100%; margin: 0;">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <select id="swal-sexFilter" class="swal2-input modern-filter-select" style="width: 100%; margin: 0;">
                            <option value="">All Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="swal-ageFilter" class="swal2-input modern-filter-select" style="width: 100%; margin: 0;">
                            <option value="">All Ages</option>
                            <option value="0-12">0-12 months</option>
                            <option value="13-24">13-24 months</option>
                            <option value="25-36">25-36 months</option>
                            <option value="37-48">37-48 months</option>
                            <option value="49-60">49-60 months</option>
                            <option value="60+">60+ months</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="swal-barangayFilter" class="swal2-input modern-filter-select" style="width: 100%; margin: 0;">
                            <option value="">All Barangays</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                <div id="swal-patientCount" class="text-muted">
                    <i class="fas fa-users me-2"></i>
                    Loading patients...
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-success" onclick="addNewPatient()" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                        <i class="fas fa-user-plus me-1"></i> Add New Patient
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="clearPatientFilters()" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </button>
                </div>
            </div>
            <div id="swal-patientListContainer" class="patient-list" style="max-height: 600px; overflow-y: auto; border: 2px solid var(--border-color); border-radius: 12px; background: white; padding: 1rem;">
                <div class="text-center p-4">
                    <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted fw-semibold">Loading patients...</p>
                </div>
            </div>
        `,
        width: '90%',
        showCancelButton: true,
        showConfirmButton: false,
        cancelButtonText: '<i class="fas fa-times me-2"></i>Close',
        cancelButtonColor: '#6c757d',
        customClass: {
            container: 'patient-selection-modal',
            popup: 'patient-selection-popup',
            htmlContainer: 'patient-selection-content',
            cancelButton: 'btn-lg'
        },
        didOpen: () => {
            loadPatientsForSelection();
            
            // Add filter functionality
            const searchInput = document.getElementById('swal-patientSearchInput');
            const sexFilter = document.getElementById('swal-sexFilter');
            const ageFilter = document.getElementById('swal-ageFilter');
            const barangayFilter = document.getElementById('swal-barangayFilter');
            
            const applyFilters = () => {
                const searchTerm = searchInput?.value.toLowerCase() || '';
                const selectedSex = sexFilter?.value || '';
                const selectedAge = ageFilter?.value || '';
                const selectedBarangay = barangayFilter?.value || '';
                
                const patientItems = document.querySelectorAll('.patient-item');
                let visibleCount = 0;
                
                patientItems.forEach(item => {
                    const patientName = item.querySelector('h6')?.textContent.toLowerCase() || '';
                    const patientInfo = item.querySelector('small')?.textContent || '';
                    
                    // Extract patient data
                    const patientSex = item.dataset.sex || '';
                    const patientAge = parseInt(item.dataset.age) || 0;
                    const patientBarangay = item.dataset.barangay || '';
                    
                    let showItem = true;
                    
                    // Name filter
                    if (searchTerm && !patientName.includes(searchTerm)) {
                        showItem = false;
                    }
                    
                    // Sex filter
                    if (selectedSex && patientSex !== selectedSex) {
                        showItem = false;
                    }
                    
                    // Age filter
                    if (selectedAge) {
                        const [min, max] = selectedAge.split('-').map(v => v === '+' ? Infinity : parseInt(v));
                        if (max) {
                            if (patientAge < min || patientAge > max) {
                                showItem = false;
                            }
                        } else {
                            if (patientAge < min) {
                                showItem = false;
                            }
                        }
                    }
                    
                    // Barangay filter
                    if (selectedBarangay && patientBarangay !== selectedBarangay) {
                        showItem = false;
                    }
                    
                    item.style.display = showItem ? 'block' : 'none';
                    if (showItem) visibleCount++;
                });
                
                // Update count
                const countElement = document.getElementById('swal-patientCount');
                if (countElement) {
                    countElement.textContent = `Showing ${visibleCount} of ${patientItems.length} patients`;
                }
            };
            
            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (sexFilter) sexFilter.addEventListener('change', applyFilters);
            if (ageFilter) ageFilter.addEventListener('change', applyFilters);
            if (barangayFilter) barangayFilter.addEventListener('change', applyFilters);
        }
    });
}

// Clear all filters in patient selection modal
window.clearPatientFilters = function() {
    const searchInput = document.getElementById('swal-patientSearchInput');
    const sexFilter = document.getElementById('swal-sexFilter');
    const ageFilter = document.getElementById('swal-ageFilter');
    const barangayFilter = document.getElementById('swal-barangayFilter');
    
    if (searchInput) searchInput.value = '';
    if (sexFilter) sexFilter.value = '';
    if (ageFilter) ageFilter.value = '';
    if (barangayFilter) barangayFilter.value = '';
    
    // Show all patients
    const patientItems = document.querySelectorAll('.patient-item');
    patientItems.forEach(item => {
        item.style.display = 'block';
    });
    
    // Update count
    const countElement = document.getElementById('swal-patientCount');
    if (countElement) {
        countElement.textContent = `Showing ${patientItems.length} of ${patientItems.length} patients`;
    }
}

// Add new patient function
window.addNewPatient = function() {
    // Close the patient selection modal first
    Swal.close();
    
    // Get barangays and parents data
    const barangays = window.barangaysData || [];
    const parents = window.parentsData || [];
    
    // Generate barangay options
    const barangayOptions = barangays.map(b => `<option value="${b.barangay_id}">${b.barangay_name}</option>`).join('');
    
    // Generate parent options
    const parentOptions = parents.map(p => `<option value="${p.user_id}">${p.first_name} ${p.last_name}</option>`).join('');
    
    // Show add patient form in a new modal
    Swal.fire({
        title: '<i class="fas fa-user-plus me-2"></i> Add New Patient',
        html: `
            <form id="addPatientForm" class="text-start" style="max-height: 75vh; overflow-y: auto; padding: 0 1.5rem;">
                <!-- Basic Information -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Parent (Optional)</label>
                            <select name="parent_id" class="form-select">
                                <option value="">No parent linked yet</option>
                                ${parentOptions}
                            </select>
                            <small class="text-muted">Parent can be linked later when they create an account</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barangay <span class="text-danger">*</span></label>
                            <select name="barangay_id" class="form-select" required>
                                <option value="">Select Barangay</option>
                                ${barangayOptions}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="contact_number" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Age (months) <span class="text-danger">*</span></label>
                            <input type="number" name="age_months" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sex <span class="text-danger">*</span></label>
                            <select name="sex" class="form-select" required>
                                <option value="">Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Admission <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_admission" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Household Information -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-home me-2"></i>Household Information</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Total Adults</label>
                            <input type="number" name="total_household_adults" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Children</label>
                            <input type="number" name="total_household_children" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Twins</label>
                            <input type="number" name="total_household_twins" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_4ps_beneficiary" class="form-check-input" id="is_4ps">
                                <label for="is_4ps" class="form-check-label">4Ps Beneficiary</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Health Information -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-heartbeat me-2"></i>Health Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                            <input type="number" name="weight_kg" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Height (cm) <span class="text-danger">*</span></label>
                            <input type="number" name="height_cm" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Weight for Age</label>
                            <input type="text" name="weight_for_age" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Height for Age</label>
                            <input type="text" name="height_for_age" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">BMI for Age</label>
                            <input type="text" name="bmi_for_age" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Breastfeeding</label>
                            <input type="text" name="breastfeeding" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Edema</label>
                            <input type="text" name="edema" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Other Medical Problems</label>
                            <textarea name="other_medical_problems" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        `,
        width: '1200px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save me-1"></i> Save Patient',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary',
            htmlContainer: 'swal-scrollable-content',
            popup: 'swal-wide-modal'
        },
        preConfirm: () => {
            const form = document.getElementById('addPatientForm');
            const formData = new FormData(form);
            
            // Validate required fields
            const requiredFields = ['first_name', 'last_name', 'barangay_id', 'contact_number', 'age_months', 'sex', 'date_of_admission', 'weight_kg', 'height_cm'];
            for (const field of requiredFields) {
                if (!formData.get(field)) {
                    Swal.showValidationMessage(`Please fill in all required fields`);
                    return false;
                }
            }
            
            // Show loading
            Swal.showLoading();
            
            // Submit the form
            return fetch('/nutritionist/patients', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Failed to create patient');
                    });
                }
                return response.json();
            })
            .then(data => {
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Error: ${error.message}`);
                return false;
            });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: 'Patient Added!',
                text: 'The patient has been successfully added.',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-clipboard-check me-1"></i> Assess Now',
                cancelButtonText: 'Back to List',
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((assessResult) => {
                if (assessResult.isConfirmed && result.value.patient_id) {
                    // Open assessment form for the new patient
                    showAssessmentForm(result.value.patient_id);
                } else {
                    // Reopen patient selection modal
                    openPatientSelectionModal();
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // User cancelled, go back to patient selection
            openPatientSelectionModal();
        }
    });
}

function loadPatientsForSelection() {
    const container = document.getElementById('swal-patientListContainer');
    if (!container) return;
    
    // Add CSRF token and proper headers
    fetch(window.assessmentsRoutes.patientsForAssessment + '?ajax=1', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
        .then(response => {
            // Check if response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response. You may need to log in again.');
            }
            
            return response.json();
        })
        .then(data => {
            if (data.patients && data.patients.length > 0) {
                // Populate barangay filter
                const barangayFilter = document.getElementById('swal-barangayFilter');
                if (barangayFilter) {
                    const uniqueBarangays = [...new Set(data.patients
                        .filter(p => p.barangay && p.barangay.barangay_name)
                        .map(p => p.barangay.barangay_name))];
                    
                    uniqueBarangays.sort().forEach(barangay => {
                        const option = document.createElement('option');
                        option.value = barangay;
                        option.textContent = barangay;
                        barangayFilter.appendChild(option);
                    });
                }
                
                container.innerHTML = data.patients.map(patient => `
                    <div class="patient-item border-bottom p-3" 
                         onclick="selectPatientForAssessment(${patient.patient_id})" 
                         data-sex="${patient.sex || ''}"
                         data-age="${patient.age_months || 0}"
                         data-barangay="${patient.barangay ? patient.barangay.barangay_name : ''}"
                         style="cursor: pointer; transition: all 0.2s;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">${patient.first_name} ${patient.last_name}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>${patient.age_months} months
                                    <span class="mx-1">•</span>
                                    <i class="fas fa-${patient.sex === 'Male' ? 'mars' : 'venus'} me-1"></i>${patient.sex}
                                    ${patient.barangay ? '<span class="mx-1">•</span><i class="fas fa-map-marker-alt me-1"></i>' + patient.barangay.barangay_name : ''}
                                </small>
                            </div>
                            <button class="btn btn-success btn-sm">
                                <i class="fas fa-clipboard-check me-1"></i>
                                Assess
                            </button>
                        </div>
                    </div>
                `).join('');
                
                // Update patient count
                const countElement = document.getElementById('swal-patientCount');
                if (countElement) {
                    countElement.textContent = `Showing ${data.patients.length} of ${data.patients.length} patients`;
                }
                
                // Add hover effect
                const patientItems = container.querySelectorAll('.patient-item');
                patientItems.forEach(item => {
                    item.addEventListener('mouseenter', () => {
                        item.style.backgroundColor = '#f0fdf4';
                        item.style.transform = 'translateX(4px)';
                    });
                    item.addEventListener('mouseleave', () => {
                        item.style.backgroundColor = '';
                        item.style.transform = 'translateX(0)';
                    });
                });
            } else {
                container.innerHTML = `
                    <div class="text-center p-4">
                        <i class="fas fa-user-slash text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted">No patients available for assessment</p>
                    </div>
                `;
                
                // Update count
                const countElement = document.getElementById('swal-patientCount');
                if (countElement) {
                    countElement.textContent = 'No patients found';
                }
            }
        })
        .catch(error => {
            console.error('Error loading patients:', error);
            container.innerHTML = `
                <div class="text-center p-4 text-danger">
                    <i class="fas fa-exclamation-triangle mb-2" style="font-size: 2rem;"></i>
                    <p>Error loading patients: ${error.message}</p>
                    <small class="d-block mt-2">Please check the console for more details.</small>
                </div>
            `;
        });
}

function selectPatientForAssessment(patientId) {
    // Close the patient selection SweetAlert
    Swal.close();
    
    // Open the assessment form modal
    showAssessmentForm(patientId);
}

function assessSpecificPatient(patientId) {
    showAssessmentForm(patientId);
}

function showAssessmentForm(patientId) {
    Swal.fire({
        title: '<i class="fas fa-stethoscope me-2"></i> New Assessment',
        html: `
            <div id="swal-assessmentFormContent" style="max-height: 700px; overflow-y: auto;">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading assessment form...</p>
                </div>
            </div>
        `,
        width: '90%',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-save me-1"></i> Submit Assessment',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        preConfirm: () => {
            // Get the form from the modal
            const form = document.querySelector('#swal-assessmentFormContent form');
            if (!form) {
                Swal.showValidationMessage('Form not loaded properly');
                return false;
            }
            return submitAssessmentFormData(form, patientId);
        },
        didOpen: () => {
            loadAssessmentForm(patientId);
        }
    });
}

function loadAssessmentForm(patientId) {
    const content = document.getElementById('swal-assessmentFormContent');
    if (!content) return;
    
    // Show loading state
    content.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading assessment form...</p>
        </div>
    `;
    
    const assessUrl = window.assessmentsRoutes.assessPatient.replace(':patientId', patientId);
    
    fetch(assessUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
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

function submitAssessmentFormData(form, patientId) {
    // Show loading on the confirm button
    Swal.showLoading();
    
    const formData = new FormData(form);
    
    return fetch(form.action, {
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
            // Refresh the assessments list
            if (window.assessmentManager) {
                window.assessmentManager.loadAssessments();
            } else {
                location.reload();
            }
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Assessment submitted successfully!',
                timer: 2000,
                showConfirmButton: false
            });
            
            return true;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to submit assessment'
            });
            return false;
        }
    })
    .catch(error => {
        console.error('Error submitting assessment:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while submitting the assessment'
        });
        return false;
    });
}

// Deprecated - keeping for backward compatibility
function setupAssessmentFormSubmission(patientId) {
    // This function is no longer needed with SweetAlert2
    // Form submission is handled by submitAssessmentFormData
}

function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.modern-filters-container').prepend(alertDiv);
    
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
    document.querySelector('.modern-filters-container').prepend(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentElement) alertDiv.remove();
    }, 5000);
}

// Assessment details modal functions using SweetAlert2
function viewAssessment(assessmentId) {
    Swal.fire({
        title: '<i class="fas fa-file-medical me-2" style="color: #4ade80;"></i> Assessment Details',
        html: `
            <div id="swal-assessmentDetailsContent" style="max-height: 70vh; overflow-y: auto; text-align: left; padding: 0 1rem;">
                <div class="text-center p-5">
                    <div class="modern-spinner mb-3">
                        <div class="spinner-ring"></div>
                        <div class="spinner-ring"></div>
                        <div class="spinner-ring"></div>
                    </div>
                    <p class="text-muted">Loading assessment details...</p>
                </div>
            </div>
        `,
        width: '1200px',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-print me-2"></i> Print Assessment',
        cancelButtonText: '<i class="fas fa-times me-2"></i> Close',
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary',
            htmlContainer: 'text-start',
            popup: 'swal-assessment-details-modal',
            title: 'swal-assessment-title'
        },
        buttonsStyling: false,
        preConfirm: () => {
            printAssessmentDetails(assessmentId);
            return false; // Prevent modal from closing
        },
        didOpen: () => {
            loadAssessmentDetails(assessmentId);
        }
    });
}

function loadAssessmentDetails(assessmentId) {
    const content = document.getElementById('swal-assessmentDetailsContent');
    if (!content) return;
    
    // Show loading state
    content.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading assessment details...</p>
        </div>
    `;
    
    const url = window.assessmentsRoutes.assessmentDetails.replace(':assessmentId', assessmentId);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAssessmentDetails(data.assessment);
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
    const content = document.getElementById('swal-assessmentDetailsContent');
    if (!content) return;
    
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
            <!-- Patient & Assessment Overview -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user-circle"></i>
                        Patient & Assessment Overview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="assessment-details-grid">
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-user"></i>
                                Patient Name
                            </div>
                            <div class="detail-info-value">${assessment.patient.name}</div>
                        </div>
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-birthday-cake"></i>
                                Age
                            </div>
                            <div class="detail-info-value">${assessment.patient.age_months} months</div>
                        </div>
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-venus-mars"></i>
                                Sex
                            </div>
                            <div class="detail-info-value">${assessment.patient.sex}</div>
                        </div>
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Barangay
                            </div>
                            <div class="detail-info-value">${assessment.patient.barangay}</div>
                        </div>
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-calendar-alt"></i>
                                Assessment Date
                            </div>
                            <div class="detail-info-value">${assessment.assessment_date}</div>
                        </div>
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-user-md"></i>
                                Assessed By
                            </div>
                            <div class="detail-info-value">${assessment.assessed_by || 'N/A'}</div>
                        </div>
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-clipboard-check"></i>
                                Status
                            </div>
                            <div class="detail-info-value">
                                <span class="badge bg-${assessment.status === 'completed' ? 'success' : 'warning'}">${assessment.status}</span>
                            </div>
                        </div>
                        ${assessment.recovery_status ? `
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-heartbeat"></i>
                                Recovery Status
                            </div>
                            <div class="detail-info-value">
                                <span class="badge ${getRecoveryStatusClass(assessment.recovery_status)}">${assessment.recovery_status}</span>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>

            <!-- Measurements -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-ruler-combined"></i>
                        Physical Measurements
                    </h6>
                </div>
                <div class="card-body">
                    <div class="measurements-grid">
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <i class="fas fa-weight"></i>
                            </div>
                            <div class="measurement-label">Weight</div>
                            <div class="measurement-value">${assessment.measurements?.weight_kg || assessment.weight || 'N/A'}</div>
                            <div class="measurement-unit">kilograms</div>
                        </div>
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <i class="fas fa-ruler-vertical"></i>
                            </div>
                            <div class="measurement-label">Height</div>
                            <div class="measurement-value">${assessment.measurements?.height_cm || assessment.height || 'N/A'}</div>
                            <div class="measurement-unit">centimeters</div>
                        </div>
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <i class="fas fa-tape"></i>
                            </div>
                            <div class="measurement-label">MUAC</div>
                            <div class="measurement-value">${assessment.measurements?.muac || assessment.muac || 'N/A'}</div>
                            <div class="measurement-unit">centimeters</div>
                        </div>
                        ${assessment.measurements?.bmi ? `
                        <div class="measurement-card">
                            <div class="measurement-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="measurement-label">BMI</div>
                            <div class="measurement-value">${assessment.measurements.bmi}</div>
                            <div class="measurement-unit">kg/m²</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>

            <!-- Diagnosis and Treatment -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-stethoscope"></i>
                        Diagnosis & Initial Treatment
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="detail-info-label mb-2">
                            <i class="fas fa-diagnoses"></i>
                            Primary Diagnosis
                        </div>
                        <span class="modern-diagnosis-badge ${getDiagnosisClass(assessment.diagnosis)}">${assessment.diagnosis}</span>
                    </div>
                    
                    ${treatmentPlan && treatmentPlan.patient_info && treatmentPlan.patient_info.confidence_level ? `
                    <div class="detail-info-box mt-3">
                        <div class="detail-info-label">
                            <i class="fas fa-chart-line"></i>
                            AI Confidence Level
                        </div>
                        <div class="detail-info-value">
                            <span class="badge" style="background: linear-gradient(135deg, var(--info-color), #2563eb); font-size: 1rem; padding: 0.5rem 1rem;">
                                ${(treatmentPlan.patient_info.confidence_level * 100).toFixed(1)}%
                            </span>
                        </div>
                    </div>
                    ` : ''}
                    
                    ${treatmentPlan ? `
                        <div class="modern-alert alert-info mt-3">
                            <div class="modern-alert-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <strong>Comprehensive Treatment Plan Available</strong><br>
                                <small>Detailed nutrition and medical interventions are outlined below.</small>
                            </div>
                        </div>
                    ` : `
                        <div class="modern-alert alert-info mt-3">
                            <div class="modern-alert-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <small>No detailed treatment plan available for this assessment.</small>
                            </div>
                        </div>
                    `}
                </div>
            </div>

            ${treatmentPlan ? `
            <!-- Immediate Actions -->
            ${treatmentPlan.immediate_actions ? `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i>
                        Immediate Actions Required
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="modern-list">
                        ${safeMap(treatmentPlan.immediate_actions, action => `
                            <li class="modern-list-item">${action}</li>
                        `)}
                    </ul>
                </div>
            </div>
            ` : ''}

            <!-- Nutrition Plan -->
            ${treatmentPlan.nutrition_plan ? `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-utensils"></i>
                        Nutrition Plan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="assessment-details-grid mb-3">
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-fire"></i>
                                Daily Caloric Needs
                            </div>
                            <div class="detail-info-value">${treatmentPlan.nutrition_plan.daily_caloric_needs || 'N/A'}</div>
                        </div>
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-clock"></i>
                                Feeding Frequency
                            </div>
                            <div class="detail-info-value">${treatmentPlan.nutrition_plan.feeding_frequency || 'N/A'}</div>
                        </div>
                    </div>
                    ${treatmentPlan.nutrition_plan.special_instructions ? `
                    <div class="mt-3">
                        <div class="detail-info-label mb-2">
                            <i class="fas fa-list-check"></i>
                            Special Instructions
                        </div>
                        <ul class="modern-list">
                            ${safeMap(treatmentPlan.nutrition_plan.special_instructions, instruction => `
                                <li class="modern-list-item">${instruction}</li>
                            `)}
                        </ul>
                    </div>
                    ` : ''}
                    ${treatmentPlan.nutrition_plan.breastfeeding ? `
                    <div class="modern-alert alert-info mt-3">
                        <div class="modern-alert-icon">
                            <i class="fas fa-baby"></i>
                        </div>
                        <div>
                            <strong>Breastfeeding Guidance:</strong><br>
                            ${treatmentPlan.nutrition_plan.breastfeeding}
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
            ` : ''}

            <!-- Medical Interventions -->
            ${treatmentPlan && treatmentPlan.medical_interventions ? `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-pills"></i>
                        Medical Interventions
                    </h6>
                </div>
                <div class="card-body">
                    ${treatmentPlan.medical_interventions.routine_medications ? `
                    <div class="mb-4">
                        <div class="detail-info-label mb-2">
                            <i class="fas fa-capsules"></i>
                            Routine Medications
                        </div>
                        <ul class="modern-list">
                            ${safeMap(treatmentPlan.medical_interventions.routine_medications, med => `
                                <li class="modern-list-item">${med}</li>
                            `)}
                        </ul>
                    </div>
                    ` : ''}
                    ${treatmentPlan.medical_interventions.therapeutic_medications ? `
                    <div class="mb-4">
                        <div class="detail-info-label mb-2">
                            <i class="fas fa-syringe"></i>
                            Therapeutic Medications
                        </div>
                        <ul class="modern-list">
                            ${safeMap(treatmentPlan.medical_interventions.therapeutic_medications, med => `
                                <li class="modern-list-item">${med}</li>
                            `)}
                        </ul>
                    </div>
                    ` : ''}
                    ${treatmentPlan.medical_interventions.medical_monitoring ? `
                    <div class="mb-3">
                        <div class="detail-info-label mb-2">
                            <i class="fas fa-heartbeat"></i>
                            Medical Monitoring
                        </div>
                        <ul class="modern-list">
                            ${safeMap(treatmentPlan.medical_interventions.medical_monitoring, monitor => `
                                <li class="modern-list-item">${monitor}</li>
                            `)}
                        </ul>
                    </div>
                    ` : ''}
                </div>
            </div>
            ` : ''}

            <!-- Monitoring Schedule -->
            ${treatmentPlan && treatmentPlan.monitoring_schedule ? `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-check"></i>
                        Monitoring Schedule
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        ${treatmentPlan.monitoring_schedule.phase_1_stabilization ? `
                        <div class="col-md-6 mb-3">
                            <div class="phase-card">
                                <div class="phase-header">
                                    <i class="fas fa-first-aid me-2"></i>
                                    Phase 1: Stabilization
                                </div>
                                <ul class="modern-list">
                                    ${Array.isArray(treatmentPlan.monitoring_schedule.phase_1_stabilization) 
                                        ? treatmentPlan.monitoring_schedule.phase_1_stabilization.map(item => `<li class="modern-list-item">${item}</li>`).join('') 
                                        : `<li class="modern-list-item">${treatmentPlan.monitoring_schedule.phase_1_stabilization}</li>`}
                                </ul>
                            </div>
                        </div>
                        ` : ''}
                        ${treatmentPlan.monitoring_schedule.phase_2_rehabilitation ? `
                        <div class="col-md-6 mb-3">
                            <div class="phase-card">
                                <div class="phase-header">
                                    <i class="fas fa-medkit me-2"></i>
                                    Phase 2: Rehabilitation
                                </div>
                                <ul class="modern-list">
                                    ${Array.isArray(treatmentPlan.monitoring_schedule.phase_2_rehabilitation) 
                                        ? treatmentPlan.monitoring_schedule.phase_2_rehabilitation.map(item => `<li class="modern-list-item">${item}</li>`).join('') 
                                        : `<li class="modern-list-item">${treatmentPlan.monitoring_schedule.phase_2_rehabilitation}</li>`}
                                </ul>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Follow-up Plan -->
            ${treatmentPlan && treatmentPlan.follow_up_plan ? `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-clock"></i>
                        Follow-up Plan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="assessment-details-grid">
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-calendar-plus"></i>
                                Initial Follow-up
                            </div>
                            <div class="detail-info-value">${treatmentPlan.follow_up_plan.initial_followup}</div>
                        </div>
                        <div class="detail-info-box">
                            <div class="detail-info-label">
                                <i class="fas fa-calendar-week"></i>
                                Ongoing Schedule
                            </div>
                            <div class="detail-info-value">${treatmentPlan.follow_up_plan.ongoing_schedule}</div>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Family Education -->
            ${treatmentPlan && treatmentPlan.family_education ? `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-graduation-cap"></i>
                        Family Education & Guidance
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="modern-list">
                        ${safeMap(treatmentPlan.family_education, education => `
                            <li class="modern-list-item">${education}</li>
                        `)}
                    </ul>
                </div>
            </div>
            ` : ''}

            <!-- Success Criteria -->
            ${treatmentPlan && treatmentPlan.success_criteria ? `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy"></i>
                        Success Criteria & Goals
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        ${treatmentPlan.success_criteria.short_term ? `
                        <div class="col-md-4 mb-3">
                            <div class="phase-card" style="border-color: var(--info-border);">
                                <div class="phase-header" style="color: var(--info-color);">
                                    <i class="fas fa-flag me-2"></i>
                                    Short Term Goals
                                </div>
                                <ul class="modern-list">
                                    ${safeMap(treatmentPlan.success_criteria.short_term, criteria => `
                                        <li class="modern-list-item">${criteria}</li>
                                    `)}
                                </ul>
                            </div>
                        </div>
                        ` : ''}
                        ${treatmentPlan.success_criteria.medium_term ? `
                        <div class="col-md-4 mb-3">
                            <div class="phase-card" style="border-color: var(--warning-border);">
                                <div class="phase-header" style="color: var(--warning-color);">
                                    <i class="fas fa-flag-checkered me-2"></i>
                                    Medium Term Goals
                                </div>
                                <ul class="modern-list">
                                    ${safeMap(treatmentPlan.success_criteria.medium_term, criteria => `
                                        <li class="modern-list-item">${criteria}</li>
                                    `)}
                                </ul>
                            </div>
                        </div>
                        ` : ''}
                        ${treatmentPlan.success_criteria.long_term ? `
                        <div class="col-md-4 mb-3">
                            <div class="phase-card" style="border-color: var(--success-border);">
                                <div class="phase-header" style="color: var(--success-color);">
                                    <i class="fas fa-trophy me-2"></i>
                                    Long Term Goals
                                </div>
                                <ul class="modern-list">
                                    ${safeMap(treatmentPlan.success_criteria.long_term, criteria => `
                                        <li class="modern-list-item">${criteria}</li>
                                    `)}
                                </ul>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Discharge Criteria -->
            ${treatmentPlan && treatmentPlan.discharge_criteria ? `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-check-circle"></i>
                        Discharge Criteria
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="modern-list">
                        ${safeMap(treatmentPlan.discharge_criteria, criteria => `
                            <li class="modern-list-item">${criteria}</li>
                        `)}
                    </ul>
                </div>
            </div>
            ` : ''}

            <!-- Emergency Signs -->
            ${treatmentPlan && treatmentPlan.emergency_signs ? `
            <div class="card emergency-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Emergency Warning Signs
                    </h6>
                </div>
                <div class="card-body">
                    <div class="modern-alert alert-danger mb-3">
                        <div class="modern-alert-icon">
                            <i class="fas fa-ambulance"></i>
                        </div>
                        <div>
                            <strong>URGENT: Seek immediate medical attention if any of these signs appear!</strong><br>
                            <small>These symptoms require emergency intervention and hospitalization.</small>
                        </div>
                    </div>
                    <ul class="modern-list" style="list-style: none; padding: 0;">
                        ${safeMap(treatmentPlan.emergency_signs, sign => `
                            <li class="emergency-list-item">${sign}</li>
                        `)}
                    </ul>
                </div>
            </div>
            ` : ''}
            ` : ''}

            <!-- Notes -->
            ${assessment.notes ? `
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-sticky-note"></i>
                        Additional Notes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="detail-info-box" style="background: var(--gray-50); border-color: var(--gray-300);">
                        <div class="detail-info-value" style="font-size: 0.95rem; font-weight: 500; white-space: pre-wrap;">
                            ${assessment.notes}
                        </div>
                    </div>
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
    const modalSearchInput = document.getElementById('patientSearchInput');
    if (modalSearchInput) {
        modalSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const patientItems = document.querySelectorAll('.patient-item');
            
            patientItems.forEach(item => {
                const nameElement = item.querySelector('h6');
                if (nameElement) {
                    const patientName = nameElement.textContent.toLowerCase();
                    item.style.display = patientName.includes(searchTerm) ? 'block' : 'none';
                }
            });
        });
    }

    // Filter functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const diagnosisFilter = document.getElementById('diagnosisFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const perPageFilter = document.getElementById('perPage');
    
    // Debounce function to limit API calls
    function debounce(func, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }
    
    // Function to apply filters and reload data
    function applyFiltersAndFetch() {
        const url = new URL(window.location.href);
        const searchParams = new URLSearchParams();
        
        // Add filter parameters
        if (searchInput && searchInput.value) {
            searchParams.set('search', searchInput.value);
        }
        if (statusFilter && statusFilter.value) {
            searchParams.set('status', statusFilter.value);
        }
        if (diagnosisFilter && diagnosisFilter.value) {
            searchParams.set('diagnosis', diagnosisFilter.value);
        }
        if (dateFrom && dateFrom.value) {
            searchParams.set('date_from', dateFrom.value);
        }
        if (dateTo && dateTo.value) {
            searchParams.set('date_to', dateTo.value);
        }
        if (perPageFilter && perPageFilter.value) {
            searchParams.set('per_page', perPageFilter.value);
        }
        
        // Reset to first page when filters change
        searchParams.set('page', '1');
        
        // Show loading indicator
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'flex';
        }
        
        // Navigate to filtered URL
        window.location.href = url.pathname + '?' + searchParams.toString();
    }
    
    // Debounced version for search input (wait 500ms after user stops typing)
    const debouncedFilter = debounce(applyFiltersAndFetch, 500);
    
    // Function to update filter visual state
    function updateFilterHighlight(input) {
        if (!input) return;
        
        const filterGroup = input.closest('.filter-group');
        if (!filterGroup) return;
        
        const hasValue = input.value && input.value.trim() !== '';
        
        if (hasValue) {
            input.classList.add('filter-active');
            filterGroup.style.transform = 'scale(1.02)';
        } else {
            input.classList.remove('filter-active');
            filterGroup.style.transform = 'scale(1)';
        }
    }
    
    // Initialize filter highlights on page load
    [searchInput, statusFilter, diagnosisFilter, dateFrom, dateTo, perPageFilter].forEach(input => {
        if (input) {
            updateFilterHighlight(input);
        }
    });
    
    // Attach event listeners to filter inputs
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            updateFilterHighlight(this);
            debouncedFilter();
        });
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            updateFilterHighlight(this);
            applyFiltersAndFetch();
        });
    }
    
    if (diagnosisFilter) {
        diagnosisFilter.addEventListener('change', function() {
            updateFilterHighlight(this);
            applyFiltersAndFetch();
        });
    }
    
    if (dateFrom) {
        dateFrom.addEventListener('change', function() {
            updateFilterHighlight(this);
            applyFiltersAndFetch();
        });
    }
    
    if (dateTo) {
        dateTo.addEventListener('change', function() {
            updateFilterHighlight(this);
            applyFiltersAndFetch();
        });
    }
    
    if (perPageFilter) {
        perPageFilter.addEventListener('change', function() {
            updateFilterHighlight(this);
            applyFiltersAndFetch();
        });
    }
    
    // Clear filters functionality
    const clearFiltersBtn = document.getElementById('clearFilters');
    const clearFiltersEmptyBtn = document.getElementById('clearFiltersEmpty');
    
    function clearAllFilters() {
        const filters = {
            searchInput: document.getElementById('searchInput'),
            statusFilter: document.getElementById('statusFilter'),
            diagnosisFilter: document.getElementById('diagnosisFilter'),
            dateFrom: document.getElementById('dateFrom'),
            dateTo: document.getElementById('dateTo'),
            perPage: document.getElementById('perPage')
        };
        
        // Clear only existing filters
        if (filters.searchInput) filters.searchInput.value = '';
        if (filters.statusFilter) filters.statusFilter.value = '';
        if (filters.diagnosisFilter) filters.diagnosisFilter.value = '';
        if (filters.dateFrom) filters.dateFrom.value = '';
        if (filters.dateTo) filters.dateTo.value = '';
        if (filters.perPage) filters.perPage.value = '15';
        
        // Reload page without query parameters
        window.location.href = window.location.pathname;
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
    
    if (clearFiltersEmptyBtn) {
        clearFiltersEmptyBtn.addEventListener('click', clearAllFilters);
    }

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

