// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function calculateBMI() {
    const weight = parseFloat(document.getElementById('weight_kg').value);
    const height = parseFloat(document.getElementById('height_cm').value);
    if (weight > 0 && height > 0) {
        const heightM = height / 100;
        const bmi = weight / (heightM * heightM);
        const bmiDisplay = document.getElementById('bmi_display');
        if (bmiDisplay) {
            bmiDisplay.textContent = `BMI: ${bmi.toFixed(2)}`;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const weightInput = document.getElementById('weight_kg');
    const heightInput = document.getElementById('height_cm');
    if (weightInput) {
        weightInput.addEventListener('input', calculateBMI);
    }
    if (heightInput) {
        heightInput.addEventListener('input', calculateBMI);
    }
    calculateBMI();
});

function autoFillFromPatientRecord() {
    const button = document.querySelector('button[data-weight]');
    if (!button) {
        showNotification('Unable to find patient data.', 'error');
        return;
    }
    const weightField = document.getElementById('weight_kg');
    const patientWeight = button.getAttribute('data-weight');
    if (patientWeight && !weightField.value) {
        weightField.value = patientWeight;
    }
    const heightField = document.getElementById('height_cm');
    const patientHeight = button.getAttribute('data-height');
    if (patientHeight && !heightField.value) {
        heightField.value = patientHeight;
    }
    const householdField = document.getElementById('household_size');
    const householdSize = button.getAttribute('data-household-size');
    if (householdSize && householdSize > 1 && !householdField.value) {
        householdField.value = householdSize;
    }
    const beneficiaryField = document.getElementById('is_4ps_beneficiary');
    const is4psBeneficiary = button.getAttribute('data-4ps-beneficiary');
    if (is4psBeneficiary === '1' && !beneficiaryField.checked) {
        beneficiaryField.checked = true;
    }
    const notesField = document.getElementById('notes');
    const medicalProblems = button.getAttribute('data-medical-problems');
    const breastfeeding = button.getAttribute('data-breastfeeding');
    const edema = button.getAttribute('data-edema');
    if (!notesField.value.trim()) {
        let notes = '';
        if (medicalProblems && medicalProblems.trim()) {
            notes += 'Previous medical history: ' + medicalProblems;
        }
        if (breastfeeding && breastfeeding !== 'unknown' && breastfeeding.trim()) {
            notes += (notes ? '\n' : '') + 'Breastfeeding status: ' + breastfeeding;
        }
        if (edema && edema.trim()) {
            notes += (notes ? '\n' : '') + 'Edema notes: ' + edema;
        }
        if (notes) {
            notesField.value = notes;
        }
    }
    calculateBMI();
    showNotification('Patient record data has been auto-filled where applicable.', 'success');
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        ${message}
    `;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
