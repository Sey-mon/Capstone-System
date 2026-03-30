/**
 * Patient Archive System - Admin View
 * Handles AJAX switching between active and archived patients
 */

(function() {
    'use strict';

    let currentStatus = 'active'; // Current view: 'active' or 'archived'
    let currentPage = 1;
    let currentView = 'table'; // Current display mode: 'table' or 'grid'

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeArchiveToggles();
        initializeArchiveButtons();
        initializePageJumpButtons();
        initializeViewTracking();
    });

    /**
     * Initialize archive toggle buttons
     */
    function initializeArchiveToggles() {
        const archiveButtons = document.querySelectorAll('.archive-btn');
        
        archiveButtons.forEach(button => {
            button.addEventListener('click', function() {
                const status = this.getAttribute('data-status');
                
                if (status !== currentStatus) {
                    switchView(status);
                }
            });
        });
    }

    /**
     * Switch between active and archived views
     */
    function switchView(status) {
        currentStatus = status;
        currentPage = 1;

        // Update button states
        document.querySelectorAll('.archive-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`.archive-btn[data-status="${status}"]`).classList.add('active');

        // Load patients
        loadPatients(status, currentPage);
    }

    /**
     * Load patients via AJAX
     */
    function loadPatients(status, page = 1) {
        // Update current page tracker
        currentPage = page;
        
        const tableBody = document.getElementById('patientsTableBody');
        const paginationWrapper = document.querySelector('.pagination-wrapper');
        
        if (!tableBody) return;

        // Show loading state
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading patients...</p>
                </td>
            </tr>
        `;

        // Get current filters
        const searchTerm = document.getElementById('searchPatient')?.value || '';
        const barangay = document.getElementById('filterBarangay')?.value || '';
        const gender = document.getElementById('filterGender')?.value || '';
        const ageRange = document.getElementById('filterAgeRange')?.value || '';
        const nutritionist = document.getElementById('filterNutritionist')?.value || '';

        // Build query parameters
        const params = new URLSearchParams({
            status: status,
            page: page,
            search: searchTerm,
            barangay: barangay,
            gender: gender,
            age_range: ageRange,
            nutritionist: nutritionist
        });

        // Fetch patients
        fetch(`/admin/patients/ajax?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderPatientsTable(data.patients, status);
                renderPatientsGrid(data.patients, status);
                updatePagination(data.pagination);
                updateCounts(data.pagination.total);
            } else {
                showError('Failed to load patients');
            }
        })
        .catch(error => {
            console.error('Error loading patients:', error);
            showError('An error occurred while loading patients');
        });
    }

    /**
     * Initialize view tracking
     */
    function initializeViewTracking() {
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                currentView = this.getAttribute('data-view');
            });
        });
        
        // Detect current view on load
        const tableView = document.getElementById('tableView');
        const gridView = document.getElementById('gridView');
        if (gridView && gridView.classList.contains('active')) {
            currentView = 'grid';
        } else if (tableView && tableView.classList.contains('active')) {
            currentView = 'table';
        }
    }

    /**
     * Render patients table
     */
    function renderPatientsTable(patients, status) {
        const tableBody = document.getElementById('patientsTableBody');
        
        if (patients.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p>No ${status} patients found.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = patients.map(patient => {
            // Define action buttons based on status
            let actionButtons = '';
            
            if (status === 'archived') {
                // Archived patients: only View and Restore buttons
                actionButtons = `
                    <button class="btn btn-sm btn-outline-primary" data-patient-id="${patient.patient_id}" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info unarchive-patient-btn" data-patient-id="${patient.patient_id}" title="Restore Patient">
                        <i class="fas fa-undo"></i>
                    </button>
                `;
            } else {
                // Active patients: all action buttons
                actionButtons = `
                    <button class="btn btn-sm btn-outline-primary" data-patient-id="${patient.patient_id}" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info" data-patient-id="${patient.patient_id}" title="Assessment History">
                        <i class="fas fa-chart-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning" data-patient-id="${patient.patient_id}" title="Edit Patient">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success archive-patient-btn" data-patient-id="${patient.patient_id}" title="Archive Patient">
                        <i class="fas fa-archive"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" data-patient-id="${patient.patient_id}" title="Delete Patient">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
            }

            return `
                <tr class="patient-row ${status === 'archived' ? 'archived' : ''}"
                    data-name="${(patient.first_name + ' ' + patient.last_name).toLowerCase()}"
                    data-admitted="${patient.date_of_admission_raw || patient.date_of_admission}"
                    data-age="${patient.age_months}"
                    data-gender="${patient.sex}"
                    data-barangay="${patient.barangay || ''}"
                    data-parent="${patient.parent || ''}"
                    data-nutritionist="${patient.nutritionist || ''}"
                    data-contact="${patient.contact_number || ''}">
                    <td><span class="badge bg-primary">${patient.custom_patient_id}</span></td>
                    <td class="patient-info-cell">
                        <div class="patient-details">
                            <div class="patient-name">${patient.first_name} ${patient.last_name}</div>
                        </div>
                    </td>
                    <td class="admission-cell">
                        <span class="admission-date">${patient.date_of_admission}</span>
                    </td>
                    <td class="age-cell"><span class="age-months">${patient.age_months} months</span></td>
                    <td><span class="badge badge-${patient.sex === 'Male' ? 'primary' : 'secondary'}">
                        <i class="fas fa-${patient.sex === 'Male' ? 'mars' : 'venus'}"></i> ${patient.sex}
                    </span></td>
                    <td class="barangay-cell">
                        ${patient.barangay ? `<div class="barangay-name">${patient.barangay}</div>` : '<span class="text-muted">Not assigned</span>'}
                    </td>
                    <td>
                        ${patient.parent ? `<span>${patient.parent}</span>` : '<span class="text-muted">Not assigned</span>'}
                    </td>
                    <td class="nutritionist-cell">
                        ${patient.nutritionist ? `<div class="nutritionist-name">${patient.nutritionist}</div>` : '<span class="text-muted">Not assigned</span>'}
                    </td>
                    <td class="actions-cell">
                        <div class="action-buttons">
                            ${actionButtons}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Reinitialize all action buttons including archive, view, edit, delete
        initializeArchiveButtons();
        initializeViewButtons();
        
        // Re-cache patient data for sorting/filtering to work properly
        if (typeof window.cachePatientData === 'function') {
            window.cachePatientData();
        }
    }

    /**
     * Render patients grid
     */
    function renderPatientsGrid(patients, status) {
        const gridContainer = document.getElementById('patientsGrid');
        
        if (!gridContainer) return;

        if (patients.length === 0) {
            gridContainer.innerHTML = `
                <div class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p>No ${status} patients found.</p>
                </div>
            `;
            return;
        }

        gridContainer.innerHTML = patients.map(patient => {
            // Define action buttons based on status
            let actionButtons = '';
            
            if (status === 'archived') {
                // Archived patients: only View and Restore buttons
                actionButtons = `
                    <button class="btn btn-sm btn-primary" data-patient-id="${patient.patient_id}" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-info unarchive-patient-btn" data-patient-id="${patient.patient_id}" title="Restore Patient">
                        <i class="fas fa-undo"></i>
                    </button>
                `;
            } else {
                // Active patients: all action buttons
                actionButtons = `
                    <button class="btn btn-sm btn-primary" data-patient-id="${patient.patient_id}" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-info" data-patient-id="${patient.patient_id}" title="Assessment History">
                        <i class="fas fa-chart-line"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" data-patient-id="${patient.patient_id}" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" data-patient-id="${patient.patient_id}" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
            }

            return `
                <div class="patient-card ${status === 'archived' ? 'archived' : ''}"
                    data-name="${(patient.first_name + ' ' + patient.last_name).toLowerCase()}"
                    data-admitted="${patient.date_of_admission_raw || patient.date_of_admission}"
                    data-age="${patient.age_months}"
                    data-gender="${patient.sex}"
                    data-barangay="${patient.barangay || ''}"
                    data-parent="${patient.parent || ''}"
                    data-nutritionist="${patient.nutritionist || ''}"
                    data-contact="${patient.contact_number || ''}">
                    <div class="card-header">
                        <div class="patient-info">
                            <h4 class="patient-name">${patient.first_name} ${patient.last_name}</h4>
                            <div class="patient-meta">
                                <span class="age">${patient.age_months} months</span>
                                <span class="gender badge badge-${patient.sex === 'Male' ? 'primary' : 'secondary'}">
                                    <i class="fas fa-${patient.sex === 'Male' ? 'mars' : 'venus'}"></i>
                                    ${patient.sex}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="label">Barangay:</span>
                            <span class="value">${patient.barangay || 'Not assigned'}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Parent:</span>
                            <span class="value">${patient.parent || 'Not assigned'}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">BNS:</span>
                            <span class="value">${patient.nutritionist || 'Not assigned'}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Contact:</span>
                            <span class="value">${patient.contact_number || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Admitted:</span>
                            <span class="value">${patient.date_of_admission}</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="action-buttons">
                            ${actionButtons}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Reinitialize all action buttons
        initializeArchiveButtons();
        initializeViewButtons();
    }

    /**
     * Initialize archive/unarchive buttons
     */
    function initializeArchiveButtons() {
        // Archive buttons - Clone and replace to remove duplicate event listeners
        document.querySelectorAll('.archive-patient-btn').forEach(button => {
            const newBtn = button.cloneNode(true);
            button.parentNode.replaceChild(newBtn, button);
            
            newBtn.addEventListener('click', function() {
                const patientId = this.getAttribute('data-patient-id');
                archivePatient(patientId);
            });
        });

        // Unarchive buttons - Clone and replace to remove duplicate event listeners
        document.querySelectorAll('.unarchive-patient-btn').forEach(button => {
            const newBtn = button.cloneNode(true);
            button.parentNode.replaceChild(newBtn, button);
            
            newBtn.addEventListener('click', function() {
                const patientId = this.getAttribute('data-patient-id');
                unarchivePatient(patientId);
            });
        });
    }

    /**
     * Archive a patient
     */
    function archivePatient(patientId) {
        if (!confirm('Are you sure you want to archive this patient?')) {
            return;
        }

        fetch(`/admin/patients/${patientId}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                loadPatients(currentStatus, currentPage);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Error archiving patient:', error);
            showError('An error occurred while archiving the patient');
        });
    }

    /**
     * Unarchive a patient
     */
    function unarchivePatient(patientId) {
        if (!confirm('Are you sure you want to unarchive this patient?')) {
            return;
        }

        fetch(`/admin/patients/${patientId}/unarchive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                loadPatients(currentStatus, currentPage);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Error unarchiving patient:', error);
            showError('An error occurred while unarchiving the patient');
        });
    }

    /**
     * Update pagination
     */
    function updatePagination(pagination) {
        // Update all pagination controls (both table and grid view)
        const paginationWrappers = document.querySelectorAll('.pagination-wrapper');
        paginationWrappers.forEach(wrapper => {
            if (wrapper && pagination.links) {
                wrapper.innerHTML = pagination.links;
            }
        });
        
        // Attach click handlers to pagination links to maintain status and view
        const paginationLinks = document.querySelectorAll('.pagination-wrapper a[href*="page="]');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || 1;
                loadPatients(currentStatus, page);
            });
        });
        
        // Update page info for both table and grid view
        const pageInfo = document.getElementById('pageInfo');
        const gridPageInfo = document.getElementById('gridPageInfo');
        if (pageInfo && pagination.from && pagination.to && pagination.total) {
            const infoText = `Showing <strong>${pagination.from}</strong> to <strong>${pagination.to}</strong> of <strong>${pagination.total}</strong> patients`;
            pageInfo.innerHTML = infoText;
            if (gridPageInfo) {
                gridPageInfo.innerHTML = infoText;
            }
        }
        
        // Update page jump inputs
        const pageJump = document.getElementById('pageJump');
        const gridPageJump = document.getElementById('gridPageJump');
        if (pageJump && pagination.current_page) {
            pageJump.value = pagination.current_page;
            pageJump.max = pagination.last_page || 1;
        }
        if (gridPageJump && pagination.current_page) {
            gridPageJump.value = pagination.current_page;
            gridPageJump.max = pagination.last_page || 1;
        }
    }

    /**
     * Update patient counts
     */
    function updateCounts(total) {
        const totalPatientsEl = document.getElementById('totalPatients');
        if (totalPatientsEl) {
            totalPatientsEl.textContent = total;
        }
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Success',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        } else {
            alert(message);
        }
    }

    /**
     * Initialize page jump buttons
     */
    function initializePageJumpButtons() {
        const jumpButton = document.getElementById('jumpToPage');
        const gridJumpButton = document.getElementById('gridJumpToPage');
        
        if (jumpButton) {
            jumpButton.addEventListener('click', function() {
                const pageInput = document.getElementById('pageJump');
                const page = parseInt(pageInput.value);
                if (page && page > 0) {
                    loadPatients(currentStatus, page);
                }
            });
        }
        
        if (gridJumpButton) {
            gridJumpButton.addEventListener('click', function() {
                const pageInput = document.getElementById('gridPageJump');
                const page = parseInt(pageInput.value);
                if (page && page > 0) {
                    loadPatients(currentStatus, page);
                }
            });
        }
    }

    /**
     * Initialize view buttons for dynamically loaded patients
     * Call the setupActionButtons function from admin-patients-swal.js if available
     */
    function initializeViewButtons() {
        // Try to call the global setupActionButtons function if it exists
        if (typeof window.setupActionButtons === 'function') {
            window.setupActionButtons();
        } else {
            // Fallback: manually setup action buttons
            setupActionButtonsFallback();
        }
    }

    /**
     * Fallback function to setup action buttons
     */
    function setupActionButtonsFallback() {
        // View buttons
        const viewBtns = document.querySelectorAll('.btn-outline-primary, .btn-primary');
        viewBtns.forEach(btn => {
            if (btn.title === 'View Details' && btn.hasAttribute('data-patient-id')) {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const patientId = parseInt(this.getAttribute('data-patient-id'));
                    if (typeof window.viewPatient === 'function') {
                        window.viewPatient(patientId);
                    }
                });
            }
        });
        
        // Edit buttons
        const editBtns = document.querySelectorAll('.btn-outline-warning, .btn-warning');
        editBtns.forEach(btn => {
            if ((btn.title === 'Edit Patient' || btn.title === 'Edit') && btn.hasAttribute('data-patient-id')) {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const patientId = parseInt(this.getAttribute('data-patient-id'));
                    if (typeof window.editPatient === 'function') {
                        window.editPatient(patientId);
                    }
                });
            }
        });
        
        // Assessment History buttons
        const assessmentBtns = document.querySelectorAll('.btn-outline-info, .btn-info');
        assessmentBtns.forEach(btn => {
            if (btn.title === 'Assessment History' && btn.hasAttribute('data-patient-id')) {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const patientId = parseInt(this.getAttribute('data-patient-id'));
                    if (typeof window.showAssessmentHistory === 'function') {
                        window.showAssessmentHistory(patientId);
                    }
                });
            }
        });
        
        // Delete buttons
        const deleteBtns = document.querySelectorAll('.btn-outline-danger, .btn-danger');
        deleteBtns.forEach(btn => {
            if ((btn.title === 'Delete Patient' || btn.title === 'Delete') && btn.hasAttribute('data-patient-id')) {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const patientId = parseInt(this.getAttribute('data-patient-id'));
                    if (typeof window.deletePatient === 'function') {
                        window.deletePatient(patientId);
                    }
                });
            }
        });
    }

})();
