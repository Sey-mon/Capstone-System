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
        title: '<i class="fas fa-user-plus me-2"></i> Select Patient to Screen',
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
            <div id="swal-patientListContainer" class="patient-list" style="max-height: 400px; overflow-y: auto; border: 2px solid var(--border-color); border-radius: 12px; background: white; padding: 1rem;">
                <div class="text-center p-4">
                    <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted fw-semibold">Loading patients...</p>
                </div>
            </div>
        `,
        width: '90%',
        maxWidth: '950px',
        showCancelButton: true,
        showConfirmButton: false,
        cancelButtonText: '<i class="fas fa-times me-2"></i>Close',
        cancelButtonColor: '#6c757d',
        scrollbarPadding: false,
        backdrop: true,
        allowOutsideClick: true,
        heightAuto: false,
        position: 'center',
        customClass: {
            container: 'patient-selection-modal',
            popup: 'patient-selection-popup',
            htmlContainer: 'patient-selection-content',
            cancelButton: 'btn-lg'
        },
        didOpen: () => {
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
            document.body.style.paddingRight = '0px';
            
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
        },
        willClose: () => {
            // Restore body scroll when modal closes
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
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
            const requiredFields = ['first_name', 'last_name', 'barangay_id', 'contact_number', 'age_months', 'sex', 'date_of_admission'];
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
                confirmButtonText: '<i class="fas fa-clipboard-check me-1"></i> Screen Now',
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
    if (!container) {
        console.error('Patient list container not found');
        return;
    }
    
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
                         style="cursor: pointer; transition: all 0.2s;"
                         role="button"
                         tabindex="0"
                         aria-label="Select ${patient.first_name} ${patient.last_name} for screening">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">${patient.first_name} ${patient.last_name}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1" aria-hidden="true"></i>${patient.age_months} months
                                    <span class="mx-1">•</span>
                                    <i class="fas fa-${patient.sex === 'Male' ? 'mars' : 'venus'} me-1" aria-hidden="true"></i>${patient.sex}
                                    ${patient.barangay ? '<span class="mx-1">•</span><i class="fas fa-map-marker-alt me-1" aria-hidden="true"></i>' + patient.barangay.barangay_name : ''}
                                </small>
                            </div>
                            <button class="btn btn-success btn-sm" aria-label="Screen ${patient.first_name} ${patient.last_name}">
                                <i class="fas fa-clipboard-check me-1" aria-hidden="true"></i>
                                Screen
                            </button>
                        </div>
                    </div>
                `).join('');
                
                // Update patient count
                const countElement = document.getElementById('swal-patientCount');
                if (countElement) {
                    countElement.innerHTML = `<i class="fas fa-users me-2" aria-hidden="true"></i>Showing ${data.patients.length} of ${data.patients.length} patients`;
                }
                
                // Add hover effect and keyboard navigation
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
                    
                    // Add keyboard support
                    item.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            item.click();
                        }
                    });
                });
            } else {
                container.innerHTML = `
                    <div class="text-center p-4" role="status" aria-live="polite">
                        <i class="fas fa-user-slash text-muted mb-2" style="font-size: 2rem;" aria-hidden="true"></i>
                        <p class="text-muted">No patients available for screening</p>
                        <small class="text-muted d-block mt-2">Add a new patient to get started</small>
                    </div>
                `;
                
                // Update count
                const countElement = document.getElementById('swal-patientCount');
                if (countElement) {
                    countElement.innerHTML = '<i class="fas fa-info-circle me-2" aria-hidden="true"></i>No patients found';
                }
            }
        })
        .catch(error => {
            console.error('Error loading patients:', error);
            
            // User-friendly error messages
            let errorMessage = 'Unable to load patients';
            let errorDetails = error.message;
            
            if (error.message.includes('HTTP error! status: 401') || error.message.includes('log in again')) {
                errorMessage = 'Session Expired';
                errorDetails = 'Please refresh the page and log in again.';
            } else if (error.message.includes('HTTP error! status: 403')) {
                errorMessage = 'Access Denied';
                errorDetails = 'You do not have permission to view this data.';
            } else if (error.message.includes('HTTP error! status: 500')) {
                errorMessage = 'Server Error';
                errorDetails = 'The server encountered an error. Please try again later.';
            } else if (error.message.includes('Failed to fetch')) {
                errorMessage = 'Network Error';
                errorDetails = 'Please check your internet connection and try again.';
            }
            
            container.innerHTML = `
                <div class="text-center p-4" role="alert" aria-live="assertive">
                    <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 2.5rem;" aria-hidden="true"></i>
                    <h5 class="text-danger mb-2">${errorMessage}</h5>
                    <p class="text-muted">${errorDetails}</p>
                    <button class="btn btn-primary mt-3" onclick="loadPatientsForSelection()">
                        <i class="fas fa-redo me-2"></i>Try Again
                    </button>
                    <small class="d-block mt-3 text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Technical details: ${error.message}
                    </small>
                </div>
            `;
            
            // Update count element
            const countElement = document.getElementById('swal-patientCount');
            if (countElement) {
                countElement.innerHTML = '<i class="fas fa-exclamation-triangle me-2 text-warning" aria-hidden="true"></i>Error loading data';
            }
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
        title: '<i class="fas fa-stethoscope me-2"></i> New Screening',
        html: `
            <div id="swal-assessmentFormContent" style="max-height: 65vh; overflow-y: auto; padding: 0 0.5rem;">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading screening form...</p>
                </div>
            </div>
        `,
        width: '95%',
        maxWidth: '1200px',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-save me-1"></i> Submit Screening',
        cancelButtonText: 'Cancel',
        scrollbarPadding: false,
        heightAuto: false,
        customClass: {
            container: 'assessment-modal-container',
            popup: 'assessment-modal-popup',
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
    
    // Collect all clinical symptoms data BEFORE creating FormData
    const clinicalData = {
        appetite: document.getElementById('appetite')?.value || '',
        edema: document.getElementById('edema')?.value || '',
        muac: document.getElementById('muac')?.value || '',
        diarrhea: document.getElementById('diarrhea_days')?.value || '0',
        vomiting: document.getElementById('vomiting_frequency')?.value || '0',
        fever: document.getElementById('fever_days')?.value || '0',
        visible_signs: [],
        breastfeeding_status: document.getElementById('breastfeeding_status')?.value || ''
    };
    
    // Collect visible signs checkboxes
    const visibleSigns = ['skin_changes', 'hair_changes', 'muscle_wasting', 'lethargy', 'pallor'];
    visibleSigns.forEach(sign => {
        if (document.getElementById(sign)?.checked) {
            clinicalData.visible_signs.push(sign.replace('_', ' '));
        }
    });
    
    // Get the additional notes from the textarea
    const notesField = document.getElementById('notes');
    const additionalNotes = notesField ? notesField.value.trim() : '';
    
    // Create structured JSON note
    const structuredNote = {
        clinical_symptoms: clinicalData,
        additional_notes: additionalNotes,
        recorded_at: new Date().toISOString()
    };
    
    // Create FormData and override the notes field with JSON
    const formData = new FormData(form);
    formData.set('notes', JSON.stringify(structuredNote));
    
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
                text: 'Screening submitted successfully!',
                timer: 2000,
                showConfirmButton: false
            });
            
            return true;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to submit screening'
            });
            return false;
        }
    })
    .catch(error => {
        console.error('Error submitting assessment:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while submitting the screening'
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
    // Fetch the patient's full assessment history first
    const url = window.assessmentsRoutes.assessmentDetails.replace(':assessmentId', assessmentId);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.assessment) {
                // Show assessment history modal using patient_id from assessment
                showAssessmentHistoryModal(data.assessment.patient_id, assessmentId);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to load screening details'
                });
            }
        })
        .catch(error => {
            console.error('Error loading assessment details:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error loading screening details. Please try again.'
            });
        });
}

function showAssessmentHistoryModal(patientId, selectedAssessmentId = null) {
    // Fetch all assessments for this patient
    fetch(`/nutritionist/patients/${patientId}/assessments`)
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.assessments || data.assessments.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Screenings',
                    text: 'No screening history available for this patient.',
                });
                return;
            }

            const assessments = data.assessments;
            const patientName = data.patient.name;
            let currentIndex = selectedAssessmentId ? 
                assessments.findIndex(a => a.assessment_id == selectedAssessmentId) : 0;
            if (currentIndex === -1) currentIndex = 0;

            renderAssessmentHistory(assessments, patientName, currentIndex);
        })
        .catch(error => {
            console.error('Error loading assessment history:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load screening history'
            });
        });
}

function renderAssessmentHistory(assessments, patientName, currentIndex) {
    const assessment = assessments[currentIndex];
    const isLatest = currentIndex === 0;
    
    // Parse treatment plan
    let treatmentPlan = null;
    if (assessment.treatment_plan) {
        try {
            treatmentPlan = typeof assessment.treatment_plan === 'string' ? 
                JSON.parse(assessment.treatment_plan) : assessment.treatment_plan;
        } catch (e) {
            console.error('Failed to parse treatment_plan:', e);
        }
    }
    if (!treatmentPlan && assessment.treatment) {
        try {
            treatmentPlan = typeof assessment.treatment === 'string' ? 
                JSON.parse(assessment.treatment) : assessment.treatment;
        } catch (e) {
            console.error('Failed to parse treatment:', e);
        }
    }

    const diagnosis = treatmentPlan?.patient_info?.diagnosis || assessment.diagnosis || 'Status Unknown';
    
    // Determine diagnosis styling
    let diagnosisClass = 'unknown';
    let diagnosisIcon = 'fa-question-circle';
    if (diagnosis.includes('Severe')) {
        diagnosisClass = 'critical';
        diagnosisIcon = 'fa-exclamation-triangle';
    } else if (diagnosis.includes('Moderate')) {
        diagnosisClass = 'warning';
        diagnosisIcon = 'fa-exclamation-circle';
    } else if (diagnosis.includes('Normal')) {
        diagnosisClass = 'normal';
        diagnosisIcon = 'fa-check-circle';
    }

    const sidebar = assessments.map((item, idx) => {
        const itemDiagnosis = item.treatment ? 
            (JSON.parse(typeof item.treatment === 'string' ? item.treatment : JSON.stringify(item.treatment))?.patient_info?.diagnosis || item.diagnosis) : 
            (item.diagnosis || 'Status Unknown');
        
        return `
            <div class="swal-assessment-item ${idx === currentIndex ? 'active' : ''}" data-index="${idx}">
                <div class="swal-assessment-item-date">
                    <i class="fas fa-calendar-alt"></i>
                    ${formatDate(item.assessment_date)}
                </div>
                <div class="swal-assessment-item-status">
                    ${itemDiagnosis}
                </div>
                ${idx === 0 ? '<span class="swal-latest-badge">LATEST</span>' : ''}
            </div>
        `;
    }).join('');

    const assessmentCount = assessments.length;
    const showSearch = assessmentCount > 10;

    // Get nutritional indicators directly from assessment data (database columns)
    let indicators = {
        weight_for_age: assessment.weight_for_age,
        height_for_age: assessment.height_for_age,
        bmi_for_age: assessment.bmi_for_age
    };

    // Parse notes field (which contains JSON clinical symptoms data)
    let clinicalData = null;
    let additionalNotes = '';
    
    if (assessment.notes) {
        try {
            // Try to parse as JSON first
            const parsedNotes = typeof assessment.notes === 'string' ? 
                JSON.parse(assessment.notes) : assessment.notes;
            
            // Extract clinical symptoms and additional notes from the new structure
            if (parsedNotes.clinical_symptoms) {
                clinicalData = parsedNotes.clinical_symptoms;
                additionalNotes = parsedNotes.additional_notes || '';
            } else {
                // Fallback for old structure
                clinicalData = parsedNotes;
                additionalNotes = parsedNotes.additional_notes || parsedNotes.additionalNotes || '';
            }
        } catch (e) {
            // If not JSON, treat it as plain text notes
            additionalNotes = assessment.notes;
        }
    }

    const htmlContent = `
        <div class="swal-assessment-container">
            <div class="swal-sidebar" id="assessment-sidebar">
                <div class="swal-sidebar-header">
                    <h4>
                        <i class="fas fa-history"></i>
                        Screening History
                    </h4>
                    <p class="swal-count-badge">${assessmentCount} Total Screening${assessmentCount !== 1 ? 's' : ''}</p>
                </div>
                <div class="swal-assessment-list" id="assessment-list">
                    ${sidebar}
                </div>
            </div>
            <div class="swal-content-area">
                <div class="swal-modal-header">
                    <h3 class="swal-header-title">
                        <i class="fas fa-chart-line"></i>
                        ${patientName}
                    </h3>
                    <p class="swal-header-subtitle">Complete screening timeline and progress tracking</p>
                    <button class="btn btn-success btn-sm swal-pdf-button" onclick="downloadAssessmentPDF(${assessment.assessment_id})">
                        <i class="fas fa-file-pdf me-1"></i>
                        Download PDF
                    </button>
                </div>
                <div class="swal-assessment-detail">
                    <div class="swal-detail-header">
                        <div class="swal-detail-date">
                            <i class="fas fa-calendar-check"></i>
                            ${formatDate(assessment.assessment_date)}
                            ${isLatest ? '<span class="swal-latest-badge">LATEST</span>' : ''}
                        </div>
                        <div class="swal-diagnosis-badge ${diagnosisClass}">
                            <i class="fas ${diagnosisIcon}"></i>
                            <span>${diagnosis}</span>
                        </div>
                    </div>
                    <div class="swal-metrics-grid">
                        <div class="swal-metric-card">
                            <div class="swal-metric-icon weight">
                                <i class="fas fa-weight"></i>
                            </div>
                            <div class="swal-metric-info">
                                <span class="swal-metric-label">Weight</span>
                                <span class="swal-metric-value">${assessment.weight_kg || assessment.weight || 'N/A'} kg</span>
                            </div>
                        </div>
                        <div class="swal-metric-card">
                            <div class="swal-metric-icon height">
                                <i class="fas fa-ruler-vertical"></i>
                            </div>
                            <div class="swal-metric-info">
                                <span class="swal-metric-label">Height</span>
                                <span class="swal-metric-value">${assessment.height_cm || assessment.height || 'N/A'} cm</span>
                            </div>
                        </div>
                        <div class="swal-metric-card">
                            <div class="swal-metric-icon nutritionist">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="swal-metric-info">
                                <span class="swal-metric-label">Assessed By</span>
                                <span class="swal-metric-value">${assessment.assessed_by || 'Nutritionist'}</span>
                            </div>
                        </div>
                    </div>
                    ${(indicators.weight_for_age || indicators.height_for_age || indicators.bmi_for_age) ? `
                    <div class="swal-indicators-section">
                        <h4 class="swal-section-title">
                            <i class="fas fa-chart-bar"></i>
                            Nutritional Indicators
                        </h4>
                        <div class="swal-indicators-grid">${indicators.weight_for_age ? `
                            <div class="swal-indicator-card">
                                <div class="swal-indicator-label">WEIGHT FOR AGE:</div>
                                <div class="swal-indicator-value ${getIndicatorClass(indicators.weight_for_age)}">${indicators.weight_for_age}</div>
                            </div>
                            ` : ''}
                            ${indicators.height_for_age ? `
                            <div class="swal-indicator-card">
                                <div class="swal-indicator-label">HEIGHT FOR AGE:</div>
                                <div class="swal-indicator-value ${getIndicatorClass(indicators.height_for_age)}">${indicators.height_for_age}</div>
                            </div>
                            ` : ''}
                            ${indicators.bmi_for_age ? `
                            <div class="swal-indicator-card">
                                <div class="swal-indicator-label">BMI FOR AGE:</div>
                                <div class="swal-indicator-value ${getIndicatorClass(indicators.bmi_for_age)}">${indicators.bmi_for_age}</div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    ` : ''}
                    
                    ${clinicalData ? `
                    <div class="swal-clinical-section">
                        <h4 class="swal-section-title">
                            <i class="fas fa-stethoscope"></i>
                            Clinical Symptoms & Physical Signs
                        </h4>
                        <div class="swal-clinical-grid">
                            ${clinicalData.appetite ? `
                            <div class="swal-clinical-item">
                                <span class="swal-clinical-label">Appetite:</span>
                                <span class="swal-clinical-value">${clinicalData.appetite}</span>
                            </div>
                            ` : ''}
                            ${clinicalData.edema ? `
                            <div class="swal-clinical-item">
                                <span class="swal-clinical-label">Edema:</span>
                                <span class="swal-clinical-value">${clinicalData.edema}</span>
                            </div>
                            ` : ''}
                            ${clinicalData.muac ? `
                            <div class="swal-clinical-item">
                                <span class="swal-clinical-label">MUAC:</span>
                                <span class="swal-clinical-value">${clinicalData.muac} cm</span>
                            </div>
                            ` : ''}
                            ${clinicalData.diarrhea && clinicalData.diarrhea !== '0' ? `
                            <div class="swal-clinical-item">
                                <span class="swal-clinical-label">Diarrhea:</span>
                                <span class="swal-clinical-value">${clinicalData.diarrhea} day(s)</span>
                            </div>
                            ` : ''}
                            ${clinicalData.vomiting && clinicalData.vomiting !== '0' ? `
                            <div class="swal-clinical-item">
                                <span class="swal-clinical-label">Vomiting:</span>
                                <span class="swal-clinical-value">${clinicalData.vomiting} times/day</span>
                            </div>
                            ` : ''}
                            ${clinicalData.fever && clinicalData.fever !== '0' ? `
                            <div class="swal-clinical-item">
                                <span class="swal-clinical-label">Fever:</span>
                                <span class="swal-clinical-value">${clinicalData.fever} day(s)</span>
                            </div>
                            ` : ''}
                            ${clinicalData.breastfeeding_status && clinicalData.breastfeeding_status !== 'not_applicable' ? `
                            <div class="swal-clinical-item">
                                <span class="swal-clinical-label">Breastfeeding Status:</span>
                                <span class="swal-clinical-value">${clinicalData.breastfeeding_status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                            </div>
                            ` : ''}
                            ${clinicalData.visible_signs && clinicalData.visible_signs.length > 0 ? `
                            <div class="swal-clinical-item full-width">
                                <span class="swal-clinical-label">Visible Signs:</span>
                                <span class="swal-clinical-value">${Array.isArray(clinicalData.visible_signs) ? clinicalData.visible_signs.join(', ') : clinicalData.visible_signs}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    ` : ''}
                    
                    ${additionalNotes ? `
                    <div class="swal-notes-section">
                        <h4 class="swal-section-title">
                            <i class="fas fa-notes-medical"></i>
                            Additional Notes
                        </h4>
                        <div class="swal-notes-content">
                            ${additionalNotes}
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;

    Swal.fire({
        html: htmlContent,
        width: '90%',
        showCancelButton: false,
        showConfirmButton: false,
        showCloseButton: true,
        scrollbarPadding: false,
        heightAuto: false,
        customClass: {
            container: 'assessment-history-modal',
            popup: 'assessment-history-popup',
            htmlContainer: 'p-0',
            closeButton: 'swal-close-button'
        },
        buttonsStyling: false,
        didOpen: () => {
            // Add click handlers to sidebar items
            document.querySelectorAll('.swal-assessment-item').forEach(item => {
                item.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    Swal.close();
                    renderAssessmentHistory(assessments, patientName, index);
                });
            });
        }
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' });
}

function getIndicatorClass(value) {
    if (!value) return '';
    const lowerValue = value.toLowerCase();
    if (lowerValue.includes('severe')) return 'severe';
    if (lowerValue.includes('stunted') || lowerValue.includes('wasted')) return 'severe';
    if (lowerValue.includes('moderate') || lowerValue.includes('overweight')) return 'warning';
    if (lowerValue.includes('normal')) return 'normal';
    return '';
}

// Old detailed view (keep for reference or remove)
function viewAssessmentOld(assessmentId) {
    Swal.fire({
        title: '<i class="fas fa-file-medical me-2" style="color: #4ade80;"></i> Screening Details',
        html: `
            <div id="swal-assessmentDetailsContent" style="max-height: 65vh; overflow-y: auto; text-align: left; padding: 0 0.5rem;">
                <div class="text-center p-5">
                    <div class="modern-spinner mb-3">
                        <div class="spinner-ring"></div>
                        <div class="spinner-ring"></div>
                        <div class="spinner-ring"></div>
                    </div>
                    <p class="text-muted">Loading screening details...</p>
                </div>
            </div>
        `,
        width: '95%',
        maxWidth: '1200px',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-download me-1"></i> Save as PDF',
        cancelButtonText: '<i class="fas fa-times me-1"></i> Close',
        scrollbarPadding: false,
        heightAuto: false,
        customClass: {
            container: 'assessment-details-modal-container',
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
            document.body.style.overflow = 'hidden';
            loadAssessmentDetails(assessmentId);
        },
        willClose: () => {
            document.body.style.overflow = '';
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
            <p class="mt-2">Loading screening details...</p>
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
                        <p>${data.message || 'Failed to load screening details'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading assessment details:', error);
            content.innerHTML = `
                <div class="text-center p-4 text-danger">
                    <i class="fas fa-exclamation-triangle mb-2" style="font-size: 2rem;"></i>
                    <p>Error loading screening details. Please try again.</p>
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
            <!-- Patient & Screening Overview -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user-circle"></i>
                        Patient & Screening Overview
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
                                Screening Date
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
    const pdfUrl = window.assessmentsRoutes.assessmentPdf.replace(':assessmentId', assessmentId);
    
    // Open PDF in new tab (browser will handle download based on headers)
    window.open(pdfUrl, '_blank');
}

// Global function for downloading assessment PDF (accessible from modal buttons)
window.downloadAssessmentPDF = function(assessmentId) {
    printAssessmentDetails(assessmentId);
};

// Modern UI Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize results info on page load
    updateResultsInfo();
    
    // Initialize date constraints if dates are already set
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    
    // Set max date to today for both inputs
    const today = new Date().toISOString().split('T')[0];
    if (dateFromInput) {
        dateFromInput.setAttribute('max', today);
    }
    if (dateToInput) {
        dateToInput.setAttribute('max', today);
    }
    
    // Set constraints based on existing values
    if (dateFromInput && dateToInput) {
        if (dateFromInput.value && dateToInput.value) {
            // Validate existing date range
            if (new Date(dateFromInput.value) > new Date(dateToInput.value)) {
                // Clear invalid date range
                dateFromInput.value = '';
                dateToInput.value = '';
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Date Range',
                    text: 'The saved date range was invalid and has been cleared.',
                    confirmButtonColor: '#22c55e'
                });
            } else {
                dateToInput.setAttribute('min', dateFromInput.value);
                dateFromInput.setAttribute('max', dateToInput.value < today ? dateToInput.value : today);
            }
        }
    }
    
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
    
    // Function to apply filters and reload data via AJAX
    function applyFiltersAndFetch() {
        try {
            // Validate date range before applying filters
            if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                const fromDate = new Date(dateFrom.value);
                const toDate = new Date(dateTo.value);
                
                if (fromDate > toDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Date Range',
                        text: 'Date From cannot be later than Date To. Please adjust your date range.',
                        confirmButtonColor: '#22c55e',
                        confirmButtonText: 'OK'
                    });
                    return; // Don't apply filters
                }
            }
            
            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams();
            
            // Add filter parameters
            if (searchInput && searchInput.value) {
                searchParams.set('search', searchInput.value.trim());
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
            
            // Build URL and fetch results
            const fetchUrl = url.pathname + '?' + searchParams.toString();
            loadAssessmentsFromUrl(fetchUrl);
        } catch (error) {
            console.error('Error applying filters:', error);
            Swal.fire({
                icon: 'error',
                title: 'Filter Error',
                text: 'Unable to apply filters. Please try again.',
                confirmButtonColor: '#22c55e'
            });
        }
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
            // Validate date range
            if (dateTo && dateTo.value && this.value) {
                if (new Date(this.value) > new Date(dateTo.value)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Date Range',
                        text: 'Date From cannot be later than Date To',
                        confirmButtonColor: '#22c55e'
                    });
                    this.value = ''; // Clear the invalid date
                    return;
                }
            }
            // Check if date is in the future
            const today = new Date().toISOString().split('T')[0];
            if (this.value > today) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Date',
                    text: 'Date From cannot be in the future',
                    confirmButtonColor: '#22c55e'
                });
                this.value = '';
                return;
            }
            // Set min attribute on dateTo
            if (dateTo) {
                dateTo.setAttribute('min', this.value);
                // Update dateTo max to be the lesser of today or its current max
                const maxDate = dateTo.getAttribute('max') || today;
                dateTo.setAttribute('max', maxDate);
            }
            // Update dateFrom max to be dateTo value or today
            if (dateTo && dateTo.value) {
                this.setAttribute('max', dateTo.value < today ? dateTo.value : today);
            }
            updateFilterHighlight(this);
            applyFiltersAndFetch();
        });
    }
    
    if (dateTo) {
        dateTo.addEventListener('change', function() {
            // Validate date range
            if (dateFrom && dateFrom.value && this.value) {
                if (new Date(this.value) < new Date(dateFrom.value)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Date Range',
                        text: 'Date To cannot be earlier than Date From',
                        confirmButtonColor: '#22c55e'
                    });
                    this.value = ''; // Clear the invalid date
                    return;
                }
            }
            // Check if date is in the future
            const today = new Date().toISOString().split('T')[0];
            if (this.value > today) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Date',
                    text: 'Date To cannot be in the future',
                    confirmButtonColor: '#22c55e'
                });
                this.value = '';
                return;
            }
            // Set max attribute on dateFrom
            if (dateFrom) {
                dateFrom.setAttribute('max', this.value);
                // Ensure dateFrom min stays valid
                if (dateFrom.value && new Date(dateFrom.value) > new Date(this.value)) {
                    dateFrom.value = '';
                }
            }
            // Update dateTo min to be dateFrom value
            if (dateFrom && dateFrom.value) {
                this.setAttribute('min', dateFrom.value);
            }
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
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
    
    if (clearFiltersEmptyBtn) {
        clearFiltersEmptyBtn.addEventListener('click', clearAllFilters);
    }
    
    // Initialize pagination click handlers
    initializePagination();

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
    loadAssessmentsFromUrl(url.toString());
}

function goToPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    
    // Smooth scroll to top
    document.querySelector('.modern-assessments-card').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
    
    // Small delay for smooth scroll, then load
    setTimeout(() => {
        loadAssessmentsFromUrl(url.toString());
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

// Clear all filters function - global scope
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
    
    // Reload data without filters via AJAX
    loadAssessmentsFromUrl(window.location.pathname);
}

// AJAX function to load assessments without page reload
function loadAssessmentsFromUrl(url) {
    // Show loading indicator
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.style.display = 'flex';
        loadingIndicator.setAttribute('aria-busy', 'true');
    }
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Your session has expired. Please log in again.');
            } else if (response.status === 403) {
                throw new Error('You do not have permission to access this data.');
            } else if (response.status === 404) {
                throw new Error('The requested page was not found.');
            } else if (response.status >= 500) {
                throw new Error('A server error occurred. Please try again later.');
            }
            throw new Error(`Server error: ${response.status}`);
        }
        return response.text();
    })
    .then(html => {
        // Update the assessments container
        const container = document.getElementById('assessmentsContainer');
        if (container) {
            container.innerHTML = html;
            container.setAttribute('aria-live', 'polite');
        }
        
        // Update browser URL without page reload
        window.history.pushState({}, '', url);
        
        // Update results info
        updateResultsInfo();
        
        // Re-attach clear filters button event listener (for empty state)
        const clearFiltersEmptyBtn = document.getElementById('clearFiltersEmpty');
        if (clearFiltersEmptyBtn) {
            clearFiltersEmptyBtn.addEventListener('click', clearAllFilters);
        }
        
        // Re-attach hover effects to new cards
        const patientCards = document.querySelectorAll('.patient-card');
        patientCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Scroll to top of content smoothly
        const mainContent = document.querySelector('.modern-assessments-card');
        if (mainContent) {
            mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    })
    .catch(error => {
        console.error('Error loading assessments:', error);
        
        // Provide context-specific error messages
        Swal.fire({
            icon: 'error',
            title: 'Unable to Load Data',
            html: `
                <p class="mb-2">${error.message}</p>
                <small class="text-muted">
                    ${error.message.includes('session') ? 
                        'Please refresh the page and log in again.' : 
                        'Please try again or contact support if the problem persists.'}
                </small>
            `,
            confirmButtonColor: '#22c55e',
            confirmButtonText: error.message.includes('session') ? 'Reload Page' : 'OK',
            showCancelButton: !error.message.includes('session'),
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed && error.message.includes('session')) {
                window.location.reload();
            }
        });
    })
    .finally(() => {
        // Hide loading indicator
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
            loadingIndicator.setAttribute('aria-busy', 'false');
        }
    });
}

// Initialize pagination click handlers (using event delegation, so only needs to run once)
let paginationInitialized = false;
function initializePagination() {
    if (paginationInitialized) return; // Prevent duplicate listeners
    
    paginationInitialized = true;
    
    // Handle pagination links using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const paginationLink = e.target.closest('.pagination a');
            const url = paginationLink.getAttribute('href');
            if (url) {
                loadAssessmentsFromUrl(url);
            }
        }
    });
}

// Update results count in header
function updateResultsInfo() {
    const resultsInfo = document.getElementById('resultsInfo');
    if (resultsInfo) {
        // Try to get total from pagination info (works for both empty and populated states)
        const totalResults = document.querySelector('.total-results');
        if (totalResults) {
            // Extract just the number and "patients" text
            const text = totalResults.textContent.trim();
            const match = text.match(/Total:\s*(\d+\s+patients?)/);
            if (match) {
                resultsInfo.innerHTML = `<i class="fas fa-chart-bar me-1"></i>${match[1]}`;
            } else {
                resultsInfo.innerHTML = '<i class="fas fa-chart-bar me-1"></i>0 patients';
            }
        } else {
            // Fallback: count cards
            const cards = document.querySelectorAll('.patient-card');
            const count = cards.length;
            resultsInfo.innerHTML = `<i class="fas fa-chart-bar me-1"></i>${count} patient${count !== 1 ? 's' : ''}`;
        }
    }
}

// Show/hide loading overlay
function showLoading() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.style.display = 'flex';
        // Force reflow
        void loadingIndicator.offsetWidth;
        loadingIndicator.classList.add('show');
    }
}

function hideLoading() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.classList.remove('show');
        setTimeout(() => {
            loadingIndicator.style.display = 'none';
        }, 200); // Match CSS transition time
    }
}

