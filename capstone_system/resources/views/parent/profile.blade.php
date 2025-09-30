@extends('layouts.dashboard')

@section('title', 'Parent Profile')

@section('navigation')
    @include('partials.navigation')
@endsection

@push('styles')
<style>
.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 40px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
    animation: float 20s infinite linear;
}

@keyframes float {
    0% { transform: translateX(-50%) translateY(-50%) rotate(0deg); }
    100% { transform: translateX(-50%) translateY(-50%) rotate(360deg); }
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin-bottom: 20px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    position: relative;
    z-index: 1;
}

.profile-info h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 600;
    position: relative;
    z-index: 1;
}

.profile-info p {
    font-size: 1.2rem;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.profile-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid #f1f3f4;
}

.tab-btn {
    padding: 15px 30px;
    border: none;
    background: none;
    font-size: 1rem;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.info-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    border: 1px solid #f1f3f4;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e1e5e9;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #fafbfc;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.btn-modern {
    padding: 15px 30px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #f8f9fa;
    color: #666;
    border: 2px solid #e1e5e9;
}

.btn-secondary:hover {
    background: #e9ecef;
}

.btn-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
}

.info-display {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-display:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #333;
    font-size: 1rem;
}

.info-value {
    color: #666;
    font-size: 1rem;
}

.edit-section {
    display: none;
}

.edit-section.active {
    display: block;
}

.row {
    display: flex;
    gap: 20px;
    margin: -10px;
}

.col {
    flex: 1;
    padding: 10px;
}

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    border-left: 4px solid;
}

.alert-success {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

@media (max-width: 768px) {
    .profile-container {
        padding: 10px;
    }
    
    .profile-header {
        padding: 30px 20px;
    }
    
    .profile-info h1 {
        font-size: 2rem;
    }
    
    .row {
        flex-direction: column;
        gap: 0;
    }
    
    .profile-tabs {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        padding: 12px 20px;
        font-size: 0.9rem;
    }
}
</style>
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

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked tab button
    event.target.classList.add('active');
}

function toggleEdit() {
    const viewMode = document.getElementById('view-mode');
    const editMode = document.getElementById('edit-mode');
    
    if (viewMode.style.display === 'none') {
        viewMode.style.display = 'block';
        editMode.style.display = 'none';
    } else {
        viewMode.style.display = 'none';
        editMode.style.display = 'block';
    }
}
</script>
@endsection
