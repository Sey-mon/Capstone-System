/**
 * Admin Patients Management JavaScript
 * Handles patient CRUD operations and modal interactions
 */

// Global variables
let currentPatientId = null;

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
            showNotification('Patient added successfully!', 'success');
            closeAddPatientModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message || 'Error adding patient', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding patient', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function editPatient(patientId) {
    currentPatientId = patientId;
    
    // Fetch patient data
    fetch(`/admin/patients/${patientId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateEditForm(data.patient);
            showEditPatientModal();
        } else {
            showNotification('Error loading patient data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading patient data', 'error');
    });
}

function updatePatient() {
    if (!currentPatientId) return;

    const form = document.getElementById('editPatientForm');
    const formData = new FormData(form);

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
            showNotification('Patient updated successfully!', 'success');
            closeEditPatientModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message || 'Error updating patient', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating patient', 'error');
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
    
    // Fetch patient details
    fetch(`/admin/patients/${patientId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPatientDetails(data.patient);
        } else {
            document.getElementById('patientDetailsContent').innerHTML = '<div class="error-message">Error loading patient details</div>';
        }
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
            showNotification('Patient deleted successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message || 'Error deleting patient', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error deleting patient', 'error');
    });
}

// Helper functions
function populateEditForm(patient) {
    document.getElementById('edit_patient_id').value = patient.patient_id;
    document.getElementById('edit_first_name').value = patient.first_name || '';
    document.getElementById('edit_last_name').value = patient.last_name || '';
    document.getElementById('edit_date_of_birth').value = patient.date_of_birth || '';
    document.getElementById('edit_gender').value = patient.gender || '';
    document.getElementById('edit_barangay_id').value = patient.barangay_id || '';
    document.getElementById('edit_status').value = patient.status || 'Active';
    document.getElementById('edit_guardian_name').value = patient.guardian_name || '';
    document.getElementById('edit_guardian_contact').value = patient.guardian_contact || '';
    document.getElementById('edit_address').value = patient.address || '';
}

function displayPatientDetails(patient) {
    const age = patient.date_of_birth ? calculateAge(patient.date_of_birth) : 'N/A';
    const barangayName = patient.barangay ? patient.barangay.barangay_name : 'Not assigned';
    
    const html = `
        <div class="patient-details-grid">
            <div class="detail-group">
                <div class="detail-label">Patient ID</div>
                <div class="detail-value">#${patient.patient_id}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Full Name</div>
                <div class="detail-value">${patient.first_name} ${patient.last_name}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Date of Birth</div>
                <div class="detail-value">${patient.date_of_birth || 'N/A'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Age</div>
                <div class="detail-value">${age} years</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Gender</div>
                <div class="detail-value">${patient.gender || 'N/A'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="badge badge-${getStatusClass(patient.status)}">${patient.status || 'Active'}</span>
                </div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Barangay</div>
                <div class="detail-value">${barangayName}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Guardian Name</div>
                <div class="detail-value">${patient.guardian_name || 'N/A'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Guardian Contact</div>
                <div class="detail-value">${patient.guardian_contact || 'N/A'}</div>
            </div>
            <div class="detail-group">
                <div class="detail-label">Address</div>
                <div class="detail-value">${patient.address || 'N/A'}</div>
            </div>
        </div>
    `;
    
    document.getElementById('patientDetailsContent').innerHTML = html;
}

function validatePatientForm(form) {
    const requiredFields = ['first_name', 'last_name', 'date_of_birth', 'gender', 'barangay_id'];
    let isValid = true;
    
    requiredFields.forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        showNotification('Please fill in all required fields', 'error');
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
