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
        </div>

        <!-- Right Column - Socioeconomic & Notes -->
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

    <!-- Clinical Symptoms & Signs - Full Width Section -->
    <div class="form-section mb-4">
        <h6 class="section-title"><i class="fas fa-stethoscope"></i> Clinical Symptoms & Physical Signs</h6>
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <!-- Appetite & Feeding -->
                <div class="mb-3">
                    <label for="appetite" class="form-label">Appetite Level</label>
                    <select class="form-control" id="appetite" name="appetite">
                        <option value="">Select appetite level</option>
                        <option value="good" {{ old('appetite') == 'good' ? 'selected' : '' }}>Good - Eats well, finishes meals</option>
                        <option value="fair" {{ old('appetite') == 'fair' ? 'selected' : '' }}>Fair - Eats moderately</option>
                        <option value="poor" {{ old('appetite') == 'poor' ? 'selected' : '' }}>Poor - Refuses food often</option>
                        <option value="very_poor" {{ old('appetite') == 'very_poor' ? 'selected' : '' }}>Very Poor - Minimal intake</option>
                    </select>
                </div>

                <!-- Edema Assessment -->
                <div class="mb-3">
                    <label for="edema" class="form-label">Edema (Swelling)</label>
                    <select class="form-control" id="edema" name="edema">
                        <option value="none" {{ old('edema', 'none') == 'none' ? 'selected' : '' }}>None</option>
                        <option value="mild" {{ old('edema') == 'mild' ? 'selected' : '' }}>Mild - Both feet/ankles</option>
                        <option value="moderate" {{ old('edema') == 'moderate' ? 'selected' : '' }}>Moderate - Both feet, legs, hands</option>
                        <option value="severe" {{ old('edema') == 'severe' ? 'selected' : '' }}>Severe - Generalized edema</option>
                    </select>
                    <small class="form-text text-muted">Check for bilateral pitting edema</small>
                </div>

                <!-- MUAC (Mid-Upper Arm Circumference) -->
                <div class="mb-3">
                    <label for="muac" class="form-label">MUAC (cm)</label>
                    <input type="number" 
                           class="form-control" 
                           id="muac" 
                           name="muac" 
                           value="{{ old('muac') }}" 
                           step="0.1" 
                           min="5" 
                           max="30"
                           placeholder="e.g., 11.5">
                    <small class="form-text text-muted">Mid-Upper Arm Circumference for malnutrition screening</small>
                </div>

                <!-- Gastrointestinal Symptoms -->
                <div class="mb-3">
                    <label class="form-label d-block">Gastrointestinal Symptoms</label>
                    
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="diarrhea_days" class="form-label small">Diarrhea (days in past 2 weeks)</label>
                            <input type="number" 
                                   class="form-control form-control-sm" 
                                   id="diarrhea_days" 
                                   name="diarrhea_days" 
                                   value="{{ old('diarrhea_days', 0) }}" 
                                   min="0" 
                                   max="14">
                        </div>
                        <div class="col-md-6">
                            <label for="vomiting_frequency" class="form-label small">Vomiting (times per day)</label>
                            <input type="number" 
                                   class="form-control form-control-sm" 
                                   id="vomiting_frequency" 
                                   name="vomiting_frequency" 
                                   value="{{ old('vomiting_frequency', 0) }}" 
                                   min="0" 
                                   max="20">
                        </div>
                    </div>
                </div>

                <!-- Fever & Infections -->
                <div class="mb-3">
                    <label for="fever_days" class="form-label">Fever (days in past 2 weeks)</label>
                    <input type="number" 
                           class="form-control" 
                           id="fever_days" 
                           name="fever_days" 
                           value="{{ old('fever_days', 0) }}" 
                           min="0" 
                           max="14">
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <!-- Visible Signs of Malnutrition -->
                <div class="mb-3">
                    <label class="form-label d-block">Visible Signs (Check all that apply)</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="skin_changes" name="skin_changes" {{ old('skin_changes') ? 'checked' : '' }}>
                        <label class="form-check-label" for="skin_changes">
                            Skin changes (dry, peeling, lesions)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="hair_changes" name="hair_changes" {{ old('hair_changes') ? 'checked' : '' }}>
                        <label class="form-check-label" for="hair_changes">
                            Hair changes (thin, discolored, easily pluckable)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="muscle_wasting" name="muscle_wasting" {{ old('muscle_wasting') ? 'checked' : '' }}>
                        <label class="form-check-label" for="muscle_wasting">
                            Visible muscle wasting
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="lethargy" name="lethargy" {{ old('lethargy') ? 'checked' : '' }}>
                        <label class="form-check-label" for="lethargy">
                            Lethargy or reduced activity
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="pallor" name="pallor" {{ old('pallor') ? 'checked' : '' }}>
                        <label class="form-check-label" for="pallor">
                            Pallor (pale skin/mucous membranes)
                        </label>
                    </div>
                </div>

                <!-- Breastfeeding Status (for infants) -->
                <div class="mb-3">
                    <label for="breastfeeding_status" class="form-label">Breastfeeding Status</label>
                    <select class="form-control" id="breastfeeding_status" name="breastfeeding_status">
                        <option value="not_applicable" {{ old('breastfeeding_status', 'not_applicable') == 'not_applicable' ? 'selected' : '' }}>Not Applicable (>24 months)</option>
                        <option value="exclusive" {{ old('breastfeeding_status') == 'exclusive' ? 'selected' : '' }}>Exclusive breastfeeding</option>
                        <option value="complementary" {{ old('breastfeeding_status') == 'complementary' ? 'selected' : '' }}>Breastfeeding + complementary foods</option>
                        <option value="formula" {{ old('breastfeeding_status') == 'formula' ? 'selected' : '' }}>Formula feeding only</option>
                        <option value="mixed" {{ old('breastfeeding_status') == 'mixed' ? 'selected' : '' }}>Mixed feeding</option>
                        <option value="weaned" {{ old('breastfeeding_status') == 'weaned' ? 'selected' : '' }}>Weaned</option>
                    </select>
                    <small class="form-text text-muted">For children under 24 months</small>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('assessmentForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        // Collect all clinical symptoms data
        const clinicalData = {
            appetite: document.getElementById('appetite')?.value || '',
            edema: document.getElementById('edema')?.value || '',
            muac: document.getElementById('muac')?.value || '',
            gastrointestinal: {
                diarrhea_days: document.getElementById('diarrhea_days')?.value || '0',
                vomiting_frequency: document.getElementById('vomiting_frequency')?.value || '0'
            },
            fever_days: document.getElementById('fever_days')?.value || '0',
            visible_signs: {
                skin_changes: document.getElementById('skin_changes')?.checked || false,
                hair_changes: document.getElementById('hair_changes')?.checked || false,
                muscle_wasting: document.getElementById('muscle_wasting')?.checked || false,
                lethargy: document.getElementById('lethargy')?.checked || false,
                pallor: document.getElementById('pallor')?.checked || false
            },
            breastfeeding_status: document.getElementById('breastfeeding_status')?.value || ''
        };

        // Get the notes textarea
        const notesField = document.getElementById('notes');
        if (notesField) {
            const currentNotes = notesField.value.trim();
            
            // Create a structured note with clinical data
            const structuredNote = {
                clinical_symptoms: clinicalData,
                additional_notes: currentNotes,
                recorded_at: new Date().toISOString()
            };

            // Store as JSON string in the notes field
            notesField.value = JSON.stringify(structuredNote, null, 2);
        }
    });
});
</script>