/**
 * Patient Archive System - Admin View
 * Handles AJAX switching between active and archived patients
 */

(function() {
    'use strict';

    let currentStatus = 'active'; // Current view: 'active' or 'archived'
    let currentPage = 1;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeArchiveToggles();
        initializeArchiveButtons();
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
        const tableBody = document.getElementById('patientsTableBody');
        const paginationWrapper = document.querySelector('.pagination-wrapper');
        
        if (!tableBody) return;

        // Show loading state
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5">
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
                renderPatients(data.patients, status);
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
     * Render patients table
     */
    function renderPatients(patients, status) {
        const tableBody = document.getElementById('patientsTableBody');
        
        if (patients.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p>No ${status} patients found.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = patients.map(patient => {
            const actionButton = status === 'archived' 
                ? `<button class="btn btn-sm btn-outline-info unarchive-patient-btn" data-patient-id="${patient.patient_id}" title="Unarchive Patient">
                       <i class="fas fa-undo"></i>
                   </button>`
                : `<button class="btn btn-sm btn-outline-success archive-patient-btn" data-patient-id="${patient.patient_id}" title="Archive Patient">
                       <i class="fas fa-archive"></i>
                   </button>`;

            return `
                <tr class="patient-row ${status === 'archived' ? 'archived' : ''}">
                    <td><span class="badge bg-primary">${patient.custom_patient_id}</span></td>
                    <td class="patient-info-cell">
                        <div class="patient-details">
                            <div class="patient-name">${patient.first_name} ${patient.last_name}</div>
                            ${patient.middle_name ? `<small class="text-muted">${patient.middle_name}</small>` : ''}
                            <div class="patient-admission">Admitted: ${patient.date_of_admission}</div>
                        </div>
                    </td>
                    <td class="age-cell"><span class="age-months">${patient.age_months} months</span></td>
                    <td><span class="badge badge-${patient.sex === 'Male' ? 'primary' : 'secondary'}">
                        <i class="fas fa-${patient.sex === 'Male' ? 'mars' : 'venus'}"></i> ${patient.sex}
                    </span></td>
                    <td class="barangay-cell">${patient.barangay || 'Not assigned'}</td>
                    <td class="parent-cell">${patient.parent || 'Not assigned'}</td>
                    <td class="nutritionist-cell">${patient.nutritionist || 'Not assigned'}</td>
                    <td class="actions-cell">
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-outline-primary" data-patient-id="${patient.patient_id}" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" data-patient-id="${patient.patient_id}" title="Assessment History">
                                <i class="fas fa-chart-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" data-patient-id="${patient.patient_id}" title="Edit Patient">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${actionButton}
                            <button class="btn btn-sm btn-outline-danger" data-patient-id="${patient.patient_id}" title="Delete Patient">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Reinitialize archive buttons
        initializeArchiveButtons();
    }

    /**
     * Initialize archive/unarchive buttons
     */
    function initializeArchiveButtons() {
        // Archive buttons
        document.querySelectorAll('.archive-patient-btn').forEach(button => {
            button.addEventListener('click', function() {
                const patientId = this.getAttribute('data-patient-id');
                archivePatient(patientId);
            });
        });

        // Unarchive buttons
        document.querySelectorAll('.unarchive-patient-btn').forEach(button => {
            button.addEventListener('click', function() {
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
        // Update pagination controls if they exist
        const paginationWrapper = document.querySelector('.pagination-wrapper');
        if (paginationWrapper && pagination.links) {
            paginationWrapper.innerHTML = pagination.links;
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
                icon: 'success',
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

})();
