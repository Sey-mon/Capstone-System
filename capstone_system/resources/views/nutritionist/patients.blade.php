@extends('layouts.dashboard')

@section('title', 'My Patients')
@section('page-title', 'My Patients')
@section('page-subtitle', 'Manage and monitor your assigned patients')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/patients.css') }}?v={{ filemtime(public_path('css/nutritionist/patients.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/nutritionist/patients-swal.css') }}?v={{ filemtime(public_path('css/nutritionist/patients-swal.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/nutritionist/patients-archive.css') }}?v={{ filemtime(public_path('css/nutritionist/patients-archive.css')) }}">
@endpush

@section('content')
    <!-- Filters & Search Bar - Compact Horizontal Design -->
    <div class="filters-search-bar">
        <div class="filters-header">
            <div class="filters-title">
                <i class="fas fa-filter"></i>
                <span>Filters & Search</span>
            </div>
            <div class="filters-actions">
                <button type="button" class="btn-filter-action btn-clear" onclick="clearFilters()">
                    <i class="fas fa-times"></i>
                    <span>Clear All</span>
                </button>
            </div>
        </div>

        <div class="filters-row">
            <div class="filter-item">
                <label class="filter-label">Search Patient</label>
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" placeholder="      Search by name" class="filter-input search-input" value="{{ request('search') }}">
                </div>
            </div>

            <div class="filter-item">
                <label class="filter-label">Barangay</label>
                <select id="barangayFilter" class="filter-select">
                    <option value="">All Barangays</option>
                    @foreach($barangays as $barangay)
                        <option value="{{ $barangay->barangay_id }}" {{ request('barangay') == $barangay->barangay_id ? 'selected' : '' }}>
                            {{ $barangay->barangay_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-item">
                <label class="filter-label">Gender</label>
                <select id="sexFilter" class="filter-select">
                    <option value="">All Genders</option>
                    <option value="Male" {{ request('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ request('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div class="filter-item">
                <label class="filter-label">Age Range</label>
                <select id="ageRangeFilter" class="filter-select">
                    <option value="">All Ages</option>
                    <option value="0-12" {{ request('age_range') == '0-12' ? 'selected' : '' }}>0-12 months</option>
                    <option value="13-24" {{ request('age_range') == '13-24' ? 'selected' : '' }}>13-24 months</option>
                    <option value="25-36" {{ request('age_range') == '25-36' ? 'selected' : '' }}>25-36 months</option>
                    <option value="37-48" {{ request('age_range') == '37-48' ? 'selected' : '' }}>37-48 months</option>
                    <option value="49-60" {{ request('age_range') == '49-60' ? 'selected' : '' }}>49-60 months</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="card patients-card">
        <div class="card-header">
            <div class="header-content">
                <h3 class="card-title">
                    <i class="fas fa-users"></i>
                    Patients List
                </h3>
            </div>
            <div class="header-actions">
                <div class="archive-toggle">
                    <button class="btn btn-sm archive-btn active" data-status="active">
                        <i class="fas fa-user-check"></i> Active
                    </button>
                    <button class="btn btn-sm archive-btn" data-status="archived">
                        <i class="fas fa-archive"></i> Archived
                    </button>
                </div>
                <div class="results-info">
                    <span class="badge results-badge" id="resultsCount">{{ $patients->total() }} patient(s)</span>
                </div>
                <button class="btn-add-patient" onclick="openAddPatientModal()">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="patientsTableContainer">
                @include('nutritionist.partials.patients-table', ['patients' => $patients])
            </div>
        </div>
    </div>

    <!-- Hidden form template for SweetAlert2 (will be cloned and used) -->
    <template id="patientFormTemplate">
        <form id="patientForm" class="patient-form-swal">
            <input type="hidden" id="patient_id" name="patient_id">
            
            <!-- Basic Information -->
            <div class="form-section">
                <h6 class="section-title">
                    <i class="fas fa-user"></i>
                    Basic Information
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required placeholder="Enter first name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" class="form-control" placeholder="Enter middle name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required placeholder="Enter last name">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sex" class="form-label">Sex <span class="required">*</span></label>
                            <select id="sex" name="sex" class="form-select" required>
                                <option value="">Select sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="birthdate" class="form-label">Birthdate <span class="required">*</span></label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control" data-lock-on-edit="true" required>
                            <small class="form-text text-muted add-only-message">Age will be automatically calculated</small>
                            <small class="form-text text-muted edit-only-message" style="display: none;"><i class="fas fa-lock"></i> Cannot be edited to preserve historical accuracy</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="age_months" class="form-label">Age (months) <span class="required">*</span></label>
                            <input type="number" id="age_months" name="age_months" class="form-control" min="0" required readonly style="background-color: #f8f9fa;">
                            <small class="form-text text-muted"><i class="fas fa-magic"></i> Auto-calculated from birthdate</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number <span class="required">*</span></label>
                            <input type="text" id="contact_number" name="contact_number" class="form-control" required placeholder="09XXXXXXXXX">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date_of_admission" class="form-label">Date of Admission <span class="required">*</span></label>
                            <input type="date" id="date_of_admission" name="date_of_admission" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent/Guardian</label>
                            <select id="parent_id" name="parent_id" class="form-select">
                                <option value="">Select parent/guardian</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->user_id }}">{{ $parent->first_name }} {{ $parent->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="barangay_id" class="form-label">Barangay <span class="required">*</span></label>
                            <select id="barangay_id" name="barangay_id" class="form-select" required>
                                <option value="">Select barangay</option>
                                @foreach($barangays as $barangay)
                                    <option value="{{ $barangay->barangay_id }}">{{ $barangay->barangay_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Household Information -->
            <div class="form-section">
                <h6 class="section-title">
                    <i class="fas fa-home"></i>
                    Household Information
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="total_household_adults" class="form-label">Total Adults</label>
                            <input type="number" id="total_household_adults" name="total_household_adults" class="form-control" min="0" value="0" placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="total_household_children" class="form-label">Total Children</label>
                            <input type="number" id="total_household_children" name="total_household_children" class="form-control" min="0" value="0" placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="total_household_twins" class="form-label">Total Twins</label>
                            <input type="number" id="total_household_twins" name="total_household_twins" class="form-control" min="0" value="0" placeholder="0">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <div class="form-check custom-checkbox">
                                <input type="checkbox" id="is_4ps_beneficiary" name="is_4ps_beneficiary" class="form-check-input">
                                <label for="is_4ps_beneficiary" class="form-check-label">
                                    <i class="fas fa-hands-helping"></i>
                                    4Ps Beneficiary
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Health Information -->
            <div class="form-section">
                <h6 class="section-title">
                    <i class="fas fa-heartbeat"></i>
                    Health Information
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="weight_kg" class="form-label">Weight (kg) <span class="required" class="add-only-required">*</span></label>
                            <input type="number" id="weight_kg" name="weight_kg" class="form-control" data-health-field="true" step="0.01" min="0" required placeholder="0.00">
                            <small class="form-text text-muted edit-only-message" style="display: none;"><i class="fas fa-info-circle"></i> Updates automatically from assessments</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="height_cm" class="form-label">Height (cm) <span class="required" class="add-only-required">*</span></label>
                            <input type="number" id="height_cm" name="height_cm" class="form-control" data-health-field="true" step="0.01" min="0" required placeholder="0.00">
                            <small class="form-text text-muted edit-only-message" style="display: none;"><i class="fas fa-info-circle"></i> Updates automatically from assessments</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="breastfeeding" class="form-label">Breastfeeding</label>
                            <select id="breastfeeding" name="breastfeeding" class="form-select">
                                <option value="">Select option</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edema" class="form-label">Edema</label>
                            <select id="edema" name="edema" class="form-select">
                                <option value="">Select option</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="allergies" class="form-label">Allergies</label>
                            <select id="allergies" name="allergies" class="form-select">
                                <option value="">Select allergy</option>
                                <option value="None">None</option>
                                <option value="Milk/Dairy">Milk/Dairy</option>
                                <option value="Eggs">Eggs</option>
                                <option value="Peanuts">Peanuts</option>
                                <option value="Tree Nuts">Tree Nuts</option>
                                <option value="Shellfish/Seafood">Shellfish/Seafood</option>
                                <option value="Fish">Fish</option>
                                <option value="Soy">Soy</option>
                                <option value="Wheat/Gluten">Wheat/Gluten</option>
                                <option value="Other">Other (Please specify)</option>
                            </select>
                            <input type="text" id="allergies_other" name="allergies_other" class="form-control mt-2" placeholder="Please specify other allergies..." style="display: none;">
                            <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Used by AI for meal plan recommendations</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="religion" class="form-label">Religion</label>
                            <select id="religion" name="religion" class="form-select">
                                <option value="">Select religion</option>
                                <option value="Roman Catholic">Roman Catholic</option>
                                <option value="Islam">Islam</option>
                                <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                                <option value="Protestant">Protestant</option>
                                <option value="Seventh-day Adventist">Seventh-day Adventist</option>
                                <option value="Aglipayan">Aglipayan</option>
                                <option value="Born Again Christian">Born Again Christian</option>
                                <option value="Other">Other (Please specify)</option>
                                <option value="Prefer not to say">Prefer not to say</option>
                            </select>
                            <input type="text" id="religion_other" name="religion_other" class="form-control mt-2" placeholder="Please specify religion..." style="display: none;">
                            <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Used for dietary restrictions</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="other_medical_problems" class="form-label">Other Medical Problems</label>
                            <textarea id="other_medical_problems" name="other_medical_problems" class="form-control" rows="3" placeholder="Describe any other medical conditions or concerns..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </template>

    <!-- Form Data Storage (Hidden) -->
    <script id="parentsData" type="application/json">
        {!! json_encode($parents ?? []) !!}
    </script>
    <script id="barangaysData" type="application/json">
        {!! json_encode($barangays ?? []) !!}
    </script>
@endsection

@section('scripts')
<script src="{{ asset('js/nutritionist/patients.js') }}?v={{ filemtime(public_path('js/nutritionist/patients.js')) }}"></script>
<script src="{{ asset('js/nutritionist/patients-archive.js') }}?v={{ filemtime(public_path('js/nutritionist/patients-archive.js')) }}"></script>
@endsection
