// Patient Management Functions
let isEditing = false;
let currentPatientId = null;
let filterTimeout = null;

// Initialize filters and event listeners
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializeModals();
    initializeSorting();
    initializePagination();
    initializePatientFormSubmit();
function initializePatientFormSubmit() {
    const form = document.getElementById('patientForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const patientId = document.getElementById('patient_id').value;
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
        formDataObj['is_4ps_beneficiary'] = document.getElementById('is_4ps_beneficiary').checked ? 'on' : '';

        fetch(url, {
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
                showError('Failed to update patient.');
                return;
            }
            if (response.status === 422 && data.errors) {
                let errorMessages = Object.values(data.errors).map(arr => arr.join(' ')).join('\n');
                showError(errorMessages);
                return;
            }
            if (data.success) {
                closePatientModal();
                showSuccess('Patient data updated successfully!');
                applyFilters();
            } else {
                showError(data.message || 'Failed to update patient.');
            }
// Show success notification (Bootstrap Toast or alert)
function showSuccess(message) {
    // If you use Bootstrap Toasts, you can trigger one here
    // For now, use a simple alert
    alert(message);
}
        })
        .catch(() => {
            showError('Failed to update patient.');
        });
    });
}
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
    // Extract total count from pagination info if available
    const paginationInfo = document.querySelector('.pagination-wrapper');
    if (paginationInfo) {
        const paginationText = paginationInfo.textContent;
        const matches = paginationText.match(/(\d+)\s+result/i);
        if (matches) {
            document.getElementById('resultsCount').textContent = `${matches[1]} patient(s) found`;
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
    // You can customize this to show a better error message
    alert(message);
}

function initializeModals() {
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded! Modal functionality will not work.');
    } else {
        console.log('Bootstrap is loaded successfully.');
    }
    
    const modal = document.getElementById('patientModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('patientForm').reset();
            isEditing = false;
            currentPatientId = null;
        });
    }
}

function openAddPatientModal() {
    isEditing = false;
    currentPatientId = null;
    document.getElementById('patientModalTitle').textContent = 'Add Patient';
    document.getElementById('submitBtn').textContent = 'Save Patient';
    document.getElementById('patientForm').reset();
    document.getElementById('patient_id').value = '';
    const modal = document.getElementById('patientModal');
    if (modal) {
        if (typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modalBackdrop';
            document.body.appendChild(backdrop);
        }
    }
}

function closePatientModal() {
    const modal = document.getElementById('patientModal');
    const backdrop = document.getElementById('modalBackdrop');
    if (modal) {
        if (typeof bootstrap !== 'undefined') {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        } else {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.style.overflow = '';
            if (backdrop) {
                backdrop.remove();
            }
        }
    }
}

function closeViewPatientModal() {
    const modal = document.getElementById('viewPatientModal');
    const backdrop = document.getElementById('modalBackdrop');
    if (modal) {
        if (typeof bootstrap !== 'undefined') {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        } else {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.style.overflow = '';
            if (backdrop) {
                backdrop.remove();
            }
        }
    }
}

function editPatient(patientId) {
    // Ensure all fields are enabled for editing
    const fieldIds = [
        'parent_id', 'barangay_id', 'first_name', 'middle_name', 'last_name', 'contact_number', 'age_months', 'sex',
        'date_of_admission', 'total_household_adults', 'total_household_children', 'total_household_twins',
        'is_4ps_beneficiary', 'weight_kg', 'height_cm', 'weight_for_age', 'height_for_age', 'bmi_for_age',
        'breastfeeding', 'edema', 'other_medical_problems'
    ];
    fieldIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.disabled = false;
            el.readOnly = false;
        }
    });
    // Show loading overlay or spinner in modal
    const modalTitle = document.getElementById('patientModalTitle');
    if (modalTitle) modalTitle.textContent = 'Edit Patient';
    const form = document.getElementById('patientForm');
    if (form) form.reset();
    document.getElementById('submitBtn').textContent = 'Update Patient';

    // Fetch patient data
    fetch(`/nutritionist/patients/${patientId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (!data.success || !data.patient) {
                alert(data.message || 'Failed to load patient data for editing.');
                return;
            }
            const patient = data.patient;
            // Fill form fields
            document.getElementById('patient_id').value = patient.patient_id ?? '';
            document.getElementById('first_name').value = patient.first_name ?? '';
            document.getElementById('middle_name').value = patient.middle_name ?? '';
            document.getElementById('last_name').value = patient.last_name ?? '';
            document.getElementById('contact_number').value = patient.contact_number ?? '';
            document.getElementById('age_months').value = patient.age_months ?? '';
            document.getElementById('sex').value = patient.sex ?? '';
            document.getElementById('date_of_admission').value = patient.date_of_admission ? patient.date_of_admission.substring(0, 10) : '';
            setSelectValue('barangay_id', patient.barangay_id);
            setSelectValue('parent_id', patient.parent_id);
            document.getElementById('total_household_adults').value = patient.total_household_adults ?? 0;
            document.getElementById('total_household_children').value = patient.total_household_children ?? 0;
            document.getElementById('total_household_twins').value = patient.total_household_twins ?? 0;
            document.getElementById('is_4ps_beneficiary').checked = !!patient.is_4ps_beneficiary;
            document.getElementById('weight_kg').value = patient.weight_kg ?? '';
            document.getElementById('height_cm').value = patient.height_cm ?? '';
            document.getElementById('weight_for_age').value = patient.weight_for_age ?? '';
            document.getElementById('height_for_age').value = patient.height_for_age ?? '';
            document.getElementById('bmi_for_age').value = patient.bmi_for_age ?? '';
            document.getElementById('breastfeeding').value = patient.breastfeeding ?? '';
            document.getElementById('edema').value = patient.edema ?? '';
            document.getElementById('other_medical_problems').value = patient.other_medical_problems ?? '';
        })
        .catch(error => {
            alert('Failed to load patient data for editing.');
        });
// Utility to set select value after options are loaded
function setSelectValue(selectId, value) {
    const select = document.getElementById(selectId);
    if (!select) return;
    // If options are not loaded yet, wait and retry
    if (!select.options.length) {
        setTimeout(() => setSelectValue(selectId, value), 100);
        return;
    }
    select.value = value ?? '';
}

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('patientModal'));
    modal.show();
}

function viewPatient(patientId) {
    // Show loading indicator
    const detailsDiv = document.getElementById('patientDetails');
    if (detailsDiv) {
        detailsDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    }

    // Fetch patient details via AJAX
    fetch(`/nutritionist/patients/${patientId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (!data.success || !data.patient) {
                detailsDiv.innerHTML = `<div class='alert alert-danger'>${data.message || 'Failed to load patient details.'}</div>`;
                return;
            }
            const patient = data.patient;
            // Build details HTML
            function show(val) {
                return (val !== undefined && val !== null && val !== '') ? val : 'N/A';
            }
            let html = '<ul class="list-group">';
            html += `<li class='list-group-item'><strong>Name:</strong> ${show(patient.first_name)} ${show(patient.middle_name)} ${show(patient.last_name)}</li>`;
            html += `<li class='list-group-item'><strong>Age (months):</strong> ${show(patient.age_months)}</li>`;
            html += `<li class='list-group-item'><strong>Sex:</strong> ${show(patient.sex)}</li>`;
            html += `<li class='list-group-item'><strong>Contact Number:</strong> ${show(patient.contact_number)}</li>`;
            html += `<li class='list-group-item'><strong>Date of Admission:</strong> ${show(patient.date_of_admission)}</li>`;
            html += `<li class='list-group-item'><strong>Barangay:</strong> ${show(patient.barangay?.barangay_name)}</li>`;
            html += `<li class='list-group-item'><strong>Parent:</strong> ${show(patient.parent?.first_name)} ${show(patient.parent?.last_name)}</li>`;
            html += `<li class='list-group-item'><strong>Weight (kg):</strong> ${show(patient.weight_kg)}</li>`;
            html += `<li class='list-group-item'><strong>Height (cm):</strong> ${show(patient.height_cm)}</li>`;
            html += `<li class='list-group-item'><strong>Other Medical Problems:</strong> ${show(patient.other_medical_problems)}</li>`;
            html += '</ul>';
            detailsDiv.innerHTML = html;
        })
        .catch(error => {
            detailsDiv.innerHTML = `<div class='alert alert-danger'>Failed to load patient details.</div>`;
        });

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewPatientModal'));
    modal.show();
}

function deletePatient(patientId) {
    if (confirm('Are you sure you want to delete this patient?')) {
        console.log('Delete patient:', patientId);
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

console.log('Enhanced patient page functions loaded with filtering and pagination');
