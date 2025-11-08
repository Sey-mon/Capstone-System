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
        <!-- Profile Banner -->
        <div class="profile-banner">
            <div class="banner-overlay"></div>
            <div class="banner-pattern"></div>
        </div>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-header-content">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="avatar-status {{ $nutritionist->account_status === 'active' ? 'online' : 'offline' }}"></div>
                </div>
                <div class="profile-info">
                    <div class="profile-name-section">
                        <h1 class="profile-name">{{ $nutritionist->first_name }} {{ $nutritionist->middle_name }} {{ $nutritionist->last_name }}</h1>
                        @if($nutritionist->verification_status === 'verified')
                            <span class="verified-icon" title="Verified Professional">
                                <i class="fas fa-certificate"></i>
                            </span>
                        @endif
                    </div>
                    <p class="profile-title"><i class="fas fa-stethoscope"></i> Licensed Nutritionist-Dietitian</p>
                    <div class="profile-meta">
                        <span class="meta-item">
                            <i class="fas fa-calendar-check"></i>
                            Member since {{ $nutritionist->created_at->format('M Y') }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-award"></i>
                            {{ $nutritionist->years_experience ?? 0 }}+ years experience
                        </span>
                        @if($nutritionist->license_number)
                        <span class="meta-item">
                            <i class="fas fa-id-card"></i>
                            License #{{ $nutritionist->license_number }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="profile-actions">
                    <button class="btn btn-primary" onclick="editPersonalInfo()">
                        <i class="fas fa-edit"></i>
                        Edit Profile
                    </button>
                    <button class="btn btn-outline" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Stats Bar -->
        <div class="quick-stats-bar">
            <div class="quick-stat">
                <div class="quick-stat-icon patients">
                    <i class="fas fa-users"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">{{ $nutritionist->patientsAsNutritionist()->count() }}</div>
                    <div class="quick-stat-label">Total Patients</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">{{ $nutritionist->assessments()->whereNotNull('completed_at')->count() }}</div>
                    <div class="quick-stat-label">Completed</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">{{ $nutritionist->assessments()->whereNull('completed_at')->count() }}</div>
                    <div class="quick-stat-label">Pending</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon rate">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">
                        @php
                            $total = $nutritionist->assessments()->count();
                            $completed = $nutritionist->assessments()->whereNotNull('completed_at')->count();
                            $rate = $total > 0 ? round(($completed / $total) * 100) : 0;
                        @endphp
                        {{ $rate }}%
                    </div>
                    <div class="quick-stat-label">Success Rate</div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="profile-content">
            <!-- Left Column -->
            <div class="content-left">
                <!-- Personal Information Card -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <div class="card-icon personal">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3 class="card-title">Personal Information</h3>
                        </div>
                        <button class="btn-icon" onclick="editPersonalInfo()" title="Edit Personal Information">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="info-list">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-signature"></i>
                                    Full Name
                                </div>
                                <div class="info-value">{{ $nutritionist->first_name }} {{ $nutritionist->middle_name }} {{ $nutritionist->last_name }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </div>
                                <div class="info-value">{{ $nutritionist->email }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-phone"></i>
                                    Contact Number
                                </div>
                                <div class="info-value">{{ $nutritionist->contact_number ?? 'Not provided' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-birthday-cake"></i>
                                    Date of Birth
                                </div>
                                <div class="info-value">{{ $nutritionist->birth_date ? $nutritionist->birth_date->format('F d, Y') : 'Not provided' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-venus-mars"></i>
                                    Gender
                                </div>
                                <div class="info-value">{{ $nutritionist->sex ? ucfirst($nutritionist->sex) : 'Not specified' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Address
                                </div>
                                <div class="info-value">{{ $nutritionist->address ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Information Card -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <div class="card-icon professional">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <h3 class="card-title">Professional Information</h3>
                        </div>
                        <button class="btn-icon" onclick="editProfessionalInfo()" title="Edit Professional Information">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="info-list">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-id-badge"></i>
                                    License Number
                                </div>
                                <div class="info-value badge-value">{{ $nutritionist->license_number ?? 'Not provided' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-clock"></i>
                                    Years of Experience
                                </div>
                                <div class="info-value">{{ $nutritionist->years_experience ?? 0 }} years</div>
                            </div>
                            <div class="info-row full-width">
                                <div class="info-label">
                                    <i class="fas fa-graduation-cap"></i>
                                    Qualifications
                                </div>
                                <div class="info-value text-block">{{ $nutritionist->qualifications ?? 'No qualifications provided yet.' }}</div>
                            </div>
                            <div class="info-row full-width">
                                <div class="info-label">
                                    <i class="fas fa-briefcase"></i>
                                    Professional Experience
                                </div>
                                <div class="info-value text-block">{{ $nutritionist->professional_experience ?? 'No experience details provided yet.' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="content-right">
                <!-- Account Status Card -->
                <div class="content-card status-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <div class="card-icon status">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="card-title">Account Status</h3>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="status-items">
                            <div class="status-item {{ $nutritionist->account_status === 'active' ? 'active' : 'inactive' }}">
                                <div class="status-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="status-details">
                                    <div class="status-title">Account Status</div>
                                    <div class="status-value">{{ ucfirst($nutritionist->account_status) }}</div>
                                </div>
                            </div>
                            <div class="status-item {{ $nutritionist->verification_status === 'verified' ? 'verified' : 'pending' }}">
                                <div class="status-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <div class="status-details">
                                    <div class="status-title">Verification Status</div>
                                    <div class="status-value">{{ ucfirst($nutritionist->verification_status) }}</div>
                                </div>
                            </div>
                            <div class="status-item {{ $nutritionist->email_verified_at ? 'verified' : 'pending' }}">
                                <div class="status-icon">
                                    <i class="fas fa-envelope-circle-check"></i>
                                </div>
                                <div class="status-details">
                                    <div class="status-title">Email Status</div>
                                    <div class="status-value">{{ $nutritionist->email_verified_at ? 'Verified' : 'Not Verified' }}</div>
                                </div>
                            </div>
                        </div>
                        @if($nutritionist->verified_at)
                        <div class="status-footer">
                            <i class="fas fa-check-double"></i>
                            Verified on {{ $nutritionist->verified_at->format('F d, Y') }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Account Security Card -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <div class="card-icon status">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h3 class="card-title">Account Security</h3>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="info-list">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-key"></i>
                                    Password
                                </div>
                                <div class="info-value">
                                    <button class="btn btn-sm btn-outline" onclick="changePassword()">
                                        <i class="fas fa-edit"></i>
                                        Change Password
                                    </button>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </div>
                                <div class="info-value">{{ $nutritionist->email }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-clock"></i>
                                    Last Updated
                                </div>
                                <div class="info-value">{{ $nutritionist->updated_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline Card -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <div class="card-icon activity">
                                <i class="fas fa-history"></i>
                            </div>
                            <h3 class="card-title">Recent Activity</h3>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker active"></div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Profile Viewed</div>
                                    <div class="timeline-date">Just now</div>
                                </div>
                            </div>
                            @if($nutritionist->assessments()->latest()->first())
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Latest Assessment</div>
                                    <div class="timeline-date">{{ $nutritionist->assessments()->latest()->first()->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            @endif
                            @if($nutritionist->email_verified_at)
                            <div class="timeline-item">
                                <div class="timeline-marker success"></div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Email Verified</div>
                                    <div class="timeline-date">{{ $nutritionist->email_verified_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                            @endif
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Account Created</div>
                                    <div class="timeline-date">{{ $nutritionist->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="content-card actions-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <div class="card-icon actions">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="action-buttons">
                            <button class="action-btn">
                                <i class="fas fa-user-plus"></i>
                                <span>Add Patient</span>
                            </button>
                            <button class="action-btn">
                                <i class="fas fa-clipboard-list"></i>
                                <span>New Assessment</span>
                            </button>
                            <button class="action-btn">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Schedule</span>
                            </button>
                            <button class="action-btn">
                                <i class="fas fa-file-medical"></i>
                                <span>View Reports</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Nutritionist data for forms
    const nutritionistData = {
        first_name: "{{ $nutritionist->first_name }}",
        middle_name: "{{ $nutritionist->middle_name }}",
        last_name: "{{ $nutritionist->last_name }}",
        contact_number: "{{ $nutritionist->contact_number }}",
        birth_date: "{{ $nutritionist->birth_date ? $nutritionist->birth_date->format('Y-m-d') : '' }}",
        sex: "{{ $nutritionist->sex }}",
        address: `{{ $nutritionist->address }}`,
        years_experience: "{{ $nutritionist->years_experience }}",
        qualifications: `{{ $nutritionist->qualifications }}`,
        professional_experience: `{{ $nutritionist->professional_experience }}`
    };

    // Edit Personal Information
    function editPersonalInfo() {
        Swal.fire({
            title: `
                <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-edit" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div style="text-align: left;">
                        <h3 style="margin: 0; color: #1f2937; font-size: 24px; font-weight: 700;">Edit Personal Information</h3>
                        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">Update your personal details</p>
                    </div>
                </div>
            `,
            html: `
                <div style="text-align: left; padding: 20px 10px;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>First Name *
                            </label>
                            <input id="swal-first-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${nutritionistData.first_name}" required>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>Middle Name
                            </label>
                            <input id="swal-middle-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${nutritionistData.middle_name}">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>Last Name *
                            </label>
                            <input id="swal-last-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${nutritionistData.last_name}" required>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-phone" style="color: #10b981; margin-right: 5px;"></i>Contact Number
                            </label>
                            <input id="swal-contact" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${nutritionistData.contact_number}">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-calendar" style="color: #10b981; margin-right: 5px;"></i>Date of Birth
                            </label>
                            <input id="swal-birth-date" type="date" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${nutritionistData.birth_date}">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-venus-mars" style="color: #10b981; margin-right: 5px;"></i>Gender
                            </label>
                            <select id="swal-gender" class="swal2-select" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                                <option value="">Select Gender</option>
                                <option value="male" ${nutritionistData.sex === 'male' ? 'selected' : ''}>Male</option>
                                <option value="female" ${nutritionistData.sex === 'female' ? 'selected' : ''}>Female</option>
                                <option value="other" ${nutritionistData.sex === 'other' ? 'selected' : ''}>Other</option>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-map-marker-alt" style="color: #10b981; margin-right: 5px;"></i>Address
                            </label>
                            <textarea id="swal-address" class="swal2-textarea" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; min-height: 80px;" rows="3">${nutritionistData.address}</textarea>
                        </div>
                    </div>
                </div>
            `,
            width: '900px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Save Changes',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'nutritionist-swal-popup',
                confirmButton: 'nutritionist-swal-confirm',
                cancelButton: 'nutritionist-swal-cancel'
            },
            didOpen: () => {
                // Focus styling
                document.querySelectorAll('.swal2-input, .swal2-select, .swal2-textarea').forEach(input => {
                    input.addEventListener('focus', function() {
                        this.style.borderColor = '#10b981';
                        this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
                    });
                    input.addEventListener('blur', function() {
                        this.style.borderColor = '#e5e7eb';
                        this.style.boxShadow = 'none';
                    });
                });
            },
            preConfirm: () => {
                const firstName = document.getElementById('swal-first-name').value;
                const lastName = document.getElementById('swal-last-name').value;
                
                if (!firstName || !lastName) {
                    Swal.showValidationMessage('First Name and Last Name are required');
                    return false;
                }
                
                return {
                    first_name: firstName,
                    middle_name: document.getElementById('swal-middle-name').value,
                    last_name: lastName,
                    contact_number: document.getElementById('swal-contact').value,
                    birth_date: document.getElementById('swal-birth-date').value,
                    sex: document.getElementById('swal-gender').value,
                    address: document.getElementById('swal-address').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updatePersonalInfo(result.value);
            }
        });
    }

    // Edit Professional Information
    function editProfessionalInfo() {
        Swal.fire({
            title: `
                <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-briefcase" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div style="text-align: left;">
                        <h3 style="margin: 0; color: #1f2937; font-size: 24px; font-weight: 700;">Edit Professional Information</h3>
                        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">Update your professional credentials</p>
                    </div>
                </div>
            `,
            html: `
                <div style="text-align: left; padding: 20px 10px;">
                    <div style="display: grid; gap: 20px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-clock" style="color: #10b981; margin-right: 5px;"></i>Years of Experience
                            </label>
                            <input id="swal-years-exp" type="number" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${nutritionistData.years_experience}" min="0" max="50">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-graduation-cap" style="color: #10b981; margin-right: 5px;"></i>Qualifications
                            </label>
                            <textarea id="swal-qualifications" class="swal2-textarea" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; min-height: 120px;" rows="4">${nutritionistData.qualifications}</textarea>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-briefcase" style="color: #10b981; margin-right: 5px;"></i>Professional Experience
                            </label>
                            <textarea id="swal-prof-exp" class="swal2-textarea" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; min-height: 120px;" rows="4">${nutritionistData.professional_experience}</textarea>
                        </div>
                    </div>
                </div>
            `,
            width: '900px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Save Changes',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'nutritionist-swal-popup',
                confirmButton: 'nutritionist-swal-confirm',
                cancelButton: 'nutritionist-swal-cancel'
            },
            didOpen: () => {
                // Focus styling
                document.querySelectorAll('.swal2-input, .swal2-textarea').forEach(input => {
                    input.addEventListener('focus', function() {
                        this.style.borderColor = '#10b981';
                        this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
                    });
                    input.addEventListener('blur', function() {
                        this.style.borderColor = '#e5e7eb';
                        this.style.boxShadow = 'none';
                    });
                });
            },
            preConfirm: () => {
                return {
                    years_experience: document.getElementById('swal-years-exp').value,
                    qualifications: document.getElementById('swal-qualifications').value,
                    professional_experience: document.getElementById('swal-prof-exp').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateProfessionalInfo(result.value);
            }
        });
    }

    // Update Personal Information
    function updatePersonalInfo(data) {
        fetch('{{ route("nutritionist.profile.update.personal") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ...data,
                _method: 'PUT'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Personal information updated successfully',
                    confirmButtonColor: '#10b981',
                    timer: 2000
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to update personal information',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating personal information',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    // Update Professional Information
    function updateProfessionalInfo(data) {
        fetch('{{ route("nutritionist.profile.update.professional") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ...data,
                _method: 'PUT'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Professional information updated successfully',
                    confirmButtonColor: '#10b981',
                    timer: 2000
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to update professional information',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating professional information',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    // Change Password
    function changePassword() {
        Swal.fire({
            title: `
                <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #06b6d4, #0891b2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-key" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div style="text-align: left;">
                        <h3 style="margin: 0; color: #1f2937; font-size: 24px; font-weight: 700;">Change Password</h3>
                        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">Update your account password</p>
                    </div>
                </div>
            `,
            html: `
                <div style="text-align: left; padding: 20px 10px;">
                    <div style="display: grid; gap: 20px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>Current Password *
                            </label>
                            <input id="swal-current-password" type="password" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Enter current password" required>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>New Password *
                            </label>
                            <input id="swal-new-password" type="password" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Enter new password (min. 8 characters)" required>
                            <small style="color: #6b7280; font-size: 12px; margin-top: 5px; display: block;">Password must be at least 8 characters long</small>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>Confirm New Password *
                            </label>
                            <input id="swal-confirm-password" type="password" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Re-enter new password" required>
                        </div>
                    </div>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Update Password',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'nutritionist-swal-popup',
                confirmButton: 'nutritionist-swal-confirm',
                cancelButton: 'nutritionist-swal-cancel'
            },
            didOpen: () => {
                // Focus styling
                document.querySelectorAll('.swal2-input').forEach(input => {
                    input.addEventListener('focus', function() {
                        this.style.borderColor = '#10b981';
                        this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
                    });
                    input.addEventListener('blur', function() {
                        this.style.borderColor = '#e5e7eb';
                        this.style.boxShadow = 'none';
                    });
                });
            },
            preConfirm: () => {
                const currentPassword = document.getElementById('swal-current-password').value;
                const newPassword = document.getElementById('swal-new-password').value;
                const confirmPassword = document.getElementById('swal-confirm-password').value;
                
                if (!currentPassword || !newPassword || !confirmPassword) {
                    Swal.showValidationMessage('All fields are required');
                    return false;
                }
                
                if (newPassword.length < 8) {
                    Swal.showValidationMessage('New password must be at least 8 characters long');
                    return false;
                }
                
                if (newPassword !== confirmPassword) {
                    Swal.showValidationMessage('New passwords do not match');
                    return false;
                }
                
                return {
                    current_password: currentPassword,
                    new_password: newPassword,
                    new_password_confirmation: confirmPassword
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updatePassword(result.value);
            }
        });
    }

    // Update Password
    function updatePassword(data) {
        fetch('{{ route("nutritionist.profile.update.password") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ...data,
                _method: 'PUT'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Updated!',
                    text: data.message || 'Your password has been updated successfully',
                    confirmButtonColor: '#10b981',
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to update password',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating password',
                confirmButtonColor: '#ef4444'
            });
        });
    }
</script>
<script src="{{ asset('js/nutritionist/profile.js') }}"></script>

<style>
    /* SweetAlert Custom Styling for Nutritionist Profile */
    .nutritionist-swal-popup {
        border-radius: 16px !important;
        padding: 0 !important;
    }

    .nutritionist-swal-popup .swal2-title {
        padding: 20px 30px 10px !important;
        margin: 0 !important;
    }

    .nutritionist-swal-popup .swal2-html-container {
        padding: 0 30px 20px !important;
        margin: 0 !important;
    }

    .nutritionist-swal-confirm {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        border: none !important;
        border-radius: 10px !important;
        padding: 12px 30px !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        transition: all 0.3s ease !important;
    }

    .nutritionist-swal-confirm:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4) !important;
    }

    .nutritionist-swal-cancel {
        background: #f3f4f6 !important;
        color: #374151 !important;
        border: 2px solid #d1d5db !important;
        border-radius: 10px !important;
        padding: 12px 30px !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        transition: all 0.3s ease !important;
    }

    .nutritionist-swal-cancel:hover {
        background: #e5e7eb !important;
        transform: translateY(-2px) !important;
    }

    .swal2-actions {
        gap: 15px !important;
        padding: 20px 30px 30px !important;
        margin: 0 !important;
    }
</style>
@endpush
