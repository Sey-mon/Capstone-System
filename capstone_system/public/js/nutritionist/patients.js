// Patient Management Functions
let isEditing = false;
let currentPatientId = null;
let filterTimeout = null;

// Form submission handler - prevent default form submission
function handlePatientFormSubmit(e) {
    e.preventDefault();
    return false;
}

// Submit patient form data
function submitPatientForm(form) {
    const patientId = form.querySelector('#patient_id').value;
    const url = patientId ? `/nutritionist/patients/${patientId}` : '/nutritionist/patients';
    const method = patientId ? 'PUT' : 'POST';

    // Get CSRF token from meta tag
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    // Collect all form data into an object
    const formDataObj = {};
    new FormData(form).forEach((value, key) => {
        formDataObj[key] = value;
    });
    
    // Checkbox handling
    formDataObj['is_4ps_beneficiary'] = form.querySelector('#is_4ps_beneficiary').checked ? 'on' : '';
    
    // Handle empty parent_id - convert empty string to null
    if (!formDataObj['parent_id'] || formDataObj['parent_id'] === '') {
        formDataObj['parent_id'] = null;
    }

    return fetch(url, {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(formDataObj)
    })
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (e) {
            throw new Error('Failed to update patient.');
        }
        if (response.status === 422 && data.errors) {
            let errorMessages = Object.values(data.errors).map(arr => arr.join(' ')).join('\n');
            Swal.showValidationMessage(errorMessages);
            throw new Error(errorMessages);
        }
        if (data.success) {
            return data;
        } else {
            throw new Error(data.message || 'Failed to update patient.');
        }
    })
    .then(data => {
        // Close the current modal first
        Swal.close();
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: patientId ? 'Patient updated successfully!' : 'Patient added successfully!',
            confirmButtonColor: '#40916c',
            timer: 2000,
            showConfirmButton: true
        }).then(() => {
            // Refresh the patient list
            applyFilters();
        });
        
        return true;
    })
    .catch(error => {
        if (error.message && !error.message.includes('Failed to')) {
            // This is a validation error, already shown via Swal.showValidationMessage
            return false;
        }
        
        // Close current modal and show error
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to save patient.',
            confirmButtonColor: '#40916c'
        });
        return false;
    });
}

// Show success message
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        confirmButtonColor: '#40916c'
    });
}

// Initialize filters and event listeners
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializeModals();
    initializeSorting();
    initializePagination();
    updateResultsCount(); // Initialize count on page load
});

function initializeFilters() {
    // Search input with debounce
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                applyFilters();
            }, 500); // 500ms delay for automatic search
        });
    }

    // Filter dropdowns with immediate response
    const filters = ['barangayFilter', 'sexFilter', 'perPageFilter'];
    filters.forEach(filterId => {
        const filterElement = document.getElementById(filterId);
        if (filterElement) {
            filterElement.addEventListener('change', applyFilters);
        }
    });

    // Age range filters with debounce
    const ageFilters = ['ageMin', 'ageMax'];
    ageFilters.forEach(filterId => {
        const filterElement = document.getElementById(filterId);
        if (filterElement) {
            filterElement.addEventListener('input', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    applyFilters();
                }, 800); // Longer delay for number inputs
            });
        }
    });
}

function initializeSorting() {
    // Handle sort links
    document.addEventListener('click', function(e) {
        if (e.target.closest('.sort-link')) {
            e.preventDefault();
            const sortLink = e.target.closest('.sort-link');
            const sortBy = sortLink.getAttribute('data-sort');
            handleSort(sortBy);
        }
    });
}

function initializePagination() {
    // Handle pagination links
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const paginationLink = e.target.closest('.pagination a');
            const url = paginationLink.getAttribute('href');
            if (url) {
                loadPatientsFromUrl(url);
            }
        }
    });
}

function applyFilters() {
    const params = new URLSearchParams();
    
    // Get filter values
    const search = document.getElementById('searchInput').value.trim();
    const barangay = document.getElementById('barangayFilter').value;
    const sex = document.getElementById('sexFilter').value;
    const ageMin = document.getElementById('ageMin').value;
    const ageMax = document.getElementById('ageMax').value;
    const perPage = document.getElementById('perPageFilter').value;
    
    // Add non-empty filters to params
    if (search) params.append('search', search);
    if (barangay) params.append('barangay', barangay);
    if (sex) params.append('sex', sex);
    if (ageMin) params.append('age_min', ageMin);
    if (ageMax) params.append('age_max', ageMax);
    if (perPage) params.append('per_page', perPage);
    
    // Get current sort parameters
    const urlParams = new URLSearchParams(window.location.search);
    const sortBy = urlParams.get('sort_by');
    const sortOrder = urlParams.get('sort_order');
    if (sortBy) params.append('sort_by', sortBy);
    if (sortOrder) params.append('sort_order', sortOrder);
    
    // Build URL and fetch results
    const baseUrl = window.location.pathname;
    const url = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
    
    loadPatientsFromUrl(url);
}

function handleSort(sortBy) {
    const urlParams = new URLSearchParams(window.location.search);
    const currentSortBy = urlParams.get('sort_by');
    const currentSortOrder = urlParams.get('sort_order') || 'asc';
    
    // Toggle sort order if same column, otherwise default to asc
    let newSortOrder = 'asc';
    if (currentSortBy === sortBy && currentSortOrder === 'asc') {
        newSortOrder = 'desc';
    }
    
    urlParams.set('sort_by', sortBy);
    urlParams.set('sort_order', newSortOrder);
    
    const url = `${window.location.pathname}?${urlParams.toString()}`;
    loadPatientsFromUrl(url);
}

function loadPatientsFromUrl(url) {
    showLoading();
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(html => {
        // Update the table container
        document.getElementById('patientsTableContainer').innerHTML = html;
        
        // Update browser URL without page reload
        window.history.pushState({}, '', url);
        
        // Update results count
        updateResultsCount();
        
        // Re-initialize pagination for new content
        initializePagination();
    })
    .catch(error => {
        console.error('Error loading patients:', error);
        showError('Failed to load patients. Please try again.');
    })
    .finally(() => {
        hideLoading();
    });
}

function updateResultsCount() {
    // Extract total count from data attribute
    const tableContainer = document.querySelector('.table-responsive[data-total-count]') || 
                          document.querySelector('.empty-state[data-total-count]');
    
    if (tableContainer) {
        const totalCount = tableContainer.getAttribute('data-total-count');
        const resultsCountElement = document.getElementById('resultsCount');
        if (resultsCountElement && totalCount !== null) {
            resultsCountElement.textContent = `${totalCount} patient(s) found`;
        }
    }
}

function clearFilters() {
    // Clear all filter inputs
    document.getElementById('searchInput').value = '';
    document.getElementById('barangayFilter').value = '';
    document.getElementById('sexFilter').value = '';
    document.getElementById('ageMin').value = '';
    document.getElementById('ageMax').value = '';
    document.getElementById('perPageFilter').value = '15';
    
    // Apply empty filters (reload without filters)
    applyFilters();
}

function showLoading() {
    // Loading overlay removed, do nothing
}

function hideLoading() {
    // Loading overlay removed, do nothing
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#40916c'
    });
}

function initializeModals() {
    // SweetAlert2 doesn't need initialization
    console.log('SweetAlert2 modals ready');
}

function getFormHTML() {
    const template = document.getElementById('patientFormTemplate');
    if (!template) return '';
    return template.innerHTML;
}

function openAddPatientModal() {
    isEditing = false;
    currentPatientId = null;
    
    Swal.fire({
        title: 'Add Patient',
        html: getFormHTML(),
        showCancelButton: true,
        confirmButtonText: 'Save Patient',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal2-patient-modal',
            confirmButton: 'swal2-confirm',
            cancelButton: 'swal2-cancel'
        },
        confirmButtonColor: '#40916c',
        cancelButtonColor: '#7f8c8d',
        width: '90vw',
        didOpen: () => {
            // Attach form submit handler after modal opens
            const form = Swal.getPopup().querySelector('#patientForm');
            if (form) {
                form.addEventListener('submit', handlePatientFormSubmit);
            }
        },
        preConfirm: () => {
            const form = Swal.getPopup().querySelector('#patientForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            return submitPatientForm(form);
        },
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading()
    });
}

function closePatientModal() {
    Swal.close();
}

function closeViewPatientModal() {
    Swal.close();
}

function editPatient(patientId) {
    isEditing = true;
    currentPatientId = patientId;
    
    // Show loading
    Swal.fire({
        title: 'Loading...',
        text: 'Fetching patient data',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fetch patient data
    fetch(`/nutritionist/patients/${patientId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (!data.success || !data.patient) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to load patient data for editing.',
                    confirmButtonColor: '#40916c'
                });
                return;
            }
            
            const patient = data.patient;
            
            // Show the form with data
            Swal.fire({
                title: 'Edit Patient',
                html: getFormHTML(),
                showCancelButton: true,
                confirmButtonText: 'Update Patient',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'swal2-patient-modal',
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                },
                confirmButtonColor: '#40916c',
                cancelButtonColor: '#7f8c8d',
                width: '90vw',
                didOpen: () => {
                    // Fill form fields
                    const popup = Swal.getPopup();
                    popup.querySelector('#patient_id').value = patient.patient_id ?? '';
                    popup.querySelector('#first_name').value = patient.first_name ?? '';
                    popup.querySelector('#middle_name').value = patient.middle_name ?? '';
                    popup.querySelector('#last_name').value = patient.last_name ?? '';
                    popup.querySelector('#contact_number').value = patient.contact_number ?? '';
                    popup.querySelector('#age_months').value = patient.age_months ?? '';
                    popup.querySelector('#sex').value = patient.sex ?? '';
                    popup.querySelector('#date_of_admission').value = patient.date_of_admission ? patient.date_of_admission.substring(0, 10) : '';
                    popup.querySelector('#barangay_id').value = patient.barangay_id ?? '';
                    popup.querySelector('#parent_id').value = patient.parent_id ?? '';
                    popup.querySelector('#total_household_adults').value = patient.total_household_adults ?? 0;
                    popup.querySelector('#total_household_children').value = patient.total_household_children ?? 0;
                    popup.querySelector('#total_household_twins').value = patient.total_household_twins ?? 0;
                    popup.querySelector('#is_4ps_beneficiary').checked = !!patient.is_4ps_beneficiary;
                    popup.querySelector('#weight_kg').value = patient.weight_kg ?? '';
                    popup.querySelector('#height_cm').value = patient.height_cm ?? '';
                    popup.querySelector('#weight_for_age').value = patient.weight_for_age ?? '';
                    popup.querySelector('#height_for_age').value = patient.height_for_age ?? '';
                    popup.querySelector('#bmi_for_age').value = patient.bmi_for_age ?? '';
                    popup.querySelector('#breastfeeding').value = patient.breastfeeding ?? '';
                    popup.querySelector('#edema').value = patient.edema ?? '';
                    popup.querySelector('#other_medical_problems').value = patient.other_medical_problems ?? '';
                    
                    // Attach form submit handler
                    const form = popup.querySelector('#patientForm');
                    if (form) {
                        form.addEventListener('submit', handlePatientFormSubmit);
                    }
                },
                preConfirm: () => {
                    const form = Swal.getPopup().querySelector('#patientForm');
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return false;
                    }
                    return submitPatientForm(form);
                },
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading()
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load patient data for editing.',
                confirmButtonColor: '#40916c'
            });
        });
}

function viewPatient(patientId) {
    // Show loading
    Swal.fire({
        title: 'Loading...',
        text: 'Fetching patient details',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fetch patient details via AJAX
    fetch(`/nutritionist/patients/${patientId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (!data.success || !data.patient) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to load patient details.',
                    confirmButtonColor: '#40916c'
                });
                return;
            }
            
            const patient = data.patient;
            
            // Build details HTML
            function show(val) {
                return (val !== undefined && val !== null && val !== '') ? val : 'N/A';
            }
            
            // Helper function to get status badge
            function getStatusBadge(value, type = 'default') {
                if (value === 'N/A' || !value) return '<span class="badge-status badge-na"><i class="fas fa-minus-circle"></i> N/A</span>';
                
                if (type === 'boolean') {
                    return value ? '<span class="badge-status badge-yes"><i class="fas fa-check-circle"></i> Yes</span>' : '<span class="badge-status badge-no"><i class="fas fa-times-circle"></i> No</span>';
                }
                
                if (type === 'sex') {
                    return value === 'Male' ? '<span class="badge-status badge-male"><i class="fas fa-mars"></i> Male</span>' : '<span class="badge-status badge-female"><i class="fas fa-venus"></i> Female</span>';
                }
                
                return `<span class="badge-status badge-default">${value}</span>`;
            }
            
            // Helper to calculate BMI
            function calculateBMI(weight, height) {
                if (weight && height && weight !== 'N/A' && height !== 'N/A') {
                    const heightInMeters = parseFloat(height) / 100;
                    const bmi = (parseFloat(weight) / (heightInMeters * heightInMeters)).toFixed(1);
                    return bmi;
                }
                return null;
            }
            
            // Helper to get age display
            function getAgeDisplay(months) {
                const m = parseInt(months);
                if (isNaN(m)) return 'N/A';
                const years = Math.floor(m / 12);
                const remainingMonths = m % 12;
                if (years > 0) {
                    return remainingMonths > 0 ? `${years}y ${remainingMonths}m` : `${years}y`;
                }
                return `${m}m`;
            }
            
            const calculatedBMI = calculateBMI(patient.weight_kg, patient.height_cm);
            const ageDisplay = getAgeDisplay(patient.age_months);
            
            let html = '<div class="patient-details-modern">';
            
            // Compact Patient Header
            html += '<div class="patient-header-compact">';
            html += '<div class="header-left">';
            html += `<div class="patient-avatar-sm">${patient.first_name.charAt(0)}${patient.last_name.charAt(0)}</div>`;
            html += '<div class="header-info">';
            html += `<h3 class="patient-name-sm">${show(patient.first_name)} ${patient.middle_name ? patient.middle_name.charAt(0) + '. ' : ''}${show(patient.last_name)}</h3>`;
            html += '<div class="patient-quick-info">';
            html += `<span class="info-chip"><i class="fas fa-birthday-cake"></i> ${ageDisplay}</span>`;
            html += `${getStatusBadge(patient.sex, 'sex')}`;
            html += `<span class="info-chip"><i class="fas fa-map-marker-alt"></i> ${show(patient.barangay?.barangay_name)}</span>`;
            html += '</div></div></div>';
            html += '<div class="header-right">';
            html += `<div class="admission-date"><i class="fas fa-calendar-check"></i> <span>Admitted</span><strong>${show(patient.date_of_admission)}</strong></div>`;
            html += '</div>';
            html += '</div>';
            
            // Two Column Layout
            html += '<div class="details-container">';
            html += '<div class="details-column details-main">';
            
            // Contact Information Card - Compact
            html += '<div class="info-card">';
            html += '<div class="card-header-sm"><i class="fas fa-address-card"></i> Contact & Location</div>';
            html += '<div class="card-content">';
            html += '<div class="info-grid">';
            html += `<div class="info-cell">`;
            html += `<label><i class="fas fa-phone"></i> Phone</label>`;
            html += `<span>${show(patient.contact_number)}</span>`;
            html += `</div>`;
            html += `<div class="info-cell">`;
            html += `<label><i class="fas fa-user-friends"></i> Parent/Guardian</label>`;
            html += `<span>${patient.parent ? show(patient.parent?.first_name) + ' ' + show(patient.parent?.last_name) : 'N/A'}</span>`;
            html += `</div>`;
            html += '</div></div></div>';
            
            // Household Information Card - Compact
            html += '<div class="info-card">';
            html += '<div class="card-header-sm"><i class="fas fa-home"></i> Household</div>';
            html += '<div class="card-content">';
            html += '<div class="stat-row">';
            html += `<div class="stat-item"><i class="fas fa-users"></i><span class="stat-num">${show(patient.total_household_adults)}</span><span class="stat-text">Adults</span></div>`;
            html += `<div class="stat-item"><i class="fas fa-child"></i><span class="stat-num">${show(patient.total_household_children)}</span><span class="stat-text">Children</span></div>`;
            html += `<div class="stat-item"><i class="fas fa-user-friends"></i><span class="stat-num">${show(patient.total_household_twins)}</span><span class="stat-text">Twins</span></div>`;
            html += '</div>';
            html += '<div class="benefit-status">';
            html += `<i class="fas fa-hand-holding-heart"></i> <span>4Ps Beneficiary:</span> ${getStatusBadge(patient.is_4ps_beneficiary, 'boolean')}`;
            html += '</div>';
            html += '</div></div>';
            
            // Health Metrics Card - Compact with visual indicators
            html += '<div class="info-card metrics-card">';
            html += '<div class="card-header-sm"><i class="fas fa-heartbeat"></i> Health Metrics</div>';
            html += '<div class="card-content">';
            html += '<div class="metrics-row">';
            html += `<div class="metric-box">`;
            html += `<i class="fas fa-weight-hanging"></i>`;
            html += `<div class="metric-data">`;
            html += `<span class="metric-value">${show(patient.weight_kg)}</span>`;
            html += `<span class="metric-unit">kg</span>`;
            html += `</div>`;
            html += `<span class="metric-label">Weight</span>`;
            html += `</div>`;
            html += `<div class="metric-box">`;
            html += `<i class="fas fa-ruler-vertical"></i>`;
            html += `<div class="metric-data">`;
            html += `<span class="metric-value">${show(patient.height_cm)}</span>`;
            html += `<span class="metric-unit">cm</span>`;
            html += `</div>`;
            html += `<span class="metric-label">Height</span>`;
            html += `</div>`;
            if (calculatedBMI) {
                html += `<div class="metric-box">`;
                html += `<i class="fas fa-calculator"></i>`;
                html += `<div class="metric-data">`;
                html += `<span class="metric-value">${calculatedBMI}</span>`;
                html += `<span class="metric-unit">BMI</span>`;
                html += `</div>`;
                html += `<span class="metric-label">Body Mass Index</span>`;
                html += `</div>`;
            }
            html += '</div>';
            html += '<div class="indicators-row">';
            html += `<div class="indicator-item"><label>Weight for Age:</label><span class="indicator-badge">${show(patient.weight_for_age)}</span></div>`;
            html += `<div class="indicator-item"><label>Height for Age:</label><span class="indicator-badge">${show(patient.height_for_age)}</span></div>`;
            html += `<div class="indicator-item"><label>BMI for Age:</label><span class="indicator-badge">${show(patient.bmi_for_age)}</span></div>`;
            html += '</div>';
            html += '</div></div>';
            
            html += '</div>'; // End main column
            
            // Sidebar Column
            html += '<div class="details-column details-sidebar">';
            
            // Medical Status Card
            html += '<div class="info-card status-card">';
            html += '<div class="card-header-sm"><i class="fas fa-stethoscope"></i> Medical Status</div>';
            html += '<div class="card-content">';
            html += '<div class="status-list">';
            html += `<div class="status-row">`;
            html += `<i class="fas fa-baby"></i>`;
            html += `<span class="status-label">Breastfeeding</span>`;
            html += `${getStatusBadge(patient.breastfeeding, patient.breastfeeding === 'Yes' ? 'boolean' : 'default')}`;
            html += `</div>`;
            html += `<div class="status-row">`;
            html += `<i class="fas fa-hand-holding-medical"></i>`;
            html += `<span class="status-label">Edema</span>`;
            html += `${getStatusBadge(patient.edema, patient.edema === 'Yes' ? 'boolean' : 'default')}`;
            html += `</div>`;
            html += '</div>';
            html += '</div></div>';
            
            // Medical Notes if exists
            if (patient.other_medical_problems && patient.other_medical_problems !== 'N/A' && patient.other_medical_problems.trim() !== '') {
                html += '<div class="info-card notes-card">';
                html += '<div class="card-header-sm"><i class="fas fa-clipboard-list"></i> Medical Notes</div>';
                html += '<div class="card-content">';
                html += `<div class="notes-text">${show(patient.other_medical_problems)}</div>`;
                html += '</div></div>';
            }
            
            html += '</div>'; // End sidebar column
            html += '</div>'; // End details-container
            html += '</div>'; // End patient-details-modern
            
            Swal.fire({
                title: 'Patient Details',
                html: html,
                width: '90vw',
                customClass: {
                    popup: 'swal2-patient-modal'
                },
                confirmButtonText: 'Close',
                confirmButtonColor: '#40916c'
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load patient details.',
                confirmButtonColor: '#40916c'
            });
        });
}

function deletePatient(patientId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! All patient data will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef476f',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            // Get CSRF token
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

            return fetch(`/nutritionist/patients/${patientId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to delete patient');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(
                    `Request failed: ${error.message || error}`
                );
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Deleted!',
                text: 'Patient has been deleted successfully.',
                icon: 'success',
                confirmButtonColor: '#40916c',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // Refresh the patient list and update count
                applyFilters();
            });
        }
    });
}

// Global functions
window.openAddPatientModal = openAddPatientModal;
window.closePatientModal = closePatientModal;
window.closeViewPatientModal = closeViewPatientModal;
window.editPatient = editPatient;
window.viewPatient = viewPatient;
window.deletePatient = deletePatient;
window.clearFilters = clearFilters;

console.log('Enhanced patient page functions loaded with filtering and pagination');
