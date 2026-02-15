@extends('layouts.dashboard')

@section('title', 'My Children')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/parent/children.css') }}?v={{ filemtime(public_path('css/parent/children.css')) }}">
@endpush

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')

<div class="desktop-page-wrapper">
    <!-- Desktop Header Section -->
    <div class="desktop-header-section">
        <div class="header-container">
            <div class="header-left">
                <div class="page-icon-wrapper">
                    <div class="page-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <div class="icon-pulse"></div>
                </div>
                <div class="page-info">
                    <div class="breadcrumb">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                        <i class="fas fa-chevron-right"></i>
                        <span class="active">My Children</span>
                    </div>
                    <h1 class="page-main-title">
                        <span class="title-text">My Children's Health Records</span>
                        <span class="title-decoration"></span>
                    </h1>
                    <p class="page-description">
                        <i class="fas fa-info-circle"></i>
                        Comprehensive monitoring and tracking of your children's nutrition and health journey
                    </p>
                </div>
            </div>
            <div class="header-right">
                <div class="header-stats-cards">
                    <div class="header-stat-item stat-primary">
                        <div class="stat-background"></div>
                        <div class="header-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="header-stat-content">
                            <div class="header-stat-value" data-count="{{ count($children ?? []) }}">{{ count($children ?? []) }}</div>
                            <div class="header-stat-label">Registered Children</div>
                        </div>
                    </div>
                    <div class="header-stat-item stat-success">
                        <div class="stat-background"></div>
                        <div class="header-stat-icon">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <div class="header-stat-content">
                            <div class="header-stat-value" data-count="{{ $children ? $children->filter(function($child) { return $child->nutritionist !== null; })->count() : 0 }}">{{ $children ? $children->filter(function($child) { return $child->nutritionist !== null; })->count() : 0 }}</div>
                            <div class="header-stat-label">Under Care</div>
                        </div>
                    </div>
                    <div class="header-stat-item stat-info">
                        <div class="stat-background"></div>
                        <div class="header-stat-icon">
                            <i class="fas fa-file-medical-alt"></i>
                        </div>
                        <div class="header-stat-content">
                            <div class="header-stat-value" data-count="{{ $children ? $children->sum(function($child) { return $child->assessments->count(); }) : 0 }}">{{ $children ? $children->sum(function($child) { return $child->assessments->count(); }) : 0 }}</div>
                            <div class="header-stat-label">Total Assessments</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="desktop-content-area">
        <!-- Add Child Section -->
        <div class="add-child-section">
            <button type="button" class="btn-add-child" onclick="showAddChildModal()">
                <i class="fas fa-link"></i>
                <span>Link Child</span>
            </button>
        </div>

        @if(isset($children) && count($children) > 0)
            <!-- Children Grid -->
            <div class="children-desktop-grid">
                @foreach($children as $child)
                    <div class="child-card">
                        <!-- Card Header -->
                        <div class="child-card-header">
                            <div class="child-profile-section">
                                <div class="child-avatar">
                                    <i class="fas fa-child"></i>
                                </div>
                                <div class="child-info">
                                    <h3 class="child-name">{{ $child->first_name }} {{ $child->last_name }}</h3>
                                    <div class="child-meta">
                                        <span class="meta-item">
                                            <i class="fas fa-birthday-cake"></i>
                                            {{ $child->age_months ? ($child->age_months . ' months old') : ($child->age . ($child->age == 1 ? ' year old' : ' years old')) }}
                                        </span>
                                        <span class="meta-divider">•</span>
                                        <span class="meta-item">
                                            <i class="fas fa-{{ ($child->gender ?? $child->sex ?? '') === 'Male' ? 'mars' : 'venus' }}"></i>
                                            {{ $child->gender ?? $child->sex ?? 'Gender not specified' }}
                                        </span>
                                        @if($child->barangay)
                                        <span class="meta-divider">•</span>
                                        <span class="meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ $child->barangay->barangay_name }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="child-actions">
                                <button type="button" 
                                    class="btn-modern btn-primary" 
                                    onclick="showChildProfile{{ $child->id }}()">
                                    <i class="fas fa-eye"></i>
                                    View Full Profile
                                </button>
                                @if($child->assessments->count() > 0)
                                <a href="{{ route('parent.assessments') }}" class="btn-modern btn-secondary">
                                    <i class="fas fa-file-medical-alt"></i>
                                    Assessment History
                                </a>
                                @endif
                                <button type="button" 
                                    class="btn-modern btn-danger" 
                                    onclick="confirmRemoveChild({{ $child->patient_id }}, '{{ $child->first_name }} {{ $child->last_name }}')">
                                    <i class="fas fa-user-minus"></i>
                                    Remove from Account
                                </button>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="child-card-body">
                            <!-- Health Metrics Row -->
                            <div class="metrics-row">
                                <div class="metric-card">
                                    <div class="metric-icon-wrapper weight">
                                        <i class="fas fa-weight"></i>
                                    </div>
                                    <div class="metric-content">
                                        <span class="metric-label">Weight</span>
                                        <span class="metric-value">{{ $child->weight_kg ?? 'N/A' }} <small>{{ $child->weight_kg ? 'kg' : '' }}</small></span>
                                    </div>
                                </div>
                                
                                <div class="metric-card">
                                    <div class="metric-icon-wrapper height">
                                        <i class="fas fa-ruler-vertical"></i>
                                    </div>
                                    <div class="metric-content">
                                        <span class="metric-label">Height</span>
                                        <span class="metric-value">{{ $child->height_cm ?? 'N/A' }} <small>{{ $child->height_cm ? 'cm' : '' }}</small></span>
                                    </div>
                                </div>
                                
                                <div class="metric-card">
                                    <div class="metric-icon-wrapper assessments">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                    <div class="metric-content">
                                        <span class="metric-label">Assessments</span>
                                        <span class="metric-value">{{ $child->assessments->count() }}</span>
                                    </div>
                                </div>
                                
                                <div class="metric-card">
                                    <div class="metric-icon-wrapper nutritionist">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div class="metric-content">
                                        <span class="metric-label">BNS</span>
                                        <span class="metric-value-small">{{ $child->nutritionist ? $child->nutritionist->first_name . ' ' . $child->nutritionist->last_name : 'Not Assigned' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Info Section -->
                            @if($child->nutritionist || $child->is_4ps_beneficiary !== null)
                            <div class="additional-info-section">
                                @if($child->nutritionist)
                                <div class="info-badge">
                                    <i class="fas fa-user-nurse"></i>
                                    <span>Nutritionist: {{ $child->nutritionist->first_name }} {{ $child->nutritionist->last_name }}</span>
                                </div>
                                @endif
                                @if($child->is_4ps_beneficiary)
                                <div class="info-badge highlight">
                                    <i class="fas fa-hands-helping"></i>
                                    <span>4Ps Beneficiary</span>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- SweetAlert2 Script -->
                    <script>
                    function showChildProfile{{ $child->id }}() {
                        Swal.fire({
                            title: ' ',
                            html: `
                                <div class="swal-form-container">
                                    <!-- Custom Header -->
                                    <div class="child-profile-modal-header">
                                        <div class="child-profile-header-content">
                                            <div class="child-profile-header-icon">
                                                <i class="fas fa-child"></i>
                                            </div>
                                            <div class="child-profile-header-info">
                                                <h3 class="child-profile-header-title">
                                                    {{ $child->first_name }} {{ $child->last_name }}
                                                </h3>
                                                <p class="child-profile-header-subtitle">
                                                    <i class="fas fa-id-card"></i> Patient ID: {{ $child->custom_patient_id ?? 'N/A' }} • Complete Health and Nutrition Profile
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Basic Information -->
                                    <div class="form-section">
                                        <h6 class="section-title">
                                            <i class="fas fa-user-circle" style="color: #059669;"></i> Basic Information
                                        </h6>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-user"></i> Full Name</label>
                                                <div class="detail-value-display">{{ $child->first_name }} {{ $child->middle_name ?? '' }} {{ $child->last_name }}</div>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-birthday-cake"></i> Birthdate</label>
                                                <div class="detail-value-display">{{ $child->birthdate ? \Carbon\Carbon::parse($child->birthdate)->format('F d, Y') : 'N/A' }}</div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-calendar-alt"></i> Age</label>
                                                <div class="detail-value-display">{{ $child->age_months ?? 'N/A' }} months</div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-venus-mars"></i> Sex</label>
                                                <div class="detail-value-display">{{ $child->sex ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-phone"></i> Contact Number</label>
                                                <div class="detail-value-display">{{ $child->contact_number ?? 'N/A' }}</div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-calendar-check"></i> Date of Admission</label>
                                                <div class="detail-value-display">{{ $child->date_of_admission ? \Carbon\Carbon::parse($child->date_of_admission)->format('F d, Y') : 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Assignment & Location -->
                                    <div class="form-section">
                                        <h6 class="section-title">
                                            <i class="fas fa-user-tag" style="color: #059669;"></i> Assignment & Location
                                        </h6>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-map-marker-alt"></i> Barangay</label>
                                                <div class="detail-value-display">{{ $child->barangay ? $child->barangay->barangay_name : 'Not assigned' }}</div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-user-md"></i> Nutritionist</label>
                                                <div class="detail-value-display">{{ $child->nutritionist ? $child->nutritionist->first_name . ' ' . $child->nutritionist->last_name : 'Not assigned' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Health Metrics -->
                                    <div class="form-section">
                                        <h6 class="section-title">
                                            <i class="fas fa-heartbeat" style="color: #dc3545;"></i> Health Metrics & Status
                                        </h6>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-weight"></i> Weight</label>
                                                <div class="detail-value-display">{{ $child->weight_kg ?? 'N/A' }} {{ $child->weight_kg ? 'kg' : '' }}</div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-ruler-vertical"></i> Height</label>
                                                <div class="detail-value-display">{{ $child->height_cm ?? 'N/A' }} {{ $child->height_cm ? 'cm' : '' }}</div>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-baby"></i> Breastfeeding Status</label>
                                                <div class="detail-value-display">{{ $child->breastfeeding ?? 'Not specified' }}</div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-disease"></i> Edema Present</label>
                                                <div class="detail-value-display">{{ $child->edema ?? 'Not specified' }}</div>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-allergies"></i> Allergies</label>
                                                <div class="detail-value-display">{{ $child->allergies ?? 'None reported' }}</div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-pray"></i> Religion</label>
                                                <div class="detail-value-display">{{ $child->religion ?? 'Not specified' }}</div>
                                            </div>
                                        </div>
                                        @if($child->other_medical_problems)
                                        <div class="form-row">
                                            <div class="form-group full-width">
                                                <label><i class="fas fa-notes-medical"></i> Other Medical Problems</label>
                                                <div class="detail-value-display">{{ $child->other_medical_problems }}</div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Household Information -->
                                    <div class="form-section">
                                        <h6 class="section-title">
                                            <i class="fas fa-home" style="color: #ffc107;"></i> Household Information
                                        </h6>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label><i class="fas fa-users"></i> Total Adults</label>
                                                <div class="detail-value-display">{{ $child->total_household_adults ?? 0 }}</div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-child"></i> Total Children</label>
                                                <div class="detail-value-display">{{ $child->total_household_children ?? 0 }}</div>
                                            </div>
                                            <div class="form-group">
                                                <label><i class="fas fa-children"></i> Total Twins</label>
                                                <div class="detail-value-display">{{ $child->total_household_twins ?? 0 }}</div>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group full-width">
                                                <label><i class="fas fa-hands-helping"></i> 4Ps Beneficiary</label>
                                                <div class="detail-value-display">
                                                    @if($child->is_4ps_beneficiary)
                                                        <span class="detail-badge badge-success"><i class="fas fa-check"></i> Yes</span>
                                                    @else
                                                        <span class="detail-badge badge-warning"><i class="fas fa-times"></i> No</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `,
                            showConfirmButton: true,
                            confirmButtonText: '<i class="fas fa-times-circle"></i> Close',
                            confirmButtonColor: '#059669',
                            customClass: {
                                container: 'swal-patient-modal',
                                popup: 'swal-patient-popup swal-view-patient-popup',
                                htmlContainer: 'swal-view-patient-content',
                                confirmButton: 'btn btn-secondary'
                            },
                            width: '950px'
                        });
                    }
                    </script>
                    
                @endforeach
            </div>
        @else
            <!-- Modern Empty State -->
            <div class="empty-state-desktop">
                <div class="empty-state-card">
                    <div class="empty-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3 class="empty-title">No Children Registered</h3>
                    <p class="empty-subtitle">Your children's information will appear here once they are registered with the nutrition program. Contact your local health center for assistance with registration.</p>
                    <div class="empty-actions">
                        <button class="btn-modern btn-primary">
                            <i class="fas fa-phone"></i>
                            Contact Health Center
                        </button>
                        <button class="btn-modern btn-secondary">
                            <i class="fas fa-info-circle"></i>
                            Learn More
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Hidden data attributes for routes -->
<div style="display: none;" 
     data-preview-url="{{ route('parent.preview-child') }}"
     data-link-url="{{ route('parent.link-child') }}"
     data-unlink-url="{{ route('parent.unlink-child') }}">
</div>

@push('scripts')
    <script src="{{ asset('js/parent/children.js') }}?v={{ filemtime(public_path('js/parent/children.js')) }}"></script>
    <script>
        function showAddChildModal() {
            Swal.fire({
                title: '<div class="swal-add-child-header"><i class="fas fa-user-plus"></i> Link Child to Account</div>',
                html: `
                    <div class="add-child-form-container">
                        <p class="add-child-description">
                            <i class="fas fa-shield-alt"></i>
                            Enter your child's Patient ID and Birthdate to verify identity
                        </p>
                        <form id="addChildForm" class="add-child-form">
                            <div class="form-group-modern">
                                <label for="patient_code" class="form-label-modern">
                                    <i class="fas fa-id-card"></i> Unique Patient ID
                                </label>
                                <input 
                                    type="text" 
                                    id="patient_code" 
                                    name="patient_code" 
                                    class="form-input-modern" 
                                    placeholder="e.g., 2025-SP-0001-01"
                                    required
                                >
                            </div>
                            <div class="form-group-modern">
                                <label for="birthdate" class="form-label-modern">
                                    <i class="fas fa-calendar-alt"></i> Child's Birthdate
                                </label>
                                <input 
                                    type="date" 
                                    id="birthdate" 
                                    name="birthdate" 
                                    class="form-input-modern" 
                                    required
                                >
                                <small class="form-help-text">
                                    <i class="fas fa-info-circle"></i>
                                    Enter the exact birthdate as registered in the system
                                </small>
                            </div>
                        </form>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-search"></i> Verify Child',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#64748b',
                width: '600px',
                customClass: {
                    popup: 'add-child-popup',
                    confirmButton: 'btn-confirm-modern',
                    cancelButton: 'btn-cancel-modern'
                },
                preConfirm: () => {
                    const patientCode = document.getElementById('patient_code').value;
                    const birthdate = document.getElementById('birthdate').value;
                    if (!patientCode || !birthdate) {
                        Swal.showValidationMessage('Please enter both Patient ID and Birthdate');
                        return false;
                    }
                    return { patient_code: patientCode, birthdate: birthdate };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    previewChildBeforeLinking(result.value.patient_code, result.value.birthdate);
                }
            });
        }

        function previewChildBeforeLinking(patientCode, birthdate) {
            // Show loading
            Swal.fire({
                title: 'Verifying...',
                html: 'Please wait while we verify the information',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send AJAX request to preview child
            fetch('{{ route('parent.preview-child') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    patient_code: patientCode,
                    birthdate: birthdate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMaskedChildConfirmation(data.child);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: data.message,
                        confirmButtonColor: '#059669'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while verifying. Please try again.',
                    confirmButtonColor: '#059669'
                });
            });
        }

        function showMaskedChildConfirmation(child) {
            const ageDisplay = child.age_months ? `${child.age_months} months old` : 'Age not recorded';
            const fullNameMasked = `${child.first_name_masked} ${child.middle_name_masked ? child.middle_name_masked + ' ' : ''}${child.last_name_masked}`;
            
            Swal.fire({
                title: '<div class="swal-add-child-header"><i class="fas fa-user-check"></i> Verify Child Identity</div>',
                html: `
                    <div class="child-confirmation-container">
                        <div class="confirmation-warning">
                            <i class="fas fa-shield-alt"></i>
                            <p><strong>Please confirm this is your child</strong></p>
                        </div>
                        <div class="child-preview-details">
                            <div class="preview-row">
                                <span class="preview-label"><i class="fas fa-id-card"></i> Patient ID:</span>
                                <span class="preview-value"><strong>${child.custom_patient_id}</strong></span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-label"><i class="fas fa-user"></i> Name:</span>
                                <span class="preview-value">${fullNameMasked}</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-label"><i class="fas fa-venus-mars"></i> Sex:</span>
                                <span class="preview-value">${child.sex || 'Not specified'}</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-label"><i class="fas fa-birthday-cake"></i> Age:</span>
                                <span class="preview-value">${ageDisplay}</span>
                            </div>
                            ${child.barangay_masked ? `
                            <div class="preview-row">
                                <span class="preview-label"><i class="fas fa-map-marker-alt"></i> Barangay:</span>
                                <span class="preview-value">${child.barangay_masked}</span>
                            </div>
                            ` : ''}
                        </div>
                        <div class="confirmation-question">
                            <i class="fas fa-question-circle"></i>
                            <p>Is this your child?</p>
                        </div>
                        <div class="privacy-note">
                            <i class="fas fa-lock"></i>
                            <small>Names are masked for privacy protection</small>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Yes, Link This Child',
                cancelButtonText: '<i class="fas fa-times"></i> No, This is Not My Child',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#dc2626',
                width: '700px',
                customClass: {
                    popup: 'add-child-popup',
                    confirmButton: 'btn-confirm-modern',
                    cancelButton: 'btn-cancel-modern'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    linkChildToParent(child.patient_id);
                }
            });
        }

        function linkChildToParent(patientId) {
            // Show loading
            Swal.fire({
                title: 'Processing...',
                html: 'Linking child to your account',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send AJAX request
            fetch('{{ route('parent.link-child') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    patient_id: patientId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: `
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i>
                                <p>${data.message}</p>
                            </div>
                        `,
                        confirmButtonColor: '#059669',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#059669'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while linking the child. Please try again.',
                    confirmButtonColor: '#059669'
                });
            });
        }

        function confirmUnlinkChild(patientId, childName) {
            Swal.fire({
                title: '<div class="swal-warning-header"><i class="fas fa-exclamation-triangle"></i> Unlink Child?</div>',
                html: `
                    <div class="unlink-confirmation">
                        <p>Are you sure you want to unlink <strong>${childName}</strong> from your account?</p>
                        <div class="warning-box">
                            <i class="fas fa-info-circle"></i>
                            <p>You can re-link this child later using their Patient ID.</p>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-unlink"></i> Yes, Unlink',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                customClass: {
                    confirmButton: 'btn-confirm-modern',
                    cancelButton: 'btn-cancel-modern'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    unlinkChild(patientId);
                }
            });
        }

        function unlinkChild(patientId) {
            Swal.fire({
                title: 'Processing...',
                html: 'Unlinking child from your account',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('{{ route('parent.unlink-child') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    patient_id: patientId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Unlinked!',
                        text: data.message,
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#059669'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while unlinking the child.',
                    confirmButtonColor: '#059669'
                });
            });
        }
    </script>
@endpush

@endsection

