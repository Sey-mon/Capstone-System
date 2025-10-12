@extends('layouts.dashboard')

@section('title', 'Patients Management')

@section('page-title', 'Patients Management')
@section('page-subtitle', 'Manage and monitor all patients in the system.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-patients.css') }}">
@endpush

@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <div class="patients-management">
        <!-- Advanced Filters Panel -->
        <div class="filters-panel">
            <div class="filters-header">
                <h4><i class="fas fa-filter"></i> Filters & Search</h4>
                <div class="header-actions">
                    <button class="btn btn-sm btn-outline">
                        <i class="fas fa-times"></i>
                        Clear All
                    </button>
                    <button class="btn btn-sm btn-secondary">
                        <i class="fas fa-sync"></i>
                        Refresh
                    </button>
                </div>
            </div>
            <div class="filters-content">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="searchPatient">Search Patient</label>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchPatient" placeholder="Search by name, contact...">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="filterBarangay">Barangay</label>
                                                <select id="filterBarangay">
                            <option value="">All Barangays</option>
                            @foreach($barangays ?? [] as $barangay)
                                <option value="{{ $barangay->barangay_name }}">{{ $barangay->barangay_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterGender">Gender</label>
                        <select id="filterGender">
                            <option value="">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterAgeRange">Age Range</label>
                        <select id="filterAgeRange">
                            <option value="">All Ages</option>
                            <option value="0-12">0-12 months</option>
                            <option value="13-24">13-24 months</option>
                            <option value="25-36">25-36 months</option>
                            <option value="37-48">37-48 months</option>
                            <option value="49+">49+ months</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterNutritionist">Nutritionist</label>
                                                <select id="filterNutritionist">
                            <option value="">All Nutritionists</option>
                            @foreach($nutritionists ?? [] as $nutritionist)
                                <option value="{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}">
                                    {{ $nutritionist->first_name }} {{ $nutritionist->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="results-summary">
            <div class="summary-stats">
                <span class="total-count">Total: <strong id="totalPatients">{{ $patients->count() }}</strong> patients</span>
                <span class="filtered-count filtered-count-hidden" id="filteredCount">Showing: <strong id="visiblePatients">0</strong> patients</span>
            </div>
            <div class="view-options">
                <div class="view-toggle">
                    <button class="btn btn-sm view-btn active" data-view="table">
                        <i class="fas fa-table"></i> Table
                    </button>
                    <button class="btn btn-sm view-btn" data-view="grid">
                        <i class="fas fa-th-large"></i> Grid
                    </button>
                </div>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Patient
                </button>
            </div>
        </div>

        <!-- Patients Content -->
        <div class="patients-content">
            @if($patients->count() > 0)
                <!-- Table View -->
                <div id="tableView" class="view-container active">
                    <div class="enhanced-table-container">
                        <table class="enhanced-patients-table" id="patientsTable">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="name">
                                        <span>Patient</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="age">
                                        <span>Age</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="gender">
                                        <span>Gender</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="barangay">
                                        <span>Barangay</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="parent">
                                        <span>Parent</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="sortable" data-sort="nutritionist">
                                        <span>Nutritionist</span>
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th>Contact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patientsTableBody">
                                @foreach($patients as $patient)
                                    <tr class="patient-row" 
                                        data-name="{{ strtolower($patient->first_name . ' ' . $patient->last_name) }}"
                                        data-age="{{ $patient->age_months }}"
                                        data-gender="{{ $patient->sex }}"
                                        data-barangay="{{ $patient->barangay ? $patient->barangay->barangay_name : '' }}"
                                        data-parent="{{ $patient->parent ? $patient->parent->first_name . ' ' . $patient->parent->last_name : '' }}"
                                        data-nutritionist="{{ $patient->nutritionist ? $patient->nutritionist->first_name . ' ' . $patient->nutritionist->last_name : '' }}"
                                        data-contact="{{ $patient->contact_number }}">
                                        <td class="patient-info-cell">
                                            <div class="patient-avatar">
                                                <i class="fas fa-child"></i>
                                            </div>
                                            <div class="patient-details">
                                                <div class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                                                @if($patient->middle_name)
                                                    <small class="text-muted">{{ $patient->middle_name }}</small>
                                                @endif
                                                <div class="patient-admission">Admitted: {{ $patient->date_of_admission->format('M d, Y') }}</div>
                                            </div>
                                        </td>
                                        <td class="age-cell">
                                            <span class="age-months">{{ $patient->age_months }} months</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $patient->sex === 'Male' ? 'primary' : 'secondary' }}">
                                                <i class="fas fa-{{ $patient->sex === 'Male' ? 'mars' : 'venus' }}"></i>
                                                {{ $patient->sex }}
                                            </span>
                                        </td>
                                        <td class="barangay-cell">
                                            @if($patient->barangay)
                                                <div class="barangay-name">{{ $patient->barangay->barangay_name }}</div>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="parent-cell">
                                            @if($patient->parent)
                                                <div class="parent-name">{{ $patient->parent->first_name }} {{ $patient->parent->last_name }}</div>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="nutritionist-cell">
                                            @if($patient->nutritionist)
                                                <div class="nutritionist-name">{{ $patient->nutritionist->first_name }} {{ $patient->nutritionist->last_name }}</div>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="contact-cell">{{ $patient->contact_number }}</td>
                                        <td class="actions-cell">
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary" data-patient-id="{{ $patient->patient_id }}" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" data-patient-id="{{ $patient->patient_id }}" title="Edit Patient">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" data-patient-id="{{ $patient->patient_id }}" title="Delete Patient">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!-- Pagination Links -->
                        <div class="pagination-wrapper">
                            {{ $patients->links() }}
                        </div>
                    </div>
                </div>

                <!-- Grid View -->
                <div id="gridView" class="view-container grid-view-hidden">
                    <div class="patients-grid" id="patientsGrid">
                        @foreach($patients as $patient)
                            <div class="patient-card" 
                                data-name="{{ strtolower($patient->first_name . ' ' . $patient->last_name) }}"
                                data-age="{{ $patient->age_months }}"
                                data-gender="{{ $patient->sex }}"
                                data-barangay="{{ $patient->barangay ? $patient->barangay->barangay_name : '' }}"
                                data-parent="{{ $patient->parent ? $patient->parent->first_name . ' ' . $patient->parent->last_name : '' }}"
                                data-nutritionist="{{ $patient->nutritionist ? $patient->nutritionist->first_name . ' ' . $patient->nutritionist->last_name : '' }}"
                                data-contact="{{ $patient->contact_number }}">
                                <div class="card-header">
                                    <div class="patient-avatar-large">
                                        <i class="fas fa-child"></i>
                                    </div>
                                    <div class="patient-info">
                                        <h4 class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</h4>
                                        @if($patient->middle_name)
                                            <p class="middle-name">{{ $patient->middle_name }}</p>
                                        @endif
                                        <div class="patient-meta">
                                            <span class="age">{{ $patient->age_months }} months</span>
                                            <span class="gender badge badge-{{ $patient->sex === 'Male' ? 'primary' : 'secondary' }}">
                                                <i class="fas fa-{{ $patient->sex === 'Male' ? 'mars' : 'venus' }}"></i>
                                                {{ $patient->sex }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <span class="label">Barangay:</span>
                                        <span class="value">{{ $patient->barangay ? $patient->barangay->barangay_name : 'Not assigned' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Parent:</span>
                                        <span class="value">{{ $patient->parent ? $patient->parent->first_name . ' ' . $patient->parent->last_name : 'Not assigned' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Nutritionist:</span>
                                        <span class="value">{{ $patient->nutritionist ? $patient->nutritionist->first_name . ' ' . $patient->nutritionist->last_name : 'Not assigned' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Contact:</span>
                                        <span class="value">{{ $patient->contact_number }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Admitted:</span>
                                        <span class="value">{{ $patient->date_of_admission->format('M d, Y') }}</span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-primary" data-patient-id="{{ $patient->patient_id }}" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" data-patient-id="{{ $patient->patient_id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-patient-id="{{ $patient->patient_id }}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- No Results Message -->
                <div id="noResults" class="no-results no-results-hidden">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No patients found</h3>
                    <p>Try adjusting your filters or search terms</p>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="empty-state-title">No Patients Found</h3>
                    <p class="empty-state-description">
                        No patients have been registered yet. Click "Add Patient" to register the first patient.
                    </p>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add First Patient
                    </button>
                </div>
            @endif
            </div>
        </div>
    </div>

    <!-- Modals Section -->

        <!-- Add Patient Modal -->
    <div id="addPatientModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h4 class="modal-title">Add New Patient</h4>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addPatientForm">
                    @csrf
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">Basic Information</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="parent_id">Parent</label>
                                <select id="parent_id" name="parent_id" class="form-control">
                                    <option value="">Select Parent</option>
                                    @foreach($parents ?? [] as $parent)
                                        <option value="{{ $parent->user_id }}">{{ $parent->first_name }} {{ $parent->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nutritionist_id">Nutritionist</label>
                                <select id="nutritionist_id" name="nutritionist_id" class="form-control">
                                    <option value="">Select Nutritionist</option>
                                    @foreach($nutritionists ?? [] as $nutritionist)
                                        <option value="{{ $nutritionist->user_id }}">{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="barangay_id">Barangay *</label>
                                <select id="barangay_id" name="barangay_id" class="form-control" required>
                                    <option value="">Select Barangay</option>
                                    @foreach($barangays ?? [] as $barangay)
                                        <option value="{{ $barangay->barangay_id }}">{{ $barangay->barangay_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact_number">Contact Number *</label>
                                <input type="text" id="contact_number" name="contact_number" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="age_months">Age (months) *</label>
                                <input type="number" id="age_months" name="age_months" class="form-control" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="sex">Sex *</label>
                                <select id="sex" name="sex" class="form-control" required>
                                    <option value="">Select Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_of_admission">Date of Admission *</label>
                                <input type="date" id="date_of_admission" name="date_of_admission" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="weight_kg">Weight (kg) *</label>
                                <input type="number" id="weight_kg" name="weight_kg" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="height_cm">Height (cm) *</label>
                                <input type="number" id="height_cm" name="height_cm" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <!-- Household Information -->
                    <div class="form-section">
                        <h6 class="section-title">Household Information</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="total_household_adults">Total Adults</label>
                                <input type="number" id="total_household_adults" name="total_household_adults" class="form-control" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label for="total_household_children">Total Children</label>
                                <input type="number" id="total_household_children" name="total_household_children" class="form-control" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label for="total_household_twins">Total Twins</label>
                                <input type="number" id="total_household_twins" name="total_household_twins" class="form-control" min="0" value="0">
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
                                <label for="weight_for_age">Weight for Age</label>
                                <input type="text" id="weight_for_age" name="weight_for_age" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="height_for_age">Height for Age</label>
                                <input type="text" id="height_for_age" name="height_for_age" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="bmi_for_age">BMI for Age</label>
                                <input type="text" id="bmi_for_age" name="bmi_for_age" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="breastfeeding">Breastfeeding</label>
                                <select id="breastfeeding" name="breastfeeding" class="form-control">
                                    <option value="">Select</option>
                                    <option value="Exclusive">Exclusive</option>
                                    <option value="Partial">Partial</option>
                                    <option value="None">None</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edema">Edema</label>
                                <select id="edema" name="edema" class="form-control">
                                    <option value="">Select</option>
                                    <option value="None">None</option>
                                    <option value="Mild">Mild</option>
                                    <option value="Moderate">Moderate</option>
                                    <option value="Severe">Severe</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="other_medical_problems">Other Medical Problems</label>
                                <textarea id="other_medical_problems" name="other_medical_problems" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary">Cancel</button>
                <button type="button" class="btn btn-primary">Save Patient</button>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div id="editPatientModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h4 class="modal-title">Edit Patient</h4>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editPatientForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_patient_id" name="patient_id">
                    
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">Basic Information</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_first_name">First Name *</label>
                                <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_middle_name">Middle Name</label>
                                <input type="text" id="edit_middle_name" name="middle_name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_last_name">Last Name *</label>
                                <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_parent_id">Parent</label>
                                <select id="edit_parent_id" name="parent_id" class="form-control">
                                    <option value="">Select Parent</option>
                                    @foreach($parents ?? [] as $parent)
                                        <option value="{{ $parent->user_id }}">{{ $parent->first_name }} {{ $parent->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_nutritionist_id">Nutritionist</label>
                                <select id="edit_nutritionist_id" name="nutritionist_id" class="form-control">
                                    <option value="">Select Nutritionist</option>
                                    @foreach($nutritionists ?? [] as $nutritionist)
                                        <option value="{{ $nutritionist->user_id }}">{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_barangay_id">Barangay *</label>
                                <select id="edit_barangay_id" name="barangay_id" class="form-control" required>
                                    <option value="">Select Barangay</option>
                                    @foreach($barangays ?? [] as $barangay)
                                        <option value="{{ $barangay->barangay_id }}">{{ $barangay->barangay_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_contact_number">Contact Number *</label>
                                <input type="text" id="edit_contact_number" name="contact_number" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_age_months">Age (months) *</label>
                                <input type="number" id="edit_age_months" name="age_months" class="form-control" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_sex">Sex *</label>
                                <select id="edit_sex" name="sex" class="form-control" required>
                                    <option value="">Select Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_date_of_admission">Date of Admission *</label>
                                <input type="date" id="edit_date_of_admission" name="date_of_admission" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_weight_kg">Weight (kg) *</label>
                                <input type="number" id="edit_weight_kg" name="weight_kg" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_height_cm">Height (cm) *</label>
                                <input type="number" id="edit_height_cm" name="height_cm" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <!-- Household Information -->
                    <div class="form-section">
                        <h6 class="section-title">Household Information</h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_total_household_adults">Total Adults</label>
                                <input type="number" id="edit_total_household_adults" name="total_household_adults" class="form-control" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_total_household_children">Total Children</label>
                                <input type="number" id="edit_total_household_children" name="total_household_children" class="form-control" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_total_household_twins">Total Twins</label>
                                <input type="number" id="edit_total_household_twins" name="total_household_twins" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <div class="form-check">
                                    <input type="checkbox" id="edit_is_4ps_beneficiary" name="is_4ps_beneficiary" class="form-check-input">
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
                                <label for="edit_weight_for_age">Weight for Age</label>
                                <input type="text" id="edit_weight_for_age" name="weight_for_age" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_height_for_age">Height for Age</label>
                                <input type="text" id="edit_height_for_age" name="height_for_age" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_bmi_for_age">BMI for Age</label>
                                <input type="text" id="edit_bmi_for_age" name="bmi_for_age" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_breastfeeding">Breastfeeding</label>
                                <select id="edit_breastfeeding" name="breastfeeding" class="form-control">
                                    <option value="">Select</option>
                                    <option value="Exclusive">Exclusive</option>
                                    <option value="Partial">Partial</option>
                                    <option value="None">None</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_edema">Edema</label>
                                <select id="edit_edema" name="edema" class="form-control">
                                    <option value="">Select</option>
                                    <option value="None">None</option>
                                    <option value="Mild">Mild</option>
                                    <option value="Moderate">Moderate</option>
                                    <option value="Severe">Severe</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="edit_other_medical_problems">Other Medical Problems</label>
                                <textarea id="edit_other_medical_problems" name="other_medical_problems" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary">Cancel</button>
                <button type="button" class="btn btn-primary">Update Patient</button>
            </div>
        </div>
    </div>

    <!-- View Patient Modal -->
    <div id="viewPatientModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h4 class="modal-title">Patient Details</h4>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="patientDetailsContent">
                    <!-- Patient details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-patients.js') }}"></script>
    <script src="{{ asset('js/admin/admin-patients-enhanced.js') }}"></script>
@endpush
