<!-- Patient Information Header -->
<div class="modal-patient-info mb-4">
    <div class="row">
        <div class="col-md-6">
            <h6 class="mb-1">{{ $patient->first_name }} {{ $patient->last_name }}</h6>
            <small class="text-muted">
                {{ $patient->age_months }} months old • {{ ucfirst($patient->sex) }}
            </small>
        </div>
        <div class="col-md-6">
            <small class="text-muted">
                <i class="fas fa-user"></i> {{ $patient->parent->first_name ?? 'N/A' }} {{ $patient->parent->last_name ?? '' }}<br>
                <i class="fas fa-map-marker-alt"></i> {{ $patient->barangay->name ?? 'N/A' }}
            </small>
        </div>
    </div>
</div>

<!-- Assessment Form -->
<form method="POST" action="{{ route('nutritionist.assessment.perform') }}" id="assessmentForm">
    @csrf
    <input type="hidden" name="patient_id" value="{{ $patient->patient_id }}">

    <div class="row">
        <!-- Left Column - Measurements -->
        <div class="col-md-6">
            <!-- Essential Measurements -->
            <div class="form-section mb-4">
                <h6 class="section-title"><i class="fas fa-ruler-combined"></i> Essential Measurements</h6>
                
                <div class="mb-3">
                    <label for="age_months" class="form-label required">Age (months)</label>
                    <input type="number" 
                           class="form-control" 
                           id="age_months" 
                           name="age_months" 
                           value="{{ old('age_months', $patient->getAgeInMonths()) }}" 
                           min="0" 
                           max="240" 
                           required>
                    <small class="form-text text-muted">Current age: {{ $patient->getAgeInMonths() }} months</small>
                </div>
                
                <div class="mb-3">
                    <label for="weight_kg" class="form-label required">Weight (kg)</label>
                    <input type="number" 
                           class="form-control" 
                           id="weight_kg" 
                           name="weight_kg" 
                           value="{{ old('weight_kg', $patient->weight_kg) }}" 
                           step="0.1" 
                           min="1" 
                           max="200" 
                           required>
                    @if($patient->weight_kg)
                        <small class="form-text text-muted">Last recorded: {{ $patient->weight_kg }} kg</small>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="height_cm" class="form-label required">Height (cm)</label>
                    <input type="number" 
                           class="form-control" 
                           id="height_cm" 
                           name="height_cm" 
                           value="{{ old('height_cm', $patient->height_cm) }}" 
                           step="0.1" 
                           min="30" 
                           max="250" 
                           required>
                    @if($patient->height_cm)
                        <small class="form-text text-muted">Last recorded: {{ $patient->height_cm }} cm</small>
                    @endif
                </div>
                
                <div class="mb-3">
                    <label for="gender" class="form-label required">Gender</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="male" {{ old('gender', $patient->sex) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $patient->sex) == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    <small class="form-text text-muted">Patient gender: {{ ucfirst($patient->sex) }}</small>
                </div>
            </div>

            <!-- Clinical Symptoms -->
            <div class="form-section">
                <h6 class="section-title"><i class="fas fa-stethoscope"></i> Clinical Symptoms</h6>
                
                <div class="mb-3">
                    <label for="appetite" class="form-label">Appetite</label>
                    <select class="form-control" id="appetite" name="appetite">
                        <option value="good" {{ old('appetite') == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="poor" {{ old('appetite') == 'poor' ? 'selected' : '' }}>Poor</option>
                        <option value="none" {{ old('appetite') == 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="diarrhea_days" class="form-label">Diarrhea (days)</label>
                    <input type="number" 
                           class="form-control" 
                           id="diarrhea_days" 
                           name="diarrhea_days" 
                           value="{{ old('diarrhea_days', 0) }}" 
                           min="0" 
                           max="30">
                    <small class="form-text text-muted">Number of days with diarrhea in past month</small>
                </div>

                <div class="mb-3">
                    <label for="fever_days" class="form-label">Fever (days)</label>
                    <input type="number" 
                           class="form-control" 
                           id="fever_days" 
                           name="fever_days" 
                           value="{{ old('fever_days', 0) }}" 
                           min="0" 
                           max="30">
                    <small class="form-text text-muted">Number of days with fever in past month</small>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="vomiting" 
                               name="vomiting" 
                               {{ old('vomiting') ? 'checked' : '' }}>
                        <label class="form-check-label" for="vomiting">
                            Recent vomiting episodes
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Socioeconomic -->
        <div class="col-md-6">
            <!-- Socioeconomic Information -->
            <div class="form-section mb-4">
                <h6 class="section-title"><i class="fas fa-home"></i> Socioeconomic Information</h6>
                
                <div class="mb-3">
                    <label for="household_size" class="form-label">Household Size</label>
                    <input type="number" 
                           class="form-control" 
                           id="household_size" 
                           name="household_size" 
                           value="{{ old('household_size', ($patient->total_household_adults ?? 0) + ($patient->total_household_children ?? 0) + ($patient->total_household_twins ?? 0) + 1) }}" 
                           min="1" 
                           max="20">
                    <small class="form-text text-muted">Total number of people in household</small>
                </div>
                
                <div class="mb-3">
                    <label for="mother_education" class="form-label">Mother's Education</label>
                    <select class="form-control" id="mother_education" name="mother_education">
                        <option value="none" {{ old('mother_education') == 'none' ? 'selected' : '' }}>No Formal Education</option>
                        <option value="primary" {{ old('mother_education', 'primary') == 'primary' ? 'selected' : '' }}>Primary Education</option>
                        <option value="secondary" {{ old('mother_education') == 'secondary' ? 'selected' : '' }}>Secondary Education</option>
                        <option value="tertiary" {{ old('mother_education') == 'tertiary' ? 'selected' : '' }}>Tertiary Education</option>
                    </select>
                </div>

                <!-- Government Benefits -->
                <div class="mb-3">
                    <h6 class="mb-2">Government Benefits & Resources</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_4ps_beneficiary" 
                               name="is_4ps_beneficiary" 
                               {{ old('is_4ps_beneficiary', $patient->is_4ps_beneficiary) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_4ps_beneficiary">
                            4Ps Beneficiary
                            @if($patient->is_4ps_beneficiary)
                                <small class="text-muted">(from patient record)</small>
                            @endif
                        </label>
                    </div>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="has_electricity" 
                               name="has_electricity" 
                               {{ old('has_electricity') ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_electricity">
                            Has Electricity
                        </label>
                    </div>
                </div>

                <!-- Household Conditions -->
                <div class="mb-3">
                    <h6 class="mb-2">Household Conditions</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="has_clean_water" 
                               name="has_clean_water" 
                               {{ old('has_clean_water') ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_clean_water">
                            Clean Water Access
                        </label>
                    </div>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="father_present" 
                               name="father_present" 
                               {{ old('father_present') ? 'checked' : '' }}>
                        <label class="form-check-label" for="father_present">
                            Father Present
                        </label>
                    </div>
                </div>
            </div>

            <!-- Clinical Notes -->
            <div class="form-section">
                <h6 class="section-title"><i class="fas fa-notes-medical"></i> Additional Notes</h6>
                <div class="mb-3">
                    <label for="notes" class="form-label">Clinical Observations</label>
                    <textarea class="form-control" 
                              id="notes" 
                              name="notes" 
                              rows="4" 
                              placeholder="Record any additional clinical observations, symptoms, or relevant information...">{{ old('notes', $patient->other_medical_problems) }}</textarea>
                    <small class="form-text text-muted">Include any relevant medical history, current medications, or special circumstances
                    @if($patient->other_medical_problems)
                        <br><strong>From patient record:</strong> {{ Str::limit($patient->other_medical_problems, 100) }}
                    @endif
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.modal-patient-info {
    background: linear-gradient(135deg, #43A047 0%, #66BB6A 100%);
    padding: 0.75rem;
    border-radius: 0.5rem;
    color: white;
    margin-bottom: 1rem;
}

.modal-patient-info h6 {
    color: white;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.modal-patient-info .text-muted {
    color: rgba(255, 255, 255, 0.9) !important;
    font-size: 0.85rem;
}

.modal-patient-info i {
    color: rgba(255, 255, 255, 0.9);
}

.section-title {
    color: #2e7d32;
    border-bottom: 2px solid #66BB6A;
    padding-bottom: 0.4rem;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
    font-weight: 600;
}

.section-title i {
    color: #43A047;
    margin-right: 0.4rem;
}

.form-label.required::after {
    content: " *";
    color: #dc3545;
}

.form-section {
    border: 1px solid #e0e0e0;
    border-radius: 0.5rem;
    padding: 0.75rem;
    background: #fafafa;
    margin-bottom: 0.75rem;
}

.form-section h6 {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.mb-3 {
    margin-bottom: 0.75rem !important;
}

.form-label {
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 0.3rem;
    color: #424242;
}

.form-control, .form-select {
    font-size: 0.875rem;
    padding: 0.4rem 0.6rem;
    border-radius: 0.375rem;
    border: 1px solid #d0d0d0;
}

.form-control:focus, .form-select:focus {
    border-color: #43A047;
    box-shadow: 0 0 0 0.2rem rgba(67, 160, 71, 0.25);
}

.form-text {
    font-size: 0.75rem;
    color: #757575;
    margin-top: 0.2rem;
}

.form-check {
    padding-left: 1.5rem;
}

.form-check-input {
    margin-top: 0.15rem;
}

.form-check-label {
    font-size: 0.85rem;
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

/* Compact spacing for modal */
#swal-assessmentFormContent .row {
    margin-left: -0.5rem;
    margin-right: -0.5rem;
}

#swal-assessmentFormContent .col-md-6 {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

/* Assessment modal specific styles */
.assessment-modal-container {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 9999 !important;
}

.assessment-modal-popup {
    max-height: 90vh !important;
    margin: auto !important;
    overflow: hidden !important;
}
</style>
