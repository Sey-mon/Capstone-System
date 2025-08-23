@extends('layouts.dashboard')

@section('title', 'Patient Assessment')

@section('page-title', 'Assessment')
@section('page-subtitle', '{{ $patient->first_name }} {{ $patient->last_name }}')

@section('navigation')
    @include('partials.nutritionist-navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/assessment-form.css') }}">
@endpush
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
                <form method="POST" action="{{ route('nutritionist.assessment.perform') }}" id="assessmentForm" data-quick-route="{{ route('nutritionist.assessment.quick') }}">
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

@push('scripts')
    <script src="{{ asset('js/nutritionist/assessment-form.js') }}"></script>
@endpush
