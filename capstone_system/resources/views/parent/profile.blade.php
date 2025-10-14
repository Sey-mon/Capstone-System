@extends('layouts.dashboard')

@section('title', 'Parent Profile')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/parent/parent-profile.css') }}?v={{ time() }}">
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
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info ms-4">
                <h1>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h1>
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
                    <span class="info-label">Address</span>
                    <span class="info-value">{{ Auth::user()->address ?? 'Not provided' }}</span>
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

                <form method="POST" action="{{ route('parent.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="{{ old('first_name', Auth::user()->first_name) }}" required>
                            </div>
                        </div>
                        <div class="col">
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

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', Auth::user()->address) }}</textarea>
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
            
            <form method="POST" action="{{ route('parent.password.update') }}">
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
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
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
