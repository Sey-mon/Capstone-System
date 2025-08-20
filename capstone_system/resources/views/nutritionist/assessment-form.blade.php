@extends('layouts.dashboard')

@section('title', 'Patient Assessment')

@section('page-title', 'Malnutrition Assessment')
@section('page-subtitle', 'Assess patient: {{ $patient->first_name }} {{ $patient->last_name }}')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@section('content')
    <!-- Error Display -->
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('nutritionist.patients') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Patients
        </a>
    </div>

    <!-- Patient Information Card -->
    <div class="patient-info-card">
        <h3>Patient Information</h3>
        <div class="patient-details">
            <div class="detail-item">
                <strong>Name:</strong> {{ $patient->first_name }} {{ $patient->last_name }}
            </div>
            <div class="detail-item">
                <strong>Age:</strong> {{ $patient->age_months }} months old
            </div>
            <div class="detail-item">
                <strong>Sex:</strong> {{ $patient->sex }}
            </div>
            <div class="detail-item">
                <strong>Parent:</strong> {{ $patient->parent->first_name ?? 'N/A' }} {{ $patient->parent->last_name ?? '' }}
            </div>
            <div class="detail-item">
                <strong>Barangay:</strong> {{ $patient->barangay->name ?? 'N/A' }}
            </div>
            <div class="detail-item">
                <strong>Contact:</strong> {{ $patient->contact_number }}
            </div>
        </div>
    </div>

    <!-- Assessment Form -->
    <div class="assessment-form-card">
        <form method="POST" action="{{ route('nutritionist.assessment.perform') }}" id="assessmentForm">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $patient->patient_id }}">

            <h3>Assessment Details</h3>

            <!-- Child Measurements -->
            <div class="form-section">
                <h4>Child Measurements</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="age_months" class="required">Age (months)</label>
                        <input type="number" 
                               class="form-control" 
                               id="age_months" 
                               name="age_months" 
                               value="{{ old('age_months', $patient->age_months) }}" 
                               min="0" 
                               max="60" 
                               required>
                        <small class="form-text text-muted">Current age: {{ $patient->age_months }} months</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender" class="required">Gender</label>
                        <select class="form-control" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', strtolower($patient->sex)) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', strtolower($patient->sex)) == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="weight_kg" class="required">Weight (kg)</label>
                        <input type="number" 
                               step="0.1" 
                               class="form-control" 
                               id="weight_kg" 
                               name="weight_kg" 
                               value="{{ old('weight_kg', $patient->weight_kg) }}" 
                               min="1" 
                               max="50" 
                               required>
                        <small class="form-text text-muted">Last recorded: {{ $patient->weight_kg }}kg</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="height_cm" class="required">Height (cm)</label>
                        <input type="number" 
                               step="0.1" 
                               class="form-control" 
                               id="height_cm" 
                               name="height_cm" 
                               value="{{ old('height_cm', $patient->height_cm) }}" 
                               min="30" 
                               max="150" 
                               required>
                        <small class="form-text text-muted">Last recorded: {{ $patient->height_cm }}cm</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="muac_cm">MUAC (cm)</label>
                        <input type="number" 
                               step="0.1" 
                               class="form-control" 
                               id="muac_cm" 
                               name="muac_cm" 
                               value="{{ old('muac_cm') }}" 
                               min="5" 
                               max="30">
                        <small class="form-text text-muted">Mid-Upper Arm Circumference (optional)</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check-container">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="has_edema" 
                                   name="has_edema" 
                                   {{ old('has_edema') ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_edema">
                                Has Edema (swelling)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical Symptoms -->
            <div class="form-section">
                <h4>Clinical Symptoms</h4>
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
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check-container">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="vomiting" 
                                   name="vomiting" 
                                   {{ old('vomiting') ? 'checked' : '' }}>
                            <label class="form-check-label" for="vomiting">
                                Vomiting
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Socioeconomic Information -->
            <div class="form-section">
                <h4>Socioeconomic Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="household_size">Household Size</label>
                        <input type="number" 
                               class="form-control" 
                               id="household_size" 
                               name="household_size" 
                               value="{{ old('household_size', 4) }}" 
                               min="1" 
                               max="20">
                    </div>
                    
                    <div class="form-group">
                        <label for="mother_education">Mother's Education</label>
                        <select class="form-control" id="mother_education" name="mother_education">
                            <option value="none" {{ old('mother_education') == 'none' ? 'selected' : '' }}>No Education</option>
                            <option value="primary" {{ old('mother_education', 'primary') == 'primary' ? 'selected' : '' }}>Primary</option>
                            <option value="secondary" {{ old('mother_education') == 'secondary' ? 'selected' : '' }}>Secondary</option>
                            <option value="tertiary" {{ old('mother_education') == 'tertiary' ? 'selected' : '' }}>Tertiary</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="form-check-container">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_4ps_beneficiary" 
                                   name="is_4ps_beneficiary" 
                                   {{ old('is_4ps_beneficiary') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_4ps_beneficiary">
                                4Ps Beneficiary
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check-container">
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
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="form-check-container">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="has_clean_water" 
                                   name="has_clean_water" 
                                   {{ old('has_clean_water') ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_clean_water">
                                Has Clean Water Access
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check-container">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="father_present" 
                                   name="father_present" 
                                   {{ old('father_present') ? 'checked' : '' }}>
                            <label class="form-check-label" for="father_present">
                                Father Present in Household
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-section">
                <h4>Additional Notes</h4>
                <div class="form-group">
                    <label for="notes">Clinical Notes</label>
                    <textarea class="form-control" 
                              id="notes" 
                              name="notes" 
                              rows="3" 
                              placeholder="Any additional observations or notes...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="button" class="btn btn-outline-primary" onclick="quickAssessment()">
                    <i class="fas fa-bolt"></i> Quick Assessment
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-clipboard-check"></i> Complete Assessment
                </button>
            </div>
        </form>
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
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h4 {
    margin: 0 0 1.5rem 0;
    color: #374151;
    font-size: 1.1rem;
    font-weight: 600;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 0.5rem;
    color: #374151;
    font-weight: 500;
}

.form-group label.required::after {
    content: " *";
    color: #ef4444;
}

.form-control {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-check-container {
    display: flex;
    align-items: center;
    padding-top: 2rem;
}

.form-check-input {
    margin-right: 0.5rem;
}

.form-check-label {
    color: #374151;
    font-weight: 500;
    margin-bottom: 0;
}

.form-text {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
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
