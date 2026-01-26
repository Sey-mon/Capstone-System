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
                    <p class="profile-title"><i class="fas fa-stethoscope"></i> Barangay Nutritionist Scholar</p>
                    <div class="profile-meta">
                        <span class="meta-item">
                            <i class="fas fa-calendar-check"></i>
                            Member since {{ $nutritionist->created_at->format('M Y') }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-award"></i>
                            {{ $nutritionist->years_experience ?? 0 }}+ years experience
                        </span>
                    </div>
                </div>
                <div class="profile-actions">
                    <button class="btn btn-primary" onclick="editPersonalInfo()">
                        <i class="fas fa-edit"></i>
                        Edit Profile
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
                                    <i class="fas fa-venus-mars"></i>
                                    Gender
                                </div>
                                <div class="info-value">{{ $nutritionist->sex ? ucfirst($nutritionist->sex) : 'Not specified' }}</div>
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

                <!-- Danger Zone Card -->
                <div class="content-card danger-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <div class="card-icon danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h3 class="card-title" style="color: #dc2626;">Danger Zone</h3>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="danger-content" style="padding: 16px; background: #fef2f2; border-radius: 8px; border-left: 4px solid #dc2626;">
                            <div style="margin-bottom: 12px;">
                                <h4 style="color: #991b1b; font-size: 16px; font-weight: 600; margin: 0 0 8px 0;">
                                    <i class="fas fa-user-slash" style="margin-right: 8px;"></i>Deactivate Account
                                </h4>
                                <p style="color: #7f1d1d; font-size: 14px; margin: 0; line-height: 1.5;">
                                    Once you deactivate your account, you will be logged out and will need to contact an administrator to reactivate it. Your patient data and assessments will be preserved for compliance purposes.
                                </p>
                            </div>
                            <button onclick="confirmAccountDeletion()" class="btn btn-danger" style="background: #dc2626; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                                <i class="fas fa-user-slash"></i> Deactivate Account
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
                                <i class="fas fa-venus-mars" style="color: #10b981; margin-right: 5px;"></i>Gender
                            </label>
                            <select id="swal-gender" class="swal2-select" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                                <option value="">Select Gender</option>
                                <option value="male" ${nutritionistData.sex === 'male' ? 'selected' : ''}>Male</option>
                                <option value="female" ${nutritionistData.sex === 'female' ? 'selected' : ''}>Female</option>
                                <option value="other" ${nutritionistData.sex === 'other' ? 'selected' : ''}>Other</option>
                            </select>
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
                    sex: document.getElementById('swal-gender').value
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
                            <div style="position: relative;">
                                <input id="swal-current-password" type="password" class="swal2-input" style="width: 100%; margin: 0; padding: 12px 45px 12px 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Enter current password" required>
                                <button type="button" onclick="togglePasswordVisibility('swal-current-password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280; cursor: pointer; padding: 8px; font-size: 16px;" title="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>New Password *
                            </label>
                            <div style="position: relative;">
                                <input id="swal-new-password" type="password" class="swal2-input" style="width: 100%; margin: 0; padding: 12px 45px 12px 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Enter new password (min. 8 characters)" required>
                                <button type="button" onclick="togglePasswordVisibility('swal-new-password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280; cursor: pointer; padding: 8px; font-size: 16px;" title="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-strength" style="margin-top: 8px; display: none;">
                                <div style="display: flex; gap: 4px; margin-bottom: 8px;">
                                    <div class="strength-bar" style="flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px;"></div>
                                    <div class="strength-bar" style="flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px;"></div>
                                    <div class="strength-bar" style="flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px;"></div>
                                    <div class="strength-bar" style="flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px;"></div>
                                </div>
                                <div id="strength-text" style="font-size: 12px; font-weight: 600; color: #6b7280;"></div>
                            </div>
                            <div id="password-requirements" style="margin-top: 10px; padding: 12px; background: #f9fafb; border-radius: 6px; border-left: 3px solid #10b981;">
                                <div style="font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 8px;">Password must contain:</div>
                                <div id="req-length" style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">
                                    <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px; color: #d1d5db;"></i>At least 8 characters
                                </div>
                                <div id="req-uppercase" style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">
                                    <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px; color: #d1d5db;"></i>One uppercase letter (A-Z)
                                </div>
                                <div id="req-lowercase" style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">
                                    <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px; color: #d1d5db;"></i>One lowercase letter (a-z)
                                </div>
                                <div id="req-number" style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">
                                    <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px; color: #d1d5db;"></i>One number (0-9)
                                </div>
                                <div id="req-special" style="font-size: 12px; color: #6b7280;">
                                    <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px; color: #d1d5db;"></i>One special character (!@#$%...)
                                </div>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>Confirm New Password *
                            </label>
                            <div style="position: relative;">
                                <input id="swal-confirm-password" type="password" class="swal2-input" style="width: 100%; margin: 0; padding: 12px 45px 12px 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Re-enter new password" required>
                                <button type="button" onclick="togglePasswordVisibility('swal-confirm-password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280; cursor: pointer; padding: 8px; font-size: 16px;" title="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-match" style="margin-top: 8px; font-size: 12px; display: none;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-right: 5px;"></i>
                                <span style="color: #10b981; font-weight: 500;">Passwords match</span>
                            </div>
                            <div id="password-mismatch" style="margin-top: 8px; font-size: 12px; display: none;">
                                <i class="fas fa-times-circle" style="color: #ef4444; margin-right: 5px;"></i>
                                <span style="color: #ef4444; font-weight: 500;">Passwords do not match</span>
                            </div>
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
                
                // Real-time password validation
                const newPasswordInput = document.getElementById('swal-new-password');
                const confirmPasswordInput = document.getElementById('swal-confirm-password');
                
                newPasswordInput.addEventListener('input', function() {
                    validatePasswordStrength(this.value);
                });
                
                confirmPasswordInput.addEventListener('input', function() {
                    validatePasswordMatch(newPasswordInput.value, this.value);
                });
            },
            preConfirm: () => {
                const currentPassword = document.getElementById('swal-current-password').value;
                const newPassword = document.getElementById('swal-new-password').value;
                const confirmPassword = document.getElementById('swal-confirm-password').value;
                
                // Check if all fields are filled
                if (!currentPassword || !newPassword || !confirmPassword) {
                    Swal.showValidationMessage('All fields are required');
                    return false;
                }
                
                // Check minimum length
                if (newPassword.length < 8) {
                    Swal.showValidationMessage('Password must be at least 8 characters long');
                    return false;
                }
                
                // Check for uppercase letter
                if (!/[A-Z]/.test(newPassword)) {
                    Swal.showValidationMessage('Password must contain at least one uppercase letter');
                    return false;
                }
                
                // Check for lowercase letter
                if (!/[a-z]/.test(newPassword)) {
                    Swal.showValidationMessage('Password must contain at least one lowercase letter');
                    return false;
                }
                
                // Check for number
                if (!/[0-9]/.test(newPassword)) {
                    Swal.showValidationMessage('Password must contain at least one number');
                    return false;
                }
                
                // Check for special character
                if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(newPassword)) {
                    Swal.showValidationMessage('Password must contain at least one special character (!@#$%^&*...)');
                    return false;
                }
                
                // Check if passwords match
                if (newPassword !== confirmPassword) {
                    Swal.showValidationMessage('New passwords do not match');
                    return false;
                }
                
                // Check if new password is different from current
                if (currentPassword === newPassword) {
                    Swal.showValidationMessage('New password must be different from current password');
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

    // Confirm Account Deactivation
    function confirmAccountDeletion() {
        Swal.fire({
            title: `
                <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #dc2626, #991b1b); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-exclamation-triangle" style="color: white; font-size: 24px;"></i>
                    </div>
                    <div style="text-align: left;">
                        <h3 style="margin: 0; color: #991b1b; font-size: 24px; font-weight: 700;">Deactivate Account?</h3>
                        <p style="margin: 5px 0 0 0; color: #7f1d1d; font-size: 14px;">This action will disable your account</p>
                    </div>
                </div>
            `,
            html: `
                <div style="text-align: left; padding: 20px 10px;">
                    <div style="background: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                        <h4 style="color: #991b1b; font-size: 16px; font-weight: 600; margin: 0 0 12px 0;">
                            <i class="fas fa-info-circle" style="margin-right: 8px;"></i>What happens when you deactivate:
                        </h4>
                        <ul style="color: #7f1d1d; font-size: 14px; margin: 0; padding-left: 24px; line-height: 1.8;">
                            <li>You will be immediately logged out</li>
                            <li>Your account will be deactivated and marked as inactive</li>
                            <li>All your patient data and assessments will be preserved for compliance</li>
                            <li>You will need to contact an administrator to reactivate your account</li>
                            <li>Assessment records will still show your name for audit trail purposes</li>
                        </ul>
                    </div>
                    <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 8px;">
                        <p style="color: #92400e; font-size: 14px; margin: 0; font-weight: 500;">
                            <i class="fas fa-shield-alt" style="margin-right: 8px; color: #f59e0b;"></i>
                            <strong>Note:</strong> As a healthcare provider, your account information cannot be permanently deleted due to regulatory compliance and audit requirements.
                        </p>
                    </div>
                </div>
            `,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-user-slash"></i> Yes, Deactivate My Account',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'nutritionist-swal-popup',
                confirmButton: 'nutritionist-swal-danger',
                cancelButton: 'nutritionist-swal-cancel'
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show final confirmation
                Swal.fire({
                    title: 'Final Confirmation',
                    html: `
                        <div style="padding: 20px 10px;">
                            <p style="color: #374151; font-size: 16px; margin: 0 0 20px 0;">
                                Type <strong style="color: #dc2626;">DEACTIVATE</strong> to confirm account deactivation:
                            </p>
                            <input id="confirm-text" type="text" class="swal2-input" placeholder="Type DEACTIVATE" style="width: 80%; margin: 0 auto; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; text-align: center; font-weight: 600;">
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Deactivate Account',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    preConfirm: () => {
                        const confirmText = document.getElementById('confirm-text').value;
                        if (confirmText !== 'DEACTIVATE') {
                            Swal.showValidationMessage('Please type DEACTIVATE to confirm');
                            return false;
                        }
                        return true;
                    }
                }).then((finalResult) => {
                    if (finalResult.isConfirmed) {
                        deleteAccount();
                    }
                });
            }
        });
    }

    // Delete Account
    function deleteAccount() {
        // Show loading
        Swal.fire({
            title: 'Processing...',
            text: 'Deactivating your account',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('{{ route("nutritionist.account.delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                _method: 'DELETE'
            })
        })
        .then(response => {
            // Log response for debugging
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Account Deactivated',
                    text: data.message || 'Your account has been deactivated successfully',
                    confirmButtonColor: '#10b981',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = '{{ route("login") }}';
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to deactivate account',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while deactivating account. Please try again.',
                confirmButtonColor: '#ef4444'
            });
        });
    }
</script>
<script src="{{ asset('js/nutritionist/profile.js') }}"></script>
@endpush
