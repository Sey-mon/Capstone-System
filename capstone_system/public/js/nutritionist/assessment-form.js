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

async function quickAssessment() {
    const form = document.getElementById('assessmentForm');
    const formData = new FormData(form);
    const requiredFields = ['age_months', 'weight_kg', 'height_cm', 'gender'];
    const missingFields = [];
    requiredFields.forEach(field => {
        if (!formData.get(field)) {
            missingFields.push(field.replace('_', ' ').toUpperCase());
        }
    });
    if (missingFields.length > 0) {
        alert('Please fill in required fields: ' + missingFields.join(', '));
        return;
    }
    try {
        const quickResults = document.getElementById('quickResults');
        quickResults.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Assessing...</div>';
        document.getElementById('quickResultsCard').style.display = 'block';
        const response = await fetch(quickAssessmentRoute, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(formData)
        });
        const data = await response.json();
        if (data.success) {
            displayQuickResults(data.data);
        } else {
            quickResults.innerHTML = `<div class="alert alert-danger">Assessment failed: ${data.error}</div>`;
        }
    } catch (error) {
        document.getElementById('quickResults').innerHTML = 
            `<div class="alert alert-danger">Error: ${error.message}</div>`;
    }
}

function displayQuickResults(data) {
    const quickResults = document.getElementById('quickResults');
    let html = '<div class="assessment-summary">';
    html += `<div class="diagnosis-badge ${getDiagnosisBadgeClass(data.primary_diagnosis)}">`;
    html += `<strong>Diagnosis:</strong> ${data.primary_diagnosis}`;
    html += '</div>';
    if (data.risk_level) {
        html += `<div class="risk-badge ${getRiskBadgeClass(data.risk_level)}">`;
        html += `<strong>Risk Level:</strong> ${data.risk_level}`;
        html += '</div>';
    }
    if (data.confidence) {
        html += `<div class="confidence-info">`;
        html += `<strong>Confidence:</strong> ${Math.round(data.confidence * 100)}%`;
        html += '</div>';
    }
    if (data.who_assessment && data.who_assessment.z_scores) {
        html += '<div class="z-scores">';
        html += '<h5>Z-Scores:</h5>';
        Object.entries(data.who_assessment.z_scores).forEach(([key, value]) => {
            html += `<div class="z-score-item">${key.replace('_', ' ').toUpperCase()}: ${value.toFixed(2)}</div>`;
        });
        html += '</div>';
    }
    html += '</div>';
    quickResults.innerHTML = html;
}

function getDiagnosisBadgeClass(diagnosis) {
    if (diagnosis.toLowerCase().includes('normal')) return 'success';
    if (diagnosis.toLowerCase().includes('severe')) return 'danger';
    if (diagnosis.toLowerCase().includes('moderate')) return 'warning';
    return 'info';
}

function getRiskBadgeClass(risk) {
    if (risk.toLowerCase() === 'low') return 'success';
    if (risk.toLowerCase() === 'high') return 'danger';
    if (risk.toLowerCase() === 'medium') return 'warning';
    return 'info';
}

function hideQuickResults() {
    document.getElementById('quickResultsCard').style.display = 'none';
}

// Route variable for AJAX (set by Blade template)
const quickAssessmentRoute = document.getElementById('assessmentForm')?.getAttribute('data-quick-route') || '';
