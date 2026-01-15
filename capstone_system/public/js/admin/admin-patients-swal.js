/**
 * Admin Patients Management JavaScript - SweetAlert2 Implementation
 * Handles patient CRUD operations with SweetAlert2 modals
 */

// Global variables
let currentPatientId = null;
let currentView = 'table';
let sortColumn = null;
let sortDirection = 'asc';
let allPatients = [];
let parentsData = [];
let nutritionistsData = [];
let barangaysData = [];

// Load data from script tags
function loadData() {
    try {
        const parentsScript = document.getElementById('parentsData');
        const nutritionistsScript = document.getElementById('nutritionistsData');
        const barangaysScript = document.getElementById('barangaysData');
        
        if (parentsScript) parentsData = JSON.parse(parentsScript.textContent);
        if (nutritionistsScript) nutritionistsData = JSON.parse(nutritionistsScript.textContent);
        if (barangaysScript) barangaysData = JSON.parse(barangaysScript.textContent);
    } catch (e) {
        console.error('Error loading data:', e);
    }
}

// Generate select options
function generateSelectOptions(data, valueKey, textKeys, emptyText = 'Select') {
    let options = `<option value="">${emptyText}</option>`;
    data.forEach(item => {
        const text = textKeys.map(key => item[key]).filter(Boolean).join(' ');
        options += `<option value="${item[valueKey]}">${text}</option>`;
    });
    return options;
}

// SweetAlert2 Modal Functions
function showAddPatientModal() {
    const parentsOptions = generateSelectOptions(parentsData, 'user_id', ['first_name', 'last_name'], 'Select Parent');
    const nutritionistsOptions = generateSelectOptions(nutritionistsData, 'user_id', ['first_name', 'last_name'], 'Select Nutritionist');
    const barangaysOptions = generateSelectOptions(barangaysData, 'barangay_id', ['barangay_name'], 'Select Barangay');

    Swal.fire({
        title: 'Add New Patient',
        html: `
            <div class="swal-form-container">
                <form id="addPatientForm">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">Basic Information</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="swal2-input" required>
                            </div>
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" class="swal2-input">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="swal2-input" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="parent_id">Parent</label>
                                <select id="parent_id" name="parent_id" class="swal2-select">
                                    ${parentsOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nutritionist_id">Nutritionist</label>
                                <select id="nutritionist_id" name="nutritionist_id" class="swal2-select">
                                    ${nutritionistsOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="barangay_id">Barangay *</label>
                                <select id="barangay_id" name="barangay_id" class="swal2-select" required>
                                    ${barangaysOptions}
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact_number">Contact Number *</label>
                                <input type="text" id="contact_number" name="contact_number" class="swal2-input" required>
                            </div>
                            <div class="form-group">
                                <label for="birthdate">Birthdate *</label>
                                <input type="date" id="birthdate" name="birthdate" class="swal2-input" required>
                            </div>
                            <div class="form-group">
                                <label for="age_months">Age (months) *</label>
                                <input type="number" id="age_months" name="age_months" class="swal2-input" min="0" required readonly>
                                <small class="form-text text-muted">Auto-calculated from birthdate</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sex">Sex *</label>
                                <select id="sex" name="sex" class="swal2-select" required>
                                    <option value="">Select Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date_of_admission">Date of Admission *</label>
                                <input type="date" id="date_of_admission" name="date_of_admission" class="swal2-input" required>
                            </div>
                            <div class="form-group">
                                <label for="weight_kg">Weight (kg) *</label>
                                <input type="number" id="weight_kg" name="weight_kg" class="swal2-input" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="height_cm">Height (cm) *</label>
                                <input type="number" id="height_cm" name="height_cm" class="swal2-input" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <!-- Household Information -->
                    <div class="form-section">
                        <h6 class="section-title">Household Information</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="total_household_adults">Total Adults</label>
                                <input type="number" id="total_household_adults" name="total_household_adults" class="swal2-input" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label for="total_household_children">Total Children</label>
                                <input type="number" id="total_household_children" name="total_household_children" class="swal2-input" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label for="total_household_twins">Total Twins</label>
                                <input type="number" id="total_household_twins" name="total_household_twins" class="swal2-input" min="0" value="0">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <div class="form-check">
                                    <input type="checkbox" id="is_4ps_beneficiary" name="is_4ps_beneficiary" class="form-check-input">
                                    <label for="is_4ps_beneficiary" class="form-check-label">4Ps Beneficiary</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Health Information -->
                    <div class="form-section">
                        <h6 class="section-title">Health Information</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="breastfeeding">Breastfeeding</label>
                                <select id="breastfeeding" name="breastfeeding" class="swal2-select">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edema">Edema</label>
                                <select id="edema" name="edema" class="swal2-select">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="other_medical_problems">Other Medical Problems</label>
                                <textarea id="other_medical_problems" name="other_medical_problems" class="swal2-textarea" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Save Patient',
        cancelButtonText: 'Cancel',
        customClass: {
            container: 'swal-patient-modal',
            popup: 'swal-patient-popup',
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        width: '900px',
        didOpen: () => {
            // Setup birthdate to age calculation
            const birthdateInput = document.getElementById('birthdate');
            const ageMonthsInput = document.getElementById('age_months');
            
            if (birthdateInput && ageMonthsInput) {
                birthdateInput.addEventListener('change', function() {
                    const birthdate = new Date(this.value);
                    const today = new Date();
                    
                    if (birthdate && !isNaN(birthdate.getTime())) {
                        let months = (today.getFullYear() - birthdate.getFullYear()) * 12;
                        months -= birthdate.getMonth();
                        months += today.getMonth();
                        
                        if (today.getDate() < birthdate.getDate()) {
                            months--;
                        }
                        
                        ageMonthsInput.value = Math.max(0, months);
                    }
                });
            }
        },
        preConfirm: () => {
            if (!validatePatientForm('addPatientForm')) {
                return false;
            }
            return savePatient();
        }
    });
}

function showEditPatientModal(patient) {
    const parentsOptions = generateSelectOptions(parentsData, 'user_id', ['first_name', 'last_name'], 'Select Parent');
    const nutritionistsOptions = generateSelectOptions(nutritionistsData, 'user_id', ['first_name', 'last_name'], 'Select Nutritionist');
    const barangaysOptions = generateSelectOptions(barangaysData, 'barangay_id', ['barangay_name'], 'Select Barangay');

    let birthdate = '';
    if (patient.birthdate) {
        birthdate = typeof patient.birthdate === 'string' ? patient.birthdate.substring(0, 10) : patient.birthdate.toISOString().substring(0, 10);
    }

    let admissionDate = '';
    if (patient.date_of_admission) {
        admissionDate = typeof patient.date_of_admission === 'string' ? patient.date_of_admission.substring(0, 10) : patient.date_of_admission.toISOString().substring(0, 10);
    }

    Swal.fire({
        title: `Edit Patient - ID: ${patient.custom_patient_id || ''}`,
        html: `
            <div class="swal-form-container">
                <form id="editPatientForm">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">Basic Information <small class="text-muted">(Demographic fields are locked)</small></h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_first_name">First Name * <i class="fas fa-lock text-muted"></i></label>
                                <input type="text" id="edit_first_name" name="first_name" class="swal2-input" value="${patient.first_name || ''}" disabled>
                            </div>
                            <div class="form-group">
                                <label for="edit_middle_name">Middle Name <i class="fas fa-lock text-muted"></i></label>
                                <input type="text" id="edit_middle_name" name="middle_name" class="swal2-input" value="${patient.middle_name || ''}" disabled>
                            </div>
                            <div class="form-group">
                                <label for="edit_last_name">Last Name * <i class="fas fa-lock text-muted"></i></label>
                                <input type="text" id="edit_last_name" name="last_name" class="swal2-input" value="${patient.last_name || ''}" disabled>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_parent_id">Parent</label>
                                <select id="edit_parent_id" name="parent_id" class="swal2-select">
                                    ${parentsOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_nutritionist_id">Nutritionist</label>
                                <select id="edit_nutritionist_id" name="nutritionist_id" class="swal2-select">
                                    ${nutritionistsOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_barangay_id">Barangay *</label>
                                <select id="edit_barangay_id" name="barangay_id" class="swal2-select" required>
                                    ${barangaysOptions}
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_contact_number">Contact Number *</label>
                                <input type="text" id="edit_contact_number" name="contact_number" class="swal2-input" value="${patient.contact_number || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_birthdate">Birthdate * <i class="fas fa-lock text-muted"></i></label>
                                <input type="date" id="edit_birthdate" name="birthdate" class="swal2-input" value="${birthdate}" disabled>
                            </div>
                            <div class="form-group">
                                <label for="edit_sex">Sex * <i class="fas fa-lock text-muted"></i></label>
                                <select id="edit_sex" name="sex" class="swal2-select" disabled>
                                    <option value="">Select Sex</option>
                                    <option value="Male" ${patient.sex === 'Male' ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${patient.sex === 'Female' ? 'selected' : ''}>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_date_of_admission">Date of Admission *</label>
                                <input type="date" id="edit_date_of_admission" name="date_of_admission" class="swal2-input" value="${admissionDate}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_weight_kg">Weight (kg) <i class="fas fa-lock text-muted"></i></label>
                                <input type="number" id="edit_weight_kg" name="weight_kg" class="swal2-input" step="0.01" min="0" value="${patient.weight_kg || ''}" disabled>
                                <small class="form-text text-muted">Updated through assessments</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_height_cm">Height (cm) <i class="fas fa-lock text-muted"></i></label>
                                <input type="number" id="edit_height_cm" name="height_cm" class="swal2-input" step="0.01" min="0" value="${patient.height_cm || ''}" disabled>
                                <small class="form-text text-muted">Updated through assessments</small>
                            </div>
                        </div>
                    </div>

                    <!-- Household Information -->
                    <div class="form-section">
                        <h6 class="section-title">Household Information</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_total_household_adults">Total Adults</label>
                                <input type="number" id="edit_total_household_adults" name="total_household_adults" class="swal2-input" min="0" value="${patient.total_household_adults || 0}">
                            </div>
                            <div class="form-group">
                                <label for="edit_total_household_children">Total Children</label>
                                <input type="number" id="edit_total_household_children" name="total_household_children" class="swal2-input" min="0" value="${patient.total_household_children || 0}">
                            </div>
                            <div class="form-group">
                                <label for="edit_total_household_twins">Total Twins</label>
                                <input type="number" id="edit_total_household_twins" name="total_household_twins" class="swal2-input" min="0" value="${patient.total_household_twins || 0}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <div class="form-check">
                                    <input type="checkbox" id="edit_is_4ps_beneficiary" name="is_4ps_beneficiary" class="form-check-input" ${patient.is_4ps_beneficiary ? 'checked' : ''}>
                                    <label for="edit_is_4ps_beneficiary" class="form-check-label">4Ps Beneficiary</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Health Information -->
                    <div class="form-section">
                        <h6 class="section-title">Health Information</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_breastfeeding">Breastfeeding</label>
                                <select id="edit_breastfeeding" name="breastfeeding" class="swal2-select">
                                    <option value="">Select</option>
                                    <option value="Yes" ${patient.breastfeeding === 'Yes' ? 'selected' : ''}>Yes</option>
                                    <option value="No" ${patient.breastfeeding === 'No' ? 'selected' : ''}>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_edema">Edema</label>
                                <select id="edit_edema" name="edema" class="swal2-select">
                                    <option value="">Select</option>
                                    <option value="Yes" ${patient.edema === 'Yes' ? 'selected' : ''}>Yes</option>
                                    <option value="No" ${patient.edema === 'No' ? 'selected' : ''}>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="edit_other_medical_problems">Other Medical Problems</label>
                                <textarea id="edit_other_medical_problems" name="other_medical_problems" class="swal2-textarea" rows="3">${patient.other_medical_problems || ''}</textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Update Patient',
        cancelButtonText: 'Cancel',
        customClass: {
            container: 'swal-patient-modal',
            popup: 'swal-patient-popup',
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        width: '900px',
        didOpen: () => {
            // Set selected values for dropdowns
            if (patient.parent_id) document.getElementById('edit_parent_id').value = patient.parent_id;
            if (patient.nutritionist_id) document.getElementById('edit_nutritionist_id').value = patient.nutritionist_id;
            if (patient.barangay_id) document.getElementById('edit_barangay_id').value = patient.barangay_id;
        },
        preConfirm: () => {
            if (!validatePatientForm('editPatientForm')) {
                return false;
            }
            return updatePatient(patient.patient_id);
        }
    });
}

function showViewPatientModal(patient) {
    const parentName = patient.parent ? `${patient.parent.first_name} ${patient.parent.last_name}` : 'Not assigned';
    const nutritionistName = patient.nutritionist ? `${patient.nutritionist.first_name} ${patient.nutritionist.last_name}` : 'Not assigned';
    const barangayName = patient.barangay ? patient.barangay.barangay_name : 'Not assigned';

    Swal.fire({
        title: 'Patient Details',
        html: `
            <div class="patient-details-grid">
                <div class="detail-section">
                    <h6>Patient ID</h6>
                    <div class="detail-group">
                        <div class="detail-value" style="font-size: 1.2em; color: #007bff; font-weight: bold;">
                            ${patient.custom_patient_id || 'N/A'}
                        </div>
                    </div>
                </div>
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
                        <div class="detail-value">${patient.latest_assessment?.weight_for_age || 'Not assessed'}</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Height for Age</div>
                        <div class="detail-value">${patient.latest_assessment?.height_for_age || 'Not assessed'}</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">BMI for Age</div>
                        <div class="detail-value">${patient.latest_assessment?.bmi_for_age || 'Not assessed'}</div>
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
        `,
        showConfirmButton: true,
        confirmButtonText: 'Close',
        customClass: {
            container: 'swal-patient-modal',
            popup: 'swal-patient-popup',
            confirmButton: 'btn btn-secondary'
        },
        width: '900px'
    });
}

// Patient CRUD operations
function savePatient() {
    const form = document.getElementById('addPatientForm');
    const formData = new FormData();

    // Get all form fields
    const fields = ['first_name', 'middle_name', 'last_name', 'parent_id', 'nutritionist_id', 'barangay_id', 
                    'contact_number', 'birthdate', 'age_months', 'sex', 'date_of_admission', 'weight_kg', 
                    'height_cm', 'total_household_adults', 'total_household_children', 'total_household_twins', 
                    'breastfeeding', 'edema', 'other_medical_problems'];

    fields.forEach(field => {
        const element = form.querySelector(`[name="${field}"]`);
        if (element) {
            if (element.type === 'checkbox') {
                formData.append(field, element.checked ? '1' : '0');
            } else {
                formData.append(field, element.value);
            }
        }
    });

    // Show loading
    Swal.showLoading();

    return fetch('/admin/patients', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Patient added successfully!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error adding patient'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error adding patient'
        });
    });
}

function editPatient(patientId) {
    currentPatientId = patientId;
    
    Swal.fire({
        title: 'Loading...',
        html: 'Please wait while we load the patient data.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/admin/patients/${patientId}`)
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid server response. Please contact support.'
            });
            return null;
        }
        if (!response.ok || !data.success) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data && data.message ? data.message : 'Error loading patient data'
            });
            return null;
        }
        showEditPatientModal(data.patient);
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error loading patient data'
        });
    });
}

function updatePatient(patientId) {
    const form = document.getElementById('editPatientForm');
    const formData = new FormData();
    
    formData.append('_method', 'PUT');

    // Get all form fields (excluding disabled ones)
    const fields = ['parent_id', 'nutritionist_id', 'barangay_id', 'contact_number', 'date_of_admission', 
                    'total_household_adults', 'total_household_children', 'total_household_twins', 
                    'breastfeeding', 'edema', 'other_medical_problems'];

    fields.forEach(field => {
        const element = form.querySelector(`[name="${field}"]`);
        if (element && !element.disabled) {
            if (element.type === 'checkbox') {
                formData.append(field, element.checked ? '1' : '0');
            } else {
                formData.append(field, element.value);
            }
        }
    });

    // Show loading
    Swal.showLoading();

    return fetch(`/admin/patients/${patientId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Patient updated successfully!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error updating patient'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error updating patient'
        });
    });
}

function viewPatient(patientId) {
    currentPatientId = patientId;
    
    Swal.fire({
        title: 'Loading...',
        html: 'Please wait while we load the patient details.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/admin/patients/${patientId}`)
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid server response. Please contact support.'
            });
            return null;
        }
        if (!response.ok || !data.success) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data && data.message ? data.message : 'Error loading patient details'
            });
            return null;
        }
        showViewPatientModal(data.patient);
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error loading patient details'
        });
    });
}

function deletePatient(patientId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                html: 'Please wait while we delete the patient.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

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
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Patient has been deleted.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error deleting patient'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error deleting patient'
                });
            });
        }
    });
}

// Helper functions
function validatePatientForm(formId) {
    const form = document.getElementById(formId);
    const requiredFields = form.querySelectorAll('[required]:not([disabled])');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    if (!isValid) {
        Swal.showValidationMessage('Please fill in all required fields');
    }
    
    return isValid;
}

// Enhanced Features Functions
function initializeEnhancedFeatures() {
    updatePatientCounts();
    setupSorting();
    switchView('table');
    console.log('Enhanced Admin Patients features initialized');
}

function refreshPatientData() {
    cachePatientData();
    updatePatientCounts();
    console.log('Patient data refreshed');
}

function cachePatientData() {
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
    const searchInput = document.getElementById('searchPatient');
    const barangayFilter = document.getElementById('filterBarangay');
    const genderFilter = document.getElementById('filterGender');
    const ageRangeFilter = document.getElementById('filterAgeRange');
    const nutritionistFilter = document.getElementById('filterNutritionist');
    
    if (searchInput) searchInput.addEventListener('input', debounce(filterPatients, 300));
    if (barangayFilter) barangayFilter.addEventListener('change', filterPatients);
    if (genderFilter) genderFilter.addEventListener('change', filterPatients);
    if (ageRangeFilter) ageRangeFilter.addEventListener('change', filterPatients);
    if (nutritionistFilter) nutritionistFilter.addEventListener('change', filterPatients);
    
    setupButtonEventListeners();
}

function setupButtonEventListeners() {
    const clearFiltersBtn = document.querySelector('.filters-header .btn-outline');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearAllFilters();
        });
    }
    
    const refreshBtn = document.querySelector('.filters-header .btn-secondary');
    if (refreshBtn && refreshBtn.textContent.includes('Refresh')) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.reload();
        });
    }
    
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
    
    const addPatientBtns = document.querySelectorAll('.btn-primary');
    addPatientBtns.forEach(btn => {
        if (btn.textContent.includes('Add Patient') || btn.textContent.includes('Add First Patient')) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                showAddPatientModal();
            });
        }
    });
    
    setupActionButtons();
}

function setupActionButtons() {
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

function setupSorting() {
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            handleSort(column);
        });
    });
}

function filterPatients() {
    const searchInput = document.getElementById('searchPatient');
    const barangayFilter = document.getElementById('filterBarangay');
    const genderFilter = document.getElementById('filterGender');
    const ageRangeFilter = document.getElementById('filterAgeRange');
    const nutritionistFilter = document.getElementById('filterNutritionist');
    
    const search = searchInput ? searchInput.value.toLowerCase() : '';
    const barangay = barangayFilter ? barangayFilter.value.toLowerCase() : '';
    const gender = genderFilter ? genderFilter.value.toLowerCase() : '';
    const ageRange = ageRangeFilter ? ageRangeFilter.value : '';
    const nutritionist = nutritionistFilter ? nutritionistFilter.value.toLowerCase() : '';

    let visibleCount = 0;

    allPatients.forEach(patient => {
        let visible = true;
        const data = patient.data;

        if (search && !data.name.includes(search) && !data.contact.includes(search)) {
            visible = false;
        }

        if (barangay && data.barangay.toLowerCase() !== barangay) {
            visible = false;
        }

        if (gender && data.gender.toLowerCase() !== gender) {
            visible = false;
        }

        if (ageRange && !isInAgeRange(data.age, ageRange)) {
            visible = false;
        }

        if (nutritionist && data.nutritionist.toLowerCase() !== nutritionist) {
            visible = false;
        }

        if (visible) {
            patient.tableElement.classList.remove('patient-hidden');
            patient.gridElement.classList.remove('patient-hidden');
            visibleCount++;
        } else {
            patient.tableElement.classList.add('patient-hidden');
            patient.gridElement.classList.add('patient-hidden');
        }
    });

    updateFilteredCounts(visibleCount);
    toggleNoResults(visibleCount === 0);
}

function isInAgeRange(age, range) {
    switch(range) {
        case '0-12': return age >= 0 && age <= 12;
        case '13-24': return age >= 13 && age <= 24;
        case '25-36': return age >= 25 && age <= 36;
        case '37-48': return age >= 37 && age <= 48;
        case '49+': return age >= 49;
        default: return true;
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
            case 'name': aValue = a.data.name; bValue = b.data.name; break;
            case 'age': aValue = a.data.age; bValue = b.data.age; break;
            case 'gender': aValue = a.data.gender; bValue = b.data.gender; break;
            case 'barangay': aValue = a.data.barangay; bValue = b.data.barangay; break;
            case 'parent': aValue = a.data.parent; bValue = b.data.parent; break;
            case 'nutritionist': aValue = a.data.nutritionist; bValue = b.data.nutritionist; break;
            default: return 0;
        }

        if (typeof aValue === 'string') {
            aValue = aValue.toLowerCase();
            bValue = bValue.toLowerCase();
        }

        if (aValue < bValue) return sortDirection === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortDirection === 'asc' ? 1 : -1;
        return 0;
    });

    const tableBody = document.getElementById('patientsTableBody');
    const gridContainer = document.getElementById('patientsGrid');

    if (tableBody && gridContainer) {
        visiblePatients.forEach(patient => {
            tableBody.appendChild(patient.tableElement);
            gridContainer.appendChild(patient.gridElement);
        });
    }
}

function updateSortIcons() {
    document.querySelectorAll('.sortable i').forEach(icon => {
        icon.className = 'fas fa-sort';
    });

    if (sortColumn) {
        const activeHeader = document.querySelector(`[data-sort="${sortColumn}"] i`);
        if (activeHeader) {
            activeHeader.className = sortDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }
    }
}

function switchView(view) {
    currentView = view;
    
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const viewBtn = document.querySelector(`[data-view="${view}"]`);
    if (viewBtn) viewBtn.classList.add('active');
    
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    
    if (tableView && gridView) {
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
}

function clearAllFilters() {
    const searchInput = document.getElementById('searchPatient');
    const barangayFilter = document.getElementById('filterBarangay');
    const genderFilter = document.getElementById('filterGender');
    const ageRangeFilter = document.getElementById('filterAgeRange');
    const nutritionistFilter = document.getElementById('filterNutritionist');
    
    if (searchInput) searchInput.value = '';
    if (barangayFilter) barangayFilter.value = '';
    if (genderFilter) genderFilter.value = '';
    if (ageRangeFilter) ageRangeFilter.value = '';
    if (nutritionistFilter) nutritionistFilter.value = '';

    sortColumn = null;
    sortDirection = 'asc';
    updateSortIcons();

    allPatients.forEach(patient => {
        patient.tableElement.classList.remove('patient-hidden');
        patient.gridElement.classList.remove('patient-hidden');
    });

    updatePatientCounts();
    toggleNoResults(false);
}

function updatePatientCounts() {
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
    const totalElement = document.getElementById('totalPatients');
    if (totalElement) totalElement.textContent = total;
    
    const filteredCountElement = document.getElementById('filteredCount');
    const visibleElement = document.getElementById('visiblePatients');
    
    if (filteredCountElement && visibleElement) {
        if (visible < total) {
            visibleElement.textContent = visible;
            filteredCountElement.classList.remove('filtered-count-hidden');
            filteredCountElement.classList.add('filtered-count-visible');
        } else {
            filteredCountElement.classList.add('filtered-count-hidden');
            filteredCountElement.classList.remove('filtered-count-visible');
        }
    }
}

function toggleNoResults(show) {
    const noResults = document.getElementById('noResults');
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    
    if (noResults && tableView && gridView) {
        if (show) {
            noResults.classList.remove('no-results-hidden');
            tableView.classList.add('grid-view-hidden');
            gridView.classList.add('grid-view-hidden');
        } else {
            noResults.classList.add('no-results-hidden');
            switchView(currentView);
        }
    }
}

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

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    loadData();
    initializeEnhancedFeatures();
    cachePatientData();
    setupEventListeners();
    
    setTimeout(() => {
        refreshPatientData();
    }, 100);

    console.log('Admin Patients page loaded with SweetAlert2');
});

// Make functions globally available
window.showAddPatientModal = showAddPatientModal;
window.editPatient = editPatient;
window.viewPatient = viewPatient;
window.deletePatient = deletePatient;
window.filterPatients = filterPatients;
window.switchView = switchView;
window.clearAllFilters = clearAllFilters;
window.refreshPatientData = refreshPatientData;
