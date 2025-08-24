@extends('layouts.dashboard')

@section('title', 'Nutritionist Profile')

@section('page-title', 'My Profile')
@section('page-subtitle', 'View and manage your professional information')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nutritionist/profile.css') }}">
@endpush

@section('content')
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    <i class="fas fa-user-md"></i>
                </div>
            </div>
            <div class="profile-info">
                <h2 class="profile-name">{{ $nutritionist->first_name }} {{ $nutritionist->middle_name }} {{ $nutritionist->last_name }}</h2>
                <p class="profile-title">Nutritionist</p>
                <div class="profile-status">
                    <span class="status-badge {{ $nutritionist->verification_status === 'verified' ? 'verified' : 'pending' }}">
                        <i class="fas {{ $nutritionist->verification_status === 'verified' ? 'fa-check-circle' : 'fa-clock' }}"></i>
                        {{ ucfirst($nutritionist->verification_status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Personal Information -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i>
                        Personal Information
                    </h3>
                    <button class="btn btn-secondary" onclick="editPersonalInfo()">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                </div>
                <div class="card-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>First Name</label>
                            <span>{{ $nutritionist->first_name }}</span>
                        </div>
                        <div class="info-item">
                            <label>Middle Name</label>
                            <span>{{ $nutritionist->middle_name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Last Name</label>
                            <span>{{ $nutritionist->last_name }}</span>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <span>{{ $nutritionist->email }}</span>
                        </div>
                        <div class="info-item">
                            <label>Contact Number</label>
                            <span>{{ $nutritionist->contact_number ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Date of Birth</label>
                            <span>{{ $nutritionist->birth_date ? $nutritionist->birth_date->format('F d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Gender</label>
                            <span>{{ $nutritionist->sex ? ucfirst($nutritionist->sex) : 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Address</label>
                            <span>{{ $nutritionist->address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-briefcase"></i>
                        Professional Information
                    </h3>
                    <button class="btn btn-secondary" onclick="editProfessionalInfo()">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                </div>
                <div class="card-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>License Number</label>
                            <span>{{ $nutritionist->license_number ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Years of Experience</label>
                            <span>{{ $nutritionist->years_experience ?? 0 }} years</span>
                        </div>
                        <div class="info-item full-width">
                            <label>Qualifications</label>
                            <span>{{ $nutritionist->qualifications ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item full-width">
                            <label>Professional Experience</label>
                            <span>{{ $nutritionist->professional_experience ?? 'N/A' }}</span>
                        </div>
                        @if($nutritionist->professional_id_path)
                        <div class="info-item">
                            <label>Professional ID Document</label>
                            <a href="{{ asset('storage/' . $nutritionist->professional_id_path) }}" target="_blank" class="btn btn-sm btn-outline">
                                <i class="fas fa-file-alt"></i>
                                View Document
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i>
                        Account Information
                    </h3>
                </div>
                <div class="card-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Account Status</label>
                            <span class="status-badge {{ $nutritionist->account_status === 'active' ? 'active' : 'pending' }}">
                                {{ ucfirst($nutritionist->account_status) }}
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Verification Status</label>
                            <span class="status-badge {{ $nutritionist->verification_status === 'verified' ? 'verified' : 'pending' }}">
                                {{ ucfirst($nutritionist->verification_status) }}
                            </span>
                        </div>
                        @if($nutritionist->verified_at)
                        <div class="info-item">
                            <label>Verified Date</label>
                            <span>{{ $nutritionist->verified_at->format('F d, Y g:i A') }}</span>
                        </div>
                        @endif
                        <div class="info-item">
                            <label>Member Since</label>
                            <span>{{ $nutritionist->created_at->format('F d, Y') }}</span>
                        </div>
                        <div class="info-item">
                            <label>Email Verified</label>
                            <span class="status-badge {{ $nutritionist->email_verified_at ? 'verified' : 'pending' }}">
                                {{ $nutritionist->email_verified_at ? 'Verified' : 'Not Verified' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        My Statistics
                    </h3>
                </div>
                <div class="card-content">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">{{ $nutritionist->patientsAsNutritionist()->count() }}</div>
                            <div class="stat-label">Total Patients</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ $nutritionist->assessments()->whereNotNull('completed_at')->count() }}</div>
                            <div class="stat-label">Completed Assessments</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ $nutritionist->assessments()->whereNull('completed_at')->count() }}</div>
                            <div class="stat-label">Pending Assessments</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Personal Info Modal -->
    <div id="editPersonalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Personal Information</h3>
                <span class="close" onclick="closeModal('editPersonalModal')">&times;</span>
            </div>
            <form id="editPersonalForm" method="POST" action="{{ route('nutritionist.profile.update.personal') }}">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ $nutritionist->first_name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="{{ $nutritionist->middle_name }}">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ $nutritionist->last_name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" value="{{ $nutritionist->contact_number }}">
                        </div>
                        <div class="form-group">
                            <label for="birth_date">Date of Birth</label>
                            <input type="date" id="birth_date" name="birth_date" value="{{ $nutritionist->birth_date ? $nutritionist->birth_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="form-group">
                            <label for="sex">Gender</label>
                            <select id="sex" name="sex">
                                <option value="">Select Gender</option>
                                <option value="male" {{ $nutritionist->sex === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $nutritionist->sex === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ $nutritionist->sex === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3">{{ $nutritionist->address }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editPersonalModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Professional Info Modal -->
    <div id="editProfessionalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Professional Information</h3>
                <span class="close" onclick="closeModal('editProfessionalModal')">&times;</span>
            </div>
            <form id="editProfessionalForm" method="POST" action="{{ route('nutritionist.profile.update.professional') }}">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="years_experience">Years of Experience</label>
                            <input type="number" id="years_experience" name="years_experience" value="{{ $nutritionist->years_experience }}" min="0" max="50">
                        </div>
                        <div class="form-group full-width">
                            <label for="qualifications">Qualifications</label>
                            <textarea id="qualifications" name="qualifications" rows="4">{{ $nutritionist->qualifications }}</textarea>
                        </div>
                        <div class="form-group full-width">
                            <label for="professional_experience">Professional Experience</label>
                            <textarea id="professional_experience" name="professional_experience" rows="4">{{ $nutritionist->professional_experience }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProfessionalModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

<script src="{{ asset('js/nutritionist/profile.js') }}"></script>
@endpush
