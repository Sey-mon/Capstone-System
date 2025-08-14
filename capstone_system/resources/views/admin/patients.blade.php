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
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i>
                Patients List
            </h3>
            <div class="card-actions">
                <button class="btn btn-secondary" onclick="window.location.reload()">
                    <i class="fas fa-sync"></i>
                    Refresh
                </button>
                <button class="btn btn-primary" onclick="showAddPatientModal()">
                    <i class="fas fa-plus"></i>
                    Add Patient
                </button>
            </div>
        </div>
        
        <div class="card-content">
            @if($patients->count() > 0)
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Patient ID</th>
                                <th>Patient Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Barangay</th>
                                <th>Guardian</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                                <tr>
                                    <td>
                                        <span class="badge badge-info">#{{ $patient->patient_id }}</span>
                                    </td>
                                    <td>
                                        <div class="patient-info-cell">
                                            <div class="patient-avatar">
                                                <i class="fas fa-child"></i>
                                            </div>
                                            <div class="patient-details">
                                                <div class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                                                <div class="patient-birth">Born: {{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="age-cell">
                                            @if($patient->date_of_birth)
                                                {{ $patient->date_of_birth->age }} years
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $patient->gender === 'Male' ? 'primary' : 'secondary' }}">
                                            <i class="fas fa-{{ $patient->gender === 'Male' ? 'mars' : 'venus' }}"></i>
                                            {{ $patient->gender }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="barangay-cell">
                                            @if($patient->barangay)
                                                <div class="barangay-name">{{ $patient->barangay->barangay_name }}</div>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="guardian-cell">
                                            @if($patient->guardian_name)
                                                <div class="guardian-name">{{ $patient->guardian_name }}</div>
                                                @if($patient->guardian_contact)
                                                    <div class="guardian-contact">{{ $patient->guardian_contact }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted">No guardian info</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match(strtolower($patient->status ?? 'active')) {
                                                'active' => 'success',
                                                'inactive' => 'secondary',
                                                'critical' => 'danger',
                                                'monitoring' => 'warning',
                                                default => 'info'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusClass }}">
                                            {{ ucfirst($patient->status ?? 'Active') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewPatient({{ $patient->patient_id }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editPatient({{ $patient->patient_id }})" title="Edit Patient">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deletePatient({{ $patient->patient_id }})" title="Delete Patient">
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
                    {{ $patients->links() }}
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
                </div>
            @endif
        </div>
    </div>

    <!-- Add Patient Modal -->
    <div id="addPatientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add New Patient</h4>
                <button class="modal-close" onclick="closeAddPatientModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addPatientForm">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="barangay_id">Barangay</label>
                            <select id="barangay_id" name="barangay_id" class="form-control" required>
                                <option value="">Select Barangay</option>
                                @foreach($barangays ?? [] as $barangay)
                                    <option value="{{ $barangay->barangay_id }}">{{ $barangay->barangay_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Monitoring">Monitoring</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="guardian_name">Guardian Name</label>
                            <input type="text" id="guardian_name" name="guardian_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="guardian_contact">Guardian Contact</label>
                            <input type="text" id="guardian_contact" name="guardian_contact" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddPatientModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePatient()">Save Patient</button>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div id="editPatientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Patient</h4>
                <button class="modal-close" onclick="closeEditPatientModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editPatientForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_patient_id" name="patient_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_first_name">First Name</label>
                            <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_last_name">Last Name</label>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_date_of_birth">Date of Birth</label>
                            <input type="date" id="edit_date_of_birth" name="date_of_birth" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_gender">Gender</label>
                            <select id="edit_gender" name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_barangay_id">Barangay</label>
                            <select id="edit_barangay_id" name="barangay_id" class="form-control" required>
                                <option value="">Select Barangay</option>
                                @foreach($barangays ?? [] as $barangay)
                                    <option value="{{ $barangay->barangay_id }}">{{ $barangay->barangay_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select id="edit_status" name="status" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Monitoring">Monitoring</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_guardian_name">Guardian Name</label>
                            <input type="text" id="edit_guardian_name" name="guardian_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit_guardian_contact">Guardian Contact</label>
                            <input type="text" id="edit_guardian_contact" name="guardian_contact" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_address">Address</label>
                        <textarea id="edit_address" name="address" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditPatientModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updatePatient()">Update Patient</button>
            </div>
        </div>
    </div>

    <!-- View Patient Modal -->
    <div id="viewPatientModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h4 class="modal-title">Patient Details</h4>
                <button class="modal-close" onclick="closeViewPatientModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="patientDetailsContent">
                    <!-- Patient details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeViewPatientModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="editPatientFromView()">Edit Patient</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-patients.js') }}"></script>
@endpush
