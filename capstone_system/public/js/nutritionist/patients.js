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
    console.log('Edit patient:', patientId);
}

function viewPatient(patientId) {
    console.log('View patient:', patientId);
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
