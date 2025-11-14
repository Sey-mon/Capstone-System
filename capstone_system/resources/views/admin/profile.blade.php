@extends('layouts.dashboard')

@section('title', 'Admin Profile')

@section('page-title', 'My Profile')
@section('page-subtitle', 'View and manage your administrator information')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/parent/parent-profile.css') }}?v={{ time() }}">
<style>
    .profile-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/parent/parent-profile.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="d-flex align-items-center">
            <div class="profile-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="profile-info ms-4">
                <h1>{{ Auth::user()->first_name }} {{ Auth::user()->middle_name }} {{ Auth::user()->last_name }}</h1>
                <p><i class="fas fa-crown me-2"></i>System Administrator</p>
                <p><i class="fas fa-envelope me-2"></i>{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>

    <!-- Profile Tabs -->
    <div class="profile-tabs">
        <button class="tab-btn active" onclick="showTab('personal')">
            <i class="fas fa-user me-2"></i>Personal Information
        </button>
        <button class="tab-btn" onclick="showTab('security')">
            <i class="fas fa-shield-alt me-2"></i>Security
        </button>
    </div>

    <!-- Personal Information Tab -->
    <div id="personal-tab" class="tab-content active">
        <div class="info-card">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- View Mode -->
            <div id="view-mode">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Personal Information</h3>
                    <button class="btn-modern btn-primary" onclick="toggleEdit()">
                        <i class="fas fa-edit"></i>Edit Profile
                    </button>
                </div>

                <div class="info-display">
                    <span class="info-label">First Name</span>
                    <span class="info-value">{{ Auth::user()->first_name }}</span>
                </div>
                <div class="info-display">
                    <span class="info-label">Middle Name</span>
                    <span class="info-value">{{ Auth::user()->middle_name ?? 'Not provided' }}</span>
                </div>
                <div class="info-display">
                    <span class="info-label">Last Name</span>
                    <span class="info-value">{{ Auth::user()->last_name }}</span>
                </div>
                <div class="info-display">
                    <span class="info-label">Email Address</span>
                    <span class="info-value">{{ Auth::user()->email }}</span>
                </div>
                <div class="info-display">
                    <span class="info-label">Contact Number</span>
                    <span class="info-value">{{ Auth::user()->contact_number ?? 'Not provided' }}</span>
                </div>
                <div class="info-display">
                    <span class="info-label">Account Status</span>
                    <span class="info-value">
                        @if(Auth::user()->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </span>
                </div>
                <div class="info-display">
                    <span class="info-label">Member Since</span>
                    <span class="info-value">{{ Auth::user()->created_at->format('F d, Y') }}</span>
                </div>
            </div>

            <!-- Edit Mode -->
            <div id="edit-mode" class="edit-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Edit Personal Information</h3>
                    <button class="btn-modern btn-secondary" onclick="toggleEdit()">
                        <i class="fas fa-times"></i>Cancel
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="{{ old('first_name', Auth::user()->first_name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" 
                                       value="{{ old('middle_name', Auth::user()->middle_name) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" 
                                       value="{{ old('last_name', Auth::user()->last_name) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" 
                               value="{{ old('email', Auth::user()->email) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" 
                               value="{{ old('contact_number', Auth::user()->contact_number) }}">
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn-modern btn-primary">
                            <i class="fas fa-save"></i>Save Changes
                        </button>
                        <button type="button" class="btn-modern btn-secondary" onclick="toggleEdit()">
                            <i class="fas fa-times"></i>Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Tab -->
    <div id="security-tab" class="tab-content">
        <div class="info-card">
            <h3 class="mb-4">Change Password</h3>
            
            <form method="POST" action="{{ route('admin.password.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-modern btn-danger">
                    <i class="fas fa-key"></i>Update Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
