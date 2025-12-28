@extends('layouts.dashboard')

@section('title', 'Parent Profile')

@section('page-title', 'My Profile')
@section('page-subtitle', 'View and manage your personal information')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/parent/profile.css') }}">
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
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="avatar-status {{ Auth::user()->is_active ? 'online' : 'offline' }}"></div>
                </div>
                <div class="profile-info">
                    <div class="profile-name-section">
                        <h1 class="profile-name">{{ Auth::user()->first_name }} {{ Auth::user()->middle_name }} {{ Auth::user()->last_name }}</h1>
                        @if(Auth::user()->email_verified_at)
                            <span class="verified-icon" title="Verified Account">
                                <i class="fas fa-certificate"></i>
                            </span>
                        @endif
                    </div>
                    <p class="profile-title"><i class="fas fa-users"></i> Parent / Guardian</p>
                    <div class="profile-meta">
                        <span class="meta-item">
                            <i class="fas fa-calendar-check"></i>
                            Member since {{ Auth::user()->created_at->format('M Y') }}
                        </span>
                        @if(Auth::user()->patientsAsParent()->count() > 0)
                        <span class="meta-item">
                            <i class="fas fa-child"></i>
                            {{ Auth::user()->patientsAsParent()->count() }} {{ Str::plural('Child', Auth::user()->patientsAsParent()->count()) }}
                        </span>
                        @endif
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
                <div class="quick-stat-icon children">
                    <i class="fas fa-child"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">{{ Auth::user()->patientsAsParent()->count() }}</div>
                    <div class="quick-stat-label">Total Children</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon assessments">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">
                        {{ Auth::user()->patientsAsParent()->withCount('assessments')->get()->sum('assessments_count') }}
                    </div>
                    <div class="quick-stat-label">Assessments</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon active">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">
                        {{ Auth::user()->patientsAsParent()->where('nutritionist_id', '!=', null)->count() }}
                    </div>
                    <div class="quick-stat-label">Under Care</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon status">
                    <i class="fas fa-shield-check"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">
                        {{ Auth::user()->is_active ? 'Active' : 'Inactive' }}
                    </div>
                    <div class="quick-stat-label">Account Status</div>
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
                                <div class="info-value">{{ Auth::user()->first_name }} {{ Auth::user()->middle_name }} {{ Auth::user()->last_name }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </div>
                                <div class="info-value">{{ Auth::user()->email }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-phone"></i>
                                    Contact Number
                                </div>
                                <div class="info-value">{{ Auth::user()->contact_number ?? 'Not provided' }}</div>
                            </div>
                            @if(Auth::user()->birth_date)
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-birthday-cake"></i>
                                    Date of Birth
                                </div>
                                <div class="info-value">{{ Auth::user()->birth_date->format('F d, Y') }}</div>
                            </div>
                            @endif
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-venus-mars"></i>
                                    Gender
                                </div>
                                <div class="info-value">{{ Auth::user()->sex ? ucfirst(Auth::user()->sex) : 'Not provided' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Address
                                </div>
                                <div class="info-value">{{ Auth::user()->address ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Children Information Card -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <div class="card-icon children">
                                <i class="fas fa-child"></i>
                            </div>
                            <h3 class="card-title">My Children</h3>
                        </div>
                    </div>
                    <div class="card-content">
                        @if(Auth::user()->patientsAsParent()->count() > 0)
                            <div class="children-list">
                                @foreach(Auth::user()->patientsAsParent as $patient)
                                <div class="child-item">
                                    <div class="child-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="child-info">
                                        <div class="child-name">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                                        <div class="child-meta">
                                            <span><i class="fas fa-calendar"></i> {{ $patient->age_months }} months old</span>
                                            @if($patient->nutritionist)
                                            <span><i class="fas fa-user-md"></i> {{ $patient->nutritionist->first_name }} {{ $patient->nutritionist->last_name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="child-status {{ $patient->nutritionist ? 'active' : 'pending' }}">
                                        {{ $patient->nutritionist ? 'Under Care' : 'Pending' }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-child"></i>
                                <p>No children registered yet</p>
                            </div>
                        @endif
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
                            <div class="status-item {{ Auth::user()->is_active ? 'active' : 'inactive' }}">
                                <div class="status-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="status-details">
                                    <div class="status-title">Account Status</div>
                                    <div class="status-value">{{ Auth::user()->is_active ? 'Active' : 'Inactive' }}</div>
                                </div>
                            </div>
                            <div class="status-item {{ Auth::user()->email_verified_at ? 'verified' : 'pending' }}">
                                <div class="status-icon">
                                    <i class="fas fa-envelope-circle-check"></i>
                                </div>
                                <div class="status-details">
                                    <div class="status-title">Email Status</div>
                                    <div class="status-value">{{ Auth::user()->email_verified_at ? 'Verified' : 'Not Verified' }}</div>
                                </div>
                            </div>
                        </div>
                        @if(Auth::user()->email_verified_at)
                        <div class="status-footer">
                            <i class="fas fa-check-double"></i>
                            Verified on {{ Auth::user()->email_verified_at->format('F d, Y') }}
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
                                <div class="info-value">
                                    <div>{{ Auth::user()->email }}</div>
                                    @if(Auth::user()->email_verified_at)
                                    <small style="color: #10b981; font-size: 12px; display: block; margin-top: 4px;">
                                        <i class="fas fa-check-circle"></i> Verified {{ Auth::user()->email_verified_at->diffForHumans() }}
                                    </small>
                                    @else
                                    <small style="color: #f59e0b; font-size: 12px; display: block; margin-top: 4px;">
                                        <i class="fas fa-exclamation-circle"></i> Not verified
                                    </small>
                                    @endif
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-clock"></i>
                                    Last Updated
                                </div>
                                <div class="info-value">
                                    <div>{{ Auth::user()->updated_at->format('M d, Y') }}</div>
                                    <small style="color: #6b7280; font-size: 12px; display: block; margin-top: 4px;">
                                        {{ Auth::user()->updated_at->diffForHumans() }}
                                    </small>
                                </div>
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
                            @if(Auth::user()->patientsAsParent()->latest()->first())
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Latest Child Registration</div>
                                    <div class="timeline-date">{{ Auth::user()->patientsAsParent()->latest()->first()->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            @endif
                            @if(Auth::user()->email_verified_at)
                            <div class="timeline-item">
                                <div class="timeline-marker success"></div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Email Verified</div>
                                    <div class="timeline-date">{{ Auth::user()->email_verified_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                            @endif
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Account Created</div>
                                    <div class="timeline-date">{{ Auth::user()->created_at->format('M d, Y') }}</div>
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
                            <button class="action-btn" onclick="window.location.href='{{ route('parent.dashboard') }}'">
                                <i class="fas fa-child"></i>
                                <span>View Children</span>
                            </button>
                            <button class="action-btn" onclick="editPersonalInfo()">
                                <i class="fas fa-user-edit"></i>
                                <span>Edit Profile</span>
                            </button>
                            <button class="action-btn" onclick="changePassword()">
                                <i class="fas fa-key"></i>
                                <span>Change Password</span>
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
    // Parent data for forms
    const parentData = {
        first_name: "{{ Auth::user()->first_name }}",
        middle_name: "{{ Auth::user()->middle_name }}",
        last_name: "{{ Auth::user()->last_name }}",
        contact_number: "{{ Auth::user()->contact_number }}",
        birth_date: "{{ Auth::user()->birth_date ? Auth::user()->birth_date->format('Y-m-d') : '' }}",
        sex: "{{ Auth::user()->sex }}",
        address: `{{ Auth::user()->address }}`
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
                            <input id="swal-first-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.first_name}" required>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>Middle Name
                            </label>
                            <input id="swal-middle-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.middle_name}">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-user" style="color: #10b981; margin-right: 5px;"></i>Last Name *
                            </label>
                            <input id="swal-last-name" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.last_name}" required>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-phone" style="color: #10b981; margin-right: 5px;"></i>Contact Number
                            </label>
                            <input id="swal-contact" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.contact_number}">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-calendar" style="color: #10b981; margin-right: 5px;"></i>Date of Birth
                            </label>
                            <input id="swal-birth-date" type="date" class="swal2-input" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;" value="${parentData.birth_date}">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-venus-mars" style="color: #10b981; margin-right: 5px;"></i>Gender
                            </label>
                            <select id="swal-gender" class="swal2-select" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                                <option value="">Select Gender</option>
                                <option value="male" ${parentData.sex === 'male' ? 'selected' : ''}>Male</option>
                                <option value="female" ${parentData.sex === 'female' ? 'selected' : ''}>Female</option>
                                <option value="other" ${parentData.sex === 'other' ? 'selected' : ''}>Other</option>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-map-marker-alt" style="color: #10b981; margin-right: 5px;"></i>Address
                            </label>
                            <textarea id="swal-address" class="swal2-textarea" style="width: 100%; margin: 0; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; min-height: 80px;" rows="3">${parentData.address}</textarea>
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
                popup: 'parent-swal-popup',
                confirmButton: 'parent-swal-confirm',
                cancelButton: 'parent-swal-cancel'
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
                                <input id="swal-current-password" type="password" class="swal2-input password-input" autocomplete="current-password" style="width: 100%; margin: 0; padding: 12px 45px 12px 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Enter current password" required>
                                <button type="button" class="password-toggle-btn" data-target="swal-current-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6b7280; padding: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>New Password *
                            </label>
                            <div style="position: relative;">
                                <input id="swal-new-password" type="password" class="swal2-input password-input" autocomplete="new-password" style="width: 100%; margin: 0; padding: 12px 45px 12px 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Enter new password" required>
                                <button type="button" class="password-toggle-btn" data-target="swal-new-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6b7280; padding: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength-info" style="margin-top: 10px; padding: 10px; background: #f9fafb; border-radius: 6px;">
                                <small style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px;">Password must contain:</small>
                                <ul style="list-style: none; padding: 0; margin: 0; font-size: 12px;">
                                    <li class="requirement" data-requirement="length" style="color: #6b7280; padding: 4px 0;">
                                        <i class="fas fa-circle" style="font-size: 8px; margin-right: 8px;"></i>At least 8 characters
                                    </li>
                                    <li class="requirement" data-requirement="uppercase" style="color: #6b7280; padding: 4px 0;">
                                        <i class="fas fa-circle" style="font-size: 8px; margin-right: 8px;"></i>One uppercase letter (A-Z)
                                    </li>
                                    <li class="requirement" data-requirement="lowercase" style="color: #6b7280; padding: 4px 0;">
                                        <i class="fas fa-circle" style="font-size: 8px; margin-right: 8px;"></i>One lowercase letter (a-z)
                                    </li>
                                    <li class="requirement" data-requirement="number" style="color: #6b7280; padding: 4px 0;">
                                        <i class="fas fa-circle" style="font-size: 8px; margin-right: 8px;"></i>One number (0-9)
                                    </li>
                                    <li class="requirement" data-requirement="special" style="color: #6b7280; padding: 4px 0;">
                                        <i class="fas fa-circle" style="font-size: 8px; margin-right: 8px;"></i>One special character (@$!%*?&#)
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                                <i class="fas fa-lock" style="color: #10b981; margin-right: 5px;"></i>Confirm New Password *
                            </label>
                            <div style="position: relative;">
                                <input id="swal-confirm-password" type="password" class="swal2-input password-input" autocomplete="new-password" style="width: 100%; margin: 0; padding: 12px 45px 12px 12px; border: 2px solid #e5e7eb; border-radius: 8px;" placeholder="Re-enter new password" required>
                                <button type="button" class="password-toggle-btn" data-target="swal-confirm-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6b7280; padding: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
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
                popup: 'parent-swal-popup',
                confirmButton: 'parent-swal-confirm',
                cancelButton: 'parent-swal-cancel'
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

                // Password toggle functionality
                document.querySelectorAll('.password-toggle-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const targetId = this.getAttribute('data-target');
                        const input = document.getElementById(targetId);
                        const icon = this.querySelector('i');
                        
                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        } else {
                            input.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    });
                });

                // Password strength validation
                const newPasswordInput = document.getElementById('swal-new-password');
                if (newPasswordInput) {
                    newPasswordInput.addEventListener('input', function() {
                        const password = this.value;
                        const requirements = {
                            length: password.length >= 8,
                            uppercase: /[A-Z]/.test(password),
                            lowercase: /[a-z]/.test(password),
                            number: /[0-9]/.test(password),
                            special: /[@$!%*?&#]/.test(password)
                        };

                        Object.keys(requirements).forEach(req => {
                            const element = document.querySelector(`[data-requirement="${req}"]`);
                            if (element) {
                                const icon = element.querySelector('i');
                                if (requirements[req]) {
                                    element.style.color = '#10b981';
                                    icon.classList.remove('fa-circle');
                                    icon.classList.add('fa-check-circle');
                                } else {
                                    element.style.color = '#6b7280';
                                    icon.classList.remove('fa-check-circle');
                                    icon.classList.add('fa-circle');
                                }
                            }
                        });
                    });
                }
            },
            preConfirm: () => {
                const currentPassword = document.getElementById('swal-current-password').value;
                const newPassword = document.getElementById('swal-new-password').value;
                const confirmPassword = document.getElementById('swal-confirm-password').value;
                
                if (!currentPassword || !newPassword || !confirmPassword) {
                    Swal.showValidationMessage('All fields are required');
                    return false;
                }
                
                // Password strength validation
                const requirements = {
                    length: newPassword.length >= 8,
                    uppercase: /[A-Z]/.test(newPassword),
                    lowercase: /[a-z]/.test(newPassword),
                    number: /[0-9]/.test(newPassword),
                    special: /[@$!%*?&#]/.test(newPassword)
                };

                const unmetRequirements = Object.keys(requirements).filter(req => !requirements[req]);
                
                if (unmetRequirements.length > 0) {
                    Swal.showValidationMessage('Password does not meet all requirements');
                    return false;
                }
                
                if (newPassword !== confirmPassword) {
                    Swal.showValidationMessage('New passwords do not match');
                    return false;
                }
                
                return {
                    current_password: currentPassword,
                    password: newPassword,
                    password_confirmation: confirmPassword
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updatePassword(result.value);
            }
        });
    }

    // Update Personal Information
    function updatePersonalInfo(data) {
        fetch('{{ route("parent.profile.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ...data,
                _method: 'PUT'
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server did not return JSON response');
            }
            return response.json();
        })
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
                text: error.message || 'An error occurred while updating personal information',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    // Update Password
    function updatePassword(data) {
        fetch('{{ route("parent.password.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ...data,
                _method: 'PUT'
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server did not return JSON response');
            }
            return response.json();
        })
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
                text: error.message || 'An error occurred while updating password',
                confirmButtonColor: '#ef4444'
            });
        });
    }
</script>
<script src="{{ asset('js/parent/profile.js') }}"></script>

<style>
    /* SweetAlert Custom Styling for Parent Profile */
    .parent-swal-popup {
        border-radius: 16px !important;
        padding: 0 !important;
    }

    .parent-swal-popup .swal2-title {
        padding: 20px 30px 10px !important;
        margin: 0 !important;
    }

    .parent-swal-popup .swal2-html-container {
        padding: 0 30px 20px !important;
        margin: 0 !important;
    }

    .parent-swal-confirm {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        border: none !important;
        border-radius: 10px !important;
        padding: 12px 30px !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        transition: all 0.3s ease !important;
    }

    .parent-swal-confirm:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4) !important;
    }

    .parent-swal-cancel {
        background: #f3f4f6 !important;
        color: #374151 !important;
        border: 2px solid #d1d5db !important;
        border-radius: 10px !important;
        padding: 12px 30px !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        transition: all 0.3s ease !important;
    }

    .parent-swal-cancel:hover {
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
