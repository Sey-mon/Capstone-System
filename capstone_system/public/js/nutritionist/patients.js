// Patient Management Functions
let isEditing = false;
let currentPatientId = null;
let filterTimeout = null;

// Auto-calculate age from birthdate
function calculateAgeFromBirthdate(birthdateInput, ageMonthsInput) {
    const birthdate = new Date(birthdateInput.value);
    const today = new Date();
    
    if (birthdate && !isNaN(birthdate.getTime())) {
        // Calculate age in months
        let months = (today.getFullYear() - birthdate.getFullYear()) * 12;
        months -= birthdate.getMonth();
        months += today.getMonth();
        
        // Adjust if birth day hasn't occurred this month yet
        if (today.getDate() < birthdate.getDate()) {
            months--;
        }
        
        ageMonthsInput.value = Math.max(0, months);
    }
}

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
    
    // Checkbox handling - send boolean value or remove from request
    const is4psBeneficiaryCheckbox = form.querySelector('#is_4ps_beneficiary');
    if (is4psBeneficiaryCheckbox.checked) {
        formDataObj['is_4ps_beneficiary'] = '1';
    } else {
        // Don't include the field at all when unchecked
        delete formDataObj['is_4ps_beneficiary'];
    }
    
    // Handle allergies - use custom value if "Other" is selected, otherwise use dropdown value
    const allergiesSelect = form.querySelector('#allergies');
    const allergiesOther = form.querySelector('#allergies_other');
    if (allergiesSelect) {
        if (allergiesSelect.value === 'Other' && allergiesOther && allergiesOther.value) {
            formDataObj['allergies'] = allergiesOther.value;
        } else if (allergiesSelect.value) {
            formDataObj['allergies'] = allergiesSelect.value;
        }
    }
    
    // Handle religion - use custom value if "Other" is selected, otherwise use dropdown value
    const religionSelect = form.querySelector('#religion');
    const religionOther = form.querySelector('#religion_other');
    if (religionSelect) {
        if (religionSelect.value === 'Other' && religionOther && religionOther.value) {
            formDataObj['religion'] = religionOther.value;
        } else if (religionSelect.value) {
            formDataObj['religion'] = religionSelect.value;
        }
    }
    
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
            title: 'Success!',
            text: patientId ? 'Patient updated successfully!' : 'Patient added successfully!',
            confirmButtonColor: '#2e7d32',
            timer: 2000,
            showConfirmButton: true,
            customClass: {
                popup: 'swal2-success-popup',
            }
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
            confirmButtonColor: '#2e7d32'
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
        confirmButtonColor: '#2e7d32'
    });
}

// Initialize filters and event listeners
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializeModals();
    initializeSorting();
    initializePagination();
    initializeViewToggle();
    updateResultsCount(); // Initialize count on page load
});

function initializeFilters() {
    // Search input with debounce
    const searchInput = document.getElementById('searchInput');
    const searchClear = document.getElementById('searchClear');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Show/hide clear button
            if (searchClear) {
                searchClear.style.display = this.value ? 'block' : 'none';
            }
            
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                applyFilters();
            }, 500); // 500ms delay for automatic search
        });
        
        // Show clear button if there's initial value
        if (searchClear && searchInput.value) {
            searchClear.style.display = 'block';
        }
    }
    
    // Clear search button
    if (searchClear) {
        searchClear.addEventListener('click', function() {
            searchInput.value = '';
            this.style.display = 'none';
            applyFilters();
        });
    }

    // Filter dropdowns with immediate response
    const filters = ['barangayFilter', 'sexFilter', 'ageRangeFilter', 'nutritionistFilter'];
    filters.forEach(filterId => {
        const filterElement = document.getElementById(filterId);
        if (filterElement) {
            filterElement.addEventListener('change', applyFilters);
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
    const ageRange = document.getElementById('ageRangeFilter').value;
    const nutritionistFilter = document.getElementById('nutritionistFilter');
    const nutritionist = nutritionistFilter ? nutritionistFilter.value : null;
    
    // Add non-empty filters to params
    if (search) params.append('search', search);
    if (barangay) params.append('barangay', barangay);
    if (sex) params.append('sex', sex);
    if (ageRange) {
        const [min, max] = ageRange.split('-');
        params.append('age_min', min);
        params.append('age_max', max);
    }
    if (nutritionist) params.append('nutritionist', nutritionist);
    
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
        
        // Re-initialize archive buttons after AJAX reload
        if (typeof window.initializeArchiveButtons === 'function') {
            window.initializeArchiveButtons();
        }
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
            resultsCountElement.textContent = totalCount;
        }
    }
}

function clearFilters() {
    // Clear all filter inputs
    document.getElementById('searchInput').value = '';
    document.getElementById('barangayFilter').value = '';
    document.getElementById('sexFilter').value = '';
    document.getElementById('ageRangeFilter').value = '';
    const nutritionistFilter = document.getElementById('nutritionistFilter');
    if (nutritionistFilter && nutritionistFilter.options.length > 1) {
        nutritionistFilter.value = '';
    }
    
    // Apply empty filters (reload without filters)
    applyFilters();
}

function initializeViewToggle() {
    // Handle view toggle buttons
    const viewButtons = document.querySelectorAll('.btn-view');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Update active state
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // TODO: Implement grid view when needed
            if (view === 'grid') {
                console.log('Grid view not yet implemented');
            }
        });
    });
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
        confirmButtonColor: '#2e7d32'
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
        confirmButtonColor: '#2e7d32',
        cancelButtonColor: '#6c757d',
        width: '90vw',
        didOpen: () => {
            // Attach form submit handler after modal opens
            const form = Swal.getPopup().querySelector('#patientForm');
            if (form) {
                form.addEventListener('submit', handlePatientFormSubmit);
                attachNutritionalCalculators(form);
                
                // Add birthdate auto-calculation
                const birthdateInput = form.querySelector('#birthdate');
                const ageMonthsInput = form.querySelector('#age_months');
                if (birthdateInput && ageMonthsInput) {
                    birthdateInput.addEventListener('change', function() {
                        calculateAgeFromBirthdate(birthdateInput, ageMonthsInput);
                    });
                }
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
    
    // Show loading with green theme
    Swal.fire({
        title: 'Loading Patient Data...',
        html: '<div style="color: #2e7d32;"><i class="fas fa-spinner fa-spin"></i> Please wait</div>',
        allowOutsideClick: false,
        showConfirmButton: false,
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
                    confirmButtonColor: '#2e7d32'
                });
                return;
            }
            
            const patient = data.patient;
            
            // Show the form with data
            Swal.fire({
                title: 'Edit Patient - ID: ' + (patient.custom_patient_id || 'N/A'),
                html: getFormHTML(),
                showCancelButton: true,
                confirmButtonText: 'Update Patient',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'swal2-patient-modal',
                    confirmButton: 'swal2-confirm-update',
                    cancelButton: 'swal2-cancel'
                },
                confirmButtonColor: '#2e7d32',
                cancelButtonColor: '#6c757d',
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
                    popup.querySelector('#breastfeeding').value = patient.breastfeeding ?? '';
                    popup.querySelector('#edema').value = patient.edema ?? '';
                    popup.querySelector('#other_medical_problems').value = patient.other_medical_problems ?? '';
                    
                    // Handle allergies field
                    const allergiesSelect = popup.querySelector('#allergies');
                    const allergiesOther = popup.querySelector('#allergies_other');
                    const allergiesValue = patient.allergies ?? '';
                    const commonAllergies = ['None', 'Milk/Dairy', 'Eggs', 'Peanuts', 'Tree Nuts', 'Shellfish/Seafood', 'Fish', 'Soy', 'Wheat/Gluten'];
                    if (commonAllergies.includes(allergiesValue)) {
                        allergiesSelect.value = allergiesValue;
                    } else if (allergiesValue) {
                        allergiesSelect.value = 'Other';
                        allergiesOther.value = allergiesValue;
                        allergiesOther.style.display = 'block';
                    }
                    
                    // Handle religion field
                    const religionSelect = popup.querySelector('#religion');
                    const religionOther = popup.querySelector('#religion_other');
                    const religionValue = patient.religion ?? '';
                    const commonReligions = ['Roman Catholic', 'Islam', 'Iglesia ni Cristo', 'Protestant', 'Seventh-day Adventist', 'Aglipayan', 'Born Again Christian', 'Prefer not to say'];
                    if (commonReligions.includes(religionValue)) {
                        religionSelect.value = religionValue;
                    } else if (religionValue) {
                        religionSelect.value = 'Other';
                        religionOther.value = religionValue;
                        religionOther.style.display = 'block';
                    }
                    
                    // Handle birthdate field
                    const birthdateField = popup.querySelector('#birthdate');
                    if (birthdateField && patient.birthdate) {
                        birthdateField.value = patient.birthdate.substring(0, 10);
                    }
                    
                    // Lock demographic fields (name, birthdate) - cannot be edited
                    const demographicFields = popup.querySelectorAll('[data-lock-on-edit="true"]');
                    demographicFields.forEach(field => {
                        field.disabled = true;
                        field.style.backgroundColor = '#f5f5f5';
                        field.style.cursor = 'not-allowed';
                    });
                    
                    // Lock health fields (weight, height, indicators) - cannot be edited
                    const healthFields = popup.querySelectorAll('[data-health-field="true"]');
                    healthFields.forEach(field => {
                        field.disabled = true;
                        field.style.backgroundColor = '#fff9e6';
                        field.style.cursor = 'not-allowed';
                        field.removeAttribute('required'); // Remove required validation
                    });
                    
                    // Show edit-only messages, hide add-only messages
                    popup.querySelectorAll('.edit-only-message').forEach(msg => {
                        msg.style.display = 'block';
                        msg.style.color = '#856404';
                        msg.style.fontWeight = '500';
                    });
                    popup.querySelectorAll('.add-only-message').forEach(msg => {
                        msg.style.display = 'none';
                    });
                    
                    // Hide required asterisks for locked fields
                    popup.querySelectorAll('.add-only-required').forEach(req => {
                        req.style.display = 'none';
                    });
                    
                    // Attach form submit handler
                    const form = popup.querySelector('#patientForm');
                    if (form) {
                        form.addEventListener('submit', handlePatientFormSubmit);
                        attachNutritionalCalculators(form);
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
                confirmButtonColor: '#2e7d32'
            });
            console.error('Error fetching patient data:', error);
        });
}

function viewPatient(patientId) {
    // Show loading with green theme
    Swal.fire({
        title: 'Loading Patient Details...',
        html: '<div style="color: #2e7d32;"><i class="fas fa-spinner fa-spin"></i> Please wait</div>',
        allowOutsideClick: false,
        showConfirmButton: false,
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
                    confirmButtonColor: '#2e7d32'
                });
                return;
            }
            
            const patient = data.patient;
            
            // Build details HTML with dynamic data
            function show(val) {
                return (val !== undefined && val !== null && val !== '') ? val : 'N/A';
            }
            
            // Helper function to get status badge with green theme
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
            
            // Format date helper
            function formatDate(dateStr) {
                if (!dateStr || dateStr === 'N/A') return 'N/A';
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            }
            
            const calculatedBMI = calculateBMI(patient.weight_kg, patient.height_cm);
            const ageDisplay = getAgeDisplay(patient.age_months);
            
            let html = '<div class="patient-details-modern">';
            
            // Compact Patient Header with dynamic data
            html += '<div class="patient-header-compact">';
            html += '<div class="header-left">';
            html += '<div class="header-info">';
            html += `<h3 class="patient-name-sm">${show(patient.first_name)} ${patient.middle_name ? patient.middle_name.charAt(0) + '. ' : ''}${show(patient.last_name)}</h3>`;
            html += '<div class="patient-quick-info">';
            html += '<span class="info-chip"><i class="fas fa-id-card"></i> ID: <strong>' + show(patient.custom_patient_id) + '</strong></span>';
            html += `<span class="info-chip"><i class="fas fa-birthday-cake"></i> ${ageDisplay}</span>`;
            html += `${getStatusBadge(patient.sex, 'sex')}`;
            html += `<span class="info-chip"><i class="fas fa-map-marker-alt"></i> ${show(patient.barangay?.barangay_name)}</span>`;
            html += '</div></div></div>';
            html += '<div class="header-right">';
            html += `<div class="admission-date"><i class="fas fa-calendar-check"></i> <span>Admitted</span><strong>${formatDate(patient.date_of_admission)}</strong></div>`;
            html += '</div>';
            html += '</div>';
            
            // Two Column Layout
            html += '<div class="details-container">';
            
            // First Row: Health Metrics (full width)
            html += '<div class="details-row">';
            
            // Health Metrics Card - Compact with visual indicators and dynamic data
            html += '<div class="info-card metrics-card">';
            html += '<div class="card-header-sm"><i class="fas fa-heartbeat"></i> Health Metrics</div>';
            html += '<div class="card-content">';
            html += '<div class="metrics-row">';
            html += `<div class="metric-box">`;
            html += `<i class="fas fa-weight"></i>`;
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
            html += `<div class="indicator-item"><label>Weight for Age:</label><span class="indicator-badge">${show(patient.latest_assessment?.weight_for_age) || 'Not assessed'}</span></div>`;
            html += `<div class="indicator-item"><label>Height for Age:</label><span class="indicator-badge">${show(patient.latest_assessment?.height_for_age) || 'Not assessed'}</span></div>`;
            html += `<div class="indicator-item"><label>BMI for Age:</label><span class="indicator-badge">${show(patient.latest_assessment?.bmi_for_age) || 'Not assessed'}</span></div>`;
            html += '</div>';
            html += '</div></div>';
            
            html += '</div>'; // End health metrics row
            
            // Dietary & Religious Information Row
            html += '<div class="details-row">';
            html += '<div class="info-card metrics-card">';
            html += '<div class="card-header-sm"><i class="fas fa-info-circle"></i> Dietary & Religious Information</div>';
            html += '<div class="card-content">';
            html += '<div class="metrics-row">';
            html += `<div class="metric-box" style="padding: 10px; min-height: auto;">`;
            html += `<div class="metric-data">`;
            html += `<span class="metric-value" style="font-size: 1rem; font-weight: 600;">${show(patient.allergies)}</span>`;
            html += `</div>`;
            html += `<span class="metric-label" style="margin-top: 4px;">Allergies</span>`;
            html += `</div>`;
            html += `<div class="metric-box" style="padding: 10px; min-height: auto;">`;
            html += `<div class="metric-data">`;
            html += `<span class="metric-value" style="font-size: 1rem; font-weight: 600;">${show(patient.religion)}</span>`;
            html += `</div>`;
            html += `<span class="metric-label" style="margin-top: 4px;">Religion</span>`;
            html += `</div>`;
            html += '</div>';
            html += '</div></div>';
            html += '</div>'; // End dietary/religious row
            
            // Household Row (full width)
            html += '<div class="details-row">';
            
            // Household Information Card - Compact with dynamic data
            html += '<div class="info-card household-card">';
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
            
            html += '</div>'; // End household row
            
            // Medical Notes if exists with dynamic data
            if (patient.other_medical_problems && patient.other_medical_problems !== 'N/A' && patient.other_medical_problems.trim() !== '') {
                html += '<div class="details-row">';
                html += '<div class="info-card notes-card">';
                html += '<div class="card-header-sm"><i class="fas fa-clipboard-list"></i> Medical Notes</div>';
                html += '<div class="card-content">';
                html += `<div class="notes-text">${show(patient.other_medical_problems)}</div>`;
                html += '</div></div>';
                html += '</div>'; // End notes row
            }
            
            // Parent Contact Information (left) + Medical Status (right) - moved to bottom
            html += '<div class="details-row">';
            
            // Contact Information Card - Compact with dynamic data
            html += '<div class="info-card contact-card">';
            html += '<div class="card-header-sm"><i class="fas fa-address-card"></i> Parent Contact Information</div>';
            html += '<div class="card-content">';
            html += '<div class="status-list">';
            html += `<div class="status-row">`;
            html += `<i class="fas fa-phone"></i>`;
            html += `<span class="status-label">Phone</span>`;
            html += `<span class="status-value">${show(patient.contact_number)}</span>`;
            html += `</div>`;
            html += `<div class="status-row">`;
            html += `<i class="fas fa-user-friends"></i>`;
            html += `<span class="status-label">Parent/Guardian</span>`;
            html += `<span class="status-value">${patient.parent ? show(patient.parent?.first_name) + ' ' + show(patient.parent?.last_name) : 'N/A'}</span>`;
            html += `</div>`;
            html += '</div></div></div>';
            
            // Medical Status Card with dynamic data
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
            
            html += '</div>'; // End parent contact/medical status row
            
            html += '</div>'; // End details-container
            html += '</div>'; // End patient-details-modern
            
            // Display the modal with green theme
            Swal.fire({
                title: 'Patient Details',
                html: html,
                width: '90vw',
                customClass: {
                    popup: 'swal2-patient-modal',
                    confirmButton: 'swal2-confirm'
                },
                confirmButtonText: 'Close',
                confirmButtonColor: '#2e7d32',
                showCloseButton: true,
                focusConfirm: false
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load patient details. Please try again.',
                confirmButtonColor: '#2e7d32'
            });
            console.error('Error fetching patient details:', error);
        });
}

// Delete function removed - only admins can permanently delete patients
// Nutritionists should use the archive functionality to maintain medical record integrity
function deletePatient(patientId) {
    Swal.fire({
        icon: 'info',
        title: 'Delete Not Available',
        text: 'Only administrators can permanently delete patient records. Please use the Archive function to remove patients from the active list while preserving medical records.',
        confirmButtonColor: '#2e7d32'
    });
}

// Auto-calculate nutritional indicators
let calculationTimeout = null;

function attachNutritionalCalculators(form) {
    const weightInput = form.querySelector('#weight_kg');
    const heightInput = form.querySelector('#height_cm');
    const ageInput = form.querySelector('#age_months');
    const sexInput = form.querySelector('#sex');
    
    const weightForAgeInput = form.querySelector('#weight_for_age');
    const heightForAgeInput = form.querySelector('#height_for_age');
    const bmiForAgeInput = form.querySelector('#bmi_for_age');

    if (!weightInput || !heightInput || !ageInput || !sexInput) {
        console.warn('Required inputs not found for nutritional calculation');
        return;
    }

    function calculateIndicators() {
        const weight = parseFloat(weightInput.value);
        const height = parseFloat(heightInput.value);
        const age = parseInt(ageInput.value);
        const sex = sexInput.value;

        // Validate all required fields have values
        if (!weight || !height || !age || !sex) {
            return;
        }

        // Validate ranges
        if (age < 0 || age > 60 || weight <= 0 || height <= 0) {
            return;
        }

        // Show loading state
        if (weightForAgeInput) weightForAgeInput.value = 'Calculating...';
        if (heightForAgeInput) heightForAgeInput.value = 'Calculating...';
        if (bmiForAgeInput) bmiForAgeInput.value = 'Calculating...';

        // Get CSRF token
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        // Call API to calculate indicators
        fetch('/calculate/all-indices', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                weight_kg: weight,
                height_cm: height,
                age_months: age,
                sex: sex
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.indicators) {
                if (weightForAgeInput) weightForAgeInput.value = data.indicators.weight_for_age || '';
                if (heightForAgeInput) heightForAgeInput.value = data.indicators.height_for_age || '';
                if (bmiForAgeInput) bmiForAgeInput.value = data.indicators.bmi_for_age || '';
            } else {
                // Clear on error
                if (weightForAgeInput) weightForAgeInput.value = '';
                if (heightForAgeInput) heightForAgeInput.value = '';
                if (bmiForAgeInput) bmiForAgeInput.value = '';
            }
        })
        .catch(error => {
            // Clear on error
            if (weightForAgeInput) weightForAgeInput.value = '';
            if (heightForAgeInput) heightForAgeInput.value = '';
            if (bmiForAgeInput) bmiForAgeInput.value = '';
        });
    }

    function debounceCalculation() {
        clearTimeout(calculationTimeout);
        calculationTimeout = setTimeout(calculateIndicators, 800);
    }

    // Attach listeners to all relevant inputs
    weightInput.addEventListener('input', debounceCalculation);
    heightInput.addEventListener('input', debounceCalculation);
    ageInput.addEventListener('input', debounceCalculation);
    sexInput.addEventListener('change', debounceCalculation);

    // If all fields are already filled (edit mode), calculate immediately
    if (weightInput.value && heightInput.value && ageInput.value && sexInput.value) {
        setTimeout(calculateIndicators, 500);
    }
}

// Global functions
window.openAddPatientModal = openAddPatientModal;
window.closePatientModal = closePatientModal;
window.closeViewPatientModal = closeViewPatientModal;
window.editPatient = editPatient;
window.viewPatient = viewPatient;
window.deletePatient = deletePatient;
window.clearFilters = clearFilters;
window.handleAllergiesChange = handleAllergiesChange;
window.handleReligionChange = handleReligionChange;

// Handle allergies dropdown change
function handleAllergiesChange(select) {
    const otherInput = document.getElementById('allergies_other');
    if (select.value === 'Other') {
        otherInput.style.display = 'block';
        otherInput.required = true;
    } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
}

// Handle religion dropdown change
function handleReligionChange(select) {
    const otherInput = document.getElementById('religion_other');
    if (select.value === 'Other') {
        otherInput.style.display = 'block';
        otherInput.required = true;
    } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
}

console.log('Enhanced patient page functions loaded with filtering and pagination');
