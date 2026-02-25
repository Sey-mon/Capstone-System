/**
 * Admin Patients Management JavaScript
 * Handles patient CRUD operations, modal interactions, filtering, sorting, and view switching
 */

// Global variables
let currentPatientId = null;
let currentView = 'table';
let sortColumn = null;
let sortDirection = 'asc';
let allPatients = [];

// Modal functions
function showAddPatientModal() {
    document.getElementById('addPatientModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeAddPatientModal() {
    document.getElementById('addPatientModal').classList.remove('show');
    document.body.style.overflow = 'auto';
    document.getElementById('addPatientForm').reset();
}

function showEditPatientModal() {
    document.getElementById('editPatientModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeEditPatientModal() {
    document.getElementById('editPatientModal').classList.remove('show');
    document.body.style.overflow = 'auto';
    document.getElementById('editPatientForm').reset();
    currentPatientId = null;
}

function showViewPatientModal() {
    document.getElementById('viewPatientModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeViewPatientModal() {
    document.getElementById('viewPatientModal').classList.remove('show');
    document.body.style.overflow = 'auto';
    currentPatientId = null;
}

// Patient CRUD operations
function savePatient() {
    const form = document.getElementById('addPatientForm');
    const formData = new FormData(form);

    // Basic validation
    if (!validatePatientForm(form)) {
        return;
    }

    // Show loading state
    const saveBtn = document.querySelector('#addPatientModal .btn-primary');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;

    fetch('/admin/patients', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showEnhancedNotification('Patient added successfully!', 'success');
            closeAddPatientModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showEnhancedNotification(data.message || 'Error adding patient', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showEnhancedNotification('Error adding patient', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function editPatient(patientId) {
    currentPatientId = patientId;
    
    fetch(`/admin/patients/${patientId}`)
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (e) {
            showEnhancedNotification('Invalid server response. Please contact support.', 'error');
            return null;
        }
        if (!response.ok || !data.success) {
            showEnhancedNotification(data && data.message ? data.message : 'Error loading patient data', 'error');
            return null;
        }
        populateEditForm(data.patient);
        // Update modal title with patient ID
        const modalTitle = document.querySelector('#editPatientModal .modal-title');
        if (modalTitle && data.patient.custom_patient_id) {
            modalTitle.textContent = 'Edit Patient - ID: ' + data.patient.custom_patient_id;
        }
        showEditPatientModal();
    })
    .catch(error => {
        console.error('Error:', error);
        showEnhancedNotification('Error loading patient data', 'error');
    });
}

function updatePatient() {
    if (!currentPatientId) return;

    const form = document.getElementById('editPatientForm');
    const formData = new FormData(form);
    
    // Add method override for PUT request
    formData.append('_method', 'PUT');

    // Basic validation
    if (!validatePatientForm(form)) {
        return;
    }

    // Show loading state
    const updateBtn = document.querySelector('#editPatientModal .btn-primary');
    const originalText = updateBtn.innerHTML;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    updateBtn.disabled = true;

    fetch(`/admin/patients/${currentPatientId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showEnhancedNotification('Patient updated successfully!', 'success');
            closeEditPatientModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showEnhancedNotification(data.message || 'Error updating patient', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showEnhancedNotification('Error updating patient', 'error');
    })
    .finally(() => {
        updateBtn.innerHTML = originalText;
        updateBtn.disabled = false;
    });
}

function viewPatient(patientId) {
    currentPatientId = patientId;
    
    // Show loading state
    document.getElementById('patientDetailsContent').innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading patient details...</div>';
    showViewPatientModal();
    
    fetch(`/admin/patients/${patientId}`)
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (e) {
            document.getElementById('patientDetailsContent').innerHTML = '<div class="error-message">Invalid server response. Please contact support.</div>';
            return null;
        }
        if (!response.ok || !data.success) {
            document.getElementById('patientDetailsContent').innerHTML = `<div class="error-message">${data && data.message ? data.message : 'Error loading patient details'}</div>`;
            return null;
        }
        displayPatientDetails(data.patient);
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('patientDetailsContent').innerHTML = '<div class="error-message">Error loading patient details</div>';
    });
}

function deletePatient(patientId) {
    if (!confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
        return;
    }

    fetch(`/admin/patients/${patientId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showEnhancedNotification('Patient deleted successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showEnhancedNotification(data.message || 'Error deleting patient', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showEnhancedNotification('Error deleting patient', 'error');
    });
}

// Helper functions
// Helper functions
function populateEditForm(patient) {
    document.getElementById('edit_patient_id').value = patient.patient_id;
    document.getElementById('edit_first_name').value = patient.first_name || '';
    document.getElementById('edit_middle_name').value = patient.middle_name || '';
    document.getElementById('edit_last_name').value = patient.last_name || '';
    document.getElementById('edit_parent_id').value = patient.parent_id || '';
    document.getElementById('edit_nutritionist_id').value = patient.nutritionist_id || '';
    document.getElementById('edit_barangay_id').value = patient.barangay_id || '';
    document.getElementById('edit_contact_number').value = patient.contact_number || '';
    
    // Set birthdate
    let birthdate = '';
    if (patient.birthdate) {
        if (typeof patient.birthdate === 'string') {
            birthdate = patient.birthdate.substring(0, 10);
        } else if (patient.birthdate instanceof Date) {
            birthdate = patient.birthdate.toISOString().substring(0, 10);
        }
    }
    document.getElementById('edit_birthdate').value = birthdate;
    document.getElementById('edit_sex').value = patient.sex || '';
    
    // Ensure date is in YYYY-MM-DD format for input type="date"
    let admissionDate = '';
    if (patient.date_of_admission) {
        if (typeof patient.date_of_admission === 'string') {
            // If already in YYYY-MM-DD or ISO format
            admissionDate = patient.date_of_admission.substring(0, 10);
        } else if (patient.date_of_admission instanceof Date) {
            // If it's a Date object
            admissionDate = patient.date_of_admission.toISOString().substring(0, 10);
        }
    }
    document.getElementById('edit_date_of_admission').value = admissionDate;
    document.getElementById('edit_weight_kg').value = patient.weight_kg || '';
    document.getElementById('edit_height_cm').value = patient.height_cm || '';
    document.getElementById('edit_total_household_adults').value = patient.total_household_adults || 0;
    document.getElementById('edit_total_household_children').value = patient.total_household_children || 0;
    document.getElementById('edit_total_household_twins').value = patient.total_household_twins || 0;
    document.getElementById('edit_is_4ps_beneficiary').checked = patient.is_4ps_beneficiary || false;
    document.getElementById('edit_breastfeeding').value = patient.breastfeeding || '';
    document.getElementById('edit_edema').value = patient.edema || '';
    document.getElementById('edit_other_medical_problems').value = patient.other_medical_problems || '';
}

function displayPatientDetails(patient) {
    const parentName = patient.parent ? `${patient.parent.first_name} ${patient.parent.last_name}` : 'Not assigned';
    const nutritionistName = patient.nutritionist ? `${patient.nutritionist.first_name} ${patient.nutritionist.last_name}` : 'Not assigned';
    const barangayName = patient.barangay ? patient.barangay.barangay_name : 'Not assigned';
    
    const html = `
        <div class="patient-details-grid">
            <div class="detail-section">
                <h6>Patient ID</h6>
                <div class="detail-group">
                    <div class="detail-value" style="font-size: 1.2em; color: #007bff; font-weight: bold;">
                        ${patient.custom_patient_id || 'N/A'}
                    </div>
                </div>
            </div>
            <div class="detail-section">
                <h6>Basic Information</h6>
                <div class="detail-group">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value">${patient.first_name} ${patient.middle_name || ''} ${patient.last_name}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Age</div>
                    <div class="detail-value">${patient.age_months} months</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Sex</div>
                    <div class="detail-value">${patient.sex || 'N/A'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Contact Number</div>
                    <div class="detail-value">${patient.contact_number || 'N/A'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Date of Admission</div>
                    <div class="detail-value">${new Date(patient.date_of_admission).toLocaleDateString()}</div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Assignment Information</h6>
                <div class="detail-group">
                    <div class="detail-label">Parent</div>
                    <div class="detail-value">${parentName}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">BNS</div>
                    <div class="detail-value">${nutritionistName}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Barangay</div>
                    <div class="detail-value">${barangayName}</div>
                </div>
            </div>
            
            <div class="detail-section">
                <h6>Health Information</h6>
                <div class="detail-group">
                    <div class="detail-label">Weight</div>
                    <div class="detail-value">${patient.weight_kg} kg</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Height</div>
                    <div class="detail-value">${patient.height_cm} cm</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Weight for Age</div>
                    <div class="detail-value">${patient.latest_assessment?.weight_for_age || 'Not assessed'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Height for Age</div>
                    <div class="detail-value">${patient.latest_assessment?.height_for_age || 'Not assessed'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">BMI for Age</div>
                    <div class="detail-value">${patient.latest_assessment?.bmi_for_age || 'Not assessed'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">4Ps Beneficiary</div>
                    <div class="detail-value">${patient.is_4ps_beneficiary ? 'Yes' : 'No'}</div>
                </div>
            </div>
            
            ${patient.other_medical_problems ? `
            <div class="detail-section">
                <h6>Medical Notes</h6>
                <div class="detail-group">
                    <div class="detail-label">Other Medical Problems</div>
                    <div class="detail-value">${patient.other_medical_problems}</div>
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('patientDetailsContent').innerHTML = html;
}

function validatePatientForm(form) {
    const requiredFields = ['first_name', 'last_name', 'barangay_id', 'contact_number', 'age_months', 'sex', 'date_of_admission', 'weight_kg', 'height_cm'];
    let isValid = true;
    
    requiredFields.forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (field && !field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else if (field) {
            field.classList.remove('is-invalid');
        }
    });

    // Additional validations
    const ageField = form.querySelector('[name="age_months"]');
    if (ageField && parseInt(ageField.value) < 0) {
        ageField.classList.add('is-invalid');
        isValid = false;
    }

    const weightField = form.querySelector('[name="weight_kg"]');
    if (weightField && parseFloat(weightField.value) <= 0) {
        weightField.classList.add('is-invalid');
        isValid = false;
    }

    const heightField = form.querySelector('[name="height_cm"]');
    if (heightField && parseFloat(heightField.value) <= 0) {
        heightField.classList.add('is-invalid');
        isValid = false;
    }
    
    if (!isValid) {
        showEnhancedNotification('Please fill in all required fields with valid values', 'error');
    }
    
    return isValid;
}

function calculateAge(birthDate) {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

function getStatusClass(status) {
    const statusMap = {
        'active': 'success',
        'inactive': 'secondary',
        'critical': 'danger',
        'monitoring': 'warning'
    };
    return statusMap[status?.toLowerCase()] || 'info';
}

function editPatientFromView() {
    if (currentPatientId) {
        closeViewPatientModal();
        editPatient(currentPatientId);
    }
}

// Enhanced Features Functions

function initializeEnhancedFeatures() {
    // Set initial patient count
    updatePatientCounts();
    
    // Setup sorting functionality
    setupSorting();
    
    // Initialize view
    switchView('table');
    
    console.log('Enhanced Admin Patients features initialized');
}

function refreshPatientData() {
    cachePatientData();
    updatePatientCounts();
    console.log('Patient data refreshed');
}

function cachePatientData() {
    // Cache all patient data for filtering
    const tableRows = document.querySelectorAll('#patientsTableBody .patient-row');
    const gridCards = document.querySelectorAll('#patientsGrid .patient-card');
    
    allPatients = [];
    
    tableRows.forEach((row, index) => {
        const gridCard = gridCards[index];
        if (gridCard) {
            allPatients.push({
                tableElement: row,
                gridElement: gridCard,
                data: {
                    name: row.dataset.name || '',
                    age: parseInt(row.dataset.age) || 0,
                    gender: row.dataset.gender || '',
                    barangay: row.dataset.barangay || '',
                    parent: row.dataset.parent || '',
                    nutritionist: row.dataset.nutritionist || '',
                    contact: row.dataset.contact || ''
                }
            });
        }
    });
    
    console.log(`Cached ${allPatients.length} patients`);
}

function setupEventListeners() {
    // Filter inputs
    const searchInput = document.getElementById('searchPatient');
    const barangayFilter = document.getElementById('filterBarangay');
    const genderFilter = document.getElementById('filterGender');
    const ageRangeFilter = document.getElementById('filterAgeRange');
    const nutritionistFilter = document.getElementById('filterNutritionist');
    
    if (searchInput) searchInput.addEventListener('input', debounce(filterPatients, 500));
    if (barangayFilter) barangayFilter.addEventListener('change', filterPatients);
    if (genderFilter) genderFilter.addEventListener('change', filterPatients);
    if (ageRangeFilter) ageRangeFilter.addEventListener('change', filterPatients);
    if (nutritionistFilter) nutritionistFilter.addEventListener('change', filterPatients);

    // "Go to page" jump boxes — preserve current filter params
    function jumpToPage(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const page = parseInt(input.value);
        if (isNaN(page) || page < 1) return;
        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        window.location.href = window.location.pathname + '?' + params.toString();
    }

    const jumpBtn = document.getElementById('jumpToPage');
    const gridJumpBtn = document.getElementById('gridJumpToPage');
    if (jumpBtn) jumpBtn.addEventListener('click', () => jumpToPage('pageJump'));
    if (gridJumpBtn) gridJumpBtn.addEventListener('click', () => jumpToPage('gridPageJump'));

    // Allow Enter key in jump inputs
    const pageJumpInput = document.getElementById('pageJump');
    const gridPageJumpInput = document.getElementById('gridPageJump');
    if (pageJumpInput) pageJumpInput.addEventListener('keydown', e => { if (e.key === 'Enter') jumpToPage('pageJump'); });
    if (gridPageJumpInput) gridPageJumpInput.addEventListener('keydown', e => { if (e.key === 'Enter') jumpToPage('gridPageJump'); });

    // Button event listeners
    setupButtonEventListeners();
}

function setupButtonEventListeners() {
    // Clear filters button
    const clearFiltersBtn = document.querySelector('.filters-header .btn-outline');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearAllFilters();
        });
    }
    
    // Refresh button
    const refreshBtn = document.querySelector('.filters-header .btn-secondary');
    if (refreshBtn && refreshBtn.textContent.includes('Refresh')) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.reload();
        });
    }
    
    // View toggle buttons
    const tableViewBtn = document.querySelector('[data-view="table"]');
    const gridViewBtn = document.querySelector('[data-view="grid"]');
    
    if (tableViewBtn) {
        tableViewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            switchView('table');
        });
    }
    
    if (gridViewBtn) {
        gridViewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            switchView('grid');
        });
    }
    
    // Add patient buttons
    const addPatientBtns = document.querySelectorAll('.btn-primary');
    addPatientBtns.forEach(btn => {
        if (btn.textContent.includes('Add Patient') || btn.textContent.includes('Add First Patient')) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                showAddPatientModal();
            });
        }
    });
    
    // Action buttons in table rows
    setupActionButtons();
    
    // Modal close buttons and form buttons
    setupModalEventListeners();
}

function setupActionButtons() {
    // View patient buttons
    const viewBtns = document.querySelectorAll('.btn-outline-primary, .btn-primary');
    viewBtns.forEach(btn => {
        if (btn.title === 'View Details' && btn.hasAttribute('data-patient-id')) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const patientId = parseInt(this.getAttribute('data-patient-id'));
                viewPatient(patientId);
            });
        }
    });
    
    // Edit patient buttons
    const editBtns = document.querySelectorAll('.btn-outline-warning, .btn-warning');
    editBtns.forEach(btn => {
        if ((btn.title === 'Edit Patient' || btn.title === 'Edit') && btn.hasAttribute('data-patient-id')) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const patientId = parseInt(this.getAttribute('data-patient-id'));
                editPatient(patientId);
            });
        }
    });
    
    // Delete patient buttons
    const deleteBtns = document.querySelectorAll('.btn-outline-danger, .btn-danger');
    deleteBtns.forEach(btn => {
        if ((btn.title === 'Delete Patient' || btn.title === 'Delete') && btn.hasAttribute('data-patient-id')) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const patientId = parseInt(this.getAttribute('data-patient-id'));
                deletePatient(patientId);
            });
        }
    });
}

function setupModalEventListeners() {
    // Add patient modal
    const addModalClose = document.querySelector('#addPatientModal .modal-close');
    if (addModalClose) {
        addModalClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeAddPatientModal();
        });
    }
    
    const addModalCancelBtn = document.querySelector('#addPatientModal .btn-secondary');
    if (addModalCancelBtn) {
        addModalCancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeAddPatientModal();
        });
    }
    
    const addModalSaveBtn = document.querySelector('#addPatientModal .btn-primary');
    if (addModalSaveBtn && addModalSaveBtn.textContent.includes('Save')) {
        addModalSaveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            savePatient();
        });
    }
    
    // Edit patient modal
    const editModalClose = document.querySelector('#editPatientModal .modal-close');
    if (editModalClose) {
        editModalClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeEditPatientModal();
        });
    }
    
    const editModalCancelBtn = document.querySelector('#editPatientModal .btn-secondary');
    if (editModalCancelBtn) {
        editModalCancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeEditPatientModal();
        });
    }
    
    const editModalUpdateBtn = document.querySelector('#editPatientModal .btn-primary');
    if (editModalUpdateBtn && editModalUpdateBtn.textContent.includes('Update')) {
        editModalUpdateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            updatePatient();
        });
    }
    
    // View patient modal
    const viewModalClose = document.querySelector('#viewPatientModal .modal-close');
    if (viewModalClose) {
        viewModalClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeViewPatientModal();
        });
    }
    
    const viewModalCloseBtn = document.querySelector('#viewPatientModal .btn-secondary');
    if (viewModalCloseBtn) {
        viewModalCloseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeViewPatientModal();
        });
    }
}

function setupSorting() {
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            handleSort(column);
        });
    });
}

// Server-side filtering function — reloads the page with filter params so all
// paginated pages are searched, not just the current DOM.
function filterPatients() {
    const searchInput = document.getElementById('searchPatient');
    const barangayFilter = document.getElementById('filterBarangay');
    const genderFilter = document.getElementById('filterGender');
    const ageRangeFilter = document.getElementById('filterAgeRange');
    const nutritionistFilter = document.getElementById('filterNutritionist');

    const params = new URLSearchParams();

    const search = searchInput ? searchInput.value.trim() : '';
    const barangay = barangayFilter ? barangayFilter.value : '';
    const gender = genderFilter ? genderFilter.value : '';
    const ageRange = ageRangeFilter ? ageRangeFilter.value : '';
    const nutritionist = nutritionistFilter ? nutritionistFilter.value : '';

    if (search) params.set('search', search);
    if (barangay) params.set('barangay', barangay);
    if (gender) params.set('gender', gender);
    if (ageRange) params.set('age_range', ageRange);
    if (nutritionist) params.set('nutritionist', nutritionist);

    // Always go back to page 1 when filters change
    const baseUrl = window.location.pathname;
    window.location.href = baseUrl + (params.toString() ? '?' + params.toString() : '');
}

function isInAgeRange(age, range) {
    switch(range) {
        case '0-12':
            return age >= 0 && age <= 12;
        case '13-24':
            return age >= 13 && age <= 24;
        case '25-36':
            return age >= 25 && age <= 36;
        case '37-48':
            return age >= 37 && age <= 48;
        case '49+':
            return age >= 49;
        default:
            return true;
    }
}

function handleSort(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }

    updateSortIcons();
    sortPatients();
}

function sortPatients() {
    const visiblePatients = allPatients.filter(patient => 
        !patient.tableElement.classList.contains('patient-hidden')
    );

    visiblePatients.sort((a, b) => {
        let aValue, bValue;

        switch(sortColumn) {
            case 'name':
                aValue = a.data.name;
                bValue = b.data.name;
                break;
            case 'age':
                aValue = a.data.age;
                bValue = b.data.age;
                break;
            case 'gender':
                aValue = a.data.gender;
                bValue = b.data.gender;
                break;
            case 'barangay':
                aValue = a.data.barangay;
                bValue = b.data.barangay;
                break;
            case 'parent':
                aValue = a.data.parent;
                bValue = b.data.parent;
                break;
            case 'nutritionist':
                aValue = a.data.nutritionist;
                bValue = b.data.nutritionist;
                break;
            default:
                return 0;
        }

        if (typeof aValue === 'string') {
            aValue = aValue.toLowerCase();
            bValue = bValue.toLowerCase();
        }

        if (aValue < bValue) return sortDirection === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortDirection === 'asc' ? 1 : -1;
        return 0;
    });

    // Reorder elements
    const tableBody = document.getElementById('patientsTableBody');
    const gridContainer = document.getElementById('patientsGrid');

    if (tableBody && gridContainer) {
        visiblePatients.forEach(patient => {
            tableBody.appendChild(patient.tableElement);
            gridContainer.appendChild(patient.gridElement);
        });
    }
}

function updateSortIcons() {
    // Reset all sort icons
    document.querySelectorAll('.sortable i').forEach(icon => {
        icon.className = 'fas fa-sort';
    });

    // Update active sort icon
    if (sortColumn) {
        const activeHeader = document.querySelector(`[data-sort="${sortColumn}"] i`);
        if (activeHeader) {
            activeHeader.className = sortDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }
    }
}

function switchView(view) {
    currentView = view;
    
    // Update view buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const viewBtn = document.querySelector(`[data-view="${view}"]`);
    if (viewBtn) viewBtn.classList.add('active');
    
    // Show/hide view containers using CSS classes
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    
    if (tableView && gridView) {
        if (view === 'table') {
            tableView.classList.add('active');
            tableView.classList.remove('grid-view-hidden');
            gridView.classList.remove('active');
            gridView.classList.add('grid-view-hidden');
        } else {
            gridView.classList.add('active');
            gridView.classList.remove('grid-view-hidden');
            tableView.classList.remove('active');
            tableView.classList.add('grid-view-hidden');
        }
    }
}

function clearAllFilters() {
    // Navigate to the base URL without any filter params
    window.location.href = window.location.pathname;
}

function updatePatientCounts() {
    // Get count from DOM elements if allPatients is not populated yet
    let total = allPatients.length;
    if (total === 0) {
        const tableRows = document.querySelectorAll('#patientsTableBody .patient-row');
        total = tableRows.length;
    }
    
    const totalElement = document.getElementById('totalPatients');
    if (totalElement) {
        totalElement.textContent = total;
    }
    
    const filteredCountElement = document.getElementById('filteredCount');
    if (filteredCountElement) {
        filteredCountElement.classList.add('filtered-count-hidden');
        filteredCountElement.classList.remove('filtered-count-visible');
    }
}

function updateFilteredCounts(visible) {
    const total = allPatients.length;
    const totalElement = document.getElementById('totalPatients');
    if (totalElement) totalElement.textContent = total;
    
    const filteredCountElement = document.getElementById('filteredCount');
    const visibleElement = document.getElementById('visiblePatients');
    
    if (filteredCountElement && visibleElement) {
        if (visible < total) {
            visibleElement.textContent = visible;
            filteredCountElement.classList.remove('filtered-count-hidden');
            filteredCountElement.classList.add('filtered-count-visible');
        } else {
            filteredCountElement.classList.add('filtered-count-hidden');
            filteredCountElement.classList.remove('filtered-count-visible');
        }
    }
}

function toggleNoResults(show) {
    const noResults = document.getElementById('noResults');
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    
    if (noResults && tableView && gridView) {
        if (show) {
            noResults.classList.remove('no-results-hidden');
            tableView.classList.add('grid-view-hidden');
            gridView.classList.add('grid-view-hidden');
        } else {
            noResults.classList.add('no-results-hidden');
            switchView(currentView); // Restore current view
        }
    }
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Enhanced notification system
function showEnhancedNotification(message, type = 'info', duration = 5000) {
    // Remove existing notifications
    document.querySelectorAll('.enhanced-notification').forEach(notification => {
        notification.remove();
    });

    const notification = document.createElement('div');
    notification.className = `enhanced-notification notification-${type}`;
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    const icon = icons[type] || icons['info'];
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-left: 4px solid var(--${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : type === 'success' ? 'success' : 'primary'}-color);
        min-width: 300px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(notification);

    // Auto remove
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => notification.remove(), 300);
        }
    }, duration);
}

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .enhanced-notification {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        color: var(--text-primary);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }
    
    .notification-close:hover {
        background: var(--bg-secondary);
    }
`;
document.head.appendChild(style);

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        color: white;
        font-weight: 500;
        z-index: 10000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;

    // Set background color based on type
    const colors = {
        'success': 'var(--success-color)',
        'error': 'var(--danger-color)',
        'warning': 'var(--warning-color)',
        'info': 'var(--primary-color)'
    };
    notification.style.backgroundColor = colors[type] || colors['info'];

    // Add icon
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    const icon = icons[type] || icons['info'];
    notification.innerHTML = `<i class="fas ${icon}" style="margin-right: 0.5rem;"></i>${message}`;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initialize enhanced features
    initializeEnhancedFeatures();
    cachePatientData();
    setupEventListeners();
    
    // Refresh patient count on page load
    setTimeout(() => {
        refreshPatientData();
    }, 100);

    // Auto-calculate age from birthdate
    const birthdateInput = document.getElementById('birthdate');
    const ageMonthsInput = document.getElementById('age_months');
    
    if (birthdateInput && ageMonthsInput) {
        birthdateInput.addEventListener('change', function() {
            const birthdate = new Date(this.value);
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
        });
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        const modals = ['addPatientModal', 'editPatientModal', 'viewPatientModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                if (modalId === 'addPatientModal') closeAddPatientModal();
                else if (modalId === 'editPatientModal') closeEditPatientModal();
                else if (modalId === 'viewPatientModal') closeViewPatientModal();
            }
        });
    });

    // Handle form validation on input
    const forms = ['addPatientForm', 'editPatientForm'];
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.hasAttribute('required') && !this.value.trim()) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        }
    });

    console.log('Admin Patients page loaded');
});

// Make functions globally available
window.showAddPatientModal = showAddPatientModal;
window.closeAddPatientModal = closeAddPatientModal;
window.showEditPatientModal = showEditPatientModal;
window.closeEditPatientModal = closeEditPatientModal;
window.showViewPatientModal = showViewPatientModal;
window.closeViewPatientModal = closeViewPatientModal;
window.savePatient = savePatient;
window.editPatient = editPatient;
window.updatePatient = updatePatient;
window.viewPatient = viewPatient;
window.deletePatient = deletePatient;
window.editPatientFromView = editPatientFromView;
window.filterPatients = filterPatients;
window.switchView = switchView;
window.clearAllFilters = clearAllFilters;
window.showEnhancedNotification = showEnhancedNotification;
window.refreshPatientData = refreshPatientData;
