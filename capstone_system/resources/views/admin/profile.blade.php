@extends('layouts.dashboard')

@section('title', 'Admin Profile')

@section('page-title', 'My Profile')
@section('page-subtitle', 'View and manage your administrator information')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/profile.css') }}?v={{ filemtime(public_path('css/admin/profile.css')) }}">
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
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="avatar-status {{ Auth::user()->is_active ? 'online' : 'offline' }}"></div>
                </div>
                <div class="profile-info">
                    <div class="profile-name-section">
                        <h1 class="profile-name">{{ Auth::user()->first_name }} {{ Auth::user()->middle_name }} {{ Auth::user()->last_name }}</h1>
                        <span class="verified-icon" title="System Administrator">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                    </div>
                    <p class="profile-title"><i class="fas fa-crown"></i> System Administrator</p>
                    <div class="profile-meta">
                        <span class="meta-item">
                            <i class="fas fa-calendar-check"></i>
                            Member since {{ Auth::user()->created_at->format('M Y') }}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-envelope"></i>
                            {{ Auth::user()->email }}
                        </span>
                        @if(Auth::user()->contact_number)
                        <span class="meta-item">
                            <i class="fas fa-phone"></i>
                            {{ Auth::user()->contact_number }}
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
                <div class="quick-stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">{{ \App\Models\User::count() }}</div>
                    <div class="quick-stat-label">Total Users</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon patients">
                    <i class="fas fa-child"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">{{ \App\Models\Patient::count() }}</div>
                    <div class="quick-stat-label">Total Patients</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon active">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">{{ \App\Models\User::where('is_active', true)->count() }}</div>
                    <div class="quick-stat-label">Active Users</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon assessments">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="quick-stat-info">
                    <div class="quick-stat-value">{{ \App\Models\Assessment::count() }}</div>
                    <div class="quick-stat-label">Screenings</div>
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
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-crown"></i>
                                    Role
                                </div>
                                <div class="info-value badge-value">System Administrator</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-calendar-plus"></i>
                                    Account Created
                                </div>
                                <div class="info-value">{{ Auth::user()->created_at->format('F d, Y') }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-clock"></i>
                                    Last Updated
                                </div>
                                <div class="info-value">{{ Auth::user()->updated_at->format('F d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Access Card -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <div class="card-icon professional">
                                <i class="fas fa-key"></i>
                            </div>
                            <h3 class="card-title">System Access & Permissions</h3>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="info-list">
                            <div class="info-row full-width">
                                <div class="info-label">
                                    <i class="fas fa-shield-alt"></i>
                                    Administrator Privileges
                                </div>
                                <div class="info-value text-block">
                                    Full system access with unrestricted permissions. Can manage users, view all data, configure system settings, and perform administrative tasks.
                                </div>
                            </div>
                            <div class="info-row full-width">
                                <div class="info-label">
                                    <i class="fas fa-cog"></i>
                                    Capabilities
                                </div>
                                <div class="info-value text-block">
                                    User Management • Patient Records • System Configuration • Reports & Analytics • Security Settings • Database Management
                                </div>
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
                            <div class="status-item {{ Auth::user()->is_active ? 'active' : 'inactive' }}">
                                <div class="status-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="status-details">
                                    <div class="status-title">Account Status</div>
                                    <div class="status-value">{{ Auth::user()->is_active ? 'Active' : 'Inactive' }}</div>
                                </div>
                            </div>
                            <div class="status-item verified">
                                <div class="status-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <div class="status-details">
                                    <div class="status-title">Role Status</div>
                                    <div class="status-value">Administrator</div>
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
                                <div class="info-value">{{ Auth::user()->email }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-clock"></i>
                                    Last Updated
                                </div>
                                <div class="info-value">{{ Auth::user()->updated_at->format('M d, Y') }}</div>
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
                            <a href="{{ route('admin.users') }}" class="action-btn">
                                <i class="fas fa-users"></i>
                                <span>Manage Users</span>
                            </a>
                            <a href="{{ route('admin.patients') }}" class="action-btn">
                                <i class="fas fa-child"></i>
                                <span>View Patients</span>
                            </a>
                            <a href="{{ route('admin.dashboard') }}" class="action-btn">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('admin.reports') }}" class="action-btn">
                                <i class="fas fa-file-medical"></i>
                                <span>View Reports</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Expose server-side data and routes as globals for external JS
    window.adminData = {
        first_name: "{{ Auth::user()->first_name }}",
        middle_name: "{{ Auth::user()->middle_name ?? '' }}",
        last_name: "{{ Auth::user()->last_name }}",
        contact_number: "{{ Auth::user()->contact_number ?? '' }}",
        email: "{{ Auth::user()->email }}"
    };
    window.adminProfileUpdateUrl = "{{ route('admin.profile.update') }}";
    window.adminPasswordUpdateUrl = "{{ route('admin.password.update') }}";
</script>
<script src="{{ asset('js/admin/profile.js') }}?v={{ filemtime(public_path('js/admin/profile.js')) }}"></script>
@endpush
