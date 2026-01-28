/**
 * Patient Archive System - Nutritionist View
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

    // Expose globally for patients.js to call after AJAX reloads
    window.initializeArchiveButtons = initializeArchiveButtons;

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
        const tableContainer = document.getElementById('patientsTableContainer');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        if (!tableContainer) return;

        // Show loading overlay
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
        }

        // Get current filters
        const searchTerm = document.getElementById('searchInput')?.value || '';
        const barangay = document.getElementById('barangayFilter')?.value || '';
        const sex = document.getElementById('sexFilter')?.value || '';
        const ageRange = document.getElementById('ageRangeFilter')?.value || '';

        // Build query parameters
        const params = new URLSearchParams({
            status: status,
            page: page,
            search: searchTerm,
            barangay: barangay,
            sex: sex,
            age_range: ageRange
        });

        // Fetch patients
        fetch(`/nutritionist/patients/ajax?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tableContainer.innerHTML = data.html;
                updateCounts(data.total);
                // Reinitialize archive buttons
                initializeArchiveButtons();
            } else {
                showError('Failed to load patients');
            }
        })
        .catch(error => {
            console.error('Error loading patients:', error);
            showError('An error occurred while loading patients');
        })
        .finally(() => {
            // Hide loading overlay
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
        });
    }

    /**
     * Initialize archive/unarchive buttons
     */
    function initializeArchiveButtons() {
        // Archive buttons
        document.querySelectorAll('.archive-patient-btn').forEach(button => {
            // Remove existing event listeners
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                const patientId = this.getAttribute('data-patient-id');
                archivePatient(patientId);
            });
        });

        // Unarchive buttons
        document.querySelectorAll('.unarchive-patient-btn').forEach(button => {
            // Remove existing event listeners
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                const patientId = this.getAttribute('data-patient-id');
                unarchivePatient(patientId);
            });
        });
    }

    /**
     * Archive a patient
     */
    function archivePatient(patientId) {
        Swal.fire({
            title: 'Archive Patient?',
            text: 'Are you sure you want to archive this patient?',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, archive',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performArchive(patientId);
            }
        });
    }

    /**
     * Perform archive action
     */
    function performArchive(patientId) {
        fetch(`/nutritionist/patients/${patientId}/archive`, {
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
                Swal.fire({
                    title: 'Archived!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                loadPatients(currentStatus, currentPage);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error archiving patient:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while archiving the patient'
            });
        });
    }

    /**
     * Unarchive a patient
     */
    function unarchivePatient(patientId) {
        Swal.fire({
            title: 'Unarchive Patient?',
            text: 'Are you sure you want to unarchive this patient?',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, unarchive',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performUnarchive(patientId);
            }
        });
    }

    /**
     * Perform unarchive action
     */
    function performUnarchive(patientId) {
        fetch(`/nutritionist/patients/${patientId}/unarchive`, {
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
                Swal.fire({
                    title: 'Unarchived!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                loadPatients(currentStatus, currentPage);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error unarchiving patient:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while unarchiving the patient'
            });
        });
    }

    /**
     * Update patient counts
     */
    function updateCounts(total) {
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            resultsCount.textContent = `${total} patient(s)`;
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }

})();
