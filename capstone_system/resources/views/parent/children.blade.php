@extends('layouts.dashboard')

@section('title', 'My Children')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/parent/children.css') }}?v={{ now()->timestamp }}">
@endpush

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')

<div class="desktop-page-wrapper">
    <!-- Desktop Header Section -->
    <div class="desktop-header-section">
        <div class="header-left">
            <div class="page-icon">
                <i class="fas fa-child"></i>
            </div>
            <div class="page-info">
                <h1 class="page-main-title">My Children's Health Records</h1>
                <p class="page-description">Comprehensive monitoring and tracking of your children's nutrition and health journey</p>
            </div>
        </div>
        <div class="header-right">
            <div class="header-stats-cards">
                <div class="header-stat-item">
                    <div class="header-stat-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <div class="header-stat-content">
                        <div class="header-stat-value">{{ count($children ?? []) }}</div>
                        <div class="header-stat-label">Registered Children</div>
                    </div>
                </div>
                <div class="header-stat-item">
                    <div class="header-stat-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="header-stat-content">
                        <div class="header-stat-value">{{ $children ? $children->filter(function($child) { return $child->nutritionist !== null; })->count() : 0 }}</div>
                        <div class="header-stat-label">Under Care</div>
                    </div>
                </div>
                <div class="header-stat-item">
                    <div class="header-stat-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="header-stat-content">
                        <div class="header-stat-value">{{ $children ? $children->sum(function($child) { return $child->assessments->count(); }) : 0 }}</div>
                        <div class="header-stat-label">Total Assessments</div>
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
                                    onclick="confirmUnlinkChild({{ $child->patient_id }}, '{{ $child->first_name }} {{ $child->last_name }}')">
                                    <i class="fas fa-unlink"></i>
                                    Unlink Child
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
                                        <span class="metric-label">Care Status</span>
                                        <span class="metric-value-small">{{ $child->nutritionist ? 'Under Care' : 'Unassigned' }}</span>
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
                            title: `<div class="swal-modal-header">
                                        <div class="swal-header-icon">
                                            <i class="fas fa-child"></i>
                                        </div>
                                        <div class="swal-header-content">
                                            <h3 class="swal-header-title">
                                                <i class="fas fa-user-circle"></i> {{ $child->first_name }} {{ $child->last_name }}
                                            </h3>
                                            <p class="swal-header-subtitle">Complete Health and Nutrition Profile</p>
                                        </div>
                                    </div>`,
                            html: `
                                <div class="swal-modal-content">
                                    <!-- Personal Information -->
                                    <div class="profile-section">
                                        <h4 class="profile-section-title">
                                            <i class="fas fa-user"></i> Personal Information
                                        </h4>
                                        <div class="profile-grid-4">
                                            <div class="profile-item profile-item-gray">
                                                <div class="profile-label profile-label-gray">Full Name</div>
                                                <div class="profile-value profile-value-dark">{{ $child->first_name }} {{ $child->middle_name ?? '' }} {{ $child->last_name }}</div>
                                            </div>
                                            <div class="profile-item profile-item-green">
                                                <div class="profile-label profile-label-green">Age</div>
                                                <div class="profile-value profile-value-green profile-value-large">{{ $child->age_months ?? $child->age }} {{ $child->age_months ? 'months' : 'years' }}</div>
                                            </div>
                                            <div class="profile-item profile-item-gray">
                                                <div class="profile-label profile-label-gray">Gender</div>
                                                <div class="profile-value profile-value-dark">{{ $child->gender ?? $child->sex ?? 'N/A' }}</div>
                                            </div>
                                            <div class="profile-item profile-item-gray">
                                                <div class="profile-label profile-label-gray">Birthdate</div>
                                                <div class="profile-value profile-value-dark">{{ $child->birthdate ? \Carbon\Carbon::parse($child->birthdate)->format('F j, Y') : 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contact & Location -->
                                    <div class="profile-section">
                                        <h4 class="profile-section-title">
                                            <i class="fas fa-map-marker-alt"></i> Contact & Location
                                        </h4>
                                        <div class="profile-grid-2">
                                            <div class="profile-item profile-item-green">
                                                <div class="profile-label profile-label-green">Barangay</div>
                                                <div class="profile-value profile-value-green profile-value-large">{{ $child->barangay->barangay_name ?? 'N/A' }}</div>
                                            </div>
                                            <div class="profile-item profile-item-gray">
                                                <div class="profile-label profile-label-gray">Contact Number</div>
                                                <div class="profile-value profile-value-dark">{{ $child->contact_number ?? 'Not provided' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Health Metrics -->
                                    <div class="profile-section">
                                        <h4 class="profile-section-title">
                                            <i class="fas fa-heartbeat"></i> Health Metrics
                                        </h4>
                                        <div class="profile-grid-4">
                                            <div class="profile-item profile-item-gray">
                                                <div class="profile-label profile-label-gray">Weight</div>
                                                <div class="profile-value profile-value-xlarge">{{ $child->weight_kg ?? 'N/A' }}{{ $child->weight_kg ? ' kg' : '' }}</div>
                                            </div>
                                            <div class="profile-item profile-item-gray">
                                                <div class="profile-label profile-label-gray">Height</div>
                                                <div class="profile-value profile-value-xlarge">{{ $child->height_cm ?? 'N/A' }}{{ $child->height_cm ? ' cm' : '' }}</div>
                                            </div>
                                            <div class="profile-item profile-item-green">
                                                <div class="profile-label profile-label-green">BMI for Age</div>
                                                <div class="profile-value profile-value-green profile-value-large">{{ $child->bmi_for_age ?? 'Not assessed' }}</div>
                                            </div>
                                            <div class="profile-item profile-item-green">
                                                <div class="profile-label profile-label-green">Weight for Age</div>
                                                <div class="profile-value profile-value-green profile-value-large">{{ $child->weight_for_age ?? 'Not assessed' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Care Information -->
                                    <div class="profile-section">
                                        <h4 class="profile-section-title">
                                            <i class="fas fa-user-nurse"></i> Care Information
                                        </h4>
                                        <div class="profile-grid-4">
                                            <div class="profile-item profile-item-green">
                                                <div class="profile-label profile-label-green">Nutritionist</div>
                                                <div class="profile-value profile-value-green">{{ $child->nutritionist ? ($child->nutritionist->first_name . ' ' . $child->nutritionist->last_name) : 'Not assigned' }}</div>
                                            </div>
                                            <div class="profile-item profile-item-gray">
                                                <div class="profile-label profile-label-gray">Admission Date</div>
                                                <div class="profile-value profile-value-dark">{{ $child->date_of_admission ? \Carbon\Carbon::parse($child->date_of_admission)->format('F j, Y') : 'Not recorded' }}</div>
                                            </div>
                                            <div class="profile-item profile-item-green">
                                                <div class="profile-label profile-label-green">Assessments</div>
                                                <div class="profile-value profile-value-number">{{ $child->assessments->count() }}</div>
                                            </div>
                                            <div class="profile-item {{ isset($child->is_4ps_beneficiary) && $child->is_4ps_beneficiary ? 'profile-item-green' : 'profile-item-gray' }}">
                                                <div class="profile-label {{ isset($child->is_4ps_beneficiary) && $child->is_4ps_beneficiary ? 'profile-label-green' : 'profile-label-gray' }}">4Ps Beneficiary</div>
                                                <div class="profile-value {{ isset($child->is_4ps_beneficiary) && $child->is_4ps_beneficiary ? 'profile-value-green profile-value-large' : 'profile-value-dark profile-value-large' }}">{{ isset($child->is_4ps_beneficiary) ? ($child->is_4ps_beneficiary ? 'Yes' : 'No') : 'Not specified' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `,
                            width: '95%',
                            showCloseButton: true,
                            showConfirmButton: false,
                            padding: '0',
                            background: '#f8fafc',
                            customClass: {
                                container: 'swal-wide',
                                popup: 'swal-popup-custom',
                                closeButton: 'swal-close-custom'
                            },
                            didOpen: () => {
                                const closeBtn = document.querySelector('.swal-close-custom');
                                if (closeBtn) {
                                    closeBtn.onmouseover = () => {
                                        closeBtn.style.background = 'rgba(255,255,255,0.3) !important';
                                        closeBtn.style.transform = 'rotate(90deg)';
                                    };
                                    closeBtn.onmouseout = () => {
                                        closeBtn.style.background = 'rgba(255,255,255,0.2) !important';
                                        closeBtn.style.transform = 'rotate(0deg)';
                                    };
                                }
                            }
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

@push('scripts')
    <script src="{{ asset('js/parent/children.js') }}?v={{ now()->timestamp }}"></script>
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
