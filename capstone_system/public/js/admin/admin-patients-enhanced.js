/**
 * Enhanced Admin Patients Management JavaScript
 * Handles advanced filtering, sorting, and view switching
 */

// Global variables for enhanced functionality
let currentView = 'table';
let sortColumn = null;
let sortDirection = 'asc';
let allPatients = [];

// Initialize enhanced functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeEnhancedFeatures();
    cachePatientData();
    setupEventListeners();
    
    // Refresh patient count on page load
    setTimeout(() => {
        refreshPatientData();
    }, 100);
});

// Function to refresh patient data and counts
function refreshPatientData() {
    cachePatientData();
    updatePatientCounts();
    console.log('Patient data refreshed');
}

// Make this function globally available for other scripts
window.refreshPatientData = refreshPatientData;

function initializeEnhancedFeatures() {
    // Set initial patient count
    updatePatientCounts();
    
    // Setup sorting functionality
    setupSorting();
    
    // Initialize view
    switchView('table');
    
    console.log('Enhanced Admin Patients features initialized');
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
    document.getElementById('searchPatient').addEventListener('input', debounce(filterPatients, 300));
    document.getElementById('filterBarangay').addEventListener('change', filterPatients);
    document.getElementById('filterGender').addEventListener('change', filterPatients);
    document.getElementById('filterAgeRange').addEventListener('change', filterPatients);
    document.getElementById('filterNutritionist').addEventListener('change', filterPatients);
    
    // Button event listeners
    setupButtonEventListeners();
}

function setupButtonEventListeners() {
    // Clear filters button
    const clearFiltersBtn = document.querySelector('.btn-outline');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearAllFilters();
        });
    }
    
    // Refresh button
    const refreshBtn = document.querySelector('.btn-secondary');
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

// Enhanced filtering function
function filterPatients() {
    const search = document.getElementById('searchPatient').value.toLowerCase();
    const barangay = document.getElementById('filterBarangay').value.toLowerCase();
    const gender = document.getElementById('filterGender').value.toLowerCase();
    const ageRange = document.getElementById('filterAgeRange').value;
    const nutritionist = document.getElementById('filterNutritionist').value.toLowerCase();

    let visibleCount = 0;

    allPatients.forEach(patient => {
        let visible = true;
        const data = patient.data;

        // Search filter
        if (search && !data.name.includes(search) && !data.contact.includes(search)) {
            visible = false;
        }

        // Barangay filter
        if (barangay && data.barangay.toLowerCase() !== barangay) {
            visible = false;
        }

        // Gender filter
        if (gender && data.gender.toLowerCase() !== gender) {
            visible = false;
        }

        // Age range filter
        if (ageRange && !isInAgeRange(data.age, ageRange)) {
            visible = false;
        }

        // Nutritionist filter
        if (nutritionist && data.nutritionist.toLowerCase() !== nutritionist) {
            visible = false;
        }

        // Show/hide elements using CSS classes
        if (visible) {
            patient.tableElement.classList.remove('patient-hidden');
            patient.gridElement.classList.remove('patient-hidden');
            visibleCount++;
        } else {
            patient.tableElement.classList.add('patient-hidden');
            patient.gridElement.classList.add('patient-hidden');
        }
    });

    // Update counts and show/hide no results
    updateFilteredCounts(visibleCount);
    toggleNoResults(visibleCount === 0);
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

    visiblePatients.forEach(patient => {
        tableBody.appendChild(patient.tableElement);
        gridContainer.appendChild(patient.gridElement);
    });
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
    document.querySelector(`[data-view="${view}"]`).classList.add('active');
    
    // Show/hide view containers using CSS classes
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    
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

function clearAllFilters() {
    // Clear all filter inputs
    document.getElementById('searchPatient').value = '';
    document.getElementById('filterBarangay').value = '';
    document.getElementById('filterGender').value = '';
    document.getElementById('filterAgeRange').value = '';
    document.getElementById('filterNutritionist').value = '';

    // Reset sort
    sortColumn = null;
    sortDirection = 'asc';
    updateSortIcons();

    // Show all patients
    allPatients.forEach(patient => {
        patient.tableElement.classList.remove('patient-hidden');
        patient.gridElement.classList.remove('patient-hidden');
    });

    // Update counts
    updatePatientCounts();
    toggleNoResults(false);
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
    document.getElementById('totalPatients').textContent = total;
    
    const filteredCountElement = document.getElementById('filteredCount');
    if (visible < total) {
        document.getElementById('visiblePatients').textContent = visible;
        filteredCountElement.classList.remove('filtered-count-hidden');
        filteredCountElement.classList.add('filtered-count-visible');
    } else {
        filteredCountElement.classList.add('filtered-count-hidden');
        filteredCountElement.classList.remove('filtered-count-visible');
    }
}

function toggleNoResults(show) {
    const noResults = document.getElementById('noResults');
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    
    if (show) {
        noResults.classList.remove('no-results-hidden');
        tableView.classList.add('grid-view-hidden');
        gridView.classList.add('grid-view-hidden');
    } else {
        noResults.classList.add('no-results-hidden');
        switchView(currentView); // Restore current view
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

// Enhanced notification system with better positioning
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

// Add CSS animations
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

// Make functions globally available
window.filterPatients = filterPatients;
window.switchView = switchView;
window.clearAllFilters = clearAllFilters;
window.showEnhancedNotification = showEnhancedNotification;
