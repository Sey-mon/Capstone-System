// Patient Management Functions
let isEditing = false;
let currentPatientId = null;

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
document.addEventListener('DOMContentLoaded', function() {
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
});
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
window.openAddPatientModal = openAddPatientModal;
window.closePatientModal = closePatientModal;
window.closeViewPatientModal = closeViewPatientModal;
window.editPatient = editPatient;
window.viewPatient = viewPatient;
window.deletePatient = deletePatient;
console.log('Patient page functions loaded:', {
    openAddPatientModal: typeof window.openAddPatientModal,
    closePatientModal: typeof window.closePatientModal,
    bootstrap: typeof bootstrap
});
