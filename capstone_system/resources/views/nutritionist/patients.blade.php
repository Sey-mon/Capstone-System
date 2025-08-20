@extends('layouts.dashboard')

@section('title', 'My Patients')

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
            <form method="GET" action="{{ route('nutritionist.patients') }}" class="search-form">
                <div class="search-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search patients..." class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="action-right">
            <button class="btn btn-primary" onclick="openAddPatientModal()">
                <i class="fas fa-plus"></i>
                Add Patient
            </button>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="card">
        <div class="card-header">
            <h3>Patients List</h3>
        </div>
        <div class="card-body">
            @if($patients->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Parent</th>
                                <th>Contact</th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Barangay</th>
                                <th>Date Admitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>
                                            @if($patient->middle_name)
                                                <small class="text-muted">{{ $patient->middle_name }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($patient->parent)
                                            {{ $patient->parent->first_name }} {{ $patient->parent->last_name }}
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>{{ $patient->contact_number }}</td>
                                    <td>{{ $patient->age_months }} months</td>
                                    <td>{{ $patient->sex }}</td>
                                    <td>{{ $patient->barangay->barangay_name ?? 'Unknown' }}</td>
                                    <td>{{ $patient->date_of_admission->format('M d, Y') }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('nutritionist.patients.assess', $patient->patient_id) }}" class="btn btn-sm btn-success" title="Assess Patient">
                                                <i class="fas fa-stethoscope"></i>
                                            </a>
                                            <button class="btn btn-sm btn-info" onclick="viewPatient({{ $patient->patient_id }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="editPatient({{ $patient->patient_id }})" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deletePatient({{ $patient->patient_id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $patients->appends(request()->query())->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <h3>No Patients Found</h3>
                    <p>You haven't been assigned any patients yet or no patients match your search criteria.</p>
                    @if(!request('search'))
                        <button class="btn btn-primary" onclick="openAddPatientModal()">
                            <i class="fas fa-plus"></i>
                            Add Your First Patient
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Patient Modal -->
    <div class="modal fade" id="patientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patientModalTitle">Add Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="patientDetails">
                    <!-- Patient details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/nutritionist/nutritionist-patients.js') }}"></script>
@endsection
