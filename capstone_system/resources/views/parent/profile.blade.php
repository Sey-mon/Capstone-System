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
                                    Sex
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
                            <button class="action-btn action-btn-danger" id="deleteAccountBtn" type="button">
                                <i class="fas fa-trash-alt"></i>
                                <span>Delete Account</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('js/parent/profile.js') }}"></script>
<script>
    // Initialize parent data and routes for profile.js
    initializeParentData({
        first_name: "{{ Auth::user()->first_name }}",
        middle_name: "{{ Auth::user()->middle_name }}",
        last_name: "{{ Auth::user()->last_name }}",
        contact_number: "{{ Auth::user()->contact_number ?? '' }}",
        birth_date: "{{ Auth::user()->birth_date ? Auth::user()->birth_date->format('Y-m-d') : '' }}",
        sex: "{{ Auth::user()->sex ?? '' }}",
        address: `{{ Auth::user()->address ?? '' }}`
    });

    // Set update routes for the profile functions
    window.updateProfileRoute = '{{ route("parent.profile.update") }}';
    window.updatePasswordRoute = '{{ route("parent.password.update") }}';
    window.deleteAccountRoute = '{{ route("parent.account.delete") }}';

    // Add event listener for delete account button - works on both mobile and desktop
    document.addEventListener('DOMContentLoaded', function() {
        const deleteBtn = document.getElementById('deleteAccountBtn');
        if (deleteBtn) {
            // Remove any existing onclick to avoid double firing
            deleteBtn.onclick = null;
            
            // Add click event listener that works on both touch and mouse
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                deleteAccount();
            }, { passive: false });
            
            // Also add touch event for better mobile support
            deleteBtn.addEventListener('touchend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                deleteAccount();
            }, { passive: false });
        }
    });
</script>
@endpush

