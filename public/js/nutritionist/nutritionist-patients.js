/**
 * Nutritionist Patients JavaScript
 * Handles patient management functionality
 */

let isEditing = false;
let currentPatientId = null;

// Open Add Patient Modal
function openAddPatientModal() {
    isEditing = false;
    currentPatientId = null;
    document.getElementById('patientModalTitle').textContent = 'Add Patient';
    document.getElementById('submitBtn').textContent = 'Save Patient';
    document.getElementById('patientForm').reset();
    document.getElementById('patient_id').value = '';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('patientModal'));
    modal.show();
}

// Edit Patient
async function editPatient(patientId) {
    try {
        const response = await fetch(`/nutritionist/patients/${patientId}`);
        const data = await response.json();
        
        if (data.success) {
            isEditing = true;
            currentPatientId = patientId;
            document.getElementById('patientModalTitle').textContent = 'Edit Patient';
            document.getElementById('submitBtn').textContent = 'Update Patient';
            
            // Populate form
            const patient = data.patient;
            document.getElementById('patient_id').value = patient.patient_id;
            document.getElementById('parent_id').value = patient.parent_id;
            document.getElementById('barangay_id').value = patient.barangay_id;
            document.getElementById('first_name').value = patient.first_name;
            document.getElementById('middle_name').value = patient.middle_name || '';
            document.getElementById('last_name').value = patient.last_name;
            document.getElementById('contact_number').value = patient.contact_number;
            document.getElementById('age_months').value = patient.age_months;
            document.getElementById('sex').value = patient.sex;
            document.getElementById('date_of_admission').value = patient.date_of_admission;
            document.getElementById('total_household_adults').value = patient.total_household_adults || 0;
            document.getElementById('total_household_children').value = patient.total_household_children || 0;
            document.getElementById('total_household_twins').value = patient.total_household_twins || 0;
            document.getElementById('is_4ps_beneficiary').checked = patient.is_4ps_beneficiary;
            document.getElementById('weight_kg').value = patient.weight_kg;
            document.getElementById('height_cm').value = patient.height_cm;
            document.getElementById('breastfeeding').value = patient.breastfeeding || '';
            document.getElementById('edema').value = patient.edema || '';
            document.getElementById('other_medical_problems').value = patient.other_medical_problems || '';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('patientModal'));
            modal.show();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Error loading patient data', 'error');
    }
}

// View Patient
async function viewPatient(patientId) {
    try {
        const response = await fetch(`/nutritionist/patients/${patientId}`);
        const data = await response.json();
        
        if (data.success) {
            const patient = data.patient;
            const detailsHtml = `
                <div class="patient-details">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <p><strong>Name:</strong> ${patient.first_name} ${patient.middle_name || ''} ${patient.last_name}</p>
                            <p><strong>Parent:</strong> ${patient.parent ? patient.parent.first_name + ' ' + patient.parent.last_name : 'Not assigned'}</p>
                            <p><strong>Contact:</strong> ${patient.contact_number}</p>
                            <p><strong>Age:</strong> ${patient.age_months} months</p>
                            <p><strong>Sex:</strong> ${patient.sex}</p>
                            <p><strong>Barangay:</strong> ${patient.barangay ? patient.barangay.barangay_name : 'Unknown'}</p>
                            <p><strong>Date of Admission:</strong> ${new Date(patient.date_of_admission).toLocaleDateString()}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Health Information</h6>
                            <p><strong>Weight:</strong> ${patient.weight_kg} kg</p>
                            <p><strong>Height:</strong> ${patient.height_cm} cm</p>
                            <p><strong>Weight for Age:</strong> ${patient.latest_assessment?.weight_for_age || 'Not assessed'}</p>
                            <p><strong>Height for Age:</strong> ${patient.latest_assessment?.height_for_age || 'Not assessed'}</p>
                            <p><strong>BMI for Age:</strong> ${patient.latest_assessment?.bmi_for_age || 'Not assessed'}</p>
                            <p><strong>4Ps Beneficiary:</strong> ${patient.is_4ps_beneficiary ? 'Yes' : 'No'}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Household Information</h6>
                            <p><strong>Adults:</strong> ${patient.total_household_adults || 0}</p>
                            <p><strong>Children:</strong> ${patient.total_household_children || 0}</p>
                            <p><strong>Twins:</strong> ${patient.total_household_twins || 0}</p>
                        </div>
                    </div>
                    ${patient.other_medical_problems ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Other Medical Problems</h6>
                            <p>${patient.other_medical_problems}</p>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('patientDetails').innerHTML = detailsHtml;
            const modal = new bootstrap.Modal(document.getElementById('viewPatientModal'));
            modal.show();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Error loading patient details', 'error');
    }
}

// Delete Patient - REMOVED
// Only administrators can permanently delete patient records
// Nutritionists should use the archive functionality instead
function deletePatient(patientId) {
    alert('Only administrators can permanently delete patient records. Please use the Archive function to maintain medical record integrity.');
    return;
}

// Show alert function
function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert at the top of the content
    const content = document.querySelector('.action-bar');
    content.insertAdjacentHTML('beforebegin', alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission
    const patientForm = document.getElementById('patientForm');
    if (patientForm) {
        patientForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            // Convert FormData to regular object
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            // Handle checkbox
            data.is_4ps_beneficiary = document.getElementById('is_4ps_beneficiary').checked;
            
            try {
                const url = isEditing ? `/nutritionist/patients/${currentPatientId}` : '/nutritionist/patients';
                const method = isEditing ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('patientModal'));
                    modal.hide();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Error saving patient', 'error');
            }
        });
    }
});

// Make functions globally available
window.openAddPatientModal = openAddPatientModal;
window.editPatient = editPatient;
window.viewPatient = viewPatient;
window.deletePatient = deletePatient;
