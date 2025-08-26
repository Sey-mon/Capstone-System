@extends('layouts.dashboard')

@section('page-title', 'My Patients')

@section('page-title', 'My Patients')
@section('page-subtitle', 'Manage and monitor your assigned patients')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/patients.css') }}">
@endpush

@section('content')
    <!-- Action Bar -->
    <div class="action-bar">
        <div class="action-left">
            <div class="filters-wrapper">
                <!-- Search Input -->
                <div class="filter-group">
                    <input type="text" id="searchInput" placeholder="Search patients..." class="form-control" value="{{ request('search') }}">
                </div>
                
                <!-- Barangay Filter -->
                <div class="filter-group">
                    <select id="barangayFilter" class="form-select">
                        <option value="">All Barangays</option>
                        @foreach($barangays as $barangay)
                            <option value="{{ $barangay->barangay_id }}" {{ request('barangay') == $barangay->barangay_id ? 'selected' : '' }}>
                                {{ $barangay->barangay_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Sex Filter -->
                <div class="filter-group">
                    <select id="sexFilter" class="form-select">
                        <option value="">All Genders</option>
                        <option value="Male" {{ request('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ request('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                
                <!-- Age Range Filter -->
                <div class="filter-group age-range">
                    <input type="number" id="ageMin" placeholder="Min age (months)" class="form-control" value="{{ request('age_min') }}" min="0">
                    <span class="range-separator">-</span>
                    <input type="number" id="ageMax" placeholder="Max age (months)" class="form-control" value="{{ request('age_max') }}" min="0">
                </div>
                
                <!-- Per Page Filter -->
                <div class="filter-group">
                    <select id="perPageFilter" class="form-select">
                        <option value="15" {{ request('per_page') == '15' ? 'selected' : '' }}>15 per page</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>
                
                <!-- Clear Filters Button -->
                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                    <i class="fas fa-times"></i>
                    Clear
                </button>
            </div>
        </div>
        <div class="action-right">
            <button class="btn btn-primary" onclick="openAddPatientModal()">
                <i class="fas fa-plus"></i>
                Add Patient
            </button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="card">
        <div class="card-header">
            <h3>Patients List</h3>
            <div class="results-info">
                <span id="resultsCount">{{ $patients->total() }} patient(s) found</span>
            </div>
        </div>
        <div class="card-body">
            <div id="patientsTableContainer">
                @include('nutritionist.partials.patients-table', ['patients' => $patients])
            </div>
        </div>
    </div>

    <!-- Add/Edit Patient Modal -->
    <div class="modal fade" id="patientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patientModalTitle">Add Patient</h5>
                    <button type="button" class="btn-close" onclick="closePatientModal()"></button>
                </div>
                <form id="patientForm">
                    <div class="modal-body">
                        <input type="hidden" id="patient_id" name="patient_id">
                        
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h6 class="section-title">Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="parent_id" class="form-label">Parent *</label>
                                        <select id="parent_id" name="parent_id" class="form-select" required>
                                            <option value="">Select Parent</option>
                                            @foreach($parents as $parent)
                                                <option value="{{ $parent->user_id }}">{{ $parent->first_name }} {{ $parent->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="barangay_id" class="form-label">Barangay *</label>
                                        <select id="barangay_id" name="barangay_id" class="form-select" required>
                                            <option value="">Select Barangay</option>
                                            @foreach($barangays as $barangay)
                                                <option value="{{ $barangay->barangay_id }}">{{ $barangay->barangay_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="middle_name" class="form-label">Middle Name</label>
                                        <input type="text" id="middle_name" name="middle_name" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_number" class="form-label">Contact Number *</label>
                                        <input type="text" id="contact_number" name="contact_number" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="age_months" class="form-label">Age (months) *</label>
                                        <input type="number" id="age_months" name="age_months" class="form-control" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="sex" class="form-label">Sex *</label>
                                        <select id="sex" name="sex" class="form-select" required>
                                            <option value="">Select Sex</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_of_admission" class="form-label">Date of Admission *</label>
                                        <input type="date" id="date_of_admission" name="date_of_admission" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Household Information -->
                        <div class="form-section">
                            <h6 class="section-title">Household Information</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="total_household_adults" class="form-label">Total Adults</label>
                                        <input type="number" id="total_household_adults" name="total_household_adults" class="form-control" min="0" value="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="total_household_children" class="form-label">Total Children</label>
                                        <input type="number" id="total_household_children" name="total_household_children" class="form-control" min="0" value="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="total_household_twins" class="form-label">Total Twins</label>
                                        <input type="number" id="total_household_twins" name="total_household_twins" class="form-control" min="0" value="0">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" id="is_4ps_beneficiary" name="is_4ps_beneficiary" class="form-check-input">
                                            <label for="is_4ps_beneficiary" class="form-check-label">4Ps Beneficiary</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Health Information -->
                        <div class="form-section">
                            <h6 class="section-title">Health Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="weight_kg" class="form-label">Weight (kg) *</label>
                                        <input type="number" id="weight_kg" name="weight_kg" class="form-control" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="height_cm" class="form-label">Height (cm) *</label>
                                        <input type="number" id="height_cm" name="height_cm" class="form-control" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="weight_for_age" class="form-label">Weight for Age</label>
                                        <input type="text" id="weight_for_age" name="weight_for_age" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="height_for_age" class="form-label">Height for Age</label>
                                        <input type="text" id="height_for_age" name="height_for_age" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="bmi_for_age" class="form-label">BMI for Age</label>
                                        <input type="text" id="bmi_for_age" name="bmi_for_age" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="breastfeeding" class="form-label">Breastfeeding</label>
                                        <input type="text" id="breastfeeding" name="breastfeeding" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edema" class="form-label">Edema</label>
                                        <input type="text" id="edema" name="edema" class="form-control">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="other_medical_problems" class="form-label">Other Medical Problems</label>
                                        <textarea id="other_medical_problems" name="other_medical_problems" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closePatientModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Save Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Patient Modal -->
    <div class="modal fade" id="viewPatientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Patient Details</h5>
                    <button type="button" class="btn-close" onclick="closeViewPatientModal()"></button>
                </div>
                <div class="modal-body" id="patientDetails">
                    <!-- Patient details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeViewPatientModal()">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/nutritionist/patients.js') }}"></script>
@endsection
