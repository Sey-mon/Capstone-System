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
    
    // Fetch patient data
    fetch(`/admin/patients/${patientId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateEditForm(data.patient);
            showEditPatientModal();
        } else {
            showEnhancedNotification('Error loading patient data', 'error');
        }
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
    document.getElementById('edit_age_months').value = patient.age_months || '';
    document.getElementById('edit_sex').value = patient.sex || '';
    document.getElementById('edit_date_of_admission').value = patient.date_of_admission || '';
    document.getElementById('edit_weight_kg').value = patient.weight_kg || '';
    document.getElementById('edit_height_cm').value = patient.height_cm || '';
    document.getElementById('edit_total_household_adults').value = patient.total_household_adults || 0;
    document.getElementById('edit_total_household_children').value = patient.total_household_children || 0;
    document.getElementById('edit_total_household_twins').value = patient.total_household_twins || 0;
    document.getElementById('edit_is_4ps_beneficiary').checked = patient.is_4ps_beneficiary || false;
    document.getElementById('edit_weight_for_age').value = patient.weight_for_age || '';
    document.getElementById('edit_height_for_age').value = patient.height_for_age || '';
    document.getElementById('edit_bmi_for_age').value = patient.bmi_for_age || '';
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
                    <div class="detail-label">Nutritionist</div>
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
                    <div class="detail-value">${patient.weight_for_age || 'N/A'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Height for Age</div>
                    <div class="detail-value">${patient.height_for_age || 'N/A'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">BMI for Age</div>
                    <div class="detail-value">${patient.bmi_for_age || 'N/A'}</div>
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
