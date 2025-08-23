@extends('layouts.dashboard')

@section('title', 'Patient Assessment')

@section('page-title', 'Assessment')
@section('page-subtitle', '{{ $patient->first_name }} {{ $patient->last_name }}')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@push('styles')
<style>
/* Modern Minimalist Assessment Form - Full Width */
.assessment-container {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 1rem;
    height: calc(100vh - 140px); /* Account for header and padding */
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.assessment-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}

.assessment-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 150px;
    height: 150px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translate(50%, -50%);
}

.header-content {
    position: relative;
    z-index: 1;
}

.patient-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    letter-spacing: -0.025em;
}

.patient-meta {
    display: flex;
    gap: 1.5rem;
    margin-top: 0.75rem;
    opacity: 0.9;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
}

.back-button {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    border: 2px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.1);
}

.back-button:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    border-color: rgba(255, 255, 255, 0.3);
}

.header-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    position: relative;
    z-index: 2;
}

.header-left-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.header-right-info {
    display: flex;
    align-items: center;
}

.assessment-status {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.add-action-btn {
    background: rgba(255, 255, 255, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.6rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
}

.add-action-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.form-double-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    flex: 1;
    min-height: 0;
}

.form-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 100%;
}

.form-left {
    border-right: 2px solid #e5e7eb;
}

.form-right {
    border-left: 2px solid #e5e7eb;
}

.form-header {
    background: #f8fafc;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.form-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.form-body {
    padding: 1.5rem;
    flex: 1;
    overflow-y: auto;
    width: 100%;
    box-sizing: border-box;
}

.section {
    margin-bottom: 3rem;
}

.section:last-child {
    margin-bottom: 0;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e2e8f0;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 2px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.form-group {
    position: relative;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-label.required::after {
    content: '*';
    color: #ef4444;
    margin-left: 0.25rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
    transition: all 0.2s ease;
}

.form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-help {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 0;
}

.checkbox-input {
    width: 18px;
    height: 18px;
    border: 2px solid #e5e7eb;
    border-radius: 4px;
    accent-color: #667eea;
}

.checkbox-label {
    font-size: 0.9rem;
    color: #374151;
    cursor: pointer;
}

.symptoms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.symptom-item {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s ease;
}

.symptom-item:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.symptom-item.checked {
    background: #eff6ff;
    border-color: #3b82f6;
}

.form-actions {
    background: #f8fafc;
    padding: 1.5rem 2rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    color: white;
    text-decoration: none;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border: none;
}

.alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

@media (max-width: 768px) {
    .assessment-header {
        padding: 1.5rem;
    }
    
    .patient-name {
        font-size: 1.5rem;
    }
    
    .patient-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-body {
        padding: 1.5rem;
    }
    
    .form-grid,
    .form-grid-2 {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}
</style>
@endpush

@section('content')
<div class="assessment-container">
    <!-- Error Display -->
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-1">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Assessment Header -->
    <div class="assessment-header">
        <div class="header-top-row">
            <div class="header-left-actions">
                <a href="{{ route('nutritionist.patients') }}" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="button" class="add-action-btn" onclick="quickAssessment()">
                    <i class="fas fa-plus"></i> Quick Assessment
                </button>
            </div>
            
            <div class="header-right-info">
                <span class="assessment-status">Assessment</span>
            </div>
        </div>
        
        <div class="header-content">
            <h1 class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</h1>
            
            <div class="patient-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>{{ $patient->age_months }} months old</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-{{ $patient->sex == 'male' ? 'mars' : 'venus' }}"></i>
                    <span>{{ ucfirst($patient->sex) }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span>{{ $patient->parent->first_name ?? 'N/A' }} {{ $patient->parent->last_name ?? '' }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ $patient->barangay->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Form - Two Column Layout -->
    <div class="form-double-container">
        <!-- Left Column -->
        <div class="form-container form-left">
            <div class="form-header">
                <h2 class="form-title">Patient Measurements</h2>
            </div>

            <div class="form-body">
                <form method="POST" action="{{ route('nutritionist.assessment.perform') }}" id="assessmentForm">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $patient->patient_id }}">

                    <!-- Essential Measurements -->
                    <div class="form-section">
                        <h4><i class="fas fa-ruler-combined"></i> Essential Measurements</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="age_months" class="required">Age (months)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="age_months" 
                                       name="age_months" 
                                       value="{{ old('age_months', $patient->getAgeInMonths()) }}" 
                                       min="0" 
                                       max="240" 
                                       required>
                                <small class="form-text">Current age: {{ $patient->getAgeInMonths() }} months</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="weight_kg" class="required">Weight (kg)</label>
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
                                    <small class="form-text">Last recorded: {{ $patient->weight_kg }} kg</small>
                                @endif
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="height_cm" class="required">Height (cm)</label>
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
                                    <small class="form-text">Last recorded: {{ $patient->height_cm }} cm</small>
                                @endif
                            </div>
                            
                            <div class="form-group">
                                <label for="gender" class="required">Gender</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="male" {{ old('gender', $patient->sex) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $patient->sex) == 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                                <small class="form-text">Patient gender: {{ ucfirst($patient->sex) }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Symptoms -->
                    <div class="form-section">
                        <h4><i class="fas fa-stethoscope"></i> Clinical Symptoms</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="appetite">Appetite</label>
                                <select class="form-control" id="appetite" name="appetite">
                                    <option value="good" {{ old('appetite') == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="poor" {{ old('appetite') == 'poor' ? 'selected' : '' }}>Poor</option>
                                    <option value="none" {{ old('appetite') == 'none' ? 'selected' : '' }}>None</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="diarrhea_days">Diarrhea (days)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="diarrhea_days" 
                                       name="diarrhea_days" 
                                       value="{{ old('diarrhea_days', 0) }}" 
                                       min="0" 
                                       max="30">
                                <small class="form-text">Number of days with diarrhea in past month</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fever_days">Fever (days)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="fever_days" 
                                       name="fever_days" 
                                       value="{{ old('fever_days', 0) }}" 
                                       min="0" 
                                       max="30">
                                <small class="form-text">Number of days with fever in past month</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check-container">
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
                </form>
            </div>
        </div>

        <!-- Right Column -->
        <div class="form-container form-right">
            <div class="form-header">
                <h2 class="form-title">Socioeconomic & Notes</h2>
            </div>

            <div class="form-body">
                <!-- Socioeconomic Information -->
                <div class="form-section">
                    <h4><i class="fas fa-home"></i> Socioeconomic Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="household_size">Household Size</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="household_size" 
                                   name="household_size" 
                                   value="{{ old('household_size', ($patient->total_household_adults ?? 0) + ($patient->total_household_children ?? 0) + ($patient->total_household_twins ?? 0) + 1) }}" 
                                   min="1" 
                                   max="20"
                                   form="assessmentForm">
                            <small class="form-text">Total number of people in household 
                            @if($patient->total_household_adults || $patient->total_household_children)
                                (Based on: {{ $patient->total_household_adults ?? 0 }} adults + {{ $patient->total_household_children ?? 0 }} children + {{ $patient->total_household_twins ?? 0 }} twins + patient)
                            @endif
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="mother_education">Mother's Education</label>
                            <select class="form-control" id="mother_education" name="mother_education" form="assessmentForm">
                                <option value="none" {{ old('mother_education') == 'none' ? 'selected' : '' }}>No Formal Education</option>
                                <option value="primary" {{ old('mother_education', 'primary') == 'primary' ? 'selected' : '' }}>Primary Education</option>
                                <option value="secondary" {{ old('mother_education') == 'secondary' ? 'selected' : '' }}>Secondary Education</option>
                                <option value="tertiary" {{ old('mother_education') == 'tertiary' ? 'selected' : '' }}>Tertiary Education</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section-grid">
                        <div class="checkbox-group">
                            <h5>Government Benefits & Resources</h5>
                            <div class="checkbox-grid">
                                <div class="form-check-container">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_4ps_beneficiary" 
                                           name="is_4ps_beneficiary" 
                                           {{ old('is_4ps_beneficiary', $patient->is_4ps_beneficiary) ? 'checked' : '' }}
                                           form="assessmentForm">
                                    <label class="form-check-label" for="is_4ps_beneficiary">
                                        <i class="fas fa-handshake"></i> 4Ps Beneficiary
                                        @if($patient->is_4ps_beneficiary)
                                            <small class="text-muted">(from patient record)</small>
                                        @endif
                                    </label>
                                </div>
                                
                                <div class="form-check-container">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="has_electricity" 
                                           name="has_electricity" 
                                           {{ old('has_electricity') ? 'checked' : '' }}
                                           form="assessmentForm">
                                    <label class="form-check-label" for="has_electricity">
                                        <i class="fas fa-bolt"></i> Has Electricity
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="checkbox-group">
                            <h5>Household Conditions</h5>
                            <div class="checkbox-grid">
                                <div class="form-check-container">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="has_clean_water" 
                                           name="has_clean_water" 
                                           {{ old('has_clean_water') ? 'checked' : '' }}
                                           form="assessmentForm">
                                    <label class="form-check-label" for="has_clean_water">
                                        <i class="fas fa-tint"></i> Clean Water Access
                                    </label>
                                </div>
                                
                                <div class="form-check-container">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="father_present" 
                                           name="father_present" 
                                           {{ old('father_present') ? 'checked' : '' }}
                                           form="assessmentForm">
                                    <label class="form-check-label" for="father_present">
                                        <i class="fas fa-user-friends"></i> Father Present
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clinical Notes -->
                <div class="form-section">
                    <h4><i class="fas fa-notes-medical"></i> Additional Notes</h4>
                    <div class="form-group">
                        <label for="notes">Clinical Observations</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4" 
                                  placeholder="Record any additional clinical observations, symptoms, or relevant information that may affect the nutritional assessment..."
                                  form="assessmentForm">{{ old('notes', $patient->other_medical_problems) }}</textarea>
                        <small class="form-text">Include any relevant medical history, current medications, or special circumstances
                        @if($patient->other_medical_problems)
                            <br><strong>From patient record:</strong> {{ Str::limit($patient->other_medical_problems, 100) }}
                        @endif
                        </small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="button" 
                            class="btn btn-info" 
                            onclick="autoFillFromPatientRecord()"
                            data-weight="{{ $patient->weight_kg ?? '' }}"
                            data-height="{{ $patient->height_cm ?? '' }}"
                            data-household-size="{{ ($patient->total_household_adults ?? 0) + ($patient->total_household_children ?? 0) + ($patient->total_household_twins ?? 0) + 1 }}"
                            data-4ps-beneficiary="{{ $patient->is_4ps_beneficiary ? '1' : '0' }}"
                            data-medical-problems="{{ $patient->other_medical_problems ?? '' }}"
                            data-breastfeeding="{{ $patient->breastfeeding ?? '' }}"
                            data-edema="{{ $patient->edema ?? '' }}">
                        <i class="fas fa-sync-alt"></i> Auto-Fill from Patient Records
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="quickAssessment()">
                        <i class="fas fa-bolt"></i> Quick Assessment
                    </button>
                    <button type="submit" class="btn btn-primary" form="assessmentForm">
                        <i class="fas fa-clipboard-check"></i> Complete Assessment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Assessment Results -->
    <div id="quickResultsCard" class="quick-results-card" style="display: none;">
        <h4>Quick Assessment Results</h4>
        <div id="quickResults"></div>
        <button type="button" class="btn btn-secondary btn-sm" onclick="hideQuickResults()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
@endsection

@section('scripts')
<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Auto-calculate BMI when weight and height are entered
function calculateBMI() {
    const weight = parseFloat(document.getElementById('weight_kg').value);
    const height = parseFloat(document.getElementById('height_cm').value);
    
    if (weight > 0 && height > 0) {
        const heightM = height / 100; // Convert cm to meters
        const bmi = weight / (heightM * heightM);
        
        // Display BMI information (you can add a field for this if needed)
        console.log('Calculated BMI:', bmi.toFixed(2));
        
        // Update any BMI display elements if they exist
        const bmiDisplay = document.getElementById('bmi_display');
        if (bmiDisplay) {
            bmiDisplay.textContent = `BMI: ${bmi.toFixed(2)}`;
        }
    }
}

// Add event listeners for weight and height inputs
document.addEventListener('DOMContentLoaded', function() {
    const weightInput = document.getElementById('weight_kg');
    const heightInput = document.getElementById('height_cm');
    
    if (weightInput) {
        weightInput.addEventListener('input', calculateBMI);
    }
    
    if (heightInput) {
        heightInput.addEventListener('input', calculateBMI);
    }
    
    // Calculate BMI on page load if values are already present
    calculateBMI();
});

// Auto-fill function to populate form with patient record data
function autoFillFromPatientRecord() {
    // Get the button that contains all the data attributes
    const button = document.querySelector('button[data-weight]');
    
    if (!button) {
        showNotification('Unable to find patient data.', 'error');
        return;
    }

    // Fill weight if available and field is empty
    const weightField = document.getElementById('weight_kg');
    const patientWeight = button.getAttribute('data-weight');
    if (patientWeight && !weightField.value) {
        weightField.value = patientWeight;
    }

    // Fill height if available and field is empty
    const heightField = document.getElementById('height_cm');
    const patientHeight = button.getAttribute('data-height');
    if (patientHeight && !heightField.value) {
        heightField.value = patientHeight;
    }

    // Fill household size
    const householdField = document.getElementById('household_size');
    const householdSize = button.getAttribute('data-household-size');
    if (householdSize && householdSize > 1 && !householdField.value) {
        householdField.value = householdSize;
    }

    // Fill 4Ps beneficiary status
    const beneficiaryField = document.getElementById('is_4ps_beneficiary');
    const is4psBeneficiary = button.getAttribute('data-4ps-beneficiary');
    if (is4psBeneficiary === '1' && !beneficiaryField.checked) {
        beneficiaryField.checked = true;
    }

    // Fill medical problems in notes if available and notes is empty
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

    // Recalculate BMI
    calculateBMI();

    // Show success message
    showNotification('Patient record data has been auto-filled where applicable.', 'success');
}

// Simple notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        ${message}
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show and auto-hide
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
    
    // Validate required fields
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
        // Show loading
        const quickResults = document.getElementById('quickResults');
        quickResults.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Assessing...</div>';
        document.getElementById('quickResultsCard').style.display = 'block';
        
        const response = await fetch('{{ route("nutritionist.assessment.quick") }}', {
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
</script>

<style>
.patient-info-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.patient-info-card h3 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 1.2rem;
}

.patient-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.detail-item {
    color: #374151;
    font-size: 0.9rem;
}

.detail-item strong {
    color: #1f2937;
}

.assessment-form-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.assessment-form-card h3 {
    margin: 0 0 2rem 0;
    color: #1f2937;
    font-size: 1.3rem;
}

.form-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h4 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section h4 i {
    color: #6366f1;
    font-size: 0.9rem;
}

.form-section-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
    margin-top: 1rem;
    width: 100%;
}

.checkbox-group h5 {
    margin: 0 0 0.75rem 0;
    color: #4b5563;
    font-size: 0.9rem;
    font-weight: 600;
}

.checkbox-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 0.75rem;
    width: 100%;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 0.4rem;
    color: #374151;
    font-weight: 500;
    font-size: 0.9rem;
}

.form-group label.required::after {
    content: " *";
    color: #ef4444;
}

.form-control {
    padding: 0.6rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.form-check-container {
    display: flex;
    align-items: center;
    padding: 0.6rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.form-check-container:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.form-check-input {
    margin-right: 0.75rem;
    transform: scale(1.1);
}

.form-check-input:checked + .form-check-label {
    color: #059669;
    font-weight: 500;
}

.form-check-label {
    color: #374151;
    font-weight: 500;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.form-check-label i {
    color: #6b7280;
    font-size: 0.9rem;
}

.form-check-input:checked + .form-check-label i {
    color: #059669;
}

.form-text {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.2rem;
}

.form-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    background: white;
    flex-shrink: 0;
}

.btn {
    padding: 0.6rem 1.25rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5);
}

.btn-outline-primary {
    background: white;
    color: #3b82f6;
    border: 2px solid #3b82f6;
}

.btn-outline-primary:hover {
    background: #3b82f6;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.quick-results-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.quick-results-card h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
}

.assessment-summary {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.diagnosis-badge, .risk-badge {
    padding: 0.75rem;
    border-radius: 8px;
    font-weight: 500;
}

.diagnosis-badge.success, .risk-badge.success {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.2);
}

.diagnosis-badge.warning, .risk-badge.warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.diagnosis-badge.danger, .risk-badge.danger {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.diagnosis-badge.info, .risk-badge.info {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.confidence-info {
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 6px;
    color: #374151;
}

.z-scores {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
}

.z-scores h5 {
    margin: 0 0 0.5rem 0;
    color: #374151;
    font-size: 1rem;
}

.z-score-item {
    padding: 0.25rem 0;
    color: #6b7280;
    font-size: 0.9rem;
}

/* Notification styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    z-index: 10000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    border-left: 4px solid #3b82f6;
}

.notification.notification-success {
    border-left-color: #10b981;
}

.notification.notification-error {
    border-left-color: #ef4444;
}

.notification.show {
    transform: translateX(0);
}

.notification i {
    color: #3b82f6;
    font-size: 1.1rem;
}

.notification-success i {
    color: #10b981;
}

.notification-error i {
    color: #ef4444;
}

.btn-info {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
}

.btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(6, 182, 212, 0.5);
}

.loading {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.loading i {
    font-size: 1.5rem;
}
</style>
@endsection
