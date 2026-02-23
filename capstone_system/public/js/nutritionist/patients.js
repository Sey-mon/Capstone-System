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
        // Show/hide clear button as user types, but don't search yet
        searchInput.addEventListener('input', function() {
            if (searchClear) {
                searchClear.style.display = this.value ? 'block' : 'none';
            }
        });

        // Search only when Enter is pressed
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(filterTimeout);
                applyFilters();
            }
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
    // Check if we're using the archive system
    if (typeof window.getCurrentStatus === 'function' && typeof window.loadPatientsWithStatus === 'function') {
        const currentStatus = window.getCurrentStatus();
        const urlParams = new URLSearchParams(window.location.search);
        const sortBy = urlParams.get('sort_by');
        const sortOrder = urlParams.get('sort_order');
        
        // Load patients with current status and sort
        window.loadPatientsWithStatus(currentStatus, 1, sortBy, sortOrder);
    } else {
        // Fallback to old method
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
}

function handleSort(sortBy) {
    // Check if we're using the archive system
    if (typeof window.getCurrentStatus === 'function' && typeof window.loadPatientsWithStatus === 'function') {
        const currentStatus = window.getCurrentStatus();
        const urlParams = new URLSearchParams(window.location.search);
        const currentSortBy = urlParams.get('sort_by');
        const currentSortOrder = urlParams.get('sort_order') || 'asc';
        
        // Toggle sort order if same column, otherwise default to asc
        let newSortOrder = 'asc';
        if (currentSortBy === sortBy && currentSortOrder === 'asc') {
            newSortOrder = 'desc';
        }
        
        // Update URL params
        urlParams.set('sort_by', sortBy);
        urlParams.set('sort_order', newSortOrder);
        window.history.pushState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
        
        // Load patients with status and sort
        window.loadPatientsWithStatus(currentStatus, 1, sortBy, newSortOrder);
    } else {
        // Fallback to old method
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
}

function loadPatientsFromUrl(url) {
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
        showError('Failed to load patients. Please try again.');
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
                // Grid view not yet implemented
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
        title: 'Error',
        text: message,
        confirmButtonColor: '#2e7d32'
    });
}

function initializeModals() {
    // SweetAlert2 doesn't need initialization
}

function getFormHTML() {
    const template = document.getElementById('patientFormTemplate');
    if (!template) {
        return '';
    }
    return template.innerHTML;
}

function openAddPatientModal() {
    isEditing = false;
    currentPatientId = null;
    
    const formHTML = getFormHTML();
    if (!formHTML) {
        Swal.fire({
            title: 'Error',
            text: 'Patient form template not found. Please refresh the page.',
            confirmButtonColor: '#2e7d32'
        });
        return;
    }
    
    Swal.fire({
        title: '<i class="fas fa-user-plus" style="color: #2e7d32;"></i> Add New Patient',
        html: `
            <div class="swal-form-container">
                <!-- New Patient Banner -->
                <div style="background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%); padding: 15px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(46,125,50,0.2);">
                    <div style="color: white; font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-plus-circle"></i> Creating New Patient Record
                    </div>
                    <div style="color: white; font-size: 18px; font-weight: 600;">
                        All fields marked with * are required
                    </div>
                </div>
                ${formHTML}
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Save Patient',
        cancelButtonText: 'Cancel',
        customClass: {
            container: 'swal-patient-modal',
            popup: 'swal-patient-popup',
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary'
        },
        width: '950px',
        didOpen: () => {
            // Setup birthdate to age calculation
            const birthdateInput = document.getElementById('birthdate');
            const ageMonthsInput = document.getElementById('age_months');
            
            if (birthdateInput && ageMonthsInput) {
                birthdateInput.addEventListener('change', function() {
                    const birthdate = new Date(this.value);
                    const today = new Date();
                    
                    if (birthdate && !isNaN(birthdate.getTime())) {
                        let months = (today.getFullYear() - birthdate.getFullYear()) * 12;
                        months -= birthdate.getMonth();
                        months += today.getMonth();
                        
                        if (today.getDate() < birthdate.getDate()) {
                            months--;
                        }
                        
                        ageMonthsInput.value = Math.max(0, months);
                        
                        // Visual feedback
                        this.style.borderColor = '#2e7d32';
                        setTimeout(() => {
                            this.style.borderColor = '';
                        }, 1000);
                    }
                });
            }
            
            // Phone number formatting and validation
            const contactInput = document.getElementById('contact_number');
            if (contactInput) {
                contactInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.substring(0, 11);
                    this.value = value;
                    
                    // Real-time validation feedback
                    if (value.length === 11 && value.startsWith('09')) {
                        this.style.borderColor = '#2e7d32';
                    } else if (value.length > 0) {
                        this.style.borderColor = '#ffc107';
                    } else {
                        this.style.borderColor = '';
                    }
                });
            }
            
            // Real-time validation for required fields
            const requiredFields = ['first_name', 'last_name', 'sex', 'weight_kg', 'height_cm', 'barangay_id', 'date_of_admission'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('blur', function() {
                        if (this.value.trim() === '') {
                            this.style.borderColor = '#dc3545';
                        } else {
                            this.style.borderColor = '#2e7d32';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 1000);
                        }
                    });
                    
                    field.addEventListener('input', function() {
                        if (this.value.trim() !== '' && this.style.borderColor === 'rgb(220, 53, 69)') {
                            this.style.borderColor = '';
                        }
                    });
                }
            });
            
            // Weight and height validation
            ['weight_kg', 'height_cm'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', function() {
                        const value = parseFloat(this.value);
                        if (value > 0) {
                            this.style.borderColor = '#2e7d32';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 1000);
                        }
                    });
                }
            });
            
            // Handle Allergies "Other" option
            const allergiesSelect = document.getElementById('allergies');
            const allergiesOtherInput = document.getElementById('allergies_other');
            if (allergiesSelect && allergiesOtherInput) {
                allergiesSelect.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        allergiesOtherInput.style.display = 'block';
                        allergiesOtherInput.required = true;
                    } else {
                        allergiesOtherInput.style.display = 'none';
                        allergiesOtherInput.required = false;
                        allergiesOtherInput.value = '';
                    }
                });
            }
            
            // Handle Religion "Other" option
            const religionSelect = document.getElementById('religion');
            const religionOtherInput = document.getElementById('religion_other');
            if (religionSelect && religionOtherInput) {
                religionSelect.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        religionOtherInput.style.display = 'block';
                        religionOtherInput.required = true;
                    } else {
                        religionOtherInput.style.display = 'none';
                        religionOtherInput.required = false;
                        religionOtherInput.value = '';
                    }
                });
            }
            
            // Attach form submit handler
            const form = document.getElementById('patientForm');
            if (form) {
                form.addEventListener('submit', handlePatientFormSubmit);
                attachNutritionalCalculators(form);
            }
        },
        preConfirm: () => {
            if (!validatePatientForm('patientForm')) {
                return false;
            }
            const form = document.getElementById('patientForm');
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

function showEditPatientModal(patient) {
    const parentsData = JSON.parse(document.getElementById('parentsData')?.textContent || '[]');
    const barangaysData = JSON.parse(document.getElementById('barangaysData')?.textContent || '[]');
    
    const parentsOptions = generateSelectOptions(parentsData, 'user_id', ['first_name', 'last_name'], 'Select Parent');
    const barangaysOptions = generateSelectOptions(barangaysData, 'barangay_id', ['barangay_name'], 'Select Barangay');

    let birthdate = '';
    if (patient.birthdate) {
        birthdate = typeof patient.birthdate === 'string' ? patient.birthdate.substring(0, 10) : patient.birthdate.toISOString().substring(0, 10);
    }

    let admissionDate = '';
    if (patient.date_of_admission) {
        admissionDate = typeof patient.date_of_admission === 'string' ? patient.date_of_admission.substring(0, 10) : patient.date_of_admission.toISOString().substring(0, 10);
    }

    Swal.fire({
        title: `<i class="fas fa-user-edit" style="color: #2e7d32;"></i> Edit Patient`,
        html: `
            <div class="swal-form-container">
                <!-- Patient ID Display -->
                <div style="background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%); padding: 15px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(46,125,50,0.2);">
                    <div style="color: white; font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-id-card"></i> Patient ID
                    </div>
                    <div style="color: white; font-size: 24px; font-weight: bold; letter-spacing: 1px;">
                        ${patient.custom_patient_id || 'N/A'}
                    </div>
                </div>

                <form id="editPatientForm">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-user-circle" style="color: #2e7d32;"></i> Basic Information
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_first_name">
                                    <i class="fas fa-user"></i> First Name *
                                </label>
                                <input type="text" id="edit_first_name" name="first_name" class="swal2-input" value="${patient.first_name || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_middle_name">
                                    <i class="fas fa-user"></i> Middle Name
                                </label>
                                <input type="text" id="edit_middle_name" name="middle_name" class="swal2-input" value="${patient.middle_name || ''}">
                            </div>
                            <div class="form-group">
                                <label for="edit_last_name">
                                    <i class="fas fa-user"></i> Last Name *
                                </label>
                                <input type="text" id="edit_last_name" name="last_name" class="swal2-input" value="${patient.last_name || ''}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_birthdate">
                                    <i class="fas fa-birthday-cake"></i> Birthdate *
                                    <i class="fas fa-lock" style="font-size: 11px; color: #6c757d; margin-left: 5px;"></i>
                                </label>
                                <input type="date" id="edit_birthdate" name="birthdate" class="swal2-input" value="${birthdate}" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                            </div>
                            <div class="form-group">
                                <label for="edit_sex">
                                    <i class="fas fa-venus-mars"></i> Sex *
                                    <i class="fas fa-lock" style="font-size: 11px; color: #6c757d; margin-left: 5px;"></i>
                                </label>
                                <select id="edit_sex" name="sex" class="swal2-select" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                    <option value="">Select Sex</option>
                                    <option value="Male" ${patient.sex === 'Male' ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${patient.sex === 'Female' ? 'selected' : ''}>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_contact_number">
                                    <i class="fas fa-phone"></i> Contact Number *
                                </label>
                                <input type="text" id="edit_contact_number" name="contact_number" class="swal2-input" value="${patient.contact_number || ''}" placeholder="09XX XXX XXXX" required>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Section -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-user-tag" style="color: #2e7d32;"></i> Assignment & Location
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_parent_id">
                                    <i class="fas fa-user-friends"></i> Parent / Guardian
                                </label>
                                <select id="edit_parent_id" name="parent_id" class="swal2-select">
                                    ${parentsOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_barangay_id">
                                    <i class="fas fa-map-marker-alt"></i> Barangay *
                                </label>
                                <select id="edit_barangay_id" name="barangay_id" class="swal2-select" required>
                                    ${barangaysOptions}
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_date_of_admission">
                                    <i class="fas fa-calendar-check"></i> Date of Admission *
                                </label>
                                <input type="date" id="edit_date_of_admission" name="date_of_admission" class="swal2-input" value="${admissionDate}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Health Metrics -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-heartbeat" style="color: #dc3545;"></i> Health Metrics & Status
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_weight_kg">
                                    <i class="fas fa-weight"></i> Weight (kg)
                                    <i class="fas fa-lock" style="font-size: 11px; color: #6c757d; margin-left: 5px;"></i>
                                </label>
                                <input type="number" id="edit_weight_kg" name="weight_kg" class="swal2-input" step="0.01" min="0" value="${patient.weight_kg || ''}" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                <small class="form-text" style="color: #6c757d; font-size: 11px; display: block; margin-top: 5px;">
                                    <i class="fas fa-info-circle"></i> Updated through assessments only
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="edit_height_cm">
                                    <i class="fas fa-ruler-vertical"></i> Height (cm)
                                    <i class="fas fa-lock" style="font-size: 11px; color: #6c757d; margin-left: 5px;"></i>
                                </label>
                                <input type="number" id="edit_height_cm" name="height_cm" class="swal2-input" step="0.01" min="0" value="${patient.height_cm || ''}" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                <small class="form-text" style="color: #6c757d; font-size: 11px; display: block; margin-top: 5px;">
                                    <i class="fas fa-info-circle"></i> Updated through assessments only
                                </small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_breastfeeding">
                                    <i class="fas fa-baby"></i> Breastfeeding Status
                                </label>
                                <select id="edit_breastfeeding" name="breastfeeding" class="swal2-select">
                                    <option value="">Select Status</option>
                                    <option value="Yes" ${patient.breastfeeding === 'Yes' ? 'selected' : ''}>Yes</option>
                                    <option value="No" ${patient.breastfeeding === 'No' ? 'selected' : ''}>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_edema">
                                    <i class="fas fa-disease"></i> Edema Present
                                </label>
                                <select id="edit_edema" name="edema" class="swal2-select">
                                    <option value="">Select Status</option>
                                    <option value="Yes" ${patient.edema === 'Yes' ? 'selected' : ''}>Yes</option>
                                    <option value="No" ${patient.edema === 'No' ? 'selected' : ''}>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_allergies">
                                    <i class="fas fa-allergies"></i> Allergies
                                </label>
                                <select id="edit_allergies" name="allergies" class="swal2-select">
                                    <option value="">Select Allergies</option>
                                    <option value="None">None</option>
                                    <option value="Milk/Dairy">Milk/Dairy</option>
                                    <option value="Eggs">Eggs</option>
                                    <option value="Peanuts">Peanuts</option>
                                    <option value="Tree Nuts">Tree Nuts</option>
                                    <option value="Shellfish/Seafood">Shellfish/Seafood</option>
                                    <option value="Fish">Fish</option>
                                    <option value="Soy">Soy</option>
                                    <option value="Wheat/Gluten">Wheat/Gluten</option>
                                    <option value="Other">Other (Specify)</option>
                                </select>
                                <input type="text" id="edit_allergies_other" name="allergies_other" class="swal2-input" placeholder="Please specify allergies" style="display: none; margin-top: 10px;">
                            </div>
                            <div class="form-group">
                                <label for="edit_religion">
                                    <i class="fas fa-pray"></i> Religion
                                </label>
                                <select id="edit_religion" name="religion" class="swal2-select">
                                    <option value="">Select Religion</option>
                                    <option value="Roman Catholic">Roman Catholic</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                                    <option value="Seventh-day Adventist">Seventh-day Adventist</option>
                                    <option value="Aglipayan">Aglipayan</option>
                                    <option value="Born Again Christian">Born Again Christian</option>
                                    <option value="Other">Other (Specify)</option>
                                    <option value="Prefer not to say">Prefer not to say</option>
                                </select>
                                <input type="text" id="edit_religion_other" name="religion_other" class="swal2-input" placeholder="Please specify religion" style="display: none; margin-top: 10px;">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="edit_other_medical_problems">
                                    <i class="fas fa-notes-medical"></i> Other Medical Problems
                                </label>
                                <textarea id="edit_other_medical_problems" name="other_medical_problems" class="swal2-textarea" rows="3" placeholder="Enter any other medical conditions or concerns...">${patient.other_medical_problems || ''}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Household Information -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-home" style="color: #ffc107;"></i> Household Information
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_total_household_adults">
                                    <i class="fas fa-users"></i> Total Adults
                                </label>
                                <input type="number" id="edit_total_household_adults" name="total_household_adults" class="swal2-input" min="0" value="${patient.total_household_adults || 0}" placeholder="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_total_household_children">
                                    <i class="fas fa-child"></i> Total Children
                                </label>
                                <input type="number" id="edit_total_household_children" name="total_household_children" class="swal2-input" min="0" value="${patient.total_household_children || 0}" placeholder="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_total_household_twins">
                                    <i class="fas fa-children"></i> Total Twins
                                </label>
                                <input type="number" id="edit_total_household_twins" name="total_household_twins" class="swal2-input" min="0" value="${patient.total_household_twins || 0}" placeholder="0">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <div class="form-check" style="display: flex; align-items: center; padding: 12px; background-color: #f8f9fa; border-radius: 6px; border: 1px solid #dee2e6;">
                                    <input type="checkbox" id="edit_is_4ps_beneficiary" name="is_4ps_beneficiary" class="form-check-input" ${patient.is_4ps_beneficiary ? 'checked' : ''} style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
                                    <label for="edit_is_4ps_beneficiary" class="form-check-label" style="margin: 0; cursor: pointer; font-weight: 500;">
                                        <i class="fas fa-hands-helping" style="color: #2e7d32; margin-right: 5px;"></i> 4Ps Beneficiary
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Update Patient',
        cancelButtonText: 'Cancel',
        customClass: {
            container: 'swal-patient-modal',
            popup: 'swal-patient-popup',
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary'
        },
        width: '900px',
        didOpen: () => {
            // Set selected values for dropdowns
            if (patient.parent_id) document.getElementById('edit_parent_id').value = patient.parent_id;
            if (patient.barangay_id) document.getElementById('edit_barangay_id').value = patient.barangay_id;
            
            // Set allergies value and handle Other option
            const editAllergiesSelect = document.getElementById('edit_allergies');
            const editAllergiesOtherInput = document.getElementById('edit_allergies_other');
            const commonAllergies = ['None', 'Milk/Dairy', 'Eggs', 'Peanuts', 'Tree Nuts', 'Shellfish/Seafood', 'Fish', 'Soy', 'Wheat/Gluten'];
            if (patient.allergies) {
                if (commonAllergies.includes(patient.allergies)) {
                    editAllergiesSelect.value = patient.allergies;
                } else {
                    editAllergiesSelect.value = 'Other';
                    editAllergiesOtherInput.value = patient.allergies;
                    editAllergiesOtherInput.style.display = 'block';
                }
            }
            
            // Set religion value and handle Other option
            const editReligionSelect = document.getElementById('edit_religion');
            const editReligionOtherInput = document.getElementById('edit_religion_other');
            const commonReligions = ['Roman Catholic', 'Islam', 'Iglesia ni Cristo', 'Seventh-day Adventist', 'Aglipayan', 'Born Again Christian', 'Prefer not to say'];
            if (patient.religion) {
                if (commonReligions.includes(patient.religion)) {
                    editReligionSelect.value = patient.religion;
                } else {
                    editReligionSelect.value = 'Other';
                    editReligionOtherInput.value = patient.religion;
                    editReligionOtherInput.style.display = 'block';
                }
            }
            
            // Handle Edit Allergies "Other" option
            if (editAllergiesSelect && editAllergiesOtherInput) {
                editAllergiesSelect.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        editAllergiesOtherInput.style.display = 'block';
                        editAllergiesOtherInput.required = true;
                    } else {
                        editAllergiesOtherInput.style.display = 'none';
                        editAllergiesOtherInput.required = false;
                        editAllergiesOtherInput.value = '';
                    }
                });
            }
            
            // Handle Edit Religion "Other" option
            if (editReligionSelect && editReligionOtherInput) {
                editReligionSelect.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        editReligionOtherInput.style.display = 'block';
                        editReligionOtherInput.required = true;
                    } else {
                        editReligionOtherInput.style.display = 'none';
                        editReligionOtherInput.required = false;
                        editReligionOtherInput.value = '';
                    }
                });
            }
            
            // Phone number formatting and validation
            const contactInput = document.getElementById('edit_contact_number');
            if (contactInput) {
                contactInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.substring(0, 11);
                    this.value = value;
                    
                    // Real-time validation feedback
                    if (value.length === 11 && value.startsWith('09')) {
                        this.style.borderColor = '#28a745';
                    } else if (value.length > 0) {
                        this.style.borderColor = '#ffc107';
                    } else {
                        this.style.borderColor = '';
                    }
                });
            }
            
            // Real-time validation for editable required fields
            const requiredFields = ['edit_contact_number', 'edit_barangay_id', 'edit_date_of_admission'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && !field.disabled) {
                    field.addEventListener('blur', function() {
                        if (this.value.trim() === '') {
                            this.style.borderColor = '#dc3545';
                        } else {
                            this.style.borderColor = '#28a745';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 1000);
                        }
                    });
                    
                    field.addEventListener('input', function() {
                        if (this.value.trim() !== '' && this.style.borderColor === 'rgb(220, 53, 69)') {
                            this.style.borderColor = '';
                        }
                    });
                }
            });
            
            // Dropdown change feedback
            ['edit_parent_id', 'edit_barangay_id'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('change', function() {
                        if (this.value) {
                            this.style.borderColor = '#28a745';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 1000);
                        }
                    });
                }
            });
        },
        preConfirm: () => {
            const form = document.getElementById('editPatientForm');
            const formData = new FormData();
            
            formData.append('_method', 'PUT');

            // Get all form fields (including disabled ones for required fields)
            const fields = ['first_name', 'middle_name', 'last_name', 'sex', 
                            'parent_id', 'barangay_id', 'contact_number', 'date_of_admission', 
                            'total_household_adults', 'total_household_children', 'total_household_twins', 
                            'is_4ps_beneficiary', 'breastfeeding', 'edema', 'other_medical_problems'];

            fields.forEach(field => {
                const element = form.querySelector(`[name="${field}"]`);
                if (element) {
                    if (element.type === 'checkbox') {
                        formData.append(field, element.checked ? '1' : '0');
                    } else {
                        formData.append(field, element.value || '');
                    }
                }
            });
            
            // Handle allergies with Other option
            const allergiesSelect = form.querySelector('[name="allergies"]');
            const allergiesOther = form.querySelector('[name="allergies_other"]');
            if (allergiesSelect) {
                if (allergiesSelect.value === 'Other' && allergiesOther && allergiesOther.value) {
                    formData.append('allergies', allergiesOther.value);
                } else {
                    formData.append('allergies', allergiesSelect.value || '');
                }
            }
            
            // Handle religion with Other option
            const religionSelect = form.querySelector('[name="religion"]');
            const religionOther = form.querySelector('[name="religion_other"]');
            if (religionSelect) {
                if (religionSelect.value === 'Other' && religionOther && religionOther.value) {
                    formData.append('religion', religionOther.value);
                } else {
                    formData.append('religion', religionSelect.value || '');
                }
            }

            // Show loading
            Swal.showLoading();

            return fetch(`/nutritionist/patients/${patient.patient_id}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(async response => {
                let data;
                try {
                    const text = await response.text();
                    console.log('Update URL:', `/nutritionist/patients/${patient.patient_id}`);
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers.get('content-type'));
                    console.log('Update response:', text.substring(0, 500));
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Parse error:', e);
                    Swal.fire({
                        title: 'Error',
                        text: `Server error (${response.status}). The page returned HTML instead of JSON. Please check if you're still logged in.`,
                        confirmButtonColor: '#2e7d32'
                    });
                    return null;
                }
                if (!response.ok || !data.success) {
                    Swal.fire({
                        title: 'Error',
                        text: data && data.message ? data.message : 'Error updating patient',
                        confirmButtonColor: '#2e7d32'
                    });
                    return null;
                }
                Swal.fire({
                    title: 'Success!',
                    text: 'Patient updated successfully!',
                    timer: 2000,
                    showConfirmButton: false,
                    confirmButtonColor: '#2e7d32'
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error updating patient',
                    confirmButtonColor: '#2e7d32'
                });
            });
        }
    });
}

function editPatient(patientId) {
    isEditing = true;
    currentPatientId = patientId;
    
    Swal.fire({
        title: 'Loading...',
        html: 'Please wait while we load the patient data.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/nutritionist/patients/${patientId}`)
    .then(async response => {
        let data;
        try {
            const text = await response.text();
            console.log('Raw response:', text);
            data = JSON.parse(text);
        } catch (e) {
            console.error('Parse error:', e);
            Swal.fire({
                title: 'Error',
                text: 'Invalid server response. Please contact support.',
                confirmButtonColor: '#2e7d32'
            });
            return null;
        }
        if (!response.ok || !data.success) {
            Swal.fire({
                title: 'Error',
                text: data && data.message ? data.message : 'Error loading patient data',
                confirmButtonColor: '#2e7d32'
            });
            return null;
        }
        showEditPatientModal(data.patient);
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error loading patient data',
            confirmButtonColor: '#2e7d32'
        });
    });
}

// Helper function to generate select options
function generateSelectOptions(data, valueKey, textKeys, emptyText = 'Select') {
    let options = `<option value="">${emptyText}</option>`;
    data.forEach(item => {
        const text = textKeys.map(key => item[key]).filter(Boolean).join(' ');
        options += `<option value="${item[valueKey]}">${text}</option>`;
    });
    return options;
}

function viewPatient(patientId) {
    currentPatientId = patientId;
    
    Swal.fire({
        title: 'Loading...',
        html: 'Please wait while we load the patient details.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/nutritionist/patients/${patientId}`)
    .then(async response => {
        let data;
        try {
            const text = await response.text();
            console.log('Raw response:', text.substring(0, 200));
            data = JSON.parse(text);
        } catch (e) {
            console.error('Parse error:', e);
            Swal.fire({
                title: 'Error',
                text: 'Invalid server response. Please contact support.',
                confirmButtonColor: '#2e7d32'
            });
            return null;
        }
        if (!response.ok || !data.success) {
            Swal.fire({
                title: 'Error',
                text: data && data.message ? data.message : 'Error loading patient details',
                confirmButtonColor: '#2e7d32'
            });
            return null;
        }
        showViewPatientModal(data.patient);
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error loading patient details',
            confirmButtonColor: '#2e7d32'
        });
    });
}

// Helper function to generate select options
function generateSelectOptions(data, valueKey, textKeys, emptyText = 'Select') {
    let options = `<option value="">${emptyText}</option>`;
    data.forEach(item => {
        const text = textKeys.map(key => item[key]).filter(Boolean).join(' ');
        options += `<option value="${item[valueKey]}">${text}</option>`;
    });
    return options;
}

function showViewPatientModal(patient) {
    const parentName = patient.parent ? `${patient.parent.first_name} ${patient.parent.last_name}` : 'Not assigned';
    const barangayName = patient.barangay ? patient.barangay.barangay_name : 'Not assigned';

    Swal.fire({
        title: '<i class="fas fa-user-circle"></i> Patient Details',
        html: `
            <div class="swal-form-container">
                <!-- Patient ID Display -->
                <div style="background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%); padding: 15px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(46,125,50,0.2);">
                    <div style="color: white; font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-id-card"></i> Patient ID
                    </div>
                    <div style="color: white; font-size: 24px; font-weight: bold; letter-spacing: 1px;">
                        ${patient.custom_patient_id || 'N/A'}
                    </div>
                </div>
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h6 class="section-title">
                        <i class="fas fa-user-circle" style="color: #2e7d32;"></i> Basic Information
                    </h6>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Full Name</label>
                            <div class="detail-value-display">${patient.first_name} ${patient.middle_name || ''} ${patient.last_name}</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-birthday-cake"></i> Birthdate</label>
                            <div class="detail-value-display">${patient.birthdate ? new Date(patient.birthdate).toLocaleDateString() : 'N/A'}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Age</label>
                            <div class="detail-value-display">${patient.age_months} months</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-venus-mars"></i> Sex</label>
                            <div class="detail-value-display">${patient.sex || 'N/A'}</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Contact Number</label>
                            <div class="detail-value-display">${patient.contact_number || 'N/A'}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Date of Admission</label>
                            <div class="detail-value-display">${new Date(patient.date_of_admission).toLocaleDateString()}</div>
                        </div>
                    </div>
                </div>

                <!-- Assignment & Location -->
                <div class="form-section">
                    <h6 class="section-title">
                        <i class="fas fa-user-tag" style="color: #2e7d32;"></i> Assignment & Location
                    </h6>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user-friends"></i> Parent / Guardian</label>
                            <div class="detail-value-display">${parentName}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt"></i> Barangay</label>
                            <div class="detail-value-display">${barangayName}</div>
                        </div>
                    </div>
                </div>

                <!-- Health Metrics -->
                <div class="form-section">
                    <h6 class="section-title">
                        <i class="fas fa-heartbeat" style="color: #dc3545;"></i> Health Metrics & Status
                    </h6>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-weight"></i> Weight</label>
                            <div class="detail-value-display">${patient.weight_kg} kg</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-ruler-vertical"></i> Height</label>
                            <div class="detail-value-display">${patient.height_cm} cm</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-baby"></i> Breastfeeding Status</label>
                            <div class="detail-value-display">${patient.breastfeeding || 'Not specified'}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-disease"></i> Edema Present</label>
                            <div class="detail-value-display">${patient.edema || 'Not specified'}</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-allergies"></i> Allergies</label>
                            <div class="detail-value-display">${patient.allergies || 'None reported'}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-pray"></i> Religion</label>
                            <div class="detail-value-display">${patient.religion || 'Not specified'}</div>
                        </div>
                    </div>
                    ${patient.other_medical_problems ? `
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label><i class="fas fa-notes-medical"></i> Other Medical Problems</label>
                            <div class="detail-value-display">${patient.other_medical_problems}</div>
                        </div>
                    </div>
                    ` : ''}
                </div>

                <!-- Household Information -->
                <div class="form-section">
                    <h6 class="section-title">
                        <i class="fas fa-home" style="color: #ffc107;"></i> Household Information
                    </h6>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Total Adults</label>
                            <div class="detail-value-display">${patient.total_household_adults || 0}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-child"></i> Total Children</label>
                            <div class="detail-value-display">${patient.total_household_children || 0}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-children"></i> Total Twins</label>
                            <div class="detail-value-display">${patient.total_household_twins || 0}</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label><i class="fas fa-hands-helping"></i> 4Ps Beneficiary</label>
                            <div class="detail-value-display">
                                ${patient.is_4ps_beneficiary ? 
                                    '<span class="detail-badge badge-success"><i class="fas fa-check"></i> Yes</span>' : 
                                    '<span class="detail-badge badge-warning"><i class="fas fa-times"></i> No</span>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-times-circle"></i> Close',
        confirmButtonColor: '#2e7d32',
        customClass: {
            container: 'swal-patient-modal',
            popup: 'swal-patient-popup swal-view-patient-popup',
            htmlContainer: 'swal-view-patient-content',
            confirmButton: 'btn btn-secondary'
        },
        width: '950px'
    });
}

// Delete function removed - only admins can permanently delete patients
// Nutritionists should use the archive functionality to maintain medical record integrity
function deletePatient(patientId) {
    Swal.fire({
        title: 'Delete Not Available',
        text: 'Only administrators can permanently delete patient records. Please use the Archive function to remove patients from the active list while preserving medical records.',
        confirmButtonColor: '#2e7d32'
    });
    return;
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

// Validate patient form
function validatePatientForm(formId) {
    const form = document.getElementById(formId);
    const requiredFields = form.querySelectorAll('[required]:not([disabled])');
    let isValid = true;
    let errorMessages = [];
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            isValid = false;
            
            // Get field label
            const label = form.querySelector(`label[for="${field.id}"]`);
            const fieldName = label ? label.textContent.replace('*', '').trim() : field.name;
            errorMessages.push(fieldName);
        } else {
            field.style.borderColor = '';
        }
    });
    
    // Validate contact number
    const contactInput = document.getElementById('contact_number');
    if (contactInput && contactInput.value) {
        const contactValue = contactInput.value.replace(/\D/g, '');
        if (contactValue.length !== 11 || !contactValue.startsWith('09')) {
            contactInput.style.borderColor = '#dc3545';
            isValid = false;
            if (!errorMessages.includes('Contact Number')) {
                errorMessages.push('Contact Number (must be 11 digits starting with 09)');
            }
        }
    }
    
    // Validate weight and height
    const weightInput = document.getElementById('weight_kg');
    const heightInput = document.getElementById('height_cm');
    
    if (weightInput && !weightInput.disabled && parseFloat(weightInput.value) <= 0) {
        weightInput.style.borderColor = '#dc3545';
        isValid = false;
        if (!errorMessages.includes('Weight')) {
            errorMessages.push('Weight (must be greater than 0)');
        }
    }
    
    if (heightInput && !heightInput.disabled && parseFloat(heightInput.value) <= 0) {
        heightInput.style.borderColor = '#dc3545';
        isValid = false;
        if (!errorMessages.includes('Height')) {
            errorMessages.push('Height (must be greater than 0)');
        }
    }
    
    if (!isValid) {
        let message = 'Please fill in all required fields correctly:';
        if (errorMessages.length > 0) {
            message += '\n\n• ' + errorMessages.join('\n• ');
        }
        Swal.showValidationMessage(message);
    }
    
    return isValid;
}
