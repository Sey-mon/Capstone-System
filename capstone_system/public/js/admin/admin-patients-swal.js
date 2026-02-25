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
    const nutritionistsOptions = generateSelectOptions(nutritionistsData, 'user_id', ['first_name', 'last_name'], 'Select BNS');
    const barangaysOptions = generateSelectOptions(barangaysData, 'barangay_id', ['barangay_name'], 'Select Barangay');

    Swal.fire({
        title: '<i class="fas fa-user-plus" style="color: #007bff;"></i> Add New Patient',
        html: `
            <div class="swal-form-container">
                <!-- New Patient Banner -->
                <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 15px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(40,167,69,0.2);">
                    <div style="color: white; font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-plus-circle"></i> Creating New Patient Record
                    </div>
                    <div style="color: white; font-size: 18px; font-weight: 600;">
                        All fields marked with * are required
                    </div>
                </div>

                <form id="addPatientForm">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-user-circle" style="color: #007bff;"></i> Basic Information
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">
                                    <i class="fas fa-user"></i> First Name *
                                </label>
                                <input type="text" id="first_name" name="first_name" class="swal2-input" placeholder="Enter first name" required>
                            </div>
                            <div class="form-group">
                                <label for="middle_name">
                                    <i class="fas fa-user"></i> Middle Name
                                </label>
                                <input type="text" id="middle_name" name="middle_name" class="swal2-input" placeholder="Enter middle name (optional)">
                            </div>
                            <div class="form-group">
                                <label for="last_name">
                                    <i class="fas fa-user"></i> Last Name *
                                </label>
                                <input type="text" id="last_name" name="last_name" class="swal2-input" placeholder="Enter last name" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="birthdate">
                                    <i class="fas fa-birthday-cake"></i> Birthdate *
                                </label>
                                <input type="date" id="birthdate" name="birthdate" class="swal2-input" required>
                            </div>
                            <div class="form-group">
                                <label for="age_months">
                                    <i class="fas fa-calendar-alt"></i> Age (months) *
                                </label>
                                <input type="number" id="age_months" name="age_months" class="swal2-input" min="0" placeholder="Auto-calculated" required readonly style="background-color: #f8f9fa;">
                                <small class="form-text" style="color: #6c757d; font-size: 11px; display: block; margin-top: 5px;">
                                    <i class="fas fa-magic"></i> Auto-calculated from birthdate
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="sex">
                                    <i class="fas fa-venus-mars"></i> Sex *
                                </label>
                                <select id="sex" name="sex" class="swal2-select" required>
                                    <option value="">Select Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact_number">
                                    <i class="fas fa-phone"></i> Contact Number *
                                </label>
                                <input type="text" id="contact_number" name="contact_number" class="swal2-input" placeholder="09XX XXX XXXX" required>
                            </div>
                            <div class="form-group">
                                <label for="date_of_admission">
                                    <i class="fas fa-calendar-check"></i> Date of Admission *
                                </label>
                                <input type="date" id="date_of_admission" name="date_of_admission" class="swal2-input" required>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment & Location -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-user-tag" style="color: #28a745;"></i> Assignment & Location
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="parent_id">
                                    <i class="fas fa-user-friends"></i> Parent / Guardian
                                </label>
                                <select id="parent_id" name="parent_id" class="swal2-select">
                                    ${parentsOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nutritionist_id">
                                    <i class="fas fa-user-md"></i> Assigned BNS
                                </label>
                                <select id="nutritionist_id" name="nutritionist_id" class="swal2-select">
                                    ${nutritionistsOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="barangay_id">
                                    <i class="fas fa-map-marker-alt"></i> Barangay *
                                </label>
                                <select id="barangay_id" name="barangay_id" class="swal2-select" required>
                                    ${barangaysOptions}
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Health Metrics -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-heartbeat" style="color: #dc3545;"></i> Health Metrics & Status
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="weight_kg">
                                    <i class="fas fa-weight"></i> Weight (kg) *
                                </label>
                                <input type="number" id="weight_kg" name="weight_kg" class="swal2-input" step="0.01" min="0" placeholder="e.g. 12.5" required>
                            </div>
                            <div class="form-group">
                                <label for="height_cm">
                                    <i class="fas fa-ruler-vertical"></i> Height (cm) *
                                </label>
                                <input type="number" id="height_cm" name="height_cm" class="swal2-input" step="0.01" min="0" placeholder="e.g. 85.5" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="breastfeeding">
                                    <i class="fas fa-baby"></i> Breastfeeding Status
                                </label>
                                <select id="breastfeeding" name="breastfeeding" class="swal2-select">
                                    <option value="">Select Status</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edema">
                                    <i class="fas fa-disease"></i> Edema Present
                                </label>
                                <select id="edema" name="edema" class="swal2-select">
                                    <option value="">Select Status</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="allergies">
                                    <i class="fas fa-allergies"></i> Allergies
                                </label>
                                <select id="allergies" name="allergies" class="swal2-select">
                                    <option value="">Select Allergies</option>
                                    <option value="None">None</option>
                                    <option value="Seafood">Seafood</option>
                                    <option value="Peanuts">Peanuts</option>
                                    <option value="Dairy">Dairy</option>
                                    <option value="Eggs">Eggs</option>
                                    <option value="Soy">Soy</option>
                                    <option value="Penicillin">Penicillin</option>
                                    <option value="Aspirin">Aspirin</option>
                                    <option value="Dust">Dust</option>
                                    <option value="Pollen">Pollen</option>
                                    <option value="Other">Other (Specify)</option>
                                </select>
                                <input type="text" id="allergies_other" name="allergies_other" class="swal2-input" placeholder="Please specify allergies" style="display: none; margin-top: 10px;">
                            </div>
                            <div class="form-group">
                                <label for="religion">
                                    <i class="fas fa-pray"></i> Religion
                                </label>
                                <select id="religion" name="religion" class="swal2-select">
                                    <option value="">Select Religion</option>
                                    <option value="Roman Catholic">Roman Catholic</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                                    <option value="Philippine Independent Church">Philippine Independent Church (Aglipayan)</option>
                                    <option value="Seventh-day Adventist">Seventh-day Adventist</option>
                                    <option value="Bible Baptist Church">Bible Baptist Church</option>
                                    <option value="United Church of Christ">United Church of Christ in the Philippines</option>
                                    <option value="Jehovah's Witnesses">Jehovah's Witnesses</option>
                                    <option value="Protestant">Protestant</option>
                                    <option value="Buddhism">Buddhism</option>
                                    <option value="Born Again Christian">Born Again Christian</option>
                                    <option value="Other">Other (Specify)</option>
                                </select>
                                <input type="text" id="religion_other" name="religion_other" class="swal2-input" placeholder="Please specify religion" style="display: none; margin-top: 10px;">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="other_medical_problems">
                                    <i class="fas fa-notes-medical"></i> Other Medical Problems
                                </label>
                                <textarea id="other_medical_problems" name="other_medical_problems" class="swal2-textarea" rows="3" placeholder="Enter any other medical conditions or concerns..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Household Information -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-home" style="color: #ffc107;"></i> Household Information
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="total_household_adults">
                                    <i class="fas fa-users"></i> Total Adults
                                </label>
                                <input type="number" id="total_household_adults" name="total_household_adults" class="swal2-input" min="0" value="0" placeholder="Number of adults">
                            </div>
                            <div class="form-group">
                                <label for="total_household_children">
                                    <i class="fas fa-child"></i> Total Children
                                </label>
                                <input type="number" id="total_household_children" name="total_household_children" class="swal2-input" min="0" value="0" placeholder="Number of children">
                            </div>
                            <div class="form-group">
                                <label for="total_household_twins">
                                    <i class="fas fa-children"></i> Total Twins
                                </label>
                                <input type="number" id="total_household_twins" name="total_household_twins" class="swal2-input" min="0" value="0" placeholder="Number of twins">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <div class="form-check" style="display: flex; align-items: center; padding: 12px; background-color: #f8f9fa; border-radius: 6px; border: 1px solid #dee2e6;">
                                    <input type="checkbox" id="is_4ps_beneficiary" name="is_4ps_beneficiary" class="form-check-input" style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
                                    <label for="is_4ps_beneficiary" class="form-check-label" style="margin: 0; cursor: pointer; font-weight: 500;">
                                        <i class="fas fa-hands-helping" style="color: #007bff; margin-right: 5px;"></i> 4Ps Beneficiary
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Save Patient',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        customClass: {
            container: 'swal-patient-modal',
            popup: 'swal-patient-popup',
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary'
        },
        width: '950px',
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
                        
                        // Visual feedback
                        this.style.borderColor = '#28a745';
                        setTimeout(() => {
                            this.style.borderColor = '';
                        }, 1000);
                    }
                });
            }
            
            // Phone number formatting and validation
            const contactInput = document.getElementById('contact_number');
            if (contactInput) {
                contactInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.substring(0, 11);
                    this.value = value;
                    
                    // Real-time validation feedback
                    if (value.length === 11 && value.startsWith('09')) {
                        this.style.borderColor = '#28a745';
                    } else if (value.length > 0) {
                        this.style.borderColor = '#ffc107';
                    } else {
                        this.style.borderColor = '';
                    }
                });
            }
            
            // Real-time validation for required fields
            const requiredFields = ['first_name', 'last_name', 'sex', 'weight_kg', 'height_cm', 'barangay_id', 'date_of_admission'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('blur', function() {
                        if (this.value.trim() === '') {
                            this.style.borderColor = '#dc3545';
                        } else {
                            this.style.borderColor = '#28a745';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 1000);
                        }
                    });
                    
                    field.addEventListener('input', function() {
                        if (this.value.trim() !== '' && this.style.borderColor === 'rgb(220, 53, 69)') {
                            this.style.borderColor = '';
                        }
                    });
                }
            });
            
            // Weight and height validation
            ['weight_kg', 'height_cm'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', function() {
                        const value = parseFloat(this.value);
                        if (value > 0) {
                            this.style.borderColor = '#28a745';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 1000);
                        }
                    });
                }
            });
            
            // Handle Allergies "Other" option
            const allergiesSelect = document.getElementById('allergies');
            const allergiesOtherInput = document.getElementById('allergies_other');
            if (allergiesSelect && allergiesOtherInput) {
                allergiesSelect.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        allergiesOtherInput.style.display = 'block';
                        allergiesOtherInput.required = true;
                    } else {
                        allergiesOtherInput.style.display = 'none';
                        allergiesOtherInput.required = false;
                        allergiesOtherInput.value = '';
                    }
                });
            }
            
            // Handle Religion "Other" option
            const religionSelect = document.getElementById('religion');
            const religionOtherInput = document.getElementById('religion_other');
            if (religionSelect && religionOtherInput) {
                religionSelect.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        religionOtherInput.style.display = 'block';
                        religionOtherInput.required = true;
                    } else {
                        religionOtherInput.style.display = 'none';
                        religionOtherInput.required = false;
                        religionOtherInput.value = '';
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
    const nutritionistsOptions = generateSelectOptions(nutritionistsData, 'user_id', ['first_name', 'last_name'], 'Select BNS');
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
        title: `<i class="fas fa-user-edit" style="color: #007bff;"></i> Edit Patient`,
        html: `
            <div class="swal-form-container">
                <!-- Patient ID Display -->
                <div style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); padding: 15px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,123,255,0.2);">
                    <div style="color: white; font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-id-card"></i> Patient ID
                    </div>
                    <div style="color: white; font-size: 24px; font-weight: bold; letter-spacing: 1px;">
                        ${patient.custom_patient_id || 'N/A'}
                    </div>
                </div>

                <form id="editPatientForm">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-user-circle" style="color: #007bff;"></i> Basic Information
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_first_name">
                                    <i class="fas fa-user"></i> First Name *
                                </label>
                                <input type="text" id="edit_first_name" name="first_name" class="swal2-input" value="${patient.first_name || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_middle_name">
                                    <i class="fas fa-user"></i> Middle Name
                                </label>
                                <input type="text" id="edit_middle_name" name="middle_name" class="swal2-input" value="${patient.middle_name || ''}">
                            </div>
                            <div class="form-group">
                                <label for="edit_last_name">
                                    <i class="fas fa-user"></i> Last Name *
                                </label>
                                <input type="text" id="edit_last_name" name="last_name" class="swal2-input" value="${patient.last_name || ''}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_birthdate">
                                    <i class="fas fa-birthday-cake"></i> Birthdate *
                                    <i class="fas fa-lock" style="font-size: 11px; color: #6c757d; margin-left: 5px;"></i>
                                </label>
                                <input type="date" id="edit_birthdate" name="birthdate" class="swal2-input" value="${birthdate}" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                            </div>
                            <div class="form-group">
                                <label for="edit_sex">
                                    <i class="fas fa-venus-mars"></i> Sex *
                                    <i class="fas fa-lock" style="font-size: 11px; color: #6c757d; margin-left: 5px;"></i>
                                </label>
                                <select id="edit_sex" name="sex" class="swal2-select" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                    <option value="">Select Sex</option>
                                    <option value="Male" ${patient.sex === 'Male' ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${patient.sex === 'Female' ? 'selected' : ''}>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_contact_number">
                                    <i class="fas fa-phone"></i> Contact Number *
                                </label>
                                <input type="text" id="edit_contact_number" name="contact_number" class="swal2-input" value="${patient.contact_number || ''}" placeholder="09XX XXX XXXX" required>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Section -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-user-tag" style="color: #28a745;"></i> Assignment & Location
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_parent_id">
                                    <i class="fas fa-user-friends"></i> Parent / Guardian
                                </label>
                                <select id="edit_parent_id" name="parent_id" class="swal2-select">
                                    ${parentsOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_nutritionist_id">
                                    <i class="fas fa-user-md"></i> Assigned BNS
                                </label>
                                <select id="edit_nutritionist_id" name="nutritionist_id" class="swal2-select">
                                    ${nutritionistsOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_barangay_id">
                                    <i class="fas fa-map-marker-alt"></i> Barangay *
                                </label>
                                <select id="edit_barangay_id" name="barangay_id" class="swal2-select" required>
                                    ${barangaysOptions}
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_date_of_admission">
                                    <i class="fas fa-calendar-check"></i> Date of Admission *
                                </label>
                                <input type="date" id="edit_date_of_admission" name="date_of_admission" class="swal2-input" value="${admissionDate}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Health Metrics -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-heartbeat" style="color: #dc3545;"></i> Health Metrics & Status
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_weight_kg">
                                    <i class="fas fa-weight"></i> Weight (kg)
                                    <i class="fas fa-lock" style="font-size: 11px; color: #6c757d; margin-left: 5px;"></i>
                                </label>
                                <input type="number" id="edit_weight_kg" name="weight_kg" class="swal2-input" step="0.01" min="0" value="${patient.weight_kg || ''}" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                <small class="form-text" style="color: #6c757d; font-size: 11px; display: block; margin-top: 5px;">
                                    <i class="fas fa-info-circle"></i> Updated through assessments only
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="edit_height_cm">
                                    <i class="fas fa-ruler-vertical"></i> Height (cm)
                                    <i class="fas fa-lock" style="font-size: 11px; color: #6c757d; margin-left: 5px;"></i>
                                </label>
                                <input type="number" id="edit_height_cm" name="height_cm" class="swal2-input" step="0.01" min="0" value="${patient.height_cm || ''}" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                <small class="form-text" style="color: #6c757d; font-size: 11px; display: block; margin-top: 5px;">
                                    <i class="fas fa-info-circle"></i> Updated through assessments only
                                </small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_breastfeeding">
                                    <i class="fas fa-baby"></i> Breastfeeding Status
                                </label>
                                <select id="edit_breastfeeding" name="breastfeeding" class="swal2-select">
                                    <option value="">Select Status</option>
                                    <option value="Yes" ${patient.breastfeeding === 'Yes' ? 'selected' : ''}>Yes</option>
                                    <option value="No" ${patient.breastfeeding === 'No' ? 'selected' : ''}>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_edema">
                                    <i class="fas fa-disease"></i> Edema Present
                                </label>
                                <select id="edit_edema" name="edema" class="swal2-select">
                                    <option value="">Select Status</option>
                                    <option value="Yes" ${patient.edema === 'Yes' ? 'selected' : ''}>Yes</option>
                                    <option value="No" ${patient.edema === 'No' ? 'selected' : ''}>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_allergies">
                                    <i class="fas fa-allergies"></i> Allergies
                                </label>
                                <select id="edit_allergies" name="allergies" class="swal2-select">
                                    <option value="">Select Allergies</option>
                                    <option value="None">None</option>
                                    <option value="Seafood">Seafood</option>
                                    <option value="Peanuts">Peanuts</option>
                                    <option value="Dairy">Dairy</option>
                                    <option value="Eggs">Eggs</option>
                                    <option value="Soy">Soy</option>
                                    <option value="Penicillin">Penicillin</option>
                                    <option value="Aspirin">Aspirin</option>
                                    <option value="Dust">Dust</option>
                                    <option value="Pollen">Pollen</option>
                                    <option value="Other">Other (Specify)</option>
                                </select>
                                <input type="text" id="edit_allergies_other" name="allergies_other" class="swal2-input" placeholder="Please specify allergies" style="display: none; margin-top: 10px;">
                            </div>
                            <div class="form-group">
                                <label for="edit_religion">
                                    <i class="fas fa-pray"></i> Religion
                                </label>
                                <select id="edit_religion" name="religion" class="swal2-select">
                                    <option value="">Select Religion</option>
                                    <option value="Roman Catholic">Roman Catholic</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                                    <option value="Philippine Independent Church">Philippine Independent Church (Aglipayan)</option>
                                    <option value="Seventh-day Adventist">Seventh-day Adventist</option>
                                    <option value="Bible Baptist Church">Bible Baptist Church</option>
                                    <option value="United Church of Christ">United Church of Christ in the Philippines</option>
                                    <option value="Jehovah's Witnesses">Jehovah's Witnesses</option>
                                    <option value="Protestant">Protestant</option>
                                    <option value="Buddhism">Buddhism</option>
                                    <option value="Born Again Christian">Born Again Christian</option>
                                    <option value="Other">Other (Specify)</option>
                                </select>
                                <input type="text" id="edit_religion_other" name="religion_other" class="swal2-input" placeholder="Please specify religion" style="display: none; margin-top: 10px;">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="edit_other_medical_problems">
                                    <i class="fas fa-notes-medical"></i> Other Medical Problems
                                </label>
                                <textarea id="edit_other_medical_problems" name="other_medical_problems" class="swal2-textarea" rows="3" placeholder="Enter any other medical conditions or concerns...">${patient.other_medical_problems || ''}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Household Information -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-home" style="color: #ffc107;"></i> Household Information
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_total_household_adults">
                                    <i class="fas fa-users"></i> Total Adults
                                </label>
                                <input type="number" id="edit_total_household_adults" name="total_household_adults" class="swal2-input" min="0" value="${patient.total_household_adults || 0}" placeholder="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_total_household_children">
                                    <i class="fas fa-child"></i> Total Children
                                </label>
                                <input type="number" id="edit_total_household_children" name="total_household_children" class="swal2-input" min="0" value="${patient.total_household_children || 0}" placeholder="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_total_household_twins">
                                    <i class="fas fa-children"></i> Total Twins
                                </label>
                                <input type="number" id="edit_total_household_twins" name="total_household_twins" class="swal2-input" min="0" value="${patient.total_household_twins || 0}" placeholder="0">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <div class="form-check" style="display: flex; align-items: center; padding: 12px; background-color: #f8f9fa; border-radius: 6px; border: 1px solid #dee2e6;">
                                    <input type="checkbox" id="edit_is_4ps_beneficiary" name="is_4ps_beneficiary" class="form-check-input" ${patient.is_4ps_beneficiary ? 'checked' : ''} style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
                                    <label for="edit_is_4ps_beneficiary" class="form-check-label" style="margin: 0; cursor: pointer; font-weight: 500;">
                                        <i class="fas fa-hands-helping" style="color: #007bff; margin-right: 5px;"></i> 4Ps Beneficiary
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Update Patient',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        customClass: {
            container: 'swal-patient-modal',
            popup: 'swal-patient-popup',
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary'
        },
        width: '900px',
        didOpen: () => {
            // Set selected values for dropdowns
            if (patient.parent_id) document.getElementById('edit_parent_id').value = patient.parent_id;
            if (patient.nutritionist_id) document.getElementById('edit_nutritionist_id').value = patient.nutritionist_id;
            if (patient.barangay_id) document.getElementById('edit_barangay_id').value = patient.barangay_id;
            
            // Set allergies value and handle Other option
            const editAllergiesSelect = document.getElementById('edit_allergies');
            const editAllergiesOtherInput = document.getElementById('edit_allergies_other');
            const commonAllergies = ['None', 'Seafood', 'Peanuts', 'Dairy', 'Eggs', 'Soy', 'Penicillin', 'Aspirin', 'Dust', 'Pollen'];
            if (patient.allergies) {
                if (commonAllergies.includes(patient.allergies)) {
                    editAllergiesSelect.value = patient.allergies;
                } else {
                    editAllergiesSelect.value = 'Other';
                    editAllergiesOtherInput.value = patient.allergies;
                    editAllergiesOtherInput.style.display = 'block';
                }
            }
            
            // Set religion value and handle Other option
            const editReligionSelect = document.getElementById('edit_religion');
            const editReligionOtherInput = document.getElementById('edit_religion_other');
            const commonReligions = ['Roman Catholic', 'Islam', 'Iglesia ni Cristo', 'Philippine Independent Church', 'Seventh-day Adventist', 'Bible Baptist Church', 'United Church of Christ', "Jehovah's Witnesses", 'Protestant', 'Buddhism', 'Born Again Christian'];
            if (patient.religion) {
                if (commonReligions.includes(patient.religion)) {
                    editReligionSelect.value = patient.religion;
                } else {
                    editReligionSelect.value = 'Other';
                    editReligionOtherInput.value = patient.religion;
                    editReligionOtherInput.style.display = 'block';
                }
            }
            
            // Handle Edit Allergies "Other" option
            if (editAllergiesSelect && editAllergiesOtherInput) {
                editAllergiesSelect.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        editAllergiesOtherInput.style.display = 'block';
                        editAllergiesOtherInput.required = true;
                    } else {
                        editAllergiesOtherInput.style.display = 'none';
                        editAllergiesOtherInput.required = false;
                        editAllergiesOtherInput.value = '';
                    }
                });
            }
            
            // Handle Edit Religion "Other" option
            if (editReligionSelect && editReligionOtherInput) {
                editReligionSelect.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        editReligionOtherInput.style.display = 'block';
                        editReligionOtherInput.required = true;
                    } else {
                        editReligionOtherInput.style.display = 'none';
                        editReligionOtherInput.required = false;
                        editReligionOtherInput.value = '';
                    }
                });
            }
            
            // Phone number formatting and validation
            const contactInput = document.getElementById('edit_contact_number');
            if (contactInput) {
                contactInput.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.substring(0, 11);
                    this.value = value;
                    
                    // Real-time validation feedback
                    if (value.length === 11 && value.startsWith('09')) {
                        this.style.borderColor = '#28a745';
                    } else if (value.length > 0) {
                        this.style.borderColor = '#ffc107';
                    } else {
                        this.style.borderColor = '';
                    }
                });
            }
            
            // Real-time validation for editable required fields
            const requiredFields = ['edit_contact_number', 'edit_barangay_id', 'edit_date_of_admission'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && !field.disabled) {
                    field.addEventListener('blur', function() {
                        if (this.value.trim() === '') {
                            this.style.borderColor = '#dc3545';
                        } else {
                            this.style.borderColor = '#28a745';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 1000);
                        }
                    });
                    
                    field.addEventListener('input', function() {
                        if (this.value.trim() !== '' && this.style.borderColor === 'rgb(220, 53, 69)') {
                            this.style.borderColor = '';
                        }
                    });
                }
            });
            
            // Dropdown change feedback
            ['edit_parent_id', 'edit_nutritionist_id', 'edit_barangay_id'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('change', function() {
                        if (this.value) {
                            this.style.borderColor = '#28a745';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 1000);
                        }
                    });
                }
            });
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
        title: '<i class="fas fa-user-circle"></i> Patient Details',
        html: `
            <div class="swal-form-container">
                <!-- Patient ID Display -->
                <div style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); padding: 15px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,123,255,0.2);">
                    <div style="color: white; font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-id-card"></i> Patient ID
                    </div>
                    <div style="color: white; font-size: 24px; font-weight: bold; letter-spacing: 1px;">
                        ${patient.custom_patient_id || 'N/A'}
                    </div>
                </div>
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h6 class="section-title">
                        <i class="fas fa-user-circle" style="color: #007bff;"></i> Basic Information
                    </h6>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Full Name</label>
                            <div class="detail-value-display">${patient.first_name} ${patient.middle_name || ''} ${patient.last_name}</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-birthday-cake"></i> Birthdate</label>
                            <div class="detail-value-display">${patient.birthdate ? new Date(patient.birthdate).toLocaleDateString() : 'N/A'}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Age</label>
                            <div class="detail-value-display">${patient.age_months} months</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-venus-mars"></i> Sex</label>
                            <div class="detail-value-display">${patient.sex || 'N/A'}</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Contact Number</label>
                            <div class="detail-value-display">${patient.contact_number || 'N/A'}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Date of Admission</label>
                            <div class="detail-value-display">${new Date(patient.date_of_admission).toLocaleDateString()}</div>
                        </div>
                    </div>
                </div>

                <!-- Assignment & Location -->
                <div class="form-section">
                    <h6 class="section-title">
                        <i class="fas fa-user-tag" style="color: #28a745;"></i> Assignment & Location
                    </h6>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user-friends"></i> Parent / Guardian</label>
                            <div class="detail-value-display">${parentName}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user-md"></i> Assigned BNS</label>
                            <div class="detail-value-display">${nutritionistName}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt"></i> Barangay</label>
                            <div class="detail-value-display">${barangayName}</div>
                        </div>
                    </div>
                </div>

                <!-- Health Metrics -->
                <div class="form-section">
                    <h6 class="section-title">
                        <i class="fas fa-heartbeat" style="color: #dc3545;"></i> Health Metrics & Status
                    </h6>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-weight"></i> Weight</label>
                            <div class="detail-value-display">${patient.weight_kg} kg</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-ruler-vertical"></i> Height</label>
                            <div class="detail-value-display">${patient.height_cm} cm</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-baby"></i> Breastfeeding Status</label>
                            <div class="detail-value-display">${patient.breastfeeding || 'Not specified'}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-disease"></i> Edema Present</label>
                            <div class="detail-value-display">${patient.edema || 'Not specified'}</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-allergies"></i> Allergies</label>
                            <div class="detail-value-display">${patient.allergies || 'None reported'}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-pray"></i> Religion</label>
                            <div class="detail-value-display">${patient.religion || 'Not specified'}</div>
                        </div>
                    </div>
                    ${patient.other_medical_problems ? `
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label><i class="fas fa-notes-medical"></i> Other Medical Problems</label>
                            <div class="detail-value-display">${patient.other_medical_problems}</div>
                        </div>
                    </div>
                    ` : ''}
                </div>

                <!-- Household Information -->
                <div class="form-section">
                    <h6 class="section-title">
                        <i class="fas fa-home" style="color: #ffc107;"></i> Household Information
                    </h6>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Total Adults</label>
                            <div class="detail-value-display">${patient.total_household_adults || 0}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-child"></i> Total Children</label>
                            <div class="detail-value-display">${patient.total_household_children || 0}</div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-children"></i> Total Twins</label>
                            <div class="detail-value-display">${patient.total_household_twins || 0}</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label><i class="fas fa-hands-helping"></i> 4Ps Beneficiary</label>
                            <div class="detail-value-display">
                                ${patient.is_4ps_beneficiary ? 
                                    '<span class="detail-badge badge-success"><i class="fas fa-check"></i> Yes</span>' : 
                                    '<span class="detail-badge badge-warning"><i class="fas fa-times"></i> No</span>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-times-circle"></i> Close',
        customClass: {
            container: 'swal-patient-modal',
            popup: 'swal-patient-popup swal-view-patient-popup',
            htmlContainer: 'swal-view-patient-content',
            confirmButton: 'btn btn-secondary'
        },
        width: '950px'
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
    
    // Handle allergies with Other option
    const allergiesSelect = form.querySelector('[name="allergies"]');
    const allergiesOther = form.querySelector('[name="allergies_other"]');
    if (allergiesSelect) {
        if (allergiesSelect.value === 'Other' && allergiesOther && allergiesOther.value) {
            formData.append('allergies', allergiesOther.value);
        } else {
            formData.append('allergies', allergiesSelect.value);
        }
    }
    
    // Handle religion with Other option
    const religionSelect = form.querySelector('[name="religion"]');
    const religionOther = form.querySelector('[name="religion_other"]');
    if (religionSelect) {
        if (religionSelect.value === 'Other' && religionOther && religionOther.value) {
            formData.append('religion', religionOther.value);
        } else {
            formData.append('religion', religionSelect.value);
        }
    }

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
    const fields = ['first_name', 'middle_name', 'last_name', 'parent_id', 'nutritionist_id', 'barangay_id', 'contact_number', 'date_of_admission', 
                    'total_household_adults', 'total_household_children', 'total_household_twins', 
                    'breastfeeding', 'edema', 'other_medical_problems', 'is_4ps_beneficiary'];

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
    
    // Handle allergies with Other option
    const allergiesSelect = form.querySelector('[name="allergies"]');
    const allergiesOther = form.querySelector('[name="allergies_other"]');
    if (allergiesSelect && !allergiesSelect.disabled) {
        if (allergiesSelect.value === 'Other' && allergiesOther && allergiesOther.value) {
            formData.append('allergies', allergiesOther.value);
        } else {
            formData.append('allergies', allergiesSelect.value);
        }
    }
    
    // Handle religion with Other option
    const religionSelect = form.querySelector('[name="religion"]');
    const religionOther = form.querySelector('[name="religion_other"]');
    if (religionSelect && !religionSelect.disabled) {
        if (religionSelect.value === 'Other' && religionOther && religionOther.value) {
            formData.append('religion', religionOther.value);
        } else {
            formData.append('religion', religionSelect.value);
        }
    }

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

function showAssessmentHistory(patientId) {
    // Fetch assessment/screening data
    fetch(`/admin/patients/${patientId}/assessments`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayScreeningHistoryModal(data.patient, data.assessments);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to load assessment history'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error loading assessment history'
            });
        });
}

function displayScreeningHistoryModal(patient, assessments) {
    const patientName = patient.name || `${patient.first_name || ''} ${patient.last_name || ''}`.trim() || 'Unknown Patient';
    const patientId = patient.custom_patient_id;
    const totalScreenings = assessments.length;
    
    // Check if there are no assessments
    if (!assessments || assessments.length === 0) {
        Swal.fire({
            title: 'No Assessment History',
            html: `
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-clipboard-list" style="font-size: 64px; color: #6c757d; margin-bottom: 20px;"></i>
                    <p style="font-size: 16px; color: #6c757d;">
                        No assessment records found for <strong>${patientName}</strong>.
                    </p>
                    <p style="font-size: 14px; color: #999;">
                        Assessment history will appear here once evaluations are recorded.
                    </p>
                </div>
            `,
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Store globally for keyboard navigation
    window.currentAssessments = assessments;
    window.currentPatientId = patient.patient_id;
    window.currentAssessmentIndex = 0;
    
    // Get latest assessment
    const latestAssessment = assessments[0];
    
    // Build screening history sidebar
    let screeningList = '';
    assessments.forEach((assessment, index) => {
        const date = new Date(assessment.assessment_date);
        const formattedDate = date.toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });
        const classification = assessment.classification || 'No Classification';
        const isLatest = index === 0;
        
        screeningList += `
            <div class="screening-item ${isLatest ? 'active' : ''}" data-assessment-id="${assessment.assessment_id}">
                <div class="screening-date">
                    <i class="fas fa-calendar"></i> ${formattedDate}
                </div>
                <div class="screening-classification">${classification}</div>
                ${isLatest ? '<span class="latest-badge">LATEST</span>' : ''}
            </div>
        `;
    });
    
    // Build latest screening details
    const classificationBadge = getClassificationBadge(latestAssessment.classification);
    const latestDate = new Date(latestAssessment.assessment_date).toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });
    
    Swal.fire({
        html: `
            <div class="screening-history-container">
                <div class="screening-sidebar">
                    <div class="sidebar-header">
                        <i class="fas fa-history"></i>
                        <h3>Screening History</h3>
                    </div>
                    <div class="total-screenings">
                        <span class="count">${totalScreenings}</span> Total Screenings
                    </div>
                    <div class="sidebar-filters">
                        <input type="text" 
                               class="screening-search" 
                               placeholder="🔍 Search by date or status..." 
                               id="screeningSearch">
                        <select class="screening-filter" id="screeningFilter">
                            <option value="all">All Classifications</option>
                            <option value="sam">Severe (SAM)</option>
                            <option value="mam">Moderate (MAM)</option>
                            <option value="normal">Normal</option>
                        </select>
                    </div>
                    <div class="screening-list" id="screeningList">
                        ${screeningList}
                    </div>
                </div>
                <div class="screening-content">
                    <div class="content-loading-overlay" id="contentLoadingOverlay" style="display: none;">
                        <div class="loading-spinner-small">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading details...</p>
                        </div>
                    </div>
                    <div class="content-header">
                        <div class="patient-title">
                            <i class="fas fa-chart-line"></i>
                            <h2>${patientName}</h2>
                        </div>
                        <div class="header-actions">
                            <button class="btn-action btn-primary" onclick="showTreatmentPlan(${latestAssessment.assessment_id}, '${patientName}', ${patient.patient_id})">
                                <i class="fas fa-file-medical"></i> View Treatment Plan
                            </button>
                        </div>
                    </div>
                    <div class="screening-details">
                        <div class="detail-header">
                            <div class="detail-date">
                                <i class="fas fa-check-circle"></i> ${latestDate}
                            </div>
                            <span class="latest-badge-main">LATEST</span>
                        </div>
                        <div class="classification-badge-large">
                            ${classificationBadge}
                        </div>
                        <div class="metrics-grid">
                            <div class="metric-card metric-weight">
                                <div class="metric-icon">
                                    <i class="fas fa-weight"></i>
                                </div>
                                <div class="metric-details">
                                    <div class="metric-label">Weight</div>
                                    <div class="metric-value">${latestAssessment.weight_kg} kg</div>
                                </div>
                            </div>
                            <div class="metric-card metric-height">
                                <div class="metric-icon">
                                    <i class="fas fa-ruler-vertical"></i>
                                </div>
                                <div class="metric-details">
                                    <div class="metric-label">Height</div>
                                    <div class="metric-value">${latestAssessment.height_cm} cm</div>
                                </div>
                            </div>
                            <div class="metric-card metric-bmi">
                                <div class="metric-icon">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div class="metric-details">
                                    <div class="metric-label">BMI</div>
                                    <div class="metric-value">${latestAssessment.bmi || 'N/A'}</div>
                                </div>
                            </div>
                            <div class="metric-card metric-assessor">
                                <div class="metric-icon">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div class="metric-details">
                                    <div class="metric-label">Assessed By</div>
                                    <div class="metric-value">${latestAssessment.assessor_name || 'N/A'}</div>
                                </div>
                            </div>
                            <div class="metric-card metric-status">
                                <div class="metric-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div class="metric-details">
                                    <div class="metric-label">Status</div>
                                    <div class="metric-value">${latestAssessment.status || 'N/A'}</div>
                                </div>
                            </div>
                            ${latestAssessment.recovery_status ? `
                            <div class="metric-card metric-recovery">
                                <div class="metric-icon">
                                    <i class="fas fa-heartbeat"></i>
                                </div>
                                <div class="metric-details">
                                    <div class="metric-label">Recovery Status</div>
                                    <div class="metric-value">${latestAssessment.recovery_status}</div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                        ${formatAssessmentNotes(latestAssessment.notes)}
                        <div class="nutritional-indicators">
                            <div class="section-title">
                                <i class="fas fa-chart-bar"></i> Nutritional Indicators
                            </div>
                            <div class="indicators-grid">
                                ${getNutritionalIndicators(latestAssessment)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        width: '95%',
        customClass: {
            popup: 'screening-history-popup',
            closeButton: 'screening-close-btn'
        },
        didOpen: () => {
            setupScreeningItemClickHandlers(assessments);
        }
    });
}

function getTrendIndicator(current, previous) {
    if (!current || !previous) return '';
    
    const currentVal = parseFloat(current);
    const previousVal = parseFloat(previous);
    const diff = currentVal - previousVal;
    const percentChange = ((diff / previousVal) * 100).toFixed(1);
    
    if (Math.abs(diff) < 0.01) {
        return '<span class="trend-neutral" title="No change"><i class="fas fa-minus"></i></span>';
    } else if (diff > 0) {
        return `<span class="trend-up" title="Increased by ${Math.abs(diff).toFixed(1)} (+${percentChange}%)"><i class="fas fa-arrow-up"></i> ${Math.abs(diff).toFixed(1)}</span>`;
    } else {
        return `<span class="trend-down" title="Decreased by ${Math.abs(diff).toFixed(1)} (-${percentChange}%)"><i class="fas fa-arrow-down"></i> ${Math.abs(diff).toFixed(1)}</span>`;
    }
}

function formatAssessmentNotes(notes) {
    if (!notes) return '';
    
    // Try to parse as JSON
    let notesData = null;
    try {
        notesData = typeof notes === 'string' ? JSON.parse(notes) : notes;
    } catch (e) {
        // If not JSON, display as plain text
        return `
        <div class="assessment-notes">
            <div class="notes-header">
                <i class="fas fa-sticky-note"></i> Assessment Notes
            </div>
            <div class="notes-content">
                ${notes}
            </div>
        </div>
        `;
    }
    
    // If we have structured clinical data
    if (notesData && notesData.clinical_symptoms) {
        const symptoms = notesData.clinical_symptoms;
        const additionalNotes = notesData.additional_notes;
        
        const labelMap = {
            appetite: 'Appetite',
            edema: 'Edema',
            muac: 'MUAC',
            diarrhea: 'Diarrhea',
            vomiting: 'Vomiting',
            fever: 'Fever',
            visible_signs: 'Visible Signs',
            breastfeeding_status: 'Breastfeeding Status'
        };
        
        const valueMap = {
            '0': 'No',
            '1': 'Yes',
            'none': 'None',
            'mild': 'Mild',
            'moderate': 'Moderate',
            'severe': 'Severe',
            'good': 'Good',
            'poor': 'Poor',
            'not_applicable': 'Not Applicable'
        };
        
        let clinicalHtml = '<div class="assessment-notes">';
        clinicalHtml += '<div class="notes-header"><i class="fas fa-stethoscope"></i> Clinical Symptoms & Physical Signs</div>';
        clinicalHtml += '<div class="clinical-symptoms-grid">';
        
        Object.entries(symptoms).forEach(([key, value]) => {
            if (key === 'visible_signs' && Array.isArray(value) && value.length === 0) {
                return; // Skip empty visible signs
            }
            
            let displayValue = value;
            if (Array.isArray(value)) {
                displayValue = value.length > 0 ? value.join(', ') : 'None';
            } else {
                displayValue = valueMap[value] || value || 'N/A';
            }
            
            const label = labelMap[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            
            clinicalHtml += `
                <div class="clinical-symptom-item">
                    <div class="symptom-label">${label}:</div>
                    <div class="symptom-value">${displayValue}</div>
                </div>
            `;
        });
        
        clinicalHtml += '</div>';
        
        if (additionalNotes && additionalNotes.trim()) {
            clinicalHtml += `
                <div class="additional-notes-section">
                    <div class="additional-notes-label">
                        <i class="fas fa-comment-medical"></i> Additional Notes
                    </div>
                    <div class="additional-notes-text">${additionalNotes}</div>
                </div>
            `;
        }
        
        clinicalHtml += '</div>';
        return clinicalHtml;
    }
    
    // Fallback for other JSON structures
    return `
    <div class="assessment-notes">
        <div class="notes-header">
            <i class="fas fa-sticky-note"></i> Assessment Notes
        </div>
        <div class="notes-content">
            <pre>${JSON.stringify(notesData, null, 2)}</pre>
        </div>
    </div>
    `;
}

function getClassificationBadge(classification) {
    const classMap = {
        'Severe Acute Malnutrition (SAM)': { class: 'badge-sam', icon: 'fa-exclamation-triangle' },
        'Moderate Acute Malnutrition (MAM)': { class: 'badge-mam', icon: 'fa-exclamation-circle' },
        'Normal': { class: 'badge-normal', icon: 'fa-check-circle' }
    };
    
    const config = classMap[classification] || { class: 'badge-default', icon: 'fa-info-circle' };
    return `
        <div class="classification-badge ${config.class}">
            <i class="fas ${config.icon}"></i>
            ${classification || 'No Classification'}
        </div>
    `;
}

function getNutritionalIndicators(assessment) {
    const indicators = [
        { label: 'WEIGHT FOR AGE:', value: assessment.weight_for_age || 'N/A', status: assessment.weight_for_age_status || 'unknown' },
        { label: 'HEIGHT FOR AGE:', value: assessment.height_for_age || 'N/A', status: assessment.height_for_age_status || 'unknown' },
        { label: 'BMI FOR AGE:', value: assessment.bmi_for_age || 'N/A', status: assessment.bmi_for_age_status || 'unknown' }
    ];
    
    return indicators.map(indicator => `
        <div class="indicator-card">
            <div class="indicator-label">${indicator.label}</div>
            <div class="indicator-value status-${indicator.status}">
                ${indicator.value}
            </div>
        </div>
    `).join('');
}

function setupScreeningItemClickHandlers(assessments) {
    const items = document.querySelectorAll('.screening-item');
    items.forEach((item, index) => {
        item.addEventListener('click', function() {
            if (this.classList.contains('active')) return;
            
            const assessmentId = this.getAttribute('data-assessment-id');
            const assessment = assessments.find(a => a.assessment_id == assessmentId);
            if (assessment) {
                // Show loading overlay
                showContentLoading();
                
                // Update after short delay for smooth transition
                setTimeout(() => {
                    updateScreeningDetails(assessment, assessments);
                    items.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    window.currentAssessmentIndex = index;
                    hideContentLoading();
                }, 300);
            }
        });
    });
    
    // Setup search functionality
    const searchInput = document.getElementById('screeningSearch');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => filterScreenings(e.target.value, assessments));
    }
    
    // Setup filter dropdown
    const filterSelect = document.getElementById('screeningFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', (e) => filterByClassification(e.target.value, assessments));
    }
    
    // Setup keyboard navigation
    setupKeyboardNavigation(assessments);
}

function showContentLoading() {
    const overlay = document.getElementById('contentLoadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
        const content = document.querySelector('.screening-details');
        if (content) content.style.opacity = '0.5';
    }
}

function hideContentLoading() {
    const overlay = document.getElementById('contentLoadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
        const content = document.querySelector('.screening-details');
        if (content) content.style.opacity = '1';
    }
}

function filterScreenings(searchTerm, assessments) {
    const items = document.querySelectorAll('.screening-item');
    const term = searchTerm.toLowerCase();
    
    items.forEach((item, index) => {
        const assessment = assessments[index];
        const date = assessment.assessment_date.toLowerCase();
        const classification = assessment.classification.toLowerCase();
        
        if (date.includes(term) || classification.includes(term)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function filterByClassification(filterValue, assessments) {
    const items = document.querySelectorAll('.screening-item');
    
    items.forEach((item, index) => {
        const assessment = assessments[index];
        const classification = assessment.classification.toLowerCase();
        
        if (filterValue === 'all') {
            item.style.display = 'block';
        } else if (filterValue === 'sam' && classification.includes('severe')) {
            item.style.display = 'block';
        } else if (filterValue === 'mam' && classification.includes('moderate')) {
            item.style.display = 'block';
        } else if (filterValue === 'normal' && classification.includes('normal')) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function setupKeyboardNavigation(assessments) {
    document.addEventListener('keydown', function(e) {
        // Only work if screening modal is open
        if (!document.querySelector('.screening-history-container')) return;
        
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
            const direction = e.key === 'ArrowUp' ? -1 : 1;
            navigateToScreening(direction, assessments);
        }
    });
}

function navigateToScreening(direction, assessments) {
    const newIndex = window.currentAssessmentIndex + direction;
    
    if (newIndex >= 0 && newIndex < assessments.length) {
        const items = document.querySelectorAll('.screening-item');
        if (items[newIndex] && items[newIndex].style.display !== 'none') {
            items[newIndex].click();
            items[newIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
}

function updateScreeningDetails(assessment, allAssessments = []) {
    const classificationBadge = getClassificationBadge(assessment.classification);
    const date = new Date(assessment.assessment_date).toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });
    
    // Calculate trends by comparing with previous assessment
    const currentIndex = allAssessments.findIndex(a => a.assessment_id === assessment.assessment_id);
    const previousAssessment = currentIndex < allAssessments.length - 1 ? allAssessments[currentIndex + 1] : null;
    
    const detailsHtml = `
        <div class="detail-header">
            <div class="detail-date">
                <i class="fas fa-check-circle"></i> ${date}
            </div>
        </div>
        <div class="classification-badge-large">
            ${classificationBadge}
        </div>
        <div class="metrics-grid">
            <div class="metric-card metric-weight">
                <div class="metric-icon">
                    <i class="fas fa-weight"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">Weight</div>
                    <div class="metric-value">
                        ${assessment.weight_kg} kg
                        ${getTrendIndicator(assessment.weight_kg, previousAssessment?.weight_kg)}
                    </div>
                </div>
            </div>
            <div class="metric-card metric-height">
                <div class="metric-icon">
                    <i class="fas fa-ruler-vertical"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">Height</div>
                    <div class="metric-value">
                        ${assessment.height_cm} cm
                        ${getTrendIndicator(assessment.height_cm, previousAssessment?.height_cm)}
                    </div>
                </div>
            </div>
            <div class="metric-card metric-bmi">
                <div class="metric-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">BMI</div>
                    <div class="metric-value">
                        ${assessment.bmi || 'N/A'}
                        ${assessment.bmi && previousAssessment?.bmi ? getTrendIndicator(assessment.bmi, previousAssessment.bmi) : ''}
                    </div>
                </div>
            </div>
            <div class="metric-card metric-assessor">
                <div class="metric-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">Assessed By</div>
                    <div class="metric-value">${assessment.assessor_name || 'N/A'}</div>
                </div>
            </div>
            <div class="metric-card metric-status">
                <div class="metric-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">Status</div>
                    <div class="metric-value">${assessment.status || 'N/A'}</div>
                </div>
            </div>
            ${assessment.recovery_status ? `
            <div class="metric-card metric-recovery">
                <div class="metric-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">Recovery Status</div>
                    <div class="metric-value">${assessment.recovery_status}</div>
                </div>
            </div>
            ` : ''}
        </div>
        ${formatAssessmentNotes(assessment.notes)}
        <div class="nutritional-indicators">
            <div class="section-title">
                <i class="fas fa-chart-bar"></i> Nutritional Indicators
            </div>
            <div class="indicators-grid">
                ${getNutritionalIndicators(assessment)}
            </div>
        </div>
    `;
    
    const detailsContainer = document.querySelector('.screening-details');
    if (detailsContainer) {
        detailsContainer.innerHTML = detailsHtml;
    }
}

function deletePatient(patientId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal-delete-simple',
            title: 'swal-delete-title',
            htmlContainer: 'swal-delete-text',
            actions: 'swal-delete-actions',
            confirmButton: 'swal-delete-confirm-btn',
            cancelButton: 'swal-delete-cancel-btn'
        },
        buttonsStyling: false
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
                    admitted: row.dataset.admitted || '',
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
    
    if (searchInput) searchInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') filterPatients(); });
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
    // Remove duplicate event listeners by cloning and replacing buttons
    const viewBtns = document.querySelectorAll('.btn-outline-primary, .btn-primary');
    viewBtns.forEach(btn => {
        if (btn.title === 'View Details' && btn.hasAttribute('data-patient-id')) {
            // Clone to remove all existing event listeners
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const patientId = parseInt(this.getAttribute('data-patient-id'));
                viewPatient(patientId);
            });
        }
    });
    
    const assessmentBtns = document.querySelectorAll('.btn-outline-info, .btn-info');
    assessmentBtns.forEach(btn => {
        if ((btn.title === 'Assessment History' || btn.title === 'Assessment History') && btn.hasAttribute('data-patient-id')) {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const patientId = parseInt(this.getAttribute('data-patient-id'));
                showAssessmentHistory(patientId);
            });
        }
    });
    
    const editBtns = document.querySelectorAll('.btn-outline-warning, .btn-warning');
    editBtns.forEach(btn => {
        if ((btn.title === 'Edit Patient' || btn.title === 'Edit') && btn.hasAttribute('data-patient-id')) {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const patientId = parseInt(this.getAttribute('data-patient-id'));
                editPatient(patientId);
            });
        }
    });
    
    const deleteBtns = document.querySelectorAll('.btn-outline-danger, .btn-danger');
    deleteBtns.forEach(btn => {
        if ((btn.title === 'Delete Patient' || btn.title === 'Delete') && btn.hasAttribute('data-patient-id')) {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', function(e) {
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

    const url = new URL(window.location.href);
    // Reset to page 1 whenever filters change
    url.searchParams.delete('page');

    const search = searchInput ? searchInput.value.trim() : '';
    if (search) { url.searchParams.set('search', search); } else { url.searchParams.delete('search'); }

    const barangay = barangayFilter ? barangayFilter.value : '';
    if (barangay) { url.searchParams.set('barangay', barangay); } else { url.searchParams.delete('barangay'); }

    const gender = genderFilter ? genderFilter.value : '';
    if (gender) { url.searchParams.set('gender', gender); } else { url.searchParams.delete('gender'); }

    const ageRange = ageRangeFilter ? ageRangeFilter.value : '';
    if (ageRange) { url.searchParams.set('age_range', ageRange); } else { url.searchParams.delete('age_range'); }

    const nutritionist = nutritionistFilter ? nutritionistFilter.value : '';
    if (nutritionist) { url.searchParams.set('nutritionist', nutritionist); } else { url.searchParams.delete('nutritionist'); }

    window.location.href = url.toString();
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
            case 'admitted': aValue = a.data.admitted; bValue = b.data.admitted; break;
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
    // Navigate to the base patients URL, stripping all filter/page query params
    window.location.href = window.location.pathname;
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
    setupPaginationControls();
    
    setTimeout(() => {
        refreshPatientData();
    }, 100);

    console.log('Admin Patients page loaded with SweetAlert2');
});

// Setup pagination controls
function setupPaginationControls() {
    // Table view page jump
    const jumpToPageBtn = document.getElementById('jumpToPage');
    const pageJumpInput = document.getElementById('pageJump');
    
    if (jumpToPageBtn && pageJumpInput) {
        jumpToPageBtn.addEventListener('click', function() {
            const page = parseInt(pageJumpInput.value);
            const maxPage = parseInt(pageJumpInput.getAttribute('max'));
            
            if (page >= 1 && page <= maxPage) {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('page', page);
                window.location.href = currentUrl.toString();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Page',
                    text: `Please enter a page number between 1 and ${maxPage}`,
                    confirmButtonColor: '#007bff'
                });
            }
        });
        
        pageJumpInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                jumpToPageBtn.click();
            }
        });
    }
    
    // Grid view page jump
    const gridJumpToPageBtn = document.getElementById('gridJumpToPage');
    const gridPageJumpInput = document.getElementById('gridPageJump');
    
    if (gridJumpToPageBtn && gridPageJumpInput) {
        gridJumpToPageBtn.addEventListener('click', function() {
            const page = parseInt(gridPageJumpInput.value);
            const maxPage = parseInt(gridPageJumpInput.getAttribute('max'));
            
            if (page >= 1 && page <= maxPage) {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('page', page);
                window.location.href = currentUrl.toString();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Page',
                    text: `Please enter a page number between 1 and ${maxPage}`,
                    confirmButtonColor: '#007bff'
                });
            }
        });
        
        gridPageJumpInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                gridJumpToPageBtn.click();
            }
        });
    }
}

// Parent name click functionality removed - parent names are now display-only
/*
     *         Swal.fire({
     *             icon: 'success',
     *             title: 'Deleted!',
     *             text: 'Parent account has been deleted.',
     *             timer: 2000
     *         }).then(() => {
     *             window.location.reload();
     *         });
     *     } else {
     *         Swal.fire({
     *             icon: 'error',
     *             title: 'Error',
     *             text: data.message || 'Failed to delete parent account'
     *         });
     *     }
     * })
     * .catch(error => {
     *     console.error('Error:', error);
     *     Swal.fire({
     *         icon: 'error',
     *         title: 'Error',
     *         text: 'An error occurred while deleting the account'
     *     });
     * });
     */

// Make functions globally available
window.showAddPatientModal = showAddPatientModal;
window.editPatient = editPatient;
window.viewPatient = viewPatient;
window.deletePatient = deletePatient;
window.filterPatients = filterPatients;
window.switchView = switchView;
window.clearAllFilters = clearAllFilters;
window.refreshPatientData = refreshPatientData;
window.showTreatmentPlan = showTreatmentPlan;
window.backToScreeningHistory = backToScreeningHistory;

// Treatment Plan Viewing Functions
function showTreatmentPlan(assessmentId, patientName, patientId) {
    // Store patient ID for back navigation
    window.currentPatientId = patientId;
    
    // Fetch treatment data via admin route
    fetch(`/admin/assessments/${assessmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.assessment) {
                renderTreatmentPlanView(data.assessment, patientName, patientId);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to load treatment plan'
                });
            }
        })
        .catch(error => {
            console.error('Error loading treatment plan:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load treatment plan. Please try again.'
            });
        });
}

function renderTreatmentPlanView(assessment, patientName, patientId) {
    // Parse treatment plan
    let treatmentPlan = null;
    if (assessment.treatment_plan) {
        try {
            treatmentPlan = typeof assessment.treatment_plan === 'string' ? 
                JSON.parse(assessment.treatment_plan) : assessment.treatment_plan;
        } catch (e) {
            console.error('Failed to parse treatment_plan:', e);
        }
    }
    if (!treatmentPlan && assessment.treatment) {
        try {
            treatmentPlan = typeof assessment.treatment === 'string' ? 
                JSON.parse(assessment.treatment) : assessment.treatment;
        } catch (e) {
            console.error('Failed to parse treatment:', e);
        }
    }

    if (!treatmentPlan) {
        Swal.fire({
            icon: 'info',
            title: 'No Treatment Plan',
            text: 'No treatment plan available for this screening.'
        });
        return;
    }

    // Helper function to safely convert to array
    const toArray = (value) => {
        if (!value) return [];
        if (Array.isArray(value)) return value;
        if (typeof value === 'string') return [value];
        return [];
    };

    const diagnosis = treatmentPlan?.patient_info?.diagnosis || assessment.diagnosis || 'Status Unknown';
    const confidence = treatmentPlan?.patient_info?.confidence_level || 'N/A';
    
    // Determine diagnosis styling
    let diagnosisClass = 'unknown';
    let diagnosisIcon = 'fa-question-circle';
    if (diagnosis.includes('Severe')) {
        diagnosisClass = 'critical';
        diagnosisIcon = 'fa-exclamation-triangle';
    } else if (diagnosis.includes('Moderate')) {
        diagnosisClass = 'warning';
        diagnosisIcon = 'fa-exclamation-circle';
    } else if (diagnosis.includes('Normal')) {
        diagnosisClass = 'normal';
        diagnosisIcon = 'fa-check-circle';
    }

    // Convert all treatment plan sections to arrays
    const immediateActions = toArray(treatmentPlan.immediate_actions);
    const monitoring = toArray(treatmentPlan.monitoring);
    const familyEducation = toArray(treatmentPlan.family_education);
    const successCriteria = toArray(treatmentPlan.success_criteria);
    const dischargeCriteria = toArray(treatmentPlan.discharge_criteria);
    const emergencySigns = toArray(treatmentPlan.emergency_signs);
    const clinicalSigns = treatmentPlan.clinical_signs || {};
    const additionalNotes = treatmentPlan.additional_notes || assessment.notes || '';

    const htmlContent = `
        <div class="swal-treatment-container">
            <div class="swal-treatment-header">
                <button class="btn btn-secondary btn-sm swal-back-button" onclick="backToScreeningHistory(${patientId})">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to Screening
                </button>
                <h3 class="swal-header-title">
                    <i class="fas fa-prescription"></i>
                    Treatment & Care Plan
                </h3>
                <p class="swal-header-subtitle">${patientName} - ${assessment.assessment_date || 'N/A'}</p>
            </div>
            
            <div class="swal-treatment-content">
                <div class="swal-diagnosis-header">
                    <div class="swal-diagnosis-badge ${diagnosisClass}">
                        <i class="fas ${diagnosisIcon}"></i>
                        <span>${diagnosis}</span>
                    </div>
                    ${confidence !== 'N/A' ? `
                    <div class="swal-confidence-badge">
                        <i class="fas fa-chart-line"></i>
                        <span>Confidence: ${confidence}</span>
                    </div>
                    ` : ''}
                </div>

                ${immediateActions.length > 0 ? `
                <div class="swal-treatment-section">
                    <h4 class="swal-section-title">
                        <i class="fas fa-bolt"></i>
                        Immediate Actions
                    </h4>
                    <ul class="swal-treatment-list">
                        ${immediateActions.map(action => `<li>${action}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}

                ${monitoring.length > 0 ? `
                <div class="swal-treatment-section">
                    <h4 class="swal-section-title">
                        <i class="fas fa-heartbeat"></i>
                        Monitoring & Follow-up
                    </h4>
                    <ul class="swal-treatment-list">
                        ${monitoring.map(item => `<li>${item}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}

                ${familyEducation.length > 0 ? `
                <div class="swal-treatment-section">
                    <h4 class="swal-section-title">
                        <i class="fas fa-users"></i>
                        Family Education & Support
                    </h4>
                    <ul class="swal-treatment-list">
                        ${familyEducation.map(item => `<li>${item}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}

                ${successCriteria.length > 0 ? `
                <div class="swal-treatment-section">
                    <h4 class="swal-section-title">
                        <i class="fas fa-check-circle"></i>
                        Success Criteria
                    </h4>
                    <ul class="swal-treatment-list">
                        ${successCriteria.map(item => `<li>${item}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}

                ${dischargeCriteria.length > 0 ? `
                <div class="swal-treatment-section">
                    <h4 class="swal-section-title">
                        <i class="fas fa-clipboard-check"></i>
                        Discharge Criteria
                    </h4>
                    <ul class="swal-treatment-list">
                        ${dischargeCriteria.map(item => `<li>${item}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}

                ${Object.keys(clinicalSigns).length > 0 ? `
                <div class="swal-treatment-section">
                    <h4 class="swal-section-title">
                        <i class="fas fa-stethoscope"></i>
                        Clinical Symptoms & Physical Signs
                    </h4>
                    <div class="clinical-signs-grid">
                        ${Object.entries(clinicalSigns).map(([key, value]) => `
                            <div class="clinical-sign-item">
                                <div class="clinical-sign-label">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</div>
                                <div class="clinical-sign-value">${value}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}

                ${additionalNotes ? `
                <div class="swal-treatment-section">
                    <h4 class="swal-section-title">
                        <i class="fas fa-sticky-note"></i>
                        Additional Notes
                    </h4>
                    <div class="additional-notes-content">
                        ${additionalNotes}
                    </div>
                </div>
                ` : ''}

                ${emergencySigns.length > 0 ? `
                <div class="swal-treatment-section swal-emergency">
                    <h4 class="swal-section-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Emergency Warning Signs
                    </h4>
                    <ul class="swal-treatment-list">
                        ${emergencySigns.map(item => `<li>${item}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}
            </div>
        </div>
    `;

    Swal.fire({
        html: htmlContent,
        width: '90%',
        showCancelButton: false,
        showConfirmButton: false,
        showCloseButton: true,
        scrollbarPadding: false,
        heightAuto: false,
        customClass: {
            popup: 'treatment-popup',
            htmlContainer: 'p-0',
            closeButton: 'screening-close-btn'
        },
        buttonsStyling: false
    });
}

function backToScreeningHistory(patientId) {
    Swal.close();
    showAssessmentHistory(patientId);
}

// Export functions to global scope for use by other scripts (e.g., patients-archive.js)
window.setupActionButtons = setupActionButtons;
window.viewPatient = viewPatient;
window.editPatient = editPatient;
window.showAssessmentHistory = showAssessmentHistory;
window.deletePatient = deletePatient;
