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
    </div>
    <div>
        @if(isset($children) && count($children) > 0)
            <ul class="modern-list">
                @foreach($children as $child)
                    <li class="flex items-center bg-gray-50 rounded-2xl border border-gray-200 shadow-sm p-5 mb-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-400 flex items-center justify-center mr-5">
                            <!-- Example SVG icon for medical/child -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10a4 4 0 118 0 4 4 0 01-8 0zm8 6a8 8 0 11-16 0 8 8 0 0116 0z" /></svg>
                        </div>
                        <!-- Details -->
                        <div class="flex-1">
                            <div class="font-bold text-lg text-gray-800 mb-1">{{ $child->first_name }} {{ $child->last_name }}</div>
                            <div class="space-y-1 text-sm text-gray-600 mb-1">
                                <div>Age: <span class="text-gray-800">{{ $child->age }}{{ $child->age == 1 ? ' year' : ' years' }}</span></div>
                                <div>Gender: <span class="text-gray-800">{{ $child->gender ?? 'N/A' }}</span></div>
                                <div>Assessments: <span class="text-gray-800">{{ $child->assessments->count() }}</span></div>
                                <div>Nutritionist: <span class="text-gray-800">{{ $child->nutritionist->first_name ?? 'Not assigned' }} {{ $child->nutritionist->last_name ?? '' }}</span></div>
                            </div>
                        </div>
                        <button type="button" class="ml-6 px-4 py-2 bg-white border border-green-600 text-green-600 rounded hover:bg-green-50 transition" data-bs-toggle="modal" data-bs-target="#childDetailsModal{{ $child->id }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-green-600 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" /></svg>
                            View Details
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
                                        <li><strong style="color:#218838;">Full Name:</strong> {{ $child->first_name }} {{ $child->middle_name ?? '' }} {{ $child->last_name }}</li>
                                        <li><strong style="color:#218838;">Age (months):</strong> {{ $child->age_months ?? $child->age }}</li>
                                        <li><strong style="color:#218838;">Gender:</strong> {{ $child->gender ?? $child->sex ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Birthdate:</strong> {{ $child->birthdate ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Barangay:</strong> {{ $child->barangay->barangay_name ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Nutritionist:</strong> {{ $child->nutritionist->first_name ?? 'Not assigned' }} {{ $child->nutritionist->last_name ?? '' }}</li>
                                        <li><strong style="color:#218838;">Parent:</strong> {{ $child->parent->first_name ?? 'N/A' }} {{ $child->parent->last_name ?? '' }}</li>
                                        <li><strong style="color:#218838;">Contact Number:</strong> {{ $child->contact_number ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Date of Admission:</strong> {{ $child->date_of_admission ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Total Household Adults:</strong> {{ $child->total_household_adults ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Total Household Children:</strong> {{ $child->total_household_children ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Total Household Twins:</strong> {{ $child->total_household_twins ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">4Ps Beneficiary:</strong> {{ isset($child->is_4ps_beneficiary) ? ($child->is_4ps_beneficiary ? 'Yes' : 'No') : 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Weight (kg):</strong> {{ $child->weight_kg ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Height (cm):</strong> {{ $child->height_cm ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Weight for Age:</strong> {{ $child->weight_for_age ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Height for Age:</strong> {{ $child->height_for_age ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">BMI for Age:</strong> {{ $child->bmi_for_age ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Breastfeeding:</strong> {{ $child->breastfeeding ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Edema:</strong> {{ $child->edema ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Other Medical Problems:</strong> {{ $child->other_medical_problems ?? 'N/A' }}</li>
                                        <li><strong style="color:#218838;">Assessments:</strong> {{ $child->assessments->count() }}</li>
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


@endsection
