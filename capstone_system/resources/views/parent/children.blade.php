<!-- Patient Edit Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPatientModalLabel">Edit Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="patientForm">
                    <input type="hidden" id="patient_id" name="patient_id">
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent</label>
                        <select id="parent_id" name="parent_id" class="form-control" required>
                            <!-- Populate with parent options -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="barangay_id" class="form-label">Barangay</label>
                        <select id="barangay_id" name="barangay_id" class="form-control" required>
                            <!-- Populate with barangay options -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="age_months" class="form-label">Age (months)</label>
                        <input type="number" id="age_months" name="age_months" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="sex" class="form-label">Sex</label>
                        <select id="sex" name="sex" class="form-control" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date_of_admission" class="form-label">Date of Admission</label>
                        <input type="date" id="date_of_admission" name="date_of_admission" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="weight_kg" class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" id="weight_kg" name="weight_kg" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="height_cm" class="form-label">Height (cm)</label>
                        <input type="number" step="0.01" id="height_cm" name="height_cm" class="form-control" required>
                    </div>
                    <!-- Add other fields as needed -->
                    <button type="submit" class="modern-btn w-100">Update Patient</button>
                </form>
            </div>
        </div>
    </div>
</div>
@extends('layouts.dashboard')

@section('title', 'My Children')
@section('page-title', 'My Children')
@section('page-subtitle', 'View all your registered children.')

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')

<style>
    .modern-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 2px 16px rgba(0,128,0,0.08);
        padding: 2rem;
        margin-bottom: 2rem;
        border: none;
    }
    .modern-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .modern-title {
        font-size: 1.6rem;
        font-weight: 600;
        color: #218838;
        letter-spacing: 0.5px;
    }
    .modern-btn {
        background: linear-gradient(90deg, #43ea7b 0%, #218838 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1.4rem;
        font-size: 1rem;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(33,136,56,0.08);
        transition: background 0.2s, box-shadow 0.2s;
    }
    .modern-btn:hover {
        background: linear-gradient(90deg, #218838 0%, #43ea7b 100%);
        box-shadow: 0 4px 16px rgba(33,136,56,0.15);
    }
    .modern-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .modern-list-item {
        background: #f6fff7;
        border-radius: 12px;
        margin-bottom: 1rem;
        padding: 1.2rem 1.5rem;
        box-shadow: 0 1px 6px rgba(33,136,56,0.05);
        border: 1px solid #e6f9ea;
        transition: box-shadow 0.2s;
    }
    .modern-list-item:hover {
        box-shadow: 0 4px 16px rgba(33,136,56,0.10);
    }
    .modern-empty {
        text-align: center;
        padding: 2rem 0;
        color: #218838;
    }
    .modern-empty i {
        font-size: 2.5rem;
        color: #43ea7b;
        margin-bottom: 0.5rem;
    }
    /* Modal overrides */
    .modal-content {
        border-radius: 16px;
        border: none;
    }
    .modal-header {
        background: #f6fff7;
        border-bottom: 1px solid #e6f9ea;
        border-radius: 16px 16px 0 0;
    }
    .modal-title {
        color: #218838;
        font-weight: 600;
    }
    .btn-close {
        background-color: #43ea7b;
        border-radius: 50%;
        opacity: 0.7;
    }
    .btn-close:hover {
        opacity: 1;
    }
    .form-label {
        color: #218838;
        font-weight: 500;
    }
    .form-control:focus {
        border-color: #43ea7b;
        box-shadow: 0 0 0 0.2rem rgba(67,234,123,0.15);
    }
</style>

<div class="modern-card">
    <div class="modern-header">
        <span class="modern-title">My Children</span>
        <button type="button" class="modern-btn" data-bs-toggle="modal" data-bs-target="#bindChildModal">
            <i class="fas fa-plus me-1"></i> Bind a Child
        </button>
    </div>
    <div>
        @if(isset($children) && count($children) > 0)
            <ul class="modern-list">
                @foreach($children as $child)
                    <li class="modern-list-item" style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="color:#218838;font-size:1.1rem;">{{ $child->first_name }} {{ $child->last_name }}</strong><br>
                            <span style="color:#555;">Age:</span> {{ $child->age }}<br>
                            <span style="color:#555;">Nutritionist:</span> <span style="color:#218838;">{{ $child->nutritionist->first_name ?? 'Not assigned' }} {{ $child->nutritionist->last_name ?? '' }}</span><br>
                            <span style="color:#555;">Assessments:</span> {{ $child->assessments->count() }}
                        </div>
                        <button type="button" class="modern-btn ms-3" data-bs-toggle="modal" data-bs-target="#childDetailsModal{{ $child->id }}">
                            <i class="fas fa-info-circle me-1"></i> View Details
                        </button>
                    </li>
                    <!-- Child Details Modal -->
                    <div class="modal fade" id="childDetailsModal{{ $child->id }}" tabindex="-1" aria-labelledby="childDetailsModalLabel{{ $child->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="childDetailsModalLabel{{ $child->id }}">Child Information</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong style="color:#218838;">Full Name:</strong> {{ $child->first_name }} {{ $child->last_name }}</li>
                                        <li><strong style="color:#218838;">Age:</strong> {{ $child->age }}</li>
                                        <li><strong style="color:#218838;">Gender:</strong> {{ $child->gender ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Birthdate:</strong> {{ $child->birthdate ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Nutritionist:</strong> {{ $child->nutritionist->first_name ?? 'Not assigned' }} {{ $child->nutritionist->last_name ?? '' }}</li>
                                        <li><strong style="color:#218838;">Assessments:</strong> {{ $child->assessments->count() }}</li>
                                        <li><strong style="color:#218838;">Contact Number:</strong> {{ $child->contact_number ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Address:</strong> {{ $child->address ?? 'N/A' }}</li>
                                        <!-- Add more fields as needed -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </ul>
        @else
            <div class="modern-empty">
                <i class="fas fa-child"></i>
                <p>No children registered yet.</p>
            </div>
        @endif
    </div>
</div>


<!-- Bind Child Modal -->
<div class="modal fade" id="bindChildModal" tabindex="-1" aria-labelledby="bindChildModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bindChildModalLabel">Bind Child to Your Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('parent.bindChild') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="age_months" class="form-label">Age (months)</label>
                        <input type="number" class="form-control" id="age_months" name="age_months" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                    </div>
                    <button type="submit" class="modern-btn w-100">Bind Child</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
